<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Product, ProductCategory, Sale, SaleItem, Stock, StockMovement, TenantOfficer, Transaction};
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesTemplateExport;
use App\Imports\SalesImport;

class SaleProcess extends Component
{
    use WithFileUploads;
    public $cart = [];
    public $searchProduct = '';
    public $selectedCategoryId = null;
    public $dukaId; // Restored dukaId
    public $customerName = '';
    public $customerPhone = '';
    public $createNewCustomer = false;
    public $customerId, $isLoan = false, $dueDate, $discountAmount = 0, $backDate;
    public $total = 0;
    public $lastSale = null;
    public $paymentMethod = 'cash';
    public $importFile;
    public $showImportModal = false;

    public function mount($dukaId = null)
    {
        $this->dukaId = $dukaId;
        $this->backDate = now()->format('Y-m-d\TH:i'); // Initializing backdate for sync
        $this->calculateTotal();
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
        $this->total = max(0, $subtotal - (float)$this->discountAmount);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotal();
    }

    public function setCategory($id = null)
    {
        $this->selectedCategoryId = $id;
    }

    public function addToCart($productId)
    {
        $product = Product::with('stocks')->findOrFail($productId);
        $stock = $product->stocks->first();
        $availableQty = $stock ? $stock->quantity : 0;

        // Check if completely out of stock
        if ($availableQty <= 0) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Out of stock!']);
            return;
        }

        // Get current quantity in cart for this product
        $currentCartQty = isset($this->cart[$productId]) ? $this->cart[$productId]['quantity'] : 0;

        // Check if adding one more would exceed available stock
        if ($currentCartQty >= $availableQty) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "Only {$availableQty} units available. You already have {$currentCartQty} in your cart."]);
            return;
        }

        // Add to cart
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product' => $product,
                'quantity' => 1,
                'unit_price' => $product->selling_price,
            ];
        }
        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        // If 0 or less, remove
        if ($quantity <= 0) {
            unset($this->cart[$productId]);
            $this->calculateTotal();
            return;
        }

        // Check stock limit
        $product = Product::with('stocks')->find($productId);
        $stock = $product->stocks->first();
        $availableQty = $stock ? $stock->quantity : 0;

        if ($quantity > $availableQty) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "Only {$availableQty} units available."]);
            // Force set to max available if they tried to go over
            $this->cart[$productId]['quantity'] = $availableQty;
            $this->calculateTotal();
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] = $quantity;
        }
        $this->calculateTotal();
    }

    public function completeSale()
    {
        if (empty($this->cart)) return;

        if ($this->createNewCustomer && !empty($this->customerName)) {
            $this->validate([
                'customerName' => 'required|string|max:255',
                'customerPhone' => 'nullable|string|max:20',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $saleDate = Carbon::parse($this->backDate);

            // Determine Duka and Tenant
            $targetDukaId = $this->dukaId;
            $targetTenantId = $user->tenant_id;

            // Try to find officer assignment if dukaId is not set or to validate
            $assignment = TenantOfficer::where('officer_id', $user->id)->where('status', true)->first();

            if ($assignment) {
                $targetDukaId = $assignment->duka_id;
                $targetTenantId = $assignment->tenant_id;
            } elseif (!$targetDukaId) {
                // If no assignment and no dukaId passed, we cannot proceed
                $this->dispatch('notify', ['type' => 'error', 'message' => 'No Duka Assigned to this user.']);
                return;
            }

            // Handle Customer Logic
            $finalCustomerId = $this->customerId;

            if ($this->createNewCustomer && !empty($this->customerName)) {
                $newCustomer = \App\Models\Customer::create([
                    'tenant_id' => $targetTenantId,
                    'duka_id' => $targetDukaId,
                    'name' => $this->customerName,
                    'phone' => $this->customerPhone,
                    'created_by' => $user->id,
                ]);
                $finalCustomerId = $newCustomer->id;
            }

            $sale = Sale::create([
                'tenant_id' => $targetTenantId,
                'duka_id' => $targetDukaId,
                'customer_id' => $finalCustomerId,
                'total_amount' => $this->total,
                'discount_amount' => $this->discountAmount,
                'is_loan' => $this->isLoan,
                'due_date' => $this->dueDate,
                'created_at' => $saleDate,
            ]);

            foreach ($this->cart as $productId => $item) {
                $qtyToProcess = $item['quantity'];
                $batches = StockMovement::whereHas('stock', fn($q) => $q->where('product_id', $productId)->where('duka_id', $targetDukaId))
                    ->whereIn('type', ['in', 'add'])
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToProcess <= 0) break;
                    $take = min($batch->quantity_remaining, $qtyToProcess);
                    $batch->decrement('quantity_remaining', $take);
                    $qtyToProcess -= $take;
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'created_at' => $saleDate,
                ]);

                // Update stock for the correct Duka
                Stock::where('product_id', $productId)
                    ->where('duka_id', $targetDukaId)
                    ->decrement('quantity', $item['quantity']);
            }


            if (!$this->isLoan) {
                Transaction::create([
                    'duka_id' => $targetDukaId,
                    'user_id' => $user->id,
                    'type' => 'income',
                    'category' => 'sale',
                    'amount' => $this->total,
                    'status' => 'active',
                    'payment_method' => $this->paymentMethod,
                    'reference_id' => $sale->id,
                    'transaction_date' => $saleDate->toDateString(),
                    'created_at' => $saleDate,
                ]);
            }

            $saleDetails = "Sale #{$sale->id} for " . number_format($this->total) . " TSH was successful.";

            DB::commit();
            $this->lastSale = Sale::with(['saleItems.product'])->find($sale->id);
            session()->flash('sale_status', $saleDetails);

            $this->dispatch('sale-success');

            $this->reset(['cart', 'discountAmount', 'selectedCategoryId', 'customerId', 'customerName', 'customerPhone', 'createNewCustomer', 'isLoan']);

            // Dispatch event to open success modal
            $this->dispatch('sale-completed');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Sale Completed Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sale Error: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sale Failed. Check logs.']);
        }
    }

    public function closeSuccessModal()
    {
        $this->lastSale = null;
        session()->forget('sale_status');
    }

    public function downloadTemplate()
    {
        return Excel::download(new SalesTemplateExport, 'sales_import_template.xlsx');
    }

    public function importSales()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $user = auth()->user();

        // Determine Duka (Similar logic to mount/completeSale)
        $targetDukaId = $this->dukaId;
        if (!$targetDukaId) {
            $assignment = TenantOfficer::where('officer_id', $user->id)->where('status', true)->first();
            if ($assignment) {
                $targetDukaId = $assignment->duka_id;
            }
        }

        if (!$targetDukaId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No Duka Assigned. Cannot import.']);
            return;
        }

        try {
            $importer = new SalesImport($targetDukaId, $user->id, $user->tenant_id);
            Excel::import($importer, $this->importFile);

            $errors = $importer->getErrors();
            $successCount = $importer->getSuccessCount();

            if (count($errors) > 0) {
                // Show errors
                $errorString = implode("<br>", array_slice($errors, 0, 5)); // Show first 5 errors
                if (count($errors) > 5) $errorString .= "<br>...and " . (count($errors) - 5) . " more errors.";

                $this->dispatch('notify', ['type' => 'warning', 'message' => "Imported $successCount sales with errors:<br>$errorString"]);
            } else {
                $this->dispatch('notify', ['type' => 'success', 'message' => "Successfully imported $successCount sales."]);
            }

            $this->reset('importFile');
            $this->calculateTotal(); // Refresh anything if needed, though existing cart is separate

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Import Error: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $categories = ProductCategory::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get(); //

        $query = Product::where('tenant_id', $tenantId)
            ->where('name', 'like', "%{$this->searchProduct}%")
            ->with('stocks')
            ->whereHas('stocks', function ($q) {
                $q->where('quantity', '>', 0);
            });

        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        return view('livewire.sale-process', [
            'products' => $query->get(),
            'categories' => $categories
        ]);
    }
}

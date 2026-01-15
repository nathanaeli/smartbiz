<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\SaleItem;
use App\Models\TenantOfficer;
use App\Models\TenantAccount;
use Illuminate\Support\Facades\DB;

class OfficerSalesIndex extends Component
{
    public $assignedDukas = [];
    public $currency = 'TZS';
    public $selectedDukaId;
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modal and sales form properties
    public $showSellModal = false;
    public $selectedCustomerId;
    public $customerSearch = '';
    public $productSearch = '';
    public $cart = [];
    public $total = 0;
    public $discount = 0;
    public $notes = '';
    public $isLoan = false;
    public $dueDate = '';

    // Available data for sales form
    public $customers = [];
    public $products = [];
    public $filteredCustomers = [];
    public $filteredProducts = [];

    public function mount()
    {
        $user = auth()->user();

        // Get officer's assigned dukas
        $this->assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        if ($this->assignedDukas->isEmpty()) {
            session()->flash('error', 'No dukas assigned to you.');
            return;
        }

        $this->currency = TenantAccount::where('tenant_id', $this->assignedDukas->first()->tenant_id)->first()->currency ?? 'TZS';

        // Set default duka (automatically selected if only one duka)
        $this->selectedDukaId = $this->assignedDukas->first()->duka_id;

    }

    public function updatedSearch()
    {
        // Re-render will happen automatically
    }

    public function updatedSelectedDukaId()
    {
        // Re-render will happen automatically
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        // Re-render will happen automatically
    }

    public function generateInvoice($saleId)
    {
        return redirect()->route('officer.sales.invoice', $saleId);
    }

    public function openSellModal()
    {
        $this->showSellModal = true;
        $this->resetForm();
        $this->loadCustomersAndProducts();
    }

    public function closeSellModal()
    {
        $this->showSellModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->productSearch = '';
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
        $this->notes = '';
        $this->isLoan = false;
        $this->dueDate = '';
        $this->filteredCustomers = [];
        $this->filteredProducts = [];
    }

    private function loadCustomersAndProducts()
    {
        $dukaIds = $this->assignedDukas->pluck('duka_id');
        $tenantId = $this->assignedDukas->first()->tenant_id;

        // Load customers without pagination for Livewire compatibility
        $this->customers = Customer::where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->orderBy('name')
            ->get();

        $this->products = Product::where('tenant_id', $tenantId)
            ->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'category'])
            ->orderBy('name')
            ->get();
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) > 1) {
            $dukaIds = $this->assignedDukas->pluck('duka_id');
            $tenantId = $this->assignedDukas->first()->tenant_id;
            $this->filteredCustomers = Customer::where('tenant_id', $tenantId)
                ->whereIn('duka_id', $dukaIds)
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('email', 'like', '%' . $this->customerSearch . '%');
                })
                ->take(10)
                ->get();
        } else {
            $this->filteredCustomers = [];
        }
    }

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) > 1) {
            $dukaIds = $this->assignedDukas->pluck('duka_id');
            $tenantId = $this->assignedDukas->first()->tenant_id;
            $this->filteredProducts = Product::where('tenant_id', $tenantId)
                ->with(['stocks' => function($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds);
                }, 'category'])
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->productSearch . '%')
                      ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
                })
                ->take(10)
                ->get();
        } else {
            $this->filteredProducts = [];
        }
    }

    public function selectCustomer($customerId)
    {
        // Validate that the customer exists and belongs to officer's assigned dukas
        $dukaIds = $this->assignedDukas->pluck('duka_id');
        $tenantId = $this->assignedDukas->first()->tenant_id;

        $customer = Customer::where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->first();

        if (!$customer) {
            session()->flash('error', 'Customer not found or not accessible.');
            return;
        }

        $this->selectedCustomerId = $customerId;
        $this->customerSearch = $customer->name;
        $this->filteredCustomers = [];
    }

    public function addToCart($productId)
    {
        $product = $this->products->find($productId);
        if (!$product) return;

        $stock = $product->stocks->where('duka_id', $this->selectedDukaId)->first();
        if (!$stock || $stock->quantity <= 0) {
            session()->flash('error', 'Product out of stock in selected duka.');
            return;
        }

        // Check if product already in cart
        $existingIndex = collect($this->cart)->search(function($item) use ($productId) {
            return $item['product_id'] == $productId;
        });

        if ($existingIndex !== false) {
            // Increase quantity if already in cart
            if ($this->cart[$existingIndex]['quantity'] >= $stock->quantity) {
                session()->flash('error', 'Cannot add more. Insufficient stock.');
                return;
            }
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['total'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            // Add new item to cart
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
                'available_stock' => $stock->quantity
            ];
        }

        $this->calculateTotal();
        $this->productSearch = '';
        $this->filteredProducts = [];
    }

    public function updateCartQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        } else {
            $availableStock = $this->cart[$index]['available_stock'];
            if ($quantity > $availableStock) {
                session()->flash('error', 'Quantity exceeds available stock.');
                $quantity = $availableStock;
            }
            $this->cart[$index]['quantity'] = $quantity;
            $this->cart[$index]['total'] = $quantity * $this->cart[$index]['price'];
        }

        $this->calculateTotal();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->total = collect($this->cart)->sum('total');
    }

    public function createSale()
    {
        $rules = [
            'selectedDukaId' => 'required|exists:dukas,id',
            'selectedCustomerId' => 'required|exists:customers,id',
            'cart' => 'required|array|min:1',
        ];

        // Add due date validation if it's a loan
        if ($this->isLoan) {
            $rules['dueDate'] = 'required|date|after:today';
        }

        $this->validate($rules);

        // Additional validation
        if (empty($this->cart)) {
            session()->flash('error', 'Please add at least one product to the cart.');
            return;
        }

        // Get tenant ID from officer's assignments
        $tenantId = $this->assignedDukas->first()->tenant_id;

        DB::beginTransaction();
        try {
            // Create sale
            $saleData = [
                'tenant_id' => $tenantId,
                'duka_id' => $this->selectedDukaId,
                'customer_id' => $this->selectedCustomerId,
                'total_amount' => $this->total - $this->discount,
                'discount_amount' => $this->discount,
                'is_loan' => $this->isLoan,
                'created_by' => auth()->id(),
            ];

            // Add due date if it's a loan
            if ($this->isLoan && $this->dueDate) {
                $saleData['due_date'] = $this->dueDate;
            }

            $sale = Sale::create($saleData);

            // Create sale items and update stock
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['total'],
                ]);

                // Update stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('duka_id', $this->selectedDukaId)
                    ->first();

                if ($stock) {
                    $previousQuantity = $stock->quantity;
                    $stock->decrement('quantity', $item['quantity']);
                    \App\Models\StockMovement::create([
                        'stock_id' => $stock->id,
                        'user_id' => auth()->id(),
                        'type' => 'remove',
                        'quantity_change' => -$item['quantity'],
                        'previous_quantity' => $previousQuantity,
                        'new_quantity' => $stock->quantity,
                        'reason' => 'Sale',
                    ]);
                }
            }

            DB::commit();
            $this->closeSellModal();
            session()->flash('success', 'Sale created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Failed to create sale: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $dukaIds = $this->assignedDukas->pluck('duka_id');
        $tenantId = $this->assignedDukas->first()->tenant_id;

        $query = Sale::with(['customer', 'duka', 'saleItems.product'])
            ->where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds);

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('customer', function($customerQuery) {
                    $customerQuery->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('id', 'like', '%' . $this->search . '%')
                ->orWhereHas('duka', function($dukaQuery) {
                    $dukaQuery->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $sales = $query->paginate($this->perPage);

        return view('livewire.officer-sales-index', [
            'sales' => $sales,
        ]);
    }
}

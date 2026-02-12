<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\TenantOfficer;
use App\Models\TenantAccount;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class OfficerSalesIndex extends Component
{
    use WithPagination;

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

    // Search results
    public $filteredCustomers = [];
    public $filteredProducts = [];

    public function mount()
    {
        $user = auth()->user();
        $this->assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        if ($this->assignedDukas->isEmpty()) {
            return;
        }

        $this->currency = TenantAccount::where('tenant_id', $this->tenantId)->first()->currency ?? 'TZS';
        $this->selectedDukaId = $this->assignedDukas->first()->duka_id;
    }

    #[Computed]
    public function tenantId()
    {
        return $this->assignedDukas->first()->tenant_id ?? null;
    }

    #[Computed]
    public function dukaIds()
    {
        return $this->assignedDukas->pluck('duka_id')->toArray();
    }

    // --- Search Logic ---

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) < 2) {
            $this->filteredCustomers = [];
            return;
        }

        $this->filteredCustomers = Customer::where('tenant_id', $this->tenantId)
            ->whereIn('duka_id', $this->dukaIds)
            ->where('name', 'like', '%' . $this->customerSearch . '%')
            ->take(5)->get();
    }

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) < 2) {
            $this->filteredProducts = [];
            return;
        }

        $this->filteredProducts = Product::where('tenant_id', $this->tenantId)
            ->with(['stocks' => fn($q) => $q->where('duka_id', $this->selectedDukaId)])
            ->where('name', 'like', '%' . $this->productSearch . '%')
            ->take(5)->get();
    }

    // --- Cart Management ---

    public function selectCustomer($id, $name)
    {
        $this->selectedCustomerId = $id;
        $this->customerSearch = $name;
        $this->filteredCustomers = [];
    }

    public function addToCart($productId)
    {
        $product = Product::with(['stocks' => fn($q) => $q->where('duka_id', $this->selectedDukaId)])
            ->findOrFail($productId);

        $stock = $product->stocks->first();
        $available = $stock ? $stock->quantity : 0;

        if ($available <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Out of stock!');
            return;
        }

        $existingKey = collect($this->cart)->search(fn($item) => $item['product_id'] == $productId);

        if ($existingKey !== false) {
            if ($this->cart[$existingKey]['quantity'] < $available) {
                $this->cart[$existingKey]['quantity']++;
                $this->cart[$existingKey]['total'] = $this->cart[$existingKey]['quantity'] * $this->cart[$existingKey]['price'];
            }
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
                'stock_id' => $stock->id
            ];
        }

        $this->calculateTotal();
        $this->productSearch = '';
        $this->filteredProducts = [];
    }

    public function calculateTotal()
    {
        $subtotal = collect($this->cart)->sum('total');
        $this->total = max(0, $subtotal - (float)$this->discount);
    }

    // --- The Sale Logic (FIFO) ---

   public function createSale()
    {
        $this->validate([
            'selectedDukaId' => 'required',
            'selectedCustomerId' => 'required',
            'cart' => 'required|array|min:1',
            'isLoan' => 'boolean',
            'dueDate' => 'required_if:isLoan,true|nullable|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'tenant_id' => $this->tenantId,
                'duka_id' => $this->selectedDukaId,
                'customer_id' => $this->selectedCustomerId,
                'total_amount' => $this->total,
                'discount_amount' => $this->discount,
                'is_loan' => $this->isLoan,
                'due_date' => $this->isLoan ? $this->dueDate : null,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->cart as $item) {
                $qtyToConsume = $item['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qtyToConsume,
                    'unit_price' => $item['price'],
                    'total' => $item['total'],
                ]);

                // FIFO Logic: Consume batches where quantity_remaining > 0
                $inMovements = StockMovement::where('stock_id', $item['stock_id'])
                    ->where('type', 'in')
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($inMovements as $inMove) {
                    if ($qtyToConsume <= 0) break;

                    $take = min($qtyToConsume, $inMove->quantity_remaining);

                    // 1. Update the original batch remaining balance
                    $inMove->decrement('quantity_remaining', $take);

                    // 2. Capture the current stock level before this movement
                    $currentStock = Stock::find($item['stock_id']);

                    // 3. Record the "OUT" movement (quantity_change is positive)
                    StockMovement::create([
                        'stock_id'          => $item['stock_id'],
                        'user_id'           => auth()->id(),
                        'type'              => 'out', // Direction defined here
                        'quantity_change'   => $take, // Stored as positive per your requirement
                        'previous_quantity' => $currentStock->quantity,
                        'new_quantity'      => $currentStock->quantity - $take,
                        'unit_cost'         => $inMove->unit_cost,
                        'unit_price'        => $item['price'],
                        'reason'            => 'sale',
                        'notes'             => "Sold from batch: " . ($inMove->batch_number ?? 'N/A'),
                    ]);

                    // 4. Update the actual inventory balance
                    $currentStock->decrement('quantity', $take);

                    $qtyToConsume -= $take;
                }
            }

            DB::commit();
            $this->resetForm();
            $this->showSellModal = false;
            session()->flash('success', 'Sale recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['cart', 'total', 'discount', 'selectedCustomerId', 'customerSearch', 'isLoan', 'dueDate']);
    }

    public function render()
    {
        return view('livewire.officer-sales-index', [
            'sales' => Sale::with(['customer', 'duka'])
                ->where('tenant_id', $this->tenantId)
                ->whereIn('duka_id', $this->dukaIds)
                ->where('id', 'like', "%{$this->search}%")
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage),
        ]);
    }
}

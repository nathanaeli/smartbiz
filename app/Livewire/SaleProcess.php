<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Transaction; // Integrated
use Illuminate\Support\Facades\DB;

class SaleProcess extends Component
{
    use WithPagination;

    public $dukaId;
    public $cart = [];
    public $customers = [];
    public $selectedCustomer;
    public $discountAmount = 0;
    public $discountReason = '';
    public $isLoan = false;
    public $dueDate = '';
    public $total = 0;
    public $profitLoss = 0;
    public $searchProduct = '';
    public $customerInput = '';

    public function mount($dukaId)
    {
        $this->dukaId = $dukaId;
        $user = auth()->user();
        $tenantId = $user->tenant->id ?? $user->id;
        // Load customers for the tenant
        $this->customers = Customer::where('tenant_id', $tenantId)->get();
    }

    public function addToCart($productId, $quantity = 1)
    {
        $product = Product::with(['stocks' => function ($query) {
            $query->where('duka_id', $this->dukaId);
        }])->find($productId);

        if (!$product) return;

        $stock = $product->stocks->first();
        if ($stock && $stock->quantity >= $quantity) {
            if (isset($this->cart[$productId])) {
                $this->cart[$productId]['quantity'] += $quantity;
            } else {
                $this->cart[$productId] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'discount' => 0,
                    'total' => $product->selling_price * $quantity,
                ];
            }
            $this->calculateTotals();
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$productId]);
            $this->calculateTotals();
            return;
        }

        $product = Product::with(['stocks' => function ($query) {
            $query->where('duka_id', $this->dukaId);
        }])->find($productId);

        $stock = $product->stocks->first();
        if ($stock && $stock->quantity >= $quantity) {
            $this->cart[$productId]['quantity'] = $quantity;
            $this->cart[$productId]['total'] = ($this->cart[$productId]['unit_price'] - $this->cart[$productId]['discount']) * $quantity;
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $this->total = collect($this->cart)->sum('total');
        $this->profitLoss = 0;

        // Estimated preview profit based on product table base_price
        foreach ($this->cart as $item) {
            $basePrice = $item['product']->base_price;
            $sellingPrice = $item['unit_price'];
            $discount = $item['discount'];
            $this->profitLoss += ($sellingPrice - $basePrice - $discount) * $item['quantity'];
        }
    }

    public function completeSale()
    {
        if (empty($this->cart)) return;

        DB::transaction(function () {
            $user = auth()->user();
            $tenantId = $user->tenant->id ?? $user->id;
            $finalSaleProfit = 0;

            // 1. Create the Sale record
            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'duka_id' => $this->dukaId,
                'customer_id' => $this->selectedCustomer,
                'total_amount' => $this->total,
                'discount_amount' => $this->discountAmount,
                'profit_loss' => 0, // Placeholder
                'is_loan' => $this->isLoan,
                'due_date' => $this->isLoan && $this->dueDate ? $this->dueDate : null,
                'discount_reason' => $this->discountReason,
            ]);

            foreach ($this->cart as $item) {
                $qtyToProcess = $item['quantity'];
                $totalCostForThisItem = 0;

                // 2. FIFO BATCH CONSUMPTION
                // Find oldest batches for this specific product in this duka
                $batches = StockMovement::whereHas('stock', function($q) use ($item) {
                        $q->where('product_id', $item['product']->id)->where('duka_id', $this->dukaId);
                    })
                    ->where('type', 'in')
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToProcess <= 0) break;

                    $take = min($batch->quantity_remaining, $qtyToProcess);

                    // Accumulate actual cost based on batch unit_cost
                    $totalCostForThisItem += ($take * $batch->unit_cost);

                    // Deduct from batch "memory"
                    $batch->decrement('quantity_remaining', $take);
                    $qtyToProcess -= $take;
                }

                $itemRevenue = $item['total'];
                $itemProfit = $itemRevenue - $totalCostForThisItem;
                $finalSaleProfit += $itemProfit;

                // 3. Create Sale Item record
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount'],
                    'total' => $itemRevenue,
                ]);

                // 4. Update Main Stock & Record "OUT" Stock Movement
                $stock = Stock::where('product_id', $item['product']->id)
                              ->where('duka_id', $this->dukaId)
                              ->first();

                if ($stock) {
                    $prevQty = $stock->quantity;
                    $stock->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'user_id' => $user->id,
                        'type' => 'out',
                        'quantity_change' => $item['quantity'],
                        'previous_quantity' => $prevQty,
                        'new_quantity' => $prevQty - $item['quantity'],
                        'unit_cost' => ($item['quantity'] > 0) ? ($totalCostForThisItem / $item['quantity']) : 0,
                        'unit_price' => $item['unit_price'] - $item['discount'],
                        'total_value' => $itemRevenue,
                        'reason' => 'sale',
                    ]);
                }
            }

            // 5. Update Sale with TRUE Profit (Realized)
            $sale->update(['profit_loss' => $finalSaleProfit]);
            if (!$this->isLoan) {
                Transaction::create([
                    'duka_id' => $this->dukaId,
                    'user_id' => $user->id,
                    'type' => 'income',
                    'category' => 'sale',
                    'amount' => $this->total,
                    'status' => 'active',
                    'payment_method' => 'cash',
                    'reference_id' => $sale->id,
                    'description' => 'Cash Sale #' . $sale->id . ' to ' . ($sale->customer->name ?? 'Walk-in'),
                    'transaction_date' => now(),
                ]);
            }

            $this->reset(['cart', 'total', 'profitLoss', 'isLoan', 'dueDate', 'selectedCustomer', 'customerInput']);
            session()->flash('message', 'Sale completed successfully!');
            return redirect()->route('sale.now');
        });
    }

    // --- Helper Methods ---

    public function updatedCustomerInput()
    {
        $this->selectedCustomer = $this->customers->first(function ($customer) {
            return $customer->name . ' (' . $customer->phone . ')' === $this->customerInput;
        })?->id ?? null;
    }

    public function getFilteredProductsProperty()
    {
        $query = Product::whereHas('stocks', function ($query) {
            $query->where('duka_id', $this->dukaId)->where('quantity', '>', 0);
        })->with(['stocks' => function ($query) {
            $query->where('duka_id', $this->dukaId);
        }]);

        if (!empty($this->searchProduct)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchProduct . '%');
            });
        }
        return $query->paginate(12);
    }

    public function render()
    {
        return view('livewire.sale-process', [
            'products' => $this->filteredProducts
        ]);
    }
}

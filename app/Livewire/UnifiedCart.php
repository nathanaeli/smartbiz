<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Product, Service, Customer, Duka, Sale, SaleItem, ServiceOrder, Transaction, Stock, StockMovement};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;

class UnifiedCart extends Component
{
    public $duka_id;
    public $customer_id;
    public $search = '';
    public $cart = [];
    public $payment_method = 'cash';
    public $notes = '';

    public $dukas = [];
    public $customers = [];
    public $searchResults = [];

    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Robust tenant identification
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if ($tenantId) {
            $this->dukas = Duka::where('tenant_id', $tenantId)->get()->all();

            if (count($this->dukas) === 1) {
                $this->duka_id = $this->dukas[0]->id;
                $this->updatedDukaId($this->duka_id);
            }
        }
    }

    public function updatedDukaId($value)
    {
        $this->customers = Customer::where('duka_id', $value)->get()->all();
        $this->cart = []; // Reset cart when duka changes
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        if (!$this->duka_id) {
            $this->searchResults = [];
            return;
        }

        $products = Product::where('duka_id', $this->duka_id)
            ->where('name', 'like', "%{$this->search}%")
            ->whereHas('stocks', fn($q) => $q->where('quantity', '>', 0))
            ->take(5)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'type' => 'product',
                    'name' => $p->name,
                    'price' => $p->selling_price,
                    'stock' => $p->stocks->sum('quantity')
                ];
            });

        $services = Service::where('duka_id', $this->duka_id)
            ->where('name', 'like', "%{$this->search}%")
            ->take(5)->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'type' => 'service',
                    'name' => $s->name,
                    'price' => $s->price,
                    'stock' => null
                ];
            });

        $this->searchResults = $products->concat($services)->toArray();
    }

    public function addToCart($id, $type)
    {
        if ($type === 'product') {
            $item = Product::find($id);
            $key = "product_{$id}";
            $price = $item->selling_price;
            $stock = $item->stocks->sum('quantity');
        } else {
            $item = Service::find($id);
            $key = "service_{$id}";
            $price = $item->price;
            $stock = 999999;
        }

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['quantity'] < $stock) {
                $this->cart[$key]['quantity']++;
            }
        } else {
            $this->cart[$key] = [
                'id' => $id,
                'type' => $type,
                'name' => $item->name,
                'price' => $price,
                'quantity' => 1
            ];
        }

        $this->search = '';
        $this->searchResults = [];
    }

    public function updateQuantity($key, $qty)
    {
        if ($qty <= 0) {
            unset($this->cart[$key]);
        } else {
            $this->cart[$key]['quantity'] = $qty;
        }
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
    }

    public function checkout()
    {
        if (empty($this->cart)) return;

        $this->validate([
            'duka_id' => 'required',
            'customer_id' => 'required',
            'payment_method' => 'required',
        ]);

        DB::beginTransaction();
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $tenant = $user->tenant()->first();
            $tenantId = $user->tenant_id ?? ($tenant ? $tenant->id : null);
            $now = now();


            $totalAmount = 0;
            $productItems = array_filter($this->cart, fn($i) => $i['type'] === 'product');
            $serviceItems = array_filter($this->cart, fn($i) => $i['type'] === 'service');

            // Calculate total amount from both products and services
            if (!empty($productItems)) {
                $totalAmount += collect($productItems)->sum(fn($i) => $i['price'] * $i['quantity']);
            }
            if (!empty($serviceItems)) {
                $totalAmount += collect($serviceItems)->sum(fn($i) => $i['price'] * $i['quantity']);
            }

            // Create ONE Sale record for everything
            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'duka_id' => $this->duka_id,
                'customer_id' => $this->customer_id,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'is_loan' => false,
                'created_at' => $now,
                'created_by' => $user->id,
            ]);

            // 1. Handle Products (Sale Items)
            if (!empty($productItems)) {
                foreach ($productItems as $item) {
                    $productId = $item['id'];
                    $qtyToProcess = $item['quantity'];

                    // FIFO Stock Reduction Logic
                    $batches = StockMovement::whereHas('stock', fn($q) => $q->where('product_id', $productId)->where('duka_id', $this->duka_id))
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
                        'unit_price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                    ]);

                    Stock::where('product_id', $productId)
                        ->where('duka_id', $this->duka_id)
                        ->decrement('quantity', $item['quantity']);
                }
            }

            // 2. Handle Services (Service Orders)
            if (!empty($serviceItems)) {
                foreach ($serviceItems as $item) {
                    $service = Service::find($item['id']);
                    ServiceOrder::create([
                        'tenant_id' => $tenantId,
                        'duka_id' => $this->duka_id,
                        'customer_id' => $this->customer_id,
                        'service_id' => $item['id'],
                        'sale_id' => $sale->id, // Link to the same Sale
                        'service_type' => $service->category->name ?? 'General',
                        'amount_paid' => $item['price'] * $item['quantity'],
                        'status' => 'completed',
                        'scheduled_at' => $now,
                        'completed_at' => $now,
                        'notes' => $this->notes,
                    ]);
                }
            }

            // Create ONE Transaction for the entire Sale
            Transaction::create([
                'duka_id' => $this->duka_id,
                'user_id' => $user->id,
                'type' => 'income', // Sales are income
                'category' => 'sale', // Unified category
                'amount' => $totalAmount,
                'status' => 'active',
                'payment_method' => $this->payment_method,
                'reference_id' => $sale->id,
                'transaction_date' => $now->toDateString(),
                'description' => 'Sale #' . $sale->id . ' (Includes Products & Services)',
            ]);

            DB::commit();
            $this->reset(['cart', 'search', 'searchResults', 'customer_id', 'notes']);
            $this->dispatch('sale-completed'); // Trigger UI update (modal hide)
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Transaction Completed!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Unified Checkout Error: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Checkout Failed: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $totalAmount = collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        return view('livewire.unified-cart', [
            'totalAmount' => $totalAmount
        ]);
    }
}

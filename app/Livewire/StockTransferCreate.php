<?php

namespace App\Livewire;

use App\Models\Duka;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockMovement;
use App\Models\TenantOfficer;
use App\Models\Message;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StockTransferCreate extends Component
{
    public $from_duka_id;
    public $to_duka_id;
    public $product_id;
    public $quantity;
    public $reason;
    public $notes;

    public $fromDukaProducts = [];
    public $toDukaProducts = [];
    public $selectedFromProductStock = null;
    public $selectedToProductStock = null;

    // Search and creation features
    public $productSearch = '';
    public $showCreateProductModal = false;
    public $newProductName = '';
    public $newProductSku = '';
    public $newProductDescription = '';
    public $newProductCategoryId = '';
    public $newProductSellingPrice = '';
    public $newProductCostPrice = '';
    public $searchResults = [];

    protected $rules = [
        'from_duka_id' => 'required|integer|exists:dukas,id',
        'to_duka_id' => 'required|integer|exists:dukas,id|different:from_duka_id',
        'product_id' => 'required|integer|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant) {
            abort(403, 'Unauthorized');
        }
    }

    public function updatedFromDukaId()
    {
        $this->loadFromDukaProducts();
        $this->product_id = null;
        $this->selectedFromProductStock = null;
    }

    public function updatedToDukaId()
    {
        $this->loadToDukaProducts();
        $this->product_id = null;
        $this->selectedToProductStock = null;
    }

    public function updatedProductId()
    {
        $this->loadProductStocks();
    }

    public function updatedProductSearch()
    {
        $this->searchProducts();
    }

    public function searchProducts()
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $tenant = Auth::user()->tenant;
        $searchTerm = strtolower($this->productSearch);

        // Search for products with fuzzy matching
        $this->searchResults = Product::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTerm}%"]);
            })
            ->active()
            ->limit(10)
            ->get()
            ->map(function ($product) use ($searchTerm) {
                // Calculate similarity score
                $nameSimilarity = $this->calculateSimilarity(strtolower($product->name), $searchTerm);
                $skuSimilarity = $this->calculateSimilarity(strtolower($product->sku), $searchTerm);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'similarity' => max($nameSimilarity, $skuSimilarity),
                    'category' => $product->category?->name ?? 'No Category',
                    'selling_price' => $product->selling_price,
                ];
            })
            ->sortByDesc('similarity')
            ->values()
            ->all();
    }

    private function calculateSimilarity($str1, $str2)
    {
        // Simple Levenshtein distance based similarity
        $lev = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));

        if ($maxLen == 0) return 1.0;

        return 1 - ($lev / $maxLen);
    }

    public function selectProduct($productId)
    {
        $this->product_id = $productId;
        $this->productSearch = '';
        $this->searchResults = [];
        $this->loadProductStocks();
    }

    public function openCreateProductModal()
    {
        $this->showCreateProductModal = true;
        $this->newProductName = $this->productSearch;
        $this->newProductSku = '';
        $this->newProductDescription = '';
        $this->newProductCategoryId = '';
        $this->newProductSellingPrice = '';
        $this->newProductCostPrice = '';
    }

    public function closeCreateProductModal()
    {
        $this->showCreateProductModal = false;
        $this->reset(['newProductName', 'newProductSku', 'newProductDescription', 'newProductCategoryId', 'newProductSellingPrice', 'newProductCostPrice']);
    }

    public function createProduct()
    {
        $this->validate([
            'newProductName' => 'required|string|max:255',
            'newProductSku' => 'required|string|max:100|unique:products,sku',
            'newProductCategoryId' => 'nullable|exists:product_categories,id',
            'newProductSellingPrice' => 'required|numeric|min:0',
            'newProductCostPrice' => 'nullable|numeric|min:0',
        ]);

        $tenant = Auth::user()->tenant;

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $this->newProductCategoryId ?: null,
            'name' => $this->newProductName,
            'sku' => $this->newProductSku,
            'description' => $this->newProductDescription,
            'selling_price' => $this->newProductSellingPrice,
            'base_price' => $this->newProductCostPrice,
            'unit' => 'pcs',
            'created_by' => Auth::id(),
        ]);

        $this->product_id = $product->id;
        $this->productSearch = $product->name;
        $this->searchResults = [];
        $this->closeCreateProductModal();
        $this->loadProductStocks();

        session()->flash('success', 'Product created successfully!');
    }

    private function loadFromDukaProducts()
    {
        if ($this->from_duka_id) {
            $this->fromDukaProducts = Stock::where('duka_id', $this->from_duka_id)
                ->where('quantity', '>', 0)
                ->with('product')
                ->get()
                ->filter(function ($stock) {
                    return $stock->product !== null;
                })
                ->map(function ($stock) {
                    return [
                        'id' => $stock->product_id,
                        'name' => $stock->product->name,
                        'sku' => $stock->product->sku,
                        'quantity' => $stock->quantity,
                        'formatted_quantity' => number_format($stock->quantity),
                    ];
                });
        } else {
            $this->fromDukaProducts = [];
        }
    }

    private function loadToDukaProducts()
    {
        if ($this->to_duka_id) {
            $this->toDukaProducts = Product::where('tenant_id', Auth::user()->tenant->id)
                ->active()
                ->get()
                ->filter(function ($product) {
                    return $product !== null && $product->name !== null;
                })
                ->map(function ($product) {
                    $stock = Stock::firstOrCreate([
                        'duka_id' => $this->to_duka_id,
                        'product_id' => $product->id,
                    ]);

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => $stock->quantity,
                        'formatted_quantity' => number_format($stock->quantity),
                    ];
                });
        } else {
            $this->toDukaProducts = [];
        }
    }

    private function loadProductStocks()
    {
        if ($this->product_id && $this->from_duka_id) {
            $stock = Stock::where('duka_id', $this->from_duka_id)
                ->where('product_id', $this->product_id)
                ->first();
            $this->selectedFromProductStock = $stock ? $stock->quantity : 0;
        }

        if ($this->product_id && $this->to_duka_id) {
            $stock = Stock::firstOrCreate([
                'duka_id' => $this->to_duka_id,
                'product_id' => $this->product_id,
            ]);
            $this->selectedToProductStock = $stock->quantity;
        }
    }

    public function transferStock()
    {
        $this->validate();

        $tenant = Auth::user()->tenant;
        if (!$tenant) {
            $this->addError('general', 'Unauthorized');
            return;
        }

        // Check if from_duka belongs to tenant
        $fromDuka = Duka::where('id', $this->from_duka_id)
            ->where('tenant_id', $tenant->id)
            ->first();
        if (!$fromDuka) {
            $this->addError('from_duka_id', 'Invalid source duka');
            return;
        }

        // Check if to_duka belongs to tenant
        $toDuka = Duka::where('id', $this->to_duka_id)
            ->where('tenant_id', $tenant->id)
            ->first();
        if (!$toDuka) {
            $this->addError('to_duka_id', 'Invalid destination duka');
            return;
        }

        // Check if product belongs to tenant
        $product = Product::where('id', $this->product_id)
            ->where('tenant_id', $tenant->id)
            ->first();
        if (!$product) {
            $this->addError('product_id', 'Invalid product');
            return;
        }

        // Check stock availability
        $fromStock = Stock::firstOrCreate([
            'duka_id' => $fromDuka->id,
            'product_id' => $product->id,
        ]);

        if ($fromStock->quantity < $this->quantity) {
            $this->addError('quantity', 'Insufficient stock in the source duka.');
            return;
        }

        DB::transaction(function () use ($tenant, $fromDuka, $toDuka, $product, $fromStock) {
            // Update from_duka stock (reduce)
            $fromStock->decrement('quantity', $this->quantity);
            $fromStock->last_updated_by = Auth::id();
            $fromStock->save();

            // Update to_duka stock (increase)
            $toStock = Stock::firstOrCreate([
                'duka_id' => $toDuka->id,
                'product_id' => $product->id,
            ]);
            $toStock->increment('quantity', $this->quantity);
            $toStock->last_updated_by = Auth::id();
            $toStock->save();

            // Create StockTransferItem (header)
            $transferItem = StockTransferItem::create([
                'tenant_id' => $tenant->id,
                'from_duka_id' => $fromDuka->id,
                'to_duka_id' => $toDuka->id,
                'transferred_by' => Auth::id(),
                'status' => 'completed',
                'reason' => $this->reason,
                'notes' => $this->notes,
            ]);

            // Create StockTransfer (line item)
            StockTransfer::create([
                'stock_transfer_id' => $transferItem->id,
                'product_id' => $product->id,
                'quantity' => $this->quantity,
                'notes' => $this->notes,
            ]);

            // Create StockMovement OUT for from_duka
            StockMovement::create([
                'stock_id' => $fromStock->id,
                'user_id' => Auth::id(),
                'type' => 'remove',
                'quantity_change' => -$this->quantity,
                'previous_quantity' => $fromStock->quantity + $this->quantity,
                'new_quantity' => $fromStock->quantity,
                'reason' => 'Stock Transfer',
                'notes' => "Transferred to {$toDuka->name}",
            ]);

            // Create StockMovement IN for to_duka
            StockMovement::create([
                'stock_id' => $toStock->id,
                'user_id' => Auth::id(),
                'type' => 'add',
                'quantity_change' => $this->quantity,
                'previous_quantity' => $toStock->quantity - $this->quantity,
                'new_quantity' => $toStock->quantity,
                'reason' => 'Stock Transfer',
                'notes' => "Transferred from {$fromDuka->name}",
            ]);

            // Send notifications to destination duka managers
            $this->sendNotifications($toDuka, $product, $this->quantity, $fromDuka);
        });

        session()->flash('success', 'Stock transferred successfully.');

        // Reset form
        $this->reset(['from_duka_id', 'to_duka_id', 'product_id', 'quantity', 'reason', 'notes']);
        $this->fromDukaProducts = [];
        $this->toDukaProducts = [];
        $this->selectedFromProductStock = null;
        $this->selectedToProductStock = null;
    }

    private function sendNotifications($toDuka, $product, $quantity, $fromDuka)
    {
        // Get users with permission to manage the destination duka
        $managers = TenantOfficer::where('duka_id', $toDuka->id)
            ->with('officer')
            ->get()
            ->pluck('officer')
            ->unique('id');

        foreach ($managers as $manager) {
            Message::create([
                'sender_id' => Auth::id(),
                'tenant_id' => Auth::user()->tenant->id,
                'subject' => 'Stock Transfer Notification',
                'body' => "Stock has been transferred to your duka.\n\n" .
                         "Product: {$product->name} ({$product->sku})\n" .
                         "Quantity: " . number_format($quantity) . "\n" .
                         "From: {$fromDuka->name}\n" .
                         "Transferred by: " . Auth::user()->name,
                'is_broadcast' => false,
            ]);
        }
    }

    public function render()
    {
        $tenant = Auth::user()->tenant;
        $dukas = $tenant->dukas;
        $categories = ProductCategory::where('tenant_id', $tenant->id)->get();

        return view('livewire.stock-transfer-create', compact('dukas', 'categories'));
    }
}

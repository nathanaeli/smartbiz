<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\TenantOfficer;
use App\Models\Duka;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OfficerProductsManage extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $filterCategory = '';
    public $filterStockStatus = '';
    public $filterDuka = '';
    public $perPage = 10;

    // Removed product form properties - now using separate component


    // Bulk Operations
    public $selectedProducts = [];
    public $selectAll = false;


    // Quick Stock Amounts (customizable)
    public $quickAddAmounts = [1, 5, 10];
    public $quickReduceAmounts = [1, 5, 10];

    protected $listeners = [
        'refreshProducts' => '$refresh',
        'confirmDelete' => 'confirmDelete',
        'confirmBulkDelete' => 'confirmBulkDelete',
        'productCreated' => '$refresh',
        'productUpdated' => '$refresh',
        'stockUpdated' => '$refresh'
    ];

    // Removed product form rules - now handled by separate component

    protected $stockRules = [
        'stockQuantity' => 'required|integer|min:1',
        'stockReason' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->checkPermissions();
    }

    private function checkPermissions()
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to manage products.');
        }
    }

    private function hasPermission($permission)
    {
        return auth()->user()->hasPermission($permission);
    }

    public function getOfficerData()
    {
        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $dukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        return [
            'tenant_id' => $assignment->tenant_id,
            'duka_ids' => $dukaIds,
            'assignment' => $assignment
        ];
    }

    public function getProductsQuery()
    {
        $officerData = $this->getOfficerData();

        $query = Product::where('tenant_id', $officerData['tenant_id'])
            ->with(['stocks' => function($q) use ($officerData) {
                $q->whereIn('duka_id', $officerData['duka_ids']);
            }, 'category', 'duka', 'items']);

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Category filter
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        // Duka filter
        if ($this->filterDuka) {
            $query->whereHas('stocks', function($q) {
                $q->where('duka_id', $this->filterDuka);
            });
        }

        // Stock status filter
        if ($this->filterStockStatus) {
            switch ($this->filterStockStatus) {
                case 'out_of_stock':
                    $query->whereHas('stocks', function($q) use ($officerData) {
                        $q->whereIn('duka_id', $officerData['duka_ids'])
                          ->where('quantity', 0);
                    });
                    break;
                case 'low_stock':
                    $query->whereHas('stocks', function($q) use ($officerData) {
                        $q->whereIn('duka_id', $officerData['duka_ids'])
                          ->where('quantity', '>', 0)
                          ->where('quantity', '<=', 10);
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('stocks', function($q) use ($officerData) {
                        $q->whereIn('duka_id', $officerData['duka_ids'])
                          ->where('quantity', '>', 10);
                    });
                    break;
            }
        }

        return $query;
    }

    public function getProductsProperty()
    {
        return $this->getProductsQuery()
            ->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        $officerData = $this->getOfficerData();
        return ProductCategory::where('tenant_id', $officerData['tenant_id'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getDukasProperty()
    {
        $officerData = $this->getOfficerData();
        return Duka::whereIn('id', $officerData['duka_ids'])
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedProducts = $this->getProductsQuery()
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts()
    {
        $this->selectAll = count($this->selectedProducts) === $this->getProductsQuery()->count();
    }

    public function addProduct()
    {
        return redirect()->route('officer.product.create');
    }

    public function editProduct($productId)
    {
        return redirect()->route('officer.product.edit', $productId);
    }


    public function deleteProduct($productId)
    {
        if (!$this->hasPermission('delete_product')) {
            session()->flash('error', 'You do not have permission to delete products.');
            return;
        }

        $officerData = $this->getOfficerData();
        $product = Product::where('id', $productId)
            ->where('tenant_id', $officerData['tenant_id'])
            ->firstOrFail();

        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        $product->delete();

        session()->flash('success', 'Product "' . $product->name . '" deleted successfully!');
        $this->dispatch('productDeleted');
    }


    public function manageStock($productId, $dukaId, $type, $quantity, $reason = '')
    {
        if (!$this->hasPermission($type === 'add' ? 'adding_stock' : 'reduce_stock')) {
            session()->flash('error', 'You do not have permission to ' . ($type === 'add' ? 'add' : 'reduce') . ' stock.');
            return;
        }

        $officerData = $this->getOfficerData();

        // Verify product and duka belong to officer
        $product = Product::where('id', $productId)
            ->where('tenant_id', $officerData['tenant_id'])
            ->firstOrFail();

        if (!in_array($dukaId, $officerData['duka_ids']->toArray())) {
            throw new \Exception('Invalid duka selected.');
        }

        // Get or create stock record
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $productId,
                'duka_id' => $dukaId,
            ],
            [
                'quantity' => 0,
                'last_updated_by' => auth()->id()
            ]
        );

        $previousQuantity = $stock->quantity;

        if ($type === 'add') {
            $stock->increment('quantity', $quantity);
        } else {
            if ($stock->quantity < $quantity) {
                session()->flash('error', 'Insufficient stock available.');
                return;
            }
            $stock->decrement('quantity', $quantity);
        }

        $stock->update(['last_updated_by' => auth()->id()]);

        // Record stock movement
        StockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity_change' => $type === 'add' ? $quantity : -$quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $stock->quantity,
            'reason' => $reason ?: ($type === 'add' ? 'Stock added' : 'Stock reduced'),
        ]);

        session()->flash('success', 'Stock ' . ($type === 'add' ? 'added' : 'reduced') . ' successfully!');
        $this->dispatch('stockUpdated');
    }


    public function bulkDelete()
    {
        if (!$this->hasPermission('delete_product')) {
            session()->flash('error', 'You do not have permission to delete products.');
            return;
        }

        if (empty($this->selectedProducts)) {
            session()->flash('error', 'No products selected.');
            return;
        }

        $officerData = $this->getOfficerData();
        $products = Product::whereIn('id', $this->selectedProducts)
            ->where('tenant_id', $officerData['tenant_id'])
            ->get();

        foreach ($products as $product) {
            // Delete image if exists
            if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            $product->delete();
        }

        $this->selectedProducts = [];
        $this->selectAll = false;
        session()->flash('success', count($products) . ' products deleted successfully!');
    }


    public function navigateToStockComponent($productId)
    {
        return redirect()->route('officer.product.stock', [
            'product' => $productId
        ]);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterCategory = '';
        $this->filterStockStatus = '';
        $this->filterDuka = '';
        $this->resetPage();
    }

    private function generateProductSKU($productName, $stockLevel)
    {
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productName));
        $namePrefix = substr($cleanName, 0, 4);
        $stockPart = str_pad($stockLevel, 3, '0', STR_PAD_LEFT);
        $randomPart = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        $sku = $namePrefix . '-' . $stockPart . '-' . $randomPart;

        $counter = 1;
        $originalSku = $sku;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    public function render()
    {
        return view('livewire.officer-products-manage', [
            'products' => $this->products,
            'categories' => $this->categories,
            'dukas' => $this->dukas,
        ]);
    }
}

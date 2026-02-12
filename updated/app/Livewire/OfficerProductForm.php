<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Duka;
use App\Models\TenantOfficer;
use App\Models\Stock;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficerProductForm extends Component
{
    use WithFileUploads;

    public $product;
    public $isEditing = true;

    // Form fields
    public $name;
    public $description;
    public $unit;
    public $buying_price;
    public $selling_price;
    public $category_name;
    public $category_id;
    public $barcode;
    public $image;
    public $existingImage;
    public $is_active = true;

    // Stock management - array of stocks for different dukas
    public $stocks = [];

    // Dropdown options
    public $availableUnits = [
        'pcs' => 'Pieces (pcs)',
        'kg' => 'Kilograms (kg)',
        'g' => 'Grams (g)',
        'ltr' => 'Liters (ltr)',
        'ml' => 'Milliliters (ml)',
        'box' => 'Boxes',
        'bag' => 'Bags',
        'pack' => 'Packs',
        'set' => 'Sets',
        'pair' => 'Pairs',
        'dozen' => 'Dozens',
        'carton' => 'Cartons'
    ];

    public $availableCategories = [];
    public $availableDukas = [];
    public $currency = 'TZS';

    // UI state
    public $showAdvancedStock = false;
    public $isLoading = false;

    // Validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'unit' => 'required|in:pcs,kg,g,ltr,ml,box,bag,pack,set,pair,dozen,carton',
        'buying_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'category_name' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:product_categories,id',
        'barcode' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'is_active' => 'required|boolean',
        'stocks.*.quantity' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'selling_price.min' => 'Selling price must be greater than 0',
        'buying_price.min' => 'Buying price must be greater than or equal to 0',
        'image.image' => 'File must be an image',
        'image.mimes' => 'Image must be JPEG, PNG, JPG, or GIF',
        'image.max' => 'Image size must be less than 2MB',
        'stocks.*.quantity.min' => 'Stock quantity cannot be negative',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->loadProductData();
        $this->loadDropdownData();
        $this->loadStockData();
    }

    public function loadProductData()
    {
        if ($this->product) {
            $this->name = $this->product->name;
            $this->description = $this->product->description;
            $this->unit = $this->product->unit;
            $this->buying_price = $this->product->base_price;
            $this->selling_price = $this->product->selling_price;
            $this->category_id = $this->product->category_id;
            $this->barcode = $this->product->barcode;
            $this->existingImage = $this->product->image;
            $this->is_active = $this->product->is_active;
        }
    }

    public function loadStockData()
    {
        $user = Auth::user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if ($assignment && $this->product) {
            $officerDukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
                ->where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id')
                ->toArray();

            // Load current stock for all assigned dukas
            $currentStocks = Stock::where('product_id', $this->product->id)
                ->whereIn('duka_id', $officerDukaIds)
                ->with('duka')
                ->get();

            // Initialize stocks array with all assigned dukas
            $this->stocks = [];
            foreach ($this->availableDukas as $duka) {
                $existingStock = $currentStocks->firstWhere('duka_id', $duka['id']);
                $this->stocks[] = [
                    'duka_id' => $duka['id'],
                    'duka_name' => $duka['name'],
                    'quantity' => $existingStock ? $existingStock->quantity : 0,
                    'current_quantity' => $existingStock ? $existingStock->quantity : 0,
                ];
            }
        }
    }

    public function loadDropdownData()
    {
        $user = Auth::user();

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if ($assignment) {
            $tenantId = $assignment->tenant_id;

            // Load categories
            $this->availableCategories = ProductCategory::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                    ];
                });

            // Load officer's assigned dukas
            $officerDukaIds = TenantOfficer::where('tenant_id', $tenantId)
                ->where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id')
                ->toArray();

            $this->availableDukas = Duka::whereIn('id', $officerDukaIds)
                ->orderBy('name')
                ->get()
                ->map(function ($duka) {
                    return [
                        'id' => $duka->id,
                        'name' => $duka->name,
                        'location' => $duka->location,
                    ];
                });

            // Get currency for the tenant
            $this->currency = \App\Models\TenantAccount::where('tenant_id', $tenantId)
                ->first()->currency ?? 'TZS';
        }
    }

    public function updatedCategoryName()
    {
        // Auto-suggest existing categories
        if (!empty($this->category_name)) {
            $matchingCategory = collect($this->availableCategories)
                ->first(function ($category) {
                    return stripos($category['name'], $this->category_name) !== false;
                });

            if ($matchingCategory) {
                $this->category_id = $matchingCategory['id'];
            } else {
                $this->category_id = null;
            }
        }
    }

    public function updatedBuyingPrice()
    {
        // Auto-suggest selling price based on buying price (markup)
        if ($this->buying_price > 0 && empty($this->selling_price)) {
            // Suggest 50% markup
            $this->selling_price = $this->buying_price * 1.5;
        }
    }

    public function calculateProfitMargin()
    {
        if ($this->buying_price > 0 && $this->selling_price > 0) {
            $margin = (($this->selling_price - $this->buying_price) / $this->buying_price) * 100;
            return round($margin, 2);
        }
        return 0;
    }

    public function save()
    {
        $this->isLoading = true;

        // Debug: Add to browser console
        $this->dispatch('console-log', 'Product save method called');

        try {
            // Log the start of save process
            Log::info('Product save started', [
                'user_id' => Auth::id(),
                'product_id' => $this->product->id ?? null,
                'form_data' => [
                    'name' => $this->name,
                    'unit' => $this->unit,
                    'buying_price' => $this->buying_price,
                    'selling_price' => $this->selling_price,
                ]
            ]);

            $this->validate();

            // Additional validation
            if ($this->selling_price <= $this->buying_price) {
                $this->addError('selling_price', 'Selling price must be greater than buying price');
                Log::warning('Validation failed: selling price <= buying price', [
                    'buying_price' => $this->buying_price,
                    'selling_price' => $this->selling_price
                ]);
                return;
            }

            $user = Auth::user();

            // Check permissions
            if (!$user->hasPermission('edit_product')) {
                $this->addError('general', 'You do not have permission to edit products');
                Log::warning('Permission denied: edit_product', ['user_id' => $user->id]);
                return;
            }

            // Get tenant ID
            $assignment = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->first();

            if (!$assignment) {
                $this->addError('general', 'No active assignments found');
                Log::warning('No active assignment found', ['user_id' => $user->id]);
                return;
            }

            $tenantId = $assignment->tenant_id;

            // Verify product ownership
            $product = Product::where('id', $this->product->id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$product) {
                $this->addError('general', 'Product not found or access denied');
                Log::warning('Product not found or access denied', [
                    'user_id' => $user->id,
                    'product_id' => $this->product->id,
                    'tenant_id' => $tenantId
                ]);
                return;
            }

            DB::beginTransaction();

            $this->updateProduct($user, $tenantId);
            $this->updateStocks($user, $tenantId);

            DB::commit();

            Log::info('Product updated successfully', [
                'user_id' => $user->id,
                'product_id' => $this->product->id
            ]);

            session()->flash('success', 'Product updated successfully');
            return redirect()->route('manageproduct');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed', [
                'user_id' => Auth::id(),
                'product_id' => $this->product->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('general', 'Failed to update product: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    private function updateProduct($user, $tenantId)
    {
        $product = Product::where('id', $this->product->id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Handle category
        $categoryId = $this->category_id;
        if (!$categoryId && !empty($this->category_name)) {
            $category = ProductCategory::where('tenant_id', $tenantId)
                ->where('name', 'like', '%' . $this->category_name . '%')
                ->where('status', 'active')
                ->first();

            if (!$category) {
                $category = ProductCategory::create([
                    'name' => $this->category_name,
                    'description' => 'Auto-created category for ' . $this->category_name,
                    'status' => 'active',
                    'tenant_id' => $tenantId,
                    'created_by' => $user->id,
                ]);
            }
            $categoryId = $category->id;
        }

        // Handle image upload
        $imagePath = $product->image;
        if ($this->image) {
            // Delete old image
            if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL) && file_exists(public_path('storage/products/' . $product->image))) {
                unlink(public_path('storage/products/' . $product->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $this->image->getClientOriginalExtension();
            $this->image->storeAs('products', $imageName, 'public');
            $imagePath = $imageName;
        }

        // Update product
        $product->update([
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'base_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'category_id' => $categoryId,
            'image' => $imagePath,
            'barcode' => $this->barcode,
            'is_active' => $this->is_active,
        ]);
    }

    private function updateStocks($user, $tenantId)
    {
        foreach ($this->stocks as $stockData) {
            $dukaId = $stockData['duka_id'];
            $newQuantity = (int) ($stockData['quantity'] ?? 0);
            $currentQuantity = (int) ($stockData['current_quantity'] ?? 0);

            if ($newQuantity !== $currentQuantity) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $this->product->id,
                        'duka_id' => $dukaId,
                    ],
                    [
                        'quantity' => 0,
                        'last_updated_by' => $user->id,
                    ]
                );

                $previousQuantity = $stock->quantity;
                $quantityChange = $newQuantity - $previousQuantity;

                $stock->update([
                    'quantity' => $newQuantity,
                    'last_updated_by' => $user->id
                ]);

                // Record stock movement
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'user_id' => $user->id,
                    'type' => $quantityChange > 0 ? 'add' : 'remove',
                    'quantity_change' => $quantityChange,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $newQuantity,
                    'reason' => 'Stock update during product edit',
                ]);
            }
        }
    }

    public function toggleAdvancedStock()
    {
        $this->showAdvancedStock = !$this->showAdvancedStock;
    }

    public function getTotalStockProperty()
    {
        return collect($this->stocks)->sum('quantity');
    }

    public function render()
    {
        return view('livewire.officer-product-form', [
            'profitMargin' => $this->calculateProfitMargin(),
            'totalStock' => $this->totalStock,
        ]);
    }
}

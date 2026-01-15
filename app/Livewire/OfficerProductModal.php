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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OfficerProductModal extends Component
{
    use WithFileUploads;

    // Modal state
    public $showModal = false;
    public $isEditing = false;
    public $productId = null;

    // Form fields
    public $name;
    public $description;
    public $unit = 'pcs';
    public $buying_price;
    public $selling_price;
    public $category_name;
    public $category_id;
    public $duka_id;
    public $initial_stock = 0;
    public $barcode;
    public $image;
    public $existingImage;

    // Dropdown options
    public $availableUnits = [
        'pcs' => 'Pieces',
        'kg' => 'Kilograms',
        'g' => 'Grams',
        'ltr' => 'Liters',
        'ml' => 'Milliliters',
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

    // Validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'unit' => 'required|in:pcs,kg,g,ltr,ml,box,bag,pack,set,pair,dozen,carton',
        'buying_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'category_name' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:product_categories,id',
        'duka_id' => 'nullable|exists:dukas,id',
        'initial_stock' => 'nullable|integer|min:0',
        'barcode' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];

    protected $messages = [
        'selling_price.min' => 'Selling price must be greater than 0',
        'buying_price.min' => 'Buying price must be greater than or equal to 0',
        'image.image' => 'File must be an image',
        'image.mimes' => 'Image must be JPEG, PNG, JPG, or GIF',
        'image.max' => 'Image size must be less than 2MB',
    ];

    public function mount()
    {
        $this->loadDropdownData();
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
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal($productId)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->productId = $productId;

        // Load product data
        $user = Auth::user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if ($assignment) {
            $product = Product::where('id', $productId)
                ->where('tenant_id', $assignment->tenant_id)
                ->first();

            if ($product) {
                $this->name = $product->name;
                $this->description = $product->description;
                $this->unit = $product->unit;
                $this->buying_price = $product->base_price;
                $this->selling_price = $product->selling_price;
                $this->category_id = $product->category_id;
                $this->barcode = $product->barcode;
                $this->existingImage = $product->image;

                // Load initial stock for first assigned duka
                $officerDukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
                    ->where('officer_id', $user->id)
                    ->where('status', true)
                    ->pluck('duka_id')
                    ->toArray();

                if (!empty($officerDukaIds)) {
                    $stock = Stock::where('product_id', $productId)
                        ->whereIn('duka_id', $officerDukaIds)
                        ->first();

                    if ($stock) {
                        $this->duka_id = $stock->duka_id;
                        $this->initial_stock = $stock->quantity;
                    }
                }
            }
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'isEditing', 'productId', 'name', 'description', 'buying_price', 'selling_price',
            'category_name', 'category_id', 'duka_id', 'initial_stock', 'barcode', 'image', 'existingImage'
        ]);
        $this->unit = 'pcs';
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
        $this->validate();

        // Additional validation
        if ($this->selling_price <= $this->buying_price) {
            $this->addError('selling_price', 'Selling price must be greater than buying price');
            return;
        }

        $user = Auth::user();

        // Check permissions
        if ($this->isEditing && !$user->hasPermission('edit_product')) {
            session()->flash('error', 'You do not have permission to edit products');
            return;
        }

        if (!$this->isEditing && !$user->hasPermission('adding_product')) {
            session()->flash('error', 'You do not have permission to add products');
            return;
        }

        // Get tenant ID
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            session()->flash('error', 'No active assignments found');
            return;
        }

        $tenantId = $assignment->tenant_id;

        try {
            if ($this->isEditing) {
                $this->updateProduct($user, $tenantId);
            } else {
                $this->createProduct($user, $tenantId);
            }

            $this->closeModal();
            $this->dispatch('productSaved');
            session()->flash('success', 'Product ' . ($this->isEditing ? 'updated' : 'created') . ' successfully');

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    private function createProduct($user, $tenantId)
    {
        // Smart category assignment
        $categoryId = $this->category_id;
        if (!$categoryId && !empty($this->category_name)) {
            // Try to find existing category
            $category = ProductCategory::where('tenant_id', $tenantId)
                ->where('name', 'like', '%' . $this->category_name . '%')
                ->where('status', 'active')
                ->first();

            if (!$category) {
                // Create new category
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

        // Determine duka_id
        $dukaId = $this->duka_id;
        if (!$dukaId) {
            // Use first assigned duka
            $officerDukaIds = TenantOfficer::where('tenant_id', $tenantId)
                ->where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id')
                ->toArray();
            $dukaId = $officerDukaIds[0] ?? null;
        }

        // Generate SKU
        $sku = $this->generateProductSKU($this->name, $this->initial_stock);

        // Handle image upload
        $imagePath = $this->existingImage;
        if ($this->image) {
            $imageName = time() . '_' . uniqid() . '.' . $this->image->getClientOriginalExtension();
            $this->image->storeAs('products', $imageName, 'public');
            $imagePath = $imageName;
        }

        // Create product
        $product = Product::create([
            'name' => $this->name,
            'sku' => $sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'category_id' => $categoryId,
            'image' => $imagePath,
            'barcode' => $this->barcode,
            'tenant_id' => $tenantId,
            'is_active' => true,
        ]);

        // Create initial stock
        if ($this->initial_stock > 0 && $dukaId) {
            $stock = Stock::create([
                'product_id' => $product->id,
                'duka_id' => $dukaId,
                'quantity' => $this->initial_stock,
                'last_updated_by' => $user->id,
            ]);

            // Record stock movement
            StockMovement::create([
                'stock_id' => $stock->id,
                'user_id' => $user->id,
                'type' => 'add',
                'quantity_change' => $this->initial_stock,
                'previous_quantity' => 0,
                'new_quantity' => $this->initial_stock,
                'reason' => 'Initial stock for new product',
            ]);
        }
    }

    private function updateProduct($user, $tenantId)
    {
        $product = Product::where('id', $this->productId)
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
            if ($product->image && file_exists(storage_path('app/public/products/' . $product->image))) {
                unlink(storage_path('app/public/products/' . $product->image));
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
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'category_id' => $categoryId,
            'image' => $imagePath,
            'barcode' => $this->barcode,
        ]);

        // Update stock if duka and stock specified
        if ($this->duka_id && $this->initial_stock >= 0) {
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'duka_id' => $this->duka_id,
                ],
                [
                    'quantity' => 0,
                    'last_updated_by' => $user->id,
                ]
            );

            $oldQuantity = $stock->quantity;
            if ($oldQuantity != $this->initial_stock) {
                $stock->update(['quantity' => $this->initial_stock]);

                // Record stock movement
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'user_id' => $user->id,
                    'type' => $this->initial_stock > $oldQuantity ? 'add' : 'remove',
                    'quantity_change' => $this->initial_stock - $oldQuantity,
                    'previous_quantity' => $oldQuantity,
                    'new_quantity' => $this->initial_stock,
                    'reason' => 'Stock update during product edit',
                ]);
            }
        }
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
        return view('livewire.officer-product-modal', [
            'profitMargin' => $this->calculateProfitMargin(),
        ]);
    }
}

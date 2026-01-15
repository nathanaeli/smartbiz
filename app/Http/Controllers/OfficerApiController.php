<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Duka;
use App\Models\LoanPayment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StaffPermission;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\TenantAccount;
use App\Models\TenantOfficer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OfficerApiController extends Controller
{
    /**
     * Retrieves all necessary information for an officer's dashboard.
     *
     * This method gathers a comprehensive set of data required to populate the mobile
     * dashboard for a specific officer. It fetches the officer's details, their
     * active assignments, and all relevant business data (products, sales, customers, etc.)
     * associated with their assigned shops (dukas) under a specific tenant.
     *
     * @param  \Illuminate\Http\Request  $request The incoming HTTP request.
     * @param  int  $officerId The ID of the officer.
     * @return \Illuminate\Http\JsonResponse A JSON response containing various data sets for the dashboard.
     *         The JSON object includes:
     *         - 'officer': The authenticated officer's User model.
     *         - 'dukas': A collection of assigned Duka models.
     *         - 'products': A collection of Product models from the assigned dukas.
     *         - 'stocks': A collection of Stock models from the assigned dukas.
     *         - 'sales': A collection of Sale models from the assigned dukas.
     *         - 'saleItems': A collection of SaleItem models from the assigned dukas.
     *         - 'categories': A collection of all ProductCategory models for the tenant.
     *         - 'productItems': A collection of individual ProductItem models.
     *         - 'customers': A collection of Customer models from the assigned dukas.
     *         - 'tenantAccount': The TenantAccount model for the tenant.
     *         - 'tenantid': The ID of the current tenant.
     *         - 'dukaid': The ID of the officer's primary duka assignment.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the officer or their active assignment is not found.
     */
    public function officerdashboardinformation(Request $request, $officerId)
    {
        // Officer
        $officer                      = User::findOrFail($officerId);
        $officer->profile_picture_url = $officer->profile_picture_url;

        // Active assignment
        $assignment = TenantOfficer::where('officer_id', $officer->id)
            ->where('status', true)
            ->firstOrFail();

        $tenantId = $assignment->tenant_id;

        // Assigned dukas
        $assignedDukas = TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officer->id)
            ->where('status', true)
            ->with('duka')
            ->get()
            ->pluck('duka');

        $dukaIds = $assignedDukas->pluck('id');

        // RAW PRODUCTS (without with)
        $products = Product::where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->whereNull('deleted_at') // ✅ exclude soft deleted
            ->get()
            ->map(function ($product) {
                $product->image_url = $product->image_url;
                return $product;
            });

        // RAW STOCKS (without with)
        $stocks = Stock::whereIn('duka_id', $dukaIds)
            ->whereHas('product', function ($q) use ($tenantId, $dukaIds) {
                $q->where('tenant_id', $tenantId)
                    ->whereIn('duka_id', $dukaIds)
                    ->whereNull('deleted_at'); // ✅ hide stocks of deleted products
            })
            ->get();

        // RAW SALES (without with)
        $sales = Sale::where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->get();

        // RAW SALE ITEMS
        $saleItems = SaleItem::whereHas('sale', function ($q) use ($tenantId, $dukaIds) {
            $q->where('tenant_id', $tenantId)
                ->whereIn('duka_id', $dukaIds);
        })
            ->get();

        // RAW CATEGORIES
        $categories = ProductCategory::where('tenant_id', $tenantId)
            ->get();

        // RAW PRODUCT ITEMS
        $productItems = ProductItem::whereHas('product', function ($q) use ($tenantId, $dukaIds) {
            $q->where('tenant_id', $tenantId)
                ->whereIn('duka_id', $dukaIds);
        })
            ->get();

        // RAW CUSTOMERS
        $customers = Customer::where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->get();

        $loanPayments = $sales->mapWithKeys(function ($sale) {
            $payments = LoanPayment::where('sale_id', $sale->id)->get();
            return [$sale->id => $payments];
        });

        // RAW TENANT ACCOUNT
        $tenantAccount = TenantAccount::where('tenant_id', $tenantId)
            ->first();

        return response()->json([
            'officer'       => $officer,
            'dukas'         => $assignedDukas,
            'products'      => $products,
            'stocks'        => $stocks,
            'sales'         => $sales,
            'saleItems'     => $saleItems,
            'categories'    => $categories,
            'productItems'  => $productItems,
            'customers'     => $customers,
            'tenantAccount' => $tenantAccount,
            'tenantid'      => $tenantId,
            'dukaid'        => $assignment->duka_id,
            'loanpaynment'  => $loanPayments,
        ]);
    }

    /**
     * Unassign officer from a duka
     *
     * Allows an officer to unassign themselves from a specific duka they are assigned to.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $dukaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUnassignOfficerFromDuka(Request $request, $dukaId)
    {
        $user = auth()->user();

        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if assigned to this duka
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('duka_id', $dukaId)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'You are not assigned to this duka'], 404);
        }

        // Unassign by setting status to false
        $assignment->update(['status' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully unassigned from the duka',
        ]);
    }

    // Category Management API Methods for Officers
    public function apiGetCategories(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $query = \App\Models\ProductCategory::whereIn('tenant_id', $tenantIds)
            ->with(['parent', 'children', 'products', 'creator']);

        // Apply filters
        if ($request->has('search') && ! empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        $categories = $query->paginate($request->get('per_page', 15));

        // Format response
        $formattedCategories = $categories->getCollection()->map(function ($category) {
            return [
                'id'             => $category->id,
                'tenant_id'      => $category->tenant_id,
                'name'           => $category->name,
                'description'    => $category->description,
                'parent_id'      => $category->parent_id,
                'status'         => $category->status,
                'parent'         => $category->parent ? [
                    'id'   => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
                'children_count' => $category->children->count(),
                'products_count' => $category->products->count(),
                'created_by'     => $category->created_by,
                'creator'        => $category->creator ? [
                    'id'   => $category->creator->id,
                    'name' => $category->creator->name,
                ] : null,
                'created_at'     => $category->created_at,
                'updated_at'     => $category->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'categories' => $formattedCategories,
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page'    => $categories->lastPage(),
                    'per_page'     => $categories->perPage(),
                    'total'        => $categories->total(),
                    'from'         => $categories->firstItem(),
                    'to'           => $categories->lastItem(),
                ],
            ],
        ]);
    }


      public function sync(Request $request)
    {
        $user = auth()->user();

        // 1️⃣ Get active assignment
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->firstOrFail();

        $tenantId = $assignment->tenant_id;

        // 2️⃣ Get assigned dukas
        $dukaIds = TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        $lastSync = $request->last_sync; // nullable

        // 3️⃣ Active products
        $products = Product::where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->when($lastSync, function ($q) use ($lastSync) {
                $q->where('updated_at', '>=', $lastSync);
            })
            ->get();

        // 4️⃣ Soft-deleted product IDs
        $deletedProductIds = Product::onlyTrashed()
            ->where('tenant_id', $tenantId)
            ->whereIn('duka_id', $dukaIds)
            ->when($lastSync, function ($q) use ($lastSync) {
                $q->where('deleted_at', '>=', $lastSync);
            })
            ->pluck('id');

        return response()->json([
            'products' => $products,
            'deleted_product_ids' => $deletedProductIds,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function apiStoreCategory(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:product_categories,id',
            'status'      => 'required|in:active,inactive',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // If parent_id is provided, ensure it belongs to the same tenant
        if ($request->parent_id) {
            $parentCategory = \App\Models\ProductCategory::where('id', $request->parent_id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $parentCategory) {
                return response()->json(['error' => 'Invalid parent category selected'], 400);
            }
        }

        $category = \App\Models\ProductCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'parent_id'   => $request->parent_id,
            'status'      => $request->status,
            'tenant_id'   => $tenantId,
            'created_by'  => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data'    => [
                'category' => [
                    'id'          => $category->id,
                    'name'        => $category->name,
                    'description' => $category->description,
                    'parent_id'   => $category->parent_id,
                    'status'      => $category->status,
                    'tenant_id'   => $category->tenant_id,
                    'created_by'  => $category->created_by,
                    'created_at'  => $category->created_at,
                    'updated_at'  => $category->updated_at,
                ],
            ],
        ], 201);
    }

    public function apiUpdateCategory(Request $request, $id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:product_categories,id',
            'status'      => 'required|in:active,inactive',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        $category = \App\Models\ProductCategory::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Prevent setting self as parent
        if ($request->parent_id == $category->id) {
            return response()->json(['error' => 'Category cannot be its own parent'], 400);
        }

        // If parent_id is provided, ensure it belongs to the same tenant and prevent circular references
        if ($request->parent_id) {
            $parentCategory = \App\Models\ProductCategory::where('id', $request->parent_id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $parentCategory) {
                return response()->json(['error' => 'Invalid parent category selected'], 400);
            }

            // Check for circular reference
            if ($this->wouldCreateCircularReference($category->id, $request->parent_id)) {
                return response()->json(['error' => 'Cannot set this parent category as it would create a circular reference'], 400);
            }
        }

        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
            'parent_id'   => $request->parent_id,
            'status'      => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data'    => [
                'category' => [
                    'id'          => $category->id,
                    'name'        => $category->name,
                    'description' => $category->description,
                    'parent_id'   => $category->parent_id,
                    'status'      => $category->status,
                    'tenant_id'   => $category->tenant_id,
                    'created_by'  => $category->created_by,
                    'updated_at'  => $category->updated_at,
                ],
            ],
        ]);
    }

    public function apiDestroyCategory($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        $category = \App\Models\ProductCategory::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Check if category has children or products
        if ($category->children->count() > 0) {
            return response()->json(['error' => 'Cannot delete category with subcategories. Please delete or reassign subcategories first'], 400);
        }

        if ($category->products->count() > 0) {
            return response()->json(['error' => 'Cannot delete category with associated products. Please reassign products to another category first'], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    private function wouldCreateCircularReference($categoryId, $parentId)
    {
        $currentId = $parentId;
        while ($currentId) {
            if ($currentId == $categoryId) {
                return true;
            }
            $parent    = \App\Models\ProductCategory::find($currentId);
            $currentId = $parent ? $parent->parent_id : null;
        }
        return false;
    }

    // Get Officer Permissions API
    public function apiGetPermissions(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get permissions using the User model's getPermissions method
        $permissions = $user->getPermissions();

        // Get detailed permission information from StaffPermission table
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        $detailedPermissions = collect();
        if ($assignment) {
            $detailedPermissions = StaffPermission::where('tenant_id', $assignment->tenant_id)
                ->where('officer_id', $user->id)
                ->where('is_granted', true)
                ->with(['duka'])
                ->get()
                ->map(function ($permission) {
                    return [
                        'id'              => $permission->id,
                        'permission_name' => $permission->permission_name,
                        'is_granted'      => $permission->is_granted,
                        'duka'            => $permission->duka ? [
                            'id'       => $permission->duka->id,
                            'name'     => $permission->duka->name,
                            'location' => $permission->duka->location,
                        ] : null,
                        'created_at'      => $permission->created_at,
                        'updated_at'      => $permission->updated_at,
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'permissions'          => $permissions->toArray(),
                'detailed_permissions' => $detailedPermissions,
                'permission_count'     => $permissions->count(),
                'officer'              => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    // Get Products with Stock Information API
    public function apiGetProductsWithStock(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas for stock filtering
        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        $dukaIds = $assignedDukas->pluck('duka_id')->toArray();

        $query = Product::whereIn('tenant_id', $tenantIds)
            ->with(['category', 'stocks' => function ($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'stocks.duka']);

        // Apply filters
        if ($request->has('search') && ! empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category_id') && ! empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('duka_id') && ! empty($request->duka_id)) {
            // Verify officer is assigned to this duka
            if (in_array($request->duka_id, $dukaIds)) {
                $query->whereHas('stocks', function ($q) use ($request) {
                    $q->where('duka_id', $request->duka_id);
                });
            }
        }

        // Stock status filter
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->whereDoesntHave('stocks', function ($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds);
                    })->orWhereHas('stocks', function ($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)->where('quantity', 0);
                    });
                    break;
                case 'low_stock':
                    $query->whereHas('stocks', function ($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)
                            ->where('quantity', '>', 0)
                            ->where('quantity', '<=', 10);
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('stocks', function ($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)->where('quantity', '>', 10);
                    });
                    break;
            }
        }

        $products = $query->paginate($request->get('per_page', 15));

        // Format response with detailed stock information
        $formattedProducts = $products->getCollection()->map(function ($product) use ($dukaIds) {
            $stocks = $product->stocks->map(function ($stock) {
                return [
                    'id'              => $stock->id,
                    'duka_id'         => $stock->duka_id,
                    'duka_name'       => $stock->duka->name,
                    'duka_location'   => $stock->duka->location,
                    'quantity'        => $stock->quantity,
                    'last_updated_by' => $stock->last_updated_by,
                    'created_at'      => $stock->created_at,
                    'updated_at'      => $stock->updated_at,
                ];
            });

            $totalStock = $stocks->sum('quantity');
            $stockValue = $totalStock * $product->selling_price;

            return [
                'id'            => $product->id,
                'tenant_id'     => $product->tenant_id,
                'sku'           => $product->sku,
                'name'          => $product->name,
                'description'   => $product->description,
                'unit'          => $product->unit,
                'base_price'    => $product->base_price,
                'selling_price' => $product->selling_price,
                'profit_margin' => $product->base_price > 0 ?
                round((($product->selling_price - $product->base_price) / $product->base_price) * 100, 2) : 0,
                'is_active'     => $product->is_active,
                'image'         => $product->image_url,
                'barcode'       => $product->barcode,
                'category'      => $product->category ? [
                    'id'          => $product->category->id,
                    'name'        => $product->category->name,
                    'description' => $product->category->description,
                    'status'      => $product->category->status,
                ] : null,
                'stocks'        => $stocks,
                'stock_summary' => [
                    'total_quantity'  => $totalStock,
                    'total_value'     => $stockValue,
                    'stock_locations' => $stocks->count(),
                    'low_stock_alert' => $totalStock <= 10 && $totalStock > 0,
                    'out_of_stock'    => $totalStock == 0,
                ],
                'created_at'    => $product->created_at,
                'updated_at'    => $product->updated_at,
            ];
        });

        // Get available categories for filtering
        $categories = \App\Models\ProductCategory::whereIn('tenant_id', $tenantIds)
            ->where('status', 'active')
            ->select('id', 'name', 'description')
            ->get();

        // Get available dukas for filtering
        $availableDukas = \App\Models\Duka::whereIn('id', $dukaIds)
            ->select('id', 'name', 'location')
            ->get();

        // Get officer's primary duka (first assigned duka)
        $officerDuka = $assignedDukas->first() ? [
            'id'       => $assignedDukas->first()->duka->id,
            'name'     => $assignedDukas->first()->duka->name,
            'location' => $assignedDukas->first()->duka->location,
            'status'   => $assignedDukas->first()->duka->status,
        ] : null;

        return response()->json([
            'success' => true,
            'data'    => [
                'products'        => $formattedProducts,
                'categories'      => $categories,
                'available_dukas' => $availableDukas,
                'officer_duka'    => $officerDuka,
                'pagination'      => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                    'from'         => $products->firstItem(),
                    'to'           => $products->lastItem(),
                ],
                'summary'         => [
                    'total_products'        => $products->total(),
                    'total_stock_value'     => $formattedProducts->sum('stock_summary.total_value'),
                    'low_stock_products'    => $formattedProducts->where('stock_summary.low_stock_alert', true)->count(),
                    'out_of_stock_products' => $formattedProducts->where('stock_summary.out_of_stock', true)->count(),
                ],
            ],
        ]);
    }

    public function apiAddProduct(Request $request)
    {
        $user = auth()->user();

        // Role check
        if (! $user->hasRole('officer')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only officers can add products.',
            ], 403);
        }

        // Permission check
        if (! $user->hasPermission('adding_product')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add products.',
            ], 403);
        }

        // Image input validation (mutually exclusive)
        $hasImageFile   = $request->hasFile('image');
        $hasImageBase64 = $request->filled('image') && is_string($request->image);
        $hasImageUrl    = $request->filled('image_url');

        if (($hasImageFile || $hasImageBase64) && $hasImageUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose only one: upload an image file/base64 OR provide an image URL.',
            ], 400);
        }

        if ($hasImageFile && $hasImageBase64) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose only one: upload a file OR send base64 image data.',
            ], 400);
        }

        // Validate uploaded file (if any)
        if ($hasImageFile) {
            $image = $request->file('image');

            if (! $image->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file uploaded. Please try again.',
                ], 400);
            }

            if ($image->getSize() > 2 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image is too large. Maximum size is 2MB.',
                ], 400);
            }

            $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (! in_array($image->getMimeType(), $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image format. Only JPEG, PNG, JPG, and GIF are allowed.',
                ], 400);
            }
        }

        // Validate form data
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'required|in:pcs,kg,g,ltr,ml,box,bag,pack,set,pair,dozen,carton',
            'buying_price'  => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:buying_price',
            'category_name' => 'nullable|string|max:255',
            'initial_stock' => 'nullable|integer|min:0',
            'track_items'   => 'sometimes|boolean', // New: whether to create individual items
            'barcode'       => 'nullable|string|max:255|unique:products,barcode',
            'image_url'     => 'nullable|url|required_without_all:image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Get tenant and duka assignment
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to any active shop (duka). Contact admin.',
            ], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Get the first assigned duka ID for the officer
        $dukaId = TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->first();

        if (! $dukaId) {
            return response()->json([
                'success' => false,
                'message' => 'No shop assigned. Please contact your administrator.',
            ], 400);
        }

        // Handle category (smart create/find)
        $categoryId = null;
        if ($request->category_name) {
            $category = ProductCategory::where('tenant_id', $tenantId)
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->category_name) . '%'])
                ->where('status', 'active')
                ->first();

            if (! $category) {
                $category = ProductCategory::create([
                    'name'        => $request->category_name,
                    'description' => 'Auto-created from product addition',
                    'status'      => 'active',
                    'tenant_id'   => $tenantId,
                    'created_by'  => $user->id,
                ]);
            }
            $categoryId = $category->id;
        }

        // Generate SKU
        $sku = $this->generateProductSKU($request->name, $request->initial_stock ?? 0);

        // Handle image
        $imagePath = null;

        try {
            if ($hasImageFile) {
                $image      = $request->file('image');
                $imageName  = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $uploadPath = public_path('storage/products');
                if (! file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $imagePath = $imageName;
            } elseif ($hasImageBase64) {
                $imageData = $request->image;
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $extension = $matches[1];
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                } else {
                    $extension = 'png';
                }

                $decoded = base64_decode($imageData);
                if ($decoded === false || strlen($decoded) > 2 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Invalid or oversized base64 image.'], 400);
                }

                $imageName  = time() . '_' . uniqid() . '.' . $extension;
                $uploadPath = public_path('storage/products');
                if (! file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                file_put_contents($uploadPath . '/' . $imageName, $decoded);
                $imagePath = $imageName;
            } elseif ($hasImageUrl) {
                $imagePath = $request->image_url;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process image. Please try again.',
            ], 500);
        }

        // Create product
        try {
            $product = Product::create([
                'name'          => $request->name,
                'sku'           => $sku,
                'description'   => $request->description,
                'unit'          => $request->unit,
                'base_price'    => $request->buying_price,
                'selling_price' => $request->selling_price,
                'category_id'   => $categoryId,
                'image'         => $imagePath,
                'barcode'       => $request->barcode,
                'tenant_id'     => $tenantId,
                'duka_id'       => $dukaId,
                'is_active'     => true,
            ]);

            $initialStock = $request->initial_stock ?? 0;
            $trackItems   = $request->boolean('track_items', false); // Default: false (bulk only)

            $messageParts = ["Product '{$product->name}' added successfully!"];

            // Handle stock
            if ($initialStock > 0) {
                if ($trackItems) {
                    // Create individual ProductItem records (e.g., for phones, laptops)
                    $itemsCreated = 0;
                    for ($i = 0; $i < $initialStock; $i++) {
                        ProductItem::create([
                            'product_id' => $product->id,
                            'qr_code'    => ProductItem::generateQrCode(),
                            'status'     => 'available',
                        ]);
                        $itemsCreated++;
                    }

                    $messageParts[] = "$itemsCreated individual items created with unique QR codes.";
                } else {
                    // Bulk stock entry
                    $stock = Stock::create([
                        'product_id'      => $product->id,
                        'duka_id'         => $dukaId,
                        'quantity'        => $initialStock,
                        'last_updated_by' => $user->id,
                    ]);

                    StockMovement::create([
                        'stock_id'          => $stock->id,
                        'user_id'           => $user->id,
                        'type'              => 'add',
                        'quantity_change'   => $initialStock,
                        'previous_quantity' => 0,
                        'new_quantity'      => $initialStock,
                        'reason'            => 'Initial stock when adding new product',
                    ]);

                    $messageParts[] = "Initial bulk stock of $initialStock units recorded.";
                }
            }

            return response()->json([
                'success' => true,
                'message' => implode(' ', $messageParts),
                'data'    => [
                    'product' => [
                        'id'            => $product->id,
                        'name'          => $product->name,
                        'sku'           => $product->sku,
                        'selling_price' => $product->selling_price,
                        'image_url'     => $product->image_url,
                        'initial_stock' => $initialStock,
                        'track_items'   => $trackItems,
                        'items_created' => $trackItems ? ($initialStock ?? 0) : 0,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save product. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get Product Details by ID
     *
     * Retrieves detailed information about a specific product including stock, category, and other details.
     * Only accessible to officers with proper tenant permissions.
     *
     * @param int $productId The ID of the product to retrieve
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetProduct($productId)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas for stock filtering
        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        $dukaIds = $assignedDukas->pluck('duka_id')->toArray();

        // Find product with relationships
        $product = Product::where('id', $productId)
            ->whereIn('tenant_id', $tenantIds)
            ->with([
                'category',
                'duka',
                'stocks' => function ($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds)->with('duka');
                },
                'items'  => function ($q) {
                    $q->latest()->take(10); // Get latest 10 items
                },
            ])
            ->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Format stocks
        $stocks = $product->stocks->map(function ($stock) {
            return [
                'id'              => $stock->id,
                'duka_id'         => $stock->duka_id,
                'duka_name'       => $stock->duka->name,
                'duka_location'   => $stock->duka->location,
                'quantity'        => $stock->quantity,
                'last_updated_by' => $stock->last_updated_by,
                'created_at'      => $stock->created_at,
                'updated_at'      => $stock->updated_at,
            ];
        });

        // Format product items
        $formattedItems = $product->items->map(function ($item) {
            return [
                'id'         => $item->id,
                'qr_code'    => $item->qr_code,
                'status'     => $item->status,
                'sold_at'    => optional($item->sold_at)->toDateTimeString(),
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });

        // Calculate totals
        $totalStock     = $stocks->sum('quantity');
        $stockValue     = $totalStock * $product->selling_price;
        $totalItems     = $product->items->count();
        $availableItems = $product->items->where('status', 'available')->count();
        $soldItems      = $product->items->where('status', 'sold')->count();

        // Format the response
        $formattedProduct = [
            'id'            => $product->id,
            'tenant_id'     => $product->tenant_id,
            'sku'           => $product->sku,
            'name'          => $product->name,
            'description'   => $product->description,
            'unit'          => $product->unit,
            'base_price'    => $product->base_price,
            'selling_price' => $product->selling_price,
            'profit_margin' => $product->profit_margin,
            'is_active'     => $product->is_active,
            'image_url'     => $product->image_url,
            'barcode'       => $product->barcode,
            'category'      => $product->category ? [
                'id'          => $product->category->id,
                'name'        => $product->category->name,
                'description' => $product->category->description,
                'status'      => $product->category->status,
            ] : null,
            'duka'          => $product->duka ? [
                'id'       => $product->duka->id,
                'name'     => $product->duka->name,
                'location' => $product->duka->location,
            ] : null,
            'stocks'        => $stocks,
            'stock_summary' => [
                'total_quantity'  => $totalStock,
                'total_value'     => $stockValue,
                'stock_locations' => $stocks->count(),
                'low_stock_alert' => $totalStock <= 10 && $totalStock > 0,
                'out_of_stock'    => $totalStock == 0,
            ],
            'items_summary' => [
                'total_items'     => $totalItems,
                'available_items' => $availableItems,
                'sold_items'      => $soldItems,
                'damaged_items'   => $product->items->where('status', 'damaged')->count(),
            ],
            'recent_items'  => $formattedItems,
            'profitability' => [
                'profit_per_unit' => $product->profit_per_unit,
                'total_profit'    => $product->total_profit,
                'profit_margin'   => $product->profit_margin,
            ],
            'created_at'    => $product->created_at,
            'updated_at'    => $product->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'product' => $formattedProduct,
            ],
        ]);
    }

    // Update Product API
    public function apiUpdateProduct(Request $request, $productId)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if officer has edit_product permission
        if (! $user->hasPermission('edit_product')) {
            return response()->json(['error' => 'You do not have permission to edit products'], 403);
        }

        // Custom validation for image handling
        if ($request->hasFile('image') && $request->filled('image_url')) {
            return response()->json(['error' => 'Cannot provide both image file and image URL. Choose one.'], 400);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            if (! $image->isValid()) {
                return response()->json(['error' => 'Invalid image file uploaded'], 400);
            }

            // Check file size (2MB limit)
            if ($image->getSize() > 2048 * 1024) {
                return response()->json(['error' => 'Image file size must be less than 2MB'], 400);
            }

            // Check mime type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (! in_array($image->getMimeType(), $allowedMimes)) {
                return response()->json(['error' => 'Image must be JPEG, PNG, JPG, or GIF format'], 400);
            }
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'required|in:pcs,kg,g,ltr,ml,box,bag,pack,set,pair,dozen,carton',
            'buying_price'  => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:buying_price',
            'category_name' => 'nullable|string|max:255',
            'is_active'     => 'required|boolean',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode,' . $productId,
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Find product and verify ownership
        $product = Product::where('id', $productId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

                                             // Smart category assignment
        $categoryId = $product->category_id; // Keep existing if not provided
        if ($request->category_name) {
            // Try to find existing category
            $category = \App\Models\ProductCategory::where('tenant_id', $tenantId)
                ->where('name', 'like', '%' . $request->category_name . '%')
                ->where('status', 'active')
                ->first();

            if (! $category) {
                // Create new category if it doesn't exist
                $category = \App\Models\ProductCategory::create([
                    'name'        => $request->category_name,
                    'description' => 'Auto-created category for ' . $request->category_name,
                    'status'      => 'active',
                    'tenant_id'   => $tenantId,
                    'created_by'  => $user->id,
                ]);
            }
            $categoryId = $category->id;
        }

                                      // Handle image upload or URL
        $imagePath = $product->image; // Keep existing if not provided
        if ($request->hasFile('image')) {
            try {
                // Delete old image if exists and is a local file
                if ($product->image && ! filter_var($product->image, FILTER_VALIDATE_URL) && file_exists(public_path('storage/products/' . $product->image))) {
                    unlink(public_path('storage/products/' . $product->image));
                }

                $image     = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Ensure directory exists
                $uploadPath = public_path('storage/products');
                if (! file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $imagePath = $imageName;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 400);
            }
        } elseif ($request->filled('image_url')) {
            // Validate URL format
            if (! filter_var($request->image_url, FILTER_VALIDATE_URL)) {
                return response()->json(['error' => 'Invalid image URL format'], 400);
            }

            // Delete old image if exists and is a local file
            if ($product->image && ! filter_var($product->image, FILTER_VALIDATE_URL) && file_exists(public_path('storage/products/' . $product->image))) {
                unlink(public_path('storage/products/' . $product->image));
            }
            // Store the URL directly
            $imagePath = $request->image_url;
        }

        // Update product
        $product->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'unit'          => $request->unit,
            'base_price'    => $request->buying_price,
            'selling_price' => $request->selling_price,
            'category_id'   => $categoryId,
            'image'         => $imagePath,
            'barcode'       => $request->barcode,
            'is_active'     => $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => [
                'product' => [
                    'id'            => $product->id,
                    'name'          => $product->name,
                    'sku'           => $product->sku,
                    'description'   => $product->description,
                    'unit'          => $product->unit,
                    'buying_price'  => number_format($product->base_price, 2),
                    'selling_price' => $product->selling_price,
                    'profit_margin' => $product->base_price > 0 ?
                    round((($product->selling_price - $product->base_price) / $product->base_price) * 100, 2) : 0,
                    'category'      => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'is_active'     => $product->is_active,
                    'image'         => $product->image,
                    'barcode'       => $product->barcode,
                    'updated_at'    => $product->updated_at,
                ],
            ],
        ]);
    }

    // Update Stock API
    public function apiUpdateStock(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'quantity_change' => 'required|integer',
            'operation'       => 'required|in:add,reduce,set',
            'reason'          => 'nullable|string|max:255',
        ]);

        // Get tenant ID and duka ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;
        $dukaId   = $assignment->duka_id;

        // Verify product belongs to tenant
        $product = Product::where('id', $request->product_id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Check permissions based on operation
        if ($request->operation === 'add' && ! $user->hasPermission('adding_stock')) {
            return response()->json(['error' => 'You do not have permission to add stock'], 403);
        }

        if ($request->operation === 'reduce' && ! $user->hasPermission('reduce_stock')) {
            return response()->json(['error' => 'You do not have permission to reduce stock'], 403);
        }

        // Get or create stock record using the officer's assigned duka
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $request->product_id,
                'duka_id'    => $dukaId,
            ],
            [
                'quantity'        => 0,
                'last_updated_by' => $user->id,
            ]
        );

        $previousQuantity = $stock->quantity;
        $quantityChange   = $request->quantity_change;

        // Calculate new quantity based on operation
        switch ($request->operation) {
            case 'add':
                if ($quantityChange <= 0) {
                    return response()->json(['error' => 'Quantity to add must be positive'], 400);
                }
                $newQuantity  = $previousQuantity + $quantityChange;
                $movementType = 'add';
                break;

            case 'reduce':
                if ($quantityChange <= 0) {
                    return response()->json(['error' => 'Quantity to reduce must be positive'], 400);
                }
                if ($quantityChange > $previousQuantity) {
                    return response()->json(['error' => 'Cannot reduce more than current stock'], 400);
                }
                $newQuantity    = $previousQuantity - $quantityChange;
                $movementType   = 'remove';
                $quantityChange = -$quantityChange; // Negative for reduction
                break;

            case 'set':
                if ($quantityChange < 0) {
                    return response()->json(['error' => 'Stock quantity cannot be negative'], 400);
                }
                $newQuantity    = $quantityChange;
                $quantityChange = $newQuantity - $previousQuantity;
                $movementType   = $quantityChange > 0 ? 'add' : 'remove';
                break;

            default:
                return response()->json(['error' => 'Invalid operation'], 400);
        }

        // Update stock
        $stock->update([
            'quantity'        => $newQuantity,
            'last_updated_by' => $user->id,
        ]);

        // Record stock movement
        StockMovement::create([
            'stock_id'          => $stock->id,
            'user_id'           => $user->id,
            'type'              => $movementType,
            'quantity_change'   => $quantityChange,
            'previous_quantity' => $previousQuantity,
            'new_quantity'      => $newQuantity,
            'reason'            => $request->reason ?: ucfirst($request->operation) . ' stock',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data'    => [
                'stock' => [
                    'id'                => $stock->id,
                    'product_id'        => $stock->product_id,
                    'product_name'      => $product->name,
                    'duka_id'           => $stock->duka_id,
                    'duka_name'         => $stock->duka->name,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity'      => $newQuantity,
                    'quantity_change'   => $quantityChange,
                    'operation'         => $request->operation,
                    'updated_at'        => $stock->updated_at,
                ],
            ],
        ]);
    }

    // Add Stock API
    public function apiAddStock(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;
        $dukaId   = $assignment->duka_id;

        // Verify product belongs to tenant
        $product = Product::where('id', $request->product_id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Get or create stock record
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $request->product_id,
                'duka_id'    => $dukaId,
            ],
            [
                'quantity'        => 0,
                'last_updated_by' => $user->id,
            ]
        );

        $previousQuantity = $stock->quantity;
        $newQuantity      = $previousQuantity + $request->quantity;

        // Update stock
        $stock->update([
            'quantity'        => $newQuantity,
            'last_updated_by' => $user->id,
        ]);

        // Record stock movement
        StockMovement::create([
            'stock_id'          => $stock->id,
            'user_id'           => $user->id,
            'type'              => 'add',
            'quantity_change'   => $request->quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity'      => $newQuantity,
            'reason'            => $request->reason ?: 'Stock addition',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully',
            'data'    => [
                'stock' => [
                    'id'                => $stock->id,
                    'product_id'        => $stock->product_id,
                    'product_name'      => $product->name,
                    'duka_id'           => $stock->duka_id,
                    'duka_name'         => $stock->duka->name,
                    'previous_quantity' => $previousQuantity,
                    'added_quantity'    => $request->quantity,
                    'new_quantity'      => $newQuantity,
                    'updated_at'        => $stock->updated_at,
                ],
            ],
        ]);
    }

    // Delete Product API
    public function apiDeleteProduct(Request $request, $productId)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if officer has delete_product permission
        if (! $user->hasPermission('delete_product')) {
            return response()->json(['error' => 'You do not have permission to delete products'], 403);
        }

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Find product and verify ownership
        $product = Product::where('id', $productId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Check if product has sales
        $hasSales = Sale::whereHas('saleItems', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        })->exists();

        // If product has sales, reset any remaining stock to 0
        if ($hasSales) {
            $stocksToReset = Stock::where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->get();

            foreach ($stocksToReset as $stock) {
                $previousQuantity = $stock->quantity;

                // Reset stock to 0
                $stock->update(['quantity' => 0]);

                // Record stock movement for the reset
                StockMovement::create([
                    'stock_id'          => $stock->id,
                    'user_id'           => $user->id,
                    'type'              => 'remove',
                    'quantity_change'   => -$previousQuantity,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity'      => 0,
                    'reason'            => 'Stock reset to 0 for product deletion',
                ]);
            }
        } else {
            // If no sales, check if product has stock (normal case)
            $hasStock = Stock::where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->exists();

            if ($hasStock) {
                return response()->json([
                    'error' => 'Cannot delete product with existing stock. Reduce stock to zero first.',
                ], 400);
            }
        }

        // Delete associated stocks and movements
        Stock::where('product_id', $productId)->delete();
        StockMovement::whereHas('stock', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        })->delete();

        // Delete associated stock transfers
        $stockTransfers = StockTransfer::where('product_id', $productId)->get();
        foreach ($stockTransfers as $transfer) {
            $transferItemId = $transfer->stock_transfer_id;

            // Delete the transfer
            $transfer->delete();

            // Check if the transfer item has any remaining transfers
            $remainingTransfers = StockTransfer::where('stock_transfer_id', $transferItemId)->count();
            if ($remainingTransfers == 0) {
                // Delete the orphaned transfer item
                StockTransferItem::where('id', $transferItemId)->delete();
            }
        }

        // Delete product image if exists and is a local file
        if ($product->image && ! filter_var($product->image, FILTER_VALIDATE_URL) && file_exists(public_path('storage/products/' . $product->image))) {
            unlink(public_path('storage/products/' . $product->image));
        }

        // Delete product
        $productName = $product->name;
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'data'    => [
                'deleted_product' => [
                    'id'         => $productId,
                    'name'       => $productName,
                    'deleted_at' => now(),
                ],
            ],
        ]);
    }

    public function apiGetProductItemsByProductId($productId)
    {
        $items = ProductItem::where('product_id', $productId)
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'qr_code'    => $item->qr_code,
                    'status'     => $item->status,
                    'sold_at'    => optional($item->sold_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $items->count(),
            'data'    => $items,
        ]);
    }

    public function apiStoreProductItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qr_code'    => 'required|string|max:255|unique:product_items,qr_code',
            'status'     => 'required|in:available,sold,damaged',
        ]);

        $item = ProductItem::create([
            'product_id' => $validated['product_id'],
            'qr_code'    => $validated['qr_code'],
            'status'     => $validated['status'],
            'created_by' => Auth::id(), // 👈 authenticated user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product item created successfully',
            'data'    => [
                'id'         => $item->id,
                'product_id' => $item->product_id,
                'qr_code'    => $item->qr_code,
                'status'     => $item->status,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    // Get Sales List with filtering and pagination
    public function apiGetSales(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->toArray();

        $query = Sale::whereIn('tenant_id', $tenantIds)
            ->whereIn('duka_id', $dukaIds)
            ->with(['customer', 'duka', 'saleItems.product']);

        // Apply filters
        if ($request->has('search') && ! empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($request) {
                        $customerQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->has('duka_id') && ! empty($request->duka_id)) {
            // Verify officer is assigned to this duka
            if (in_array($request->duka_id, $dukaIds)) {
                $query->where('duka_id', $request->duka_id);
            }
        }

        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('is_loan')) {
            $query->where('is_loan', $request->boolean('is_loan'));
        }

        if ($request->has('payment_status') && ! empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filters
        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Amount filters
        if ($request->has('min_amount') && ! empty($request->min_amount)) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->has('max_amount') && ! empty($request->max_amount)) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Sort by latest first
        $query->orderBy('created_at', 'desc');

        $sales = $query->paginate($request->get('per_page', 15));

        // Format response
        $formattedSales = $sales->getCollection()->map(function ($sale) {
            return [
                'id'                => $sale->id,
                'tenant_id'         => $sale->tenant_id,
                'duka_id'           => $sale->duka_id,
                'duka_name'         => $sale->duka->name,
                'customer_id'       => $sale->customer_id,
                'customer'          => $sale->customer ? [
                    'id'    => $sale->customer->id,
                    'name'  => $sale->customer->name,
                    'phone' => $sale->customer->phone,
                ] : null,
                'total_amount'      => $sale->total_amount,
                'discount_amount'   => $sale->discount_amount,
                'profit_loss'       => $sale->profit_loss,
                'is_loan'           => $sale->is_loan,
                'due_date'          => $sale->due_date,
                'payment_status'    => $sale->payment_status,
                'total_payments'    => $sale->total_payments,
                'remaining_balance' => $sale->remaining_balance,
                'discount_reason'   => $sale->discount_reason,
                'item_count'        => $sale->saleItems->count(),
                'created_at'        => $sale->created_at,
                'updated_at'        => $sale->updated_at,
            ];
        });

        // Calculate summary statistics
        $totalAmount             = $formattedSales->sum('total_amount');
        $totalLoans              = $formattedSales->where('is_loan', true)->count();
        $totalOutstandingBalance = $formattedSales->where('is_loan', true)->sum('remaining_balance');
        $cashSalesCount          = $formattedSales->where('is_loan', false)->count();
        $loanSalesCount          = $totalLoans;

        return response()->json([
            'success' => true,
            'data'    => [
                'sales'      => $formattedSales,
                'pagination' => [
                    'current_page' => $sales->currentPage(),
                    'last_page'    => $sales->lastPage(),
                    'per_page'     => $sales->perPage(),
                    'total'        => $sales->total(),
                    'from'         => $sales->firstItem(),
                    'to'           => $sales->lastItem(),
                ],
                'summary'    => [
                    'total_sales'               => $sales->total(),
                    'total_amount'              => $totalAmount,
                    'total_loans'               => $totalLoans,
                    'total_outstanding_balance' => $totalOutstandingBalance,
                    'cash_sales_count'          => $cashSalesCount,
                    'loan_sales_count'          => $loanSalesCount,
                ],
            ],
        ]);
    }

    // Get Sale Details API
    public function apiGetSaleDetails($id, Request $request)
    {
        $user = auth()->user();

        // 1. Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Get officer's assigned dukas
        $assignedDukaIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->toArray();

        // 3. Load sale with full relationships
        $sale = Sale::with([
            'customer',
            'duka',
            'saleItems.product.category',
            'loanPayments',
        ])
            ->whereIn('duka_id', $assignedDukaIds) // Officer must be assigned to this duka
            ->find($id);

        if (! $sale) {
            return response()->json(['error' => 'Sale not found or unauthorized'], 404);
        }

        // 4. Format the sale details
        $formattedSale = [
            'id'                => $sale->id,
            'tenant_id'         => $sale->tenant_id,
            'duka_id'           => $sale->duka_id,
            'duka'              => [
                'id'       => $sale->duka->id,
                'name'     => $sale->duka->name,
                'location' => $sale->duka->location ?? null,
                'phone'    => $sale->duka->phone ?? null,
            ],
            'customer'          => $sale->customer ? [
                'id'      => $sale->customer->id,
                'name'    => $sale->customer->name,
                'phone'   => $sale->customer->phone,
                'email'   => $sale->customer->email,
                'address' => $sale->customer->address ?? null,
            ] : null,

            // Items
            'items'             => $sale->saleItems->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'product_id'      => $item->product_id,
                    'product_name'    => $item->product->name,
                    'sku'             => $item->product->sku,
                    'category'        => $item->product->category->name ?? null,
                    'unit'            => $item->product->unit,
                    'quantity'        => $item->quantity,
                    'unit_price'      => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'total_price'     => $item->total,
                ];
            }),

            // Payment & Loan info
            'is_loan'           => $sale->is_loan,
            'due_date'          => $sale->due_date,
            'payment_status'    => $sale->payment_status,
            'total_payments'    => $sale->total_payments,
            'remaining_balance' => $sale->remaining_balance,
            'loan_payments'     => $sale->loanPayments->map(function ($payment) {
                return [
                    'id'      => $payment->id,
                    'amount'  => $payment->amount,
                    'method'  => $payment->method,
                    'paid_at' => $payment->created_at,
                ];
            }),

            // Summary
            'total_amount'      => $sale->total_amount,
            'discount_amount'   => $sale->discount_amount,
            'profit_loss'       => $sale->profit_loss,
            'item_count'        => $sale->saleItems->count(),

            'discount_reason'   => $sale->discount_reason,
            'created_at'        => $sale->created_at,
            'updated_at'        => $sale->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data'    => $formattedSale,
        ]);
    }

    // Create Sale API
    public function apiCreateSale(Request $request)
    {
        $user    = auth()->user();
        $allData = $request->all();

        Log::info('Sale creation request initiated', [
            'user_id'      => $user->id,
            'user_name'    => $user->name,
            'request_data' => $request->all(),
        ]);

        $request->validate([
            'customer_id'             => 'nullable|exists:customers,id',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_amount'         => 'nullable|numeric|min:0',
            'discount_reason'         => 'nullable|string',
            'is_loan'                 => 'boolean',
            'due_date'                => 'nullable|date|after_or_equal:today',
        ]);

        Log::info('Request validation passed', [
            'user_id'         => $user->id,
            'customer_id'     => $request->customer_id,
            'items_count'     => count($request->items),
            'discount_amount' => $request->discount_amount,
            'is_loan'         => $request->boolean('is_loan', false),
            'due_date'        => $request->due_date,
        ]);

        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        Log::info('Assigned dukas retrieved for officer', [
            'user_id'              => $user->id,
            'assigned_dukas_count' => $assignedDukas->count(),
            'assigned_dukas'       => $assignedDukas->map(function ($assignment) {
                return [
                    'duka_id'   => $assignment->duka_id,
                    'duka_name' => $assignment->duka->name,
                    'tenant_id' => $assignment->tenant_id,
                ];
            }),
        ]);

        if ($assignedDukas->isEmpty()) {
            Log::warning('No active duka assignments found for officer', [
                'user_id'           => $user->id,
                'assignments_count' => TenantOfficer::where('officer_id', $user->id)->count(),
            ]);
            return response()->json(['error' => 'No active duka assignments found'], 403);
        }

        $primaryAssignment = $assignedDukas->first();
        $assignedDukaId    = $primaryAssignment->duka_id;
        $tenantId          = $primaryAssignment->tenant_id;
        $dukaId            = $assignedDukaId;

        Log::info('Officer duka assignment found', [
            'user_id'              => $user->id,
            'tenant_id'            => $tenantId,
            'assigned_duka_id'     => $assignedDukaId,
            'duka_name'            => $primaryAssignment->duka->name,
            'total_assigned_dukas' => $assignedDukas->count(),
        ]);

        // Always use the officer's assigned duka ID (ignore any duka_id from request for security)
        $dukaId = $assignedDukaId;

        Log::info('Using officer assigned duka for sale creation', [
            'user_id'          => $user->id,
            'assigned_duka_id' => $dukaId,
            'duka_name'        => $primaryAssignment->duka->name,
        ]);

        // Verify customer belongs to tenant (if provided)
        if ($request->customer_id) {
            $customer = \App\Models\Customer::where('id', $request->customer_id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $customer) {
                return response()->json(['error' => 'Invalid customer selected'], 400);
            }
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $totalProfit = 0;

            // Calculate totals and validate stock
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (! $product) {
                    Log::error('Product not found or does not belong to officer tenant', [
                        'user_id'    => $user->id,
                        'product_id' => $item['product_id'],
                        'tenant_id'  => $tenantId,
                        'duka_id'    => $dukaId,
                    ]);
                    throw new \Exception('Product not found or you do not have permission to sell this product');
                }

                // Check stock availability
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('duka_id', $dukaId)
                    ->first();

                Log::info('Stock check for product', [
                    'user_id'            => $user->id,
                    'product_id'         => $item['product_id'],
                    'product_name'       => $product->name,
                    'duka_id'            => $dukaId,
                    'requested_quantity' => $item['quantity'],
                    'available_stock'    => $stock ? $stock->quantity : 0,
                ]);

                if (! $stock) {
                    Log::error('No stock record found for product in assigned duka', [
                        'user_id'      => $user->id,
                        'product_id'   => $item['product_id'],
                        'product_name' => $product->name,
                        'duka_id'      => $dukaId,
                        'duka_name'    => $primaryAssignment->duka->name,
                    ]);
                    throw new \Exception("No stock available for product '{$product->name}' in your assigned duka '{$primaryAssignment->duka->name}'");
                }

                if ($stock->quantity < $item['quantity']) {
                    Log::error('Insufficient stock for product', [
                        'user_id'            => $user->id,
                        'product_id'         => $item['product_id'],
                        'product_name'       => $product->name,
                        'duka_id'            => $dukaId,
                        'requested_quantity' => $item['quantity'],
                        'available_quantity' => $stock->quantity,
                    ]);
                    throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$stock->quantity}, Requested: {$item['quantity']}");
                }

                $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
                $totalAmount += $itemTotal;

                // Calculate profit (selling price - buying price)
                $profit = ($item['unit_price'] - $product->base_price) * $item['quantity'];
                $totalProfit += $profit;
            }

            // Apply overall discount
            $finalTotal  = $totalAmount - ($request->discount_amount ?? 0);
            $finalProfit = $totalProfit - ($request->discount_amount ?? 0);

            // Create sale
            $sale = Sale::create([
                'tenant_id'       => $tenantId,
                'duka_id'         => $dukaId,
                'customer_id'     => $request->customer_id,
                'total_amount'    => $finalTotal,
                'discount_amount' => $request->discount_amount ?? 0,
                'profit_loss'     => $finalProfit,
                'is_loan'         => $request->boolean('is_loan', false),
                'due_date'        => $request->due_date,
                'discount_reason' => $request->discount_reason,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);

                // Create sale item
                $saleItem = SaleItem::create([
                    'sale_id'         => $sale->id,
                    'product_id'      => $item['product_id'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total'           => $itemTotal,
                ]);

                Log::info('Sale item created', [
                    'user_id'      => $user->id,
                    'sale_id'      => $sale->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                ]);

                // Update stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('duka_id', $dukaId)
                    ->first();

                if (! $stock) {
                    Log::error('Stock not found during sale creation', [
                        'user_id'    => $user->id,
                        'product_id' => $item['product_id'],
                        'duka_id'    => $dukaId,
                    ]);
                    throw new \Exception("Stock record not found for product {$product->name}");
                }

                $previousQuantity = $stock->quantity;
                $newQuantity      = $previousQuantity - $item['quantity'];

                // Update stock quantity
                $stock->update(['quantity' => $newQuantity]);

                Log::info('Stock updated for sale', [
                    'user_id'           => $user->id,
                    'stock_id'          => $stock->id,
                    'product_id'        => $item['product_id'],
                    'previous_quantity' => $previousQuantity,
                    'new_quantity'      => $newQuantity,
                    'quantity_sold'     => $item['quantity'],
                ]);

                // Record stock movement
                $stockMovement = StockMovement::create([
                    'stock_id'          => $stock->id,
                    'user_id'           => $user->id,
                    'type'              => 'remove',
                    'quantity_change'   => -$item['quantity'],
                    'previous_quantity' => $previousQuantity,
                    'new_quantity'      => $newQuantity,
                    'reason'            => 'Sale #' . $sale->id,
                ]);

                Log::info('Stock movement recorded', [
                    'user_id'           => $user->id,
                    'stock_movement_id' => $stockMovement->id,
                    'stock_id'          => $stock->id,
                    'quantity_change'   => -$item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data'    => [
                    'sale' => [
                        'id'              => $sale->id,
                        'total_amount'    => $sale->total_amount,
                        'discount_amount' => $sale->discount_amount,
                        'profit_loss'     => $sale->profit_loss,
                        'is_loan'         => $sale->is_loan,
                        'due_date'        => $sale->due_date,
                        'item_count'      => $sale->saleItems->count(),
                        'created_at'      => $sale->created_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Get Sale Details API
    public function apiGetSale($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->toArray();

        $sale = Sale::where('id', $id)
            ->whereIn('tenant_id', $tenantIds)
            ->whereIn('duka_id', $dukaIds)
            ->with(['customer', 'duka', 'saleItems.product', 'loanPayments.user'])
            ->firstOrFail();

        // Format sale items
        $formattedItems = $sale->saleItems->map(function ($item) {
            return [
                'id'              => $item->id,
                'product_id'      => $item->product_id,
                'product_name'    => $item->product->name,
                'product_sku'     => $item->product->sku,
                'quantity'        => $item->quantity,
                'unit_price'      => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'total'           => $item->total,
            ];
        });

        // Format loan payments
        $formattedPayments = $sale->loanPayments->map(function ($payment) {
            return [
                'id'           => $payment->id,
                'amount'       => $payment->amount,
                'payment_date' => $payment->payment_date,
                'notes'        => $payment->notes,
                'recorded_by'  => $payment->user ? [
                    'id'   => $payment->user->id,
                    'name' => $payment->user->name,
                ] : null,
                'created_at'   => $payment->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'sale' => [
                    'id'                => $sale->id,
                    'tenant_id'         => $sale->tenant_id,
                    'duka_id'           => $sale->duka_id,
                    'duka_name'         => $sale->duka->name,
                    'customer_id'       => $sale->customer_id,
                    'customer'          => $sale->customer ? [
                        'id'    => $sale->customer->id,
                        'name'  => $sale->customer->name,
                        'phone' => $sale->customer->phone,
                        'email' => $sale->customer->email,
                    ] : null,
                    'total_amount'      => $sale->total_amount,
                    'discount_amount'   => $sale->discount_amount,
                    'profit_loss'       => $sale->profit_loss,
                    'is_loan'           => $sale->is_loan,
                    'due_date'          => $sale->due_date,
                    'discount_reason'   => $sale->discount_reason,
                    'payment_status'    => $sale->payment_status,
                    'total_payments'    => $sale->total_payments,
                    'remaining_balance' => $sale->remaining_balance,
                    'items'             => $formattedItems,
                    'payments'          => $formattedPayments,
                    'created_at'        => $sale->created_at,
                    'updated_at'        => $sale->updated_at,
                ],
            ],
        ]);
    }

    // Get Sale Invoice Data API
    public function apiGetSaleInvoice($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->toArray();

        $sale = Sale::where('id', $id)
            ->whereIn('tenant_id', $tenantIds)
            ->whereIn('duka_id', $dukaIds)
            ->with(['customer', 'duka', 'saleItems.product', 'tenant'])
            ->firstOrFail();

        // Format sale items for invoice
        $formattedItems = $sale->saleItems->map(function ($item) {
            return [
                'id'              => $item->id,
                'product_name'    => $item->product->name,
                'product_sku'     => $item->product->sku,
                'quantity'        => $item->quantity,
                'unit_price'      => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'total'           => $item->total,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'invoice' => [
                    'sale_id'           => $sale->id,
                    'invoice_number'    => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    'sale_date'         => $sale->created_at->format('Y-m-d H:i:s'),
                    'due_date'          => $sale->due_date,
                    'tenant'            => [
                        'name' => $sale->tenant->name ?? 'N/A',
                    ],
                    'duka'              => [
                        'name'     => $sale->duka->name,
                        'location' => $sale->duka->location,
                    ],
                    'customer'          => $sale->customer ? [
                        'name'  => $sale->customer->name,
                        'phone' => $sale->customer->phone,
                        'email' => $sale->customer->email,
                    ] : null,
                    'items'             => $formattedItems,
                    'subtotal'          => $sale->total_amount + $sale->discount_amount,
                    'discount_amount'   => $sale->discount_amount,
                    'total_amount'      => $sale->total_amount,
                    'profit_loss'       => $sale->profit_loss,
                    'is_loan'           => $sale->is_loan,
                    'payment_status'    => $sale->payment_status,
                    'total_payments'    => $sale->total_payments,
                    'remaining_balance' => $sale->remaining_balance,
                    'discount_reason'   => $sale->discount_reason,
                ],
            ],
        ]);
    }

    // Customer API Methods for Officers

    // Get Customers API
    public function apiGetCustomers(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas for filtering
        $assignedDukas = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        $dukaIds = $assignedDukas->pluck('duka_id')->toArray();

        $query = Customer::whereIn('tenant_id', $tenantIds)
            ->with(['duka', 'sales', 'creator']);

        // Apply filters
        if ($request->has('search') && ! empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('duka_id') && ! empty($request->duka_id)) {
            // Verify officer is assigned to this duka
            if (in_array($request->duka_id, $dukaIds)) {
                $query->where('duka_id', $request->duka_id);
            }
        }

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Sort by latest first
        $query->orderBy('created_at', 'desc');

        $customers = $query->paginate($request->get('per_page', 15));

        // Format response
        $formattedCustomers = $customers->getCollection()->map(function ($customer) {
            return [
                'id'             => $customer->id,
                'tenant_id'      => $customer->tenant_id,
                'duka_id'        => $customer->duka_id,
                'duka_name'      => $customer->duka ? $customer->duka->name : null,
                'name'           => $customer->name,
                'email'          => $customer->email,
                'phone'          => $customer->phone,
                'address'        => $customer->address,
                'status'         => $customer->status,
                'total_sales'    => $customer->sales->count(),
                'total_amount'   => $customer->sales->sum('total_amount'),
                'last_sale_date' => $customer->sales->max('created_at'),
                'created_by'     => $customer->created_by,
                'creator_name'   => $customer->creator ? $customer->creator->name : null,
                'created_at'     => $customer->created_at,
                'updated_at'     => $customer->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'customers'  => $formattedCustomers,
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page'    => $customers->lastPage(),
                    'per_page'     => $customers->perPage(),
                    'total'        => $customers->total(),
                    'from'         => $customers->firstItem(),
                    'to'           => $customers->lastItem(),
                ],
                'summary'    => [
                    'total_customers'    => $customers->total(),
                    'total_sales_amount' => $formattedCustomers->sum('total_amount'),
                    'active_customers'   => $formattedCustomers->where('status', 'active')->count(),
                    'inactive_customers' => $formattedCustomers->where('status', 'inactive')->count(),
                ],
            ],
        ]);
    }

    // Create Customer API
    public function apiCreateCustomer(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'duka_id' => 'nullable|exists:dukas,id',
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|unique:customers,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status'  => 'nullable|in:active,inactive',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // If duka_id is provided, verify officer is assigned to it
        if ($request->duka_id) {
            $dukaAssignment = TenantOfficer::where('officer_id', $user->id)
                ->where('duka_id', $request->duka_id)
                ->where('status', true)
                ->exists();

            if (! $dukaAssignment) {
                return response()->json(['error' => 'You are not assigned to this duka'], 403);
            }
        }

        $customer = Customer::create([
            'tenant_id'  => $tenantId,
            'duka_id'    => $request->duka_id,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'status'     => $request->status ?? 'active',
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data'    => [
                'customer' => [
                    'id'           => $customer->id,
                    'tenant_id'    => $customer->tenant_id,
                    'duka_id'      => $customer->duka_id,
                    'duka_name'    => $customer->duka ? $customer->duka->name : null,
                    'name'         => $customer->name,
                    'email'        => $customer->email,
                    'phone'        => $customer->phone,
                    'address'      => $customer->address,
                    'status'       => $customer->status,
                    'total_sales'  => 0,
                    'total_amount' => 0,
                    'created_by'   => $customer->created_by,
                    'creator_name' => $customer->creator->name,
                    'created_at'   => $customer->created_at,
                ],
            ],
        ], 201);
    }

    // Get Customer Details API
    public function apiGetCustomer($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $customer = Customer::where('id', $id)
            ->whereIn('tenant_id', $tenantIds)
            ->with(['duka', 'sales' => function ($query) {
                $query->with('saleItems.product')->latest()->take(10);
            }, 'creator'])
            ->firstOrFail();

        // Format recent sales
        $formattedSales = $customer->sales->map(function ($sale) {
            return [
                'id'              => $sale->id,
                'total_amount'    => $sale->total_amount,
                'discount_amount' => $sale->discount_amount,
                'is_loan'         => $sale->is_loan,
                'payment_status'  => $sale->payment_status,
                'created_at'      => $sale->created_at,
                'items_count'     => $sale->saleItems->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'customer' => [
                    'id'                  => $customer->id,
                    'tenant_id'           => $customer->tenant_id,
                    'duka_id'             => $customer->duka_id,
                    'duka_name'           => $customer->duka ? $customer->duka->name : null,
                    'name'                => $customer->name,
                    'email'               => $customer->email,
                    'phone'               => $customer->phone,
                    'address'             => $customer->address,
                    'status'              => $customer->status,
                    'total_sales'         => $customer->sales->count(),
                    'total_amount'        => $customer->sales->sum('total_amount'),
                    'total_loans'         => $customer->sales->where('is_loan', true)->count(),
                    'outstanding_balance' => $customer->sales->where('is_loan', true)->sum(function ($sale) {
                        return $sale->remaining_balance;
                    }),
                    'last_sale_date'      => $customer->sales->max('created_at'),
                    'created_by'          => $customer->created_by,
                    'creator_name'        => $customer->creator ? $customer->creator->name : null,
                    'recent_sales'        => $formattedSales,
                    'created_at'          => $customer->created_at,
                    'updated_at'          => $customer->updated_at,
                ],
            ],
        ]);
    }

    // Update Customer API
    public function apiUpdateCustomer(Request $request, $id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $customer = Customer::where('id', $id)
            ->whereIn('tenant_id', $tenantIds)
            ->firstOrFail();

        $request->validate([
            'duka_id' => 'nullable|exists:dukas,id',
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|unique:customers,email,' . $id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status'  => 'nullable|in:active,inactive',
        ]);

        // If duka_id is provided, verify officer is assigned to it
        if ($request->duka_id) {
            $dukaAssignment = TenantOfficer::where('officer_id', $user->id)
                ->where('duka_id', $request->duka_id)
                ->where('status', true)
                ->exists();

            if (! $dukaAssignment) {
                return response()->json(['error' => 'You are not assigned to this duka'], 403);
            }
        }

        $customer->update([
            'duka_id' => $request->duka_id,
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'status'  => $request->status ?? $customer->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data'    => [
                'customer' => [
                    'id'           => $customer->id,
                    'tenant_id'    => $customer->tenant_id,
                    'duka_id'      => $customer->duka_id,
                    'duka_name'    => $customer->duka ? $customer->duka->name : null,
                    'name'         => $customer->name,
                    'email'        => $customer->email,
                    'phone'        => $customer->phone,
                    'address'      => $customer->address,
                    'status'       => $customer->status,
                    'total_sales'  => $customer->sales->count(),
                    'total_amount' => $customer->sales->sum('total_amount'),
                    'updated_at'   => $customer->updated_at,
                ],
            ],
        ]);
    }

    // Generate unique SKU for products
    private function generateProductSKU($productName, $stockLevel)
    {
        // Clean the product name: remove special characters, convert to uppercase
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productName));

        // Take first 3-4 characters of the name
        $namePrefix = substr($cleanName, 0, 4);

        // Add stock level (padded to 3 digits)
        $stockPart = str_pad($stockLevel, 3, '0', STR_PAD_LEFT);

        // Add a random 2-digit number for uniqueness
        $randomPart = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);

        // Combine: NAME-STOCK-RANDOM
        $sku = $namePrefix . '-' . $stockPart . '-' . $randomPart;

        // Ensure uniqueness by checking against existing SKUs
        $counter     = 1;
        $originalSku = $sku;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    public function apiGetTenantAccount(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Get tenant account information
        $tenantAccount = TenantAccount::where('tenant_id', $tenantId)
            ->first();

        if (! $tenantAccount) {
            return response()->json(['error' => 'Tenant account not found'], 404);
        }

        // Format tenant account data
        $formattedAccount = [
            'id'           => $tenantAccount->id,
            'tenant_id'    => $tenantAccount->tenant_id,
            'company_name' => $tenantAccount->company_name,
            'logo_url'     => $tenantAccount->logo_url,
            'logo'         => $tenantAccount->logo,
            'phone'        => $tenantAccount->phone,
            'email'        => $tenantAccount->email,
            'address'      => $tenantAccount->address,
            'currency'     => $tenantAccount->currency,
            'timezone'     => $tenantAccount->timezone,
            'website'      => $tenantAccount->website,
            'description'  => $tenantAccount->description,
            'created_at'   => $tenantAccount->created_at,
            'updated_at'   => $tenantAccount->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'tenant_account' => $formattedAccount,
            ],
        ]);
    }

    /**
     * Update tenant account information
     *
     * This method allows officers to update the tenant account details for their assigned tenant.
     * Only certain fields are updatable by officers.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateTenantAccount(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate request data
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'address'      => 'nullable|string',
            'currency'     => 'nullable|string|max:10',
            'timezone'     => 'nullable|string|max:50',
            'website'      => 'nullable|url|max:255',
            'description'  => 'nullable|string|max:1000',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Get or create tenant account
        $tenantAccount = TenantAccount::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['company_name' => 'Default Company']// Default company name
        );

        // Update only the provided fields
        $updateData = [];
        if ($request->filled('company_name')) {
            $updateData['company_name'] = $request->company_name;
        }

        if ($request->filled('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->filled('address')) {
            $updateData['address'] = $request->address;
        }

        if ($request->filled('currency')) {
            $updateData['currency'] = $request->currency;
        }

        if ($request->filled('timezone')) {
            $updateData['timezone'] = $request->timezone;
        }

        if ($request->filled('website')) {
            $updateData['website'] = $request->website;
        }

        if ($request->filled('description')) {
            $updateData['description'] = $request->description;
        }

        $tenantAccount->update($updateData);

        // Format updated tenant account data
        $formattedAccount = [
            'id'           => $tenantAccount->id,
            'tenant_id'    => $tenantAccount->tenant_id,
            'company_name' => $tenantAccount->company_name,
            'logo_url'     => $tenantAccount->logo_url,
            'logo'         => $tenantAccount->logo,
            'phone'        => $tenantAccount->phone,
            'email'        => $tenantAccount->email,
            'address'      => $tenantAccount->address,
            'currency'     => $tenantAccount->currency,
            'timezone'     => $tenantAccount->timezone,
            'website'      => $tenantAccount->website,
            'description'  => $tenantAccount->description,
            'updated_at'   => $tenantAccount->updated_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Tenant account updated successfully',
            'data'    => [
                'tenant_account' => $formattedAccount,
            ],
        ]);
    }

    /**
     * Get tenant logo URL
     *
     * This method returns the full URL for the tenant's logo image.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetTenantLogo(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Get tenant account information
        $tenantAccount = TenantAccount::where('tenant_id', $tenantId)
            ->first();

        if (! $tenantAccount) {
            return response()->json(['error' => 'Tenant account not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'logo_url'  => $tenantAccount->logo_url,
                'has_logo'  => ! empty($tenantAccount->logo),
                'logo_path' => $tenantAccount->logo,
            ],
        ]);
    }

    /**
     * Upload tenant logo
     *
     * This method allows officers to upload a logo for their tenant.
     * Supports both file upload and base64 image data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUploadTenantLogo(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate image input (mutually exclusive)
        $hasImageFile   = $request->hasFile('logo');
        $hasImageBase64 = $request->filled('logo_base64');
        $hasImageUrl    = $request->filled('logo_url');

        if (($hasImageFile || $hasImageBase64) && $hasImageUrl) {
            return response()->json([
                'error' => 'Please choose only one: upload an image file/base64 OR provide an image URL.',
            ], 400);
        }

        if ($hasImageFile && $hasImageBase64) {
            return response()->json([
                'error' => 'Please choose only one: upload a file OR send base64 image data.',
            ], 400);
        }

        if (! $hasImageFile && ! $hasImageBase64 && ! $hasImageUrl) {
            return response()->json([
                'error' => 'Please provide an image: upload a file, base64 data, or URL.',
            ], 400);
        }

        // Validate uploaded file (if any)
        if ($hasImageFile) {
            $logo = $request->file('logo');

            if (! $logo->isValid()) {
                return response()->json([
                    'error' => 'Invalid logo file uploaded. Please try again.',
                ], 400);
            }

            if ($logo->getSize() > 2 * 1024 * 1024) { // 2MB limit
                return response()->json([
                    'error' => 'Logo is too large. Maximum size is 2MB.',
                ], 400);
            }

            $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (! in_array($logo->getMimeType(), $allowed)) {
                return response()->json([
                    'error' => 'Invalid logo format. Only JPEG, PNG, JPG, and GIF are allowed.',
                ], 400);
            }
        }

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Get or create tenant account
        $tenantAccount = TenantAccount::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['company_name' => 'Default Company']// Default company name
        );

        // Handle logo upload
        $logoPath = null;

        try {
            if ($hasImageFile) {
                // Handle file upload
                $logo       = $request->file('logo');
                $logoName   = 'tenant_logo_' . $tenantId . '_' . time() . '.' . $logo->getClientOriginalExtension();
                $uploadPath = public_path('storage/account');

                if (! file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $logo->move($uploadPath, $logoName);
                $logoPath = $logoName;
            } elseif ($hasImageBase64) {
                // Handle base64 image
                $imageData = $request->logo_base64;
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $extension = $matches[1];
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                } else {
                    $extension = 'png';
                }

                $decoded = base64_decode($imageData);
                if ($decoded === false || strlen($decoded) > 2 * 1024 * 1024) {
                    return response()->json(['error' => 'Invalid or oversized base64 image.'], 400);
                }

                $logoName   = 'tenant_logo_' . $tenantId . '_' . time() . '.' . $extension;
                $uploadPath = public_path('storage/account');

                if (! file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                file_put_contents($uploadPath . '/' . $logoName, $decoded);
                $logoPath = $logoName;
            } elseif ($hasImageUrl) {
                // Store URL directly
                $logoPath = $request->logo_url;
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process logo. Please try again.',
            ], 500);
        }

        // Delete old logo if exists and is a local file
        if ($tenantAccount->logo && ! filter_var($tenantAccount->logo, FILTER_VALIDATE_URL)) {
            $oldLogoPath = public_path('storage/account/' . $tenantAccount->logo);
            if (file_exists($oldLogoPath)) {
                unlink($oldLogoPath);
            }
        }

        // Update tenant account with new logo
        $tenantAccount->update(['logo' => $logoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant logo uploaded successfully',
            'data'    => [
                'logo_url'  => $tenantAccount->logo_url,
                'logo_path' => $logoPath,
                'has_logo'  => true,
            ],
        ]);
    }

    // Loan Payments API Methods for Officers

    /**
     * Get Loan Payments List
     *
     * Retrieves a paginated list of loan payments with filtering and search capabilities.
     * Officers can only access payments for sales in their assigned tenant and dukas.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetLoanPayments(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id')
            ->toArray();

        $query = \App\Models\LoanPayment::with(['sale.customer', 'sale.duka', 'user'])
            ->whereHas('sale', function ($q) use ($tenantIds, $dukaIds) {
                $q->whereIn('tenant_id', $tenantIds)
                    ->whereIn('duka_id', $dukaIds);
            });

        // Apply filters
        if ($request->has('search') && ! empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('sale.customer', function ($customerQuery) use ($request) {
                        $customerQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->has('sale_id') && ! empty($request->sale_id)) {
            $query->where('sale_id', $request->sale_id);
        }

        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->whereHas('sale', function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // Date range filters
        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Amount filters
        if ($request->has('min_amount') && ! empty($request->min_amount)) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->has('max_amount') && ! empty($request->max_amount)) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Sort by latest first
        $query->orderBy('created_at', 'desc');

        $payments = $query->paginate($request->get('per_page', 15));

        // Format response
        $formattedPayments = $payments->getCollection()->map(function ($payment) {
            return [
                'id'           => $payment->id,
                'sale_id'      => $payment->sale_id,
                'amount'       => $payment->amount,
                'payment_date' => $payment->payment_date,
                'notes'        => $payment->notes,
                'user_id'      => $payment->user_id,
                'user'         => $payment->user ? [
                    'id'    => $payment->user->id,
                    'name'  => $payment->user->name,
                    'email' => $payment->user->email,
                ] : null,
                'sale'         => [
                    'id'                => $payment->sale->id,
                    'customer'          => $payment->sale->customer ? [
                        'id'    => $payment->sale->customer->id,
                        'name'  => $payment->sale->customer->name,
                        'phone' => $payment->sale->customer->phone,
                    ] : null,
                    'total_amount'      => $payment->sale->total_amount,
                    'remaining_balance' => $payment->sale->remaining_balance,
                ],
                'created_at'   => $payment->created_at,
                'updated_at'   => $payment->updated_at,
            ];
        });

        // Calculate summary statistics
        $totalAmount    = $formattedPayments->sum('amount');
        $totalPayments  = $formattedPayments->count();
        $averagePayment = $totalPayments > 0 ? round($totalAmount / $totalPayments, 2) : 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'payments'   => $formattedPayments,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page'    => $payments->lastPage(),
                    'per_page'     => $payments->perPage(),
                    'total'        => $payments->total(),
                    'from'         => $payments->firstItem(),
                    'to'           => $payments->lastItem(),
                ],
                'summary'    => [
                    'total_payments'  => $totalPayments,
                    'total_amount'    => $totalAmount,
                    'average_payment' => $averagePayment,
                ],
            ],
        ]);
    }

    /**
     * Create Loan Payment
     *
     * Records a new loan payment for a sale, updating the sale's payment status and remaining balance.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCreateLoanPayment(Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'sale_id'      => 'required|exists:sales,id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date|before_or_equal:today',
            'notes'        => 'nullable|string|max:1000',
        ]);

        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $tenantId = $assignment->tenant_id;

        // Verify sale belongs to officer's tenant and dukas
        $sale = \App\Models\Sale::where('id', $request->sale_id)
            ->where('tenant_id', $tenantId)
            ->where('is_loan', true)
            ->first();

        if (! $sale) {
            return response()->json(['error' => 'Sale not found or not a loan sale'], 404);
        }

        // Verify officer is assigned to the duka
        $dukaAssignment = TenantOfficer::where('officer_id', $user->id)
            ->where('duka_id', $sale->duka_id)
            ->where('status', true)
            ->exists();

        if (! $dukaAssignment) {
            return response()->json(['error' => 'You are not assigned to this duka'], 403);
        }

        // Check if payment amount exceeds remaining balance
        if ($request->amount > $sale->remaining_balance) {
            return response()->json([
                'error' => 'Payment amount cannot exceed remaining balance of ' . $sale->remaining_balance,
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create loan payment
            $payment = \App\Models\LoanPayment::create([
                'sale_id'      => $request->sale_id,
                'amount'       => $request->amount,
                'payment_date' => $request->payment_date ?? now(),
                'notes'        => $request->notes,
                'user_id'      => $user->id,
            ]);

            // Update sale payment information
            $newTotalPayments    = $sale->total_payments + $request->amount;
            $newRemainingBalance = $sale->remaining_balance - $request->amount;

            // Determine payment status
            $paymentStatus = 'partial';
            if ($newRemainingBalance <= 0) {
                $paymentStatus       = 'paid';
                $newRemainingBalance = 0;
            }

            $sale->update([
                'total_payments'    => $newTotalPayments,
                'remaining_balance' => $newRemainingBalance,
                'payment_status'    => $paymentStatus,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan payment recorded successfully',
                'data'    => [
                    'payment'     => [
                        'id'           => $payment->id,
                        'sale_id'      => $payment->sale_id,
                        'amount'       => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'notes'        => $payment->notes,
                        'user_id'      => $payment->user_id,
                        'created_at'   => $payment->created_at,
                    ],
                    'sale_update' => [
                        'total_payments'    => $newTotalPayments,
                        'remaining_balance' => $newRemainingBalance,
                        'payment_status'    => $paymentStatus,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Loan Payment Details
     *
     * Retrieves detailed information about a specific loan payment.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetLoanPayment($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $payment = \App\Models\LoanPayment::with(['sale.customer', 'sale.duka', 'user'])
            ->where('id', $id)
            ->whereHas('sale', function ($q) use ($tenantIds) {
                $q->whereIn('tenant_id', $tenantIds);
            })
            ->first();

        if (! $payment) {
            return response()->json(['error' => 'Loan payment not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'payment' => [
                    'id'           => $payment->id,
                    'sale_id'      => $payment->sale_id,
                    'amount'       => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'notes'        => $payment->notes,
                    'user_id'      => $payment->user_id,
                    'user'         => $payment->user ? [
                        'id'    => $payment->user->id,
                        'name'  => $payment->user->name,
                        'email' => $payment->user->email,
                    ] : null,
                    'sale'         => [
                        'id'                => $payment->sale->id,
                        'customer'          => $payment->sale->customer ? [
                            'id'    => $payment->sale->customer->id,
                            'name'  => $payment->sale->customer->name,
                            'phone' => $payment->sale->customer->phone,
                        ] : null,
                        'total_amount'      => $payment->sale->total_amount,
                        'total_payments'    => $payment->sale->total_payments,
                        'remaining_balance' => $payment->sale->remaining_balance,
                        'payment_status'    => $payment->sale->payment_status,
                    ],
                    'created_at'   => $payment->created_at,
                    'updated_at'   => $payment->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Update Loan Payment
     *
     * Updates an existing loan payment record and adjusts sale balances accordingly.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateLoanPayment(Request $request, $id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount'       => 'nullable|numeric|min:0.01',
            'payment_date' => 'nullable|date|before_or_equal:today',
            'notes'        => 'nullable|string|max:1000',
        ]);

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $payment = \App\Models\LoanPayment::with('sale')
            ->where('id', $id)
            ->whereHas('sale', function ($q) use ($tenantIds) {
                $q->whereIn('tenant_id', $tenantIds);
            })
            ->first();

        if (! $payment) {
            return response()->json(['error' => 'Loan payment not found'], 404);
        }

        $sale      = $payment->sale;
        $oldAmount = $payment->amount;

        DB::beginTransaction();
        try {
            // Update payment
            $updateData = [];
            if ($request->has('amount')) {
                $updateData['amount'] = $request->amount;
            }

            if ($request->has('payment_date')) {
                $updateData['payment_date'] = $request->payment_date;
            }

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            $payment->update($updateData);

            // Recalculate sale balances if amount changed
            if ($request->has('amount') && $request->amount != $oldAmount) {
                $amountDifference = $request->amount - $oldAmount;

                $newTotalPayments    = $sale->total_payments + $amountDifference;
                $newRemainingBalance = $sale->remaining_balance - $amountDifference;

                // Ensure remaining balance doesn't go negative
                if ($newRemainingBalance < 0) {
                    $newRemainingBalance = 0;
                }

                // Determine payment status
                $paymentStatus = 'partial';
                if ($newRemainingBalance <= 0) {
                    $paymentStatus       = 'paid';
                    $newRemainingBalance = 0;
                }

                $sale->update([
                    'total_payments'    => $newTotalPayments,
                    'remaining_balance' => $newRemainingBalance,
                    'payment_status'    => $paymentStatus,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan payment updated successfully',
                'data'    => [
                    'payment'     => [
                        'id'           => $payment->id,
                        'sale_id'      => $payment->sale_id,
                        'amount'       => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'notes'        => $payment->notes,
                        'updated_at'   => $payment->updated_at,
                    ],
                    'sale_update' => isset($newTotalPayments) ? [
                        'total_payments'    => $newTotalPayments,
                        'remaining_balance' => $newRemainingBalance,
                        'payment_status'    => $paymentStatus,
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Loan Payment
     *
     * Deletes a loan payment record and updates the associated sale's payment status.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiDeleteLoanPayment($id)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        $payment = \App\Models\LoanPayment::with('sale')
            ->where('id', $id)
            ->whereHas('sale', function ($q) use ($tenantIds) {
                $q->whereIn('tenant_id', $tenantIds);
            })
            ->first();

        if (! $payment) {
            return response()->json(['error' => 'Loan payment not found'], 404);
        }

        $sale = $payment->sale;

        DB::beginTransaction();
        try {
            // Update sale balances
            $newTotalPayments    = $sale->total_payments - $payment->amount;
            $newRemainingBalance = $sale->remaining_balance + $payment->amount;

            // Determine payment status
            $paymentStatus = 'pending';
            if ($newTotalPayments > 0 && $newRemainingBalance > 0) {
                $paymentStatus = 'partial';
            } elseif ($newTotalPayments >= $sale->total_amount) {
                $paymentStatus       = 'paid';
                $newRemainingBalance = 0;
            }

            $sale->update([
                'total_payments'    => $newTotalPayments,
                'remaining_balance' => $newRemainingBalance,
                'payment_status'    => $paymentStatus,
            ]);

            // Delete the payment
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan payment deleted successfully',
                'data'    => [
                    'payment_id'  => $id,
                    'sale_update' => [
                        'total_payments'    => $newTotalPayments,
                        'remaining_balance' => $newRemainingBalance,
                        'payment_status'    => $paymentStatus,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Loan Payments for Sale
     *
     * Retrieves all loan payments associated with a specific sale.
     *
     * @param int $saleId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetLoanPaymentsBySale($saleId, Request $request)
    {
        $user = auth()->user();

        // Verify user is an officer
        if (! $user->hasRole('officer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get tenant IDs for this officer
        $tenantIds = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        if (empty($tenantIds)) {
            return response()->json(['error' => 'No active assignments found'], 403);
        }

        // Verify sale belongs to officer's tenant and dukas
        $sale = \App\Models\Sale::where('id', $saleId)
            ->whereIn('tenant_id', $tenantIds)
            ->where('is_loan', true)
            ->first();

        if (! $sale) {
            return response()->json(['error' => 'Sale not found or not a loan sale'], 404);
        }

        // Verify officer is assigned to the duka
        $dukaAssignment = TenantOfficer::where('officer_id', $user->id)
            ->where('duka_id', $sale->duka_id)
            ->where('status', true)
            ->exists();

        if (! $dukaAssignment) {
            return response()->json(['error' => 'You are not assigned to this duka'], 403);
        }

        $payments = \App\Models\LoanPayment::with('user')
            ->where('sale_id', $saleId)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format payments
        $formattedPayments = $payments->getCollection()->map(function ($payment) {
            return [
                'id'           => $payment->id,
                'amount'       => $payment->amount,
                'payment_date' => $payment->payment_date,
                'notes'        => $payment->notes,
                'user'         => $payment->user ? [
                    'id'   => $payment->user->id,
                    'name' => $payment->user->name,
                ] : null,
                'created_at'   => $payment->created_at,
            ];
        });

        // Calculate summary
        $totalPaymentsCount = $formattedPayments->count();
        $totalAmountPaid    = $formattedPayments->sum('amount');
        $firstPaymentDate   = $formattedPayments->last()?->payment_date;
        $lastPaymentDate    = $formattedPayments->first()?->payment_date;

        return response()->json([
            'success' => true,
            'data'    => [
                'sale'       => [
                    'id'                => $sale->id,
                    'total_amount'      => $sale->total_amount,
                    'total_payments'    => $sale->total_payments,
                    'remaining_balance' => $sale->remaining_balance,
                    'payment_status'    => $sale->payment_status,
                ],
                'payments'   => $formattedPayments,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page'    => $payments->lastPage(),
                    'per_page'     => $payments->perPage(),
                    'total'        => $payments->total(),
                    'from'         => $payments->firstItem(),
                    'to'           => $payments->lastItem(),
                ],
                'summary'    => [
                    'total_payments_count' => $totalPaymentsCount,
                    'total_amount_paid'    => $totalAmountPaid,
                    'remaining_balance'    => $sale->remaining_balance,
                    'first_payment_date'   => $firstPaymentDate,
                    'last_payment_date'    => $lastPaymentDate,
                ],
            ],
        ]);
    }

    public function storeApiforSales(Request $request)
    {
        Log::info('storeApiforSales function called', [
            'user_id'      => auth()->id(),
            'user_email'   => auth()->user()->email ?? null,
            'request_data' => $request->all(),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        $validated = $request->validate([
            'sale_id'      => 'required|exists:sales,id',
            'amount'       => 'required|numeric',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        Log::info('Request validation passed', [
            'user_id'        => auth()->id(),
            'validated_data' => $validated,
            'sale_id'        => $validated['sale_id'],
            'amount'         => $validated['amount'],
            'payment_date'   => $validated['payment_date'],
        ]);

        // Use auth() helper to get the ID and merge it with validated data
        $loanPayment = LoanPayment::create(array_merge(
            $validated,
            ['user_id' => auth()->id()]
        ));

        Log::info('Loan payment created successfully', [
            'user_id'         => auth()->id(),
            'loan_payment_id' => $loanPayment->id,
            'sale_id'         => $loanPayment->sale_id,
            'amount'          => $loanPayment->amount,
            'payment_date'    => $loanPayment->payment_date,
        ]);

        Log::info('storeApiforSales function completed', [
            'user_id'         => auth()->id(),
            'loan_payment_id' => $loanPayment->id,
            'response_status' => 201,
        ]);

        return response()->json([
            'success'     => true,
            'loanPayment' => $loanPayment,
        ], 201);
    }

    /**
     * Get product items by product ID with filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Create a new product item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

}

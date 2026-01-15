<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Sale;
use App\Models\Duka;
use App\Models\TenantOfficer;
use App\Models\TenantAccount;
use App\Models\Plan;
use App\Models\Feature;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\CashFlow;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        //
    }

    /**
     * Get tenant details including dukas and summary for API.
     */
    public function apiGetDetails(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Load tenant with relationships
        $tenant->load(['dukas', 'customers', 'productCategories']);

        // Calculate summary for dukas
        $totalSales = Sale::whereHas('duka', function($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id);
        });

        $dukasSummary = [
            'total_dukas' => $tenant->dukas->count(),
            'active_dukas' => $tenant->dukas->where('status', 'active')->count(),
            'inactive_dukas' => $tenant->dukas->where('status', 'inactive')->count(),
            'total_sales' => $totalSales->count(),
            'total_sales_revenue' => $totalSales->sum('total_amount'),
            'total_loans' => $totalSales->where('is_loan', true)->count(),
            'total_loans_amount' => $totalSales->where('is_loan', true)->sum('total_amount'),
            'total_profit_loss' => $totalSales->sum('profit_loss'),
            'today_total_sales' => $totalSales->whereDate('created_at', today())->count(),
            'today_total_revenue' => $totalSales->whereDate('created_at', today())->sum('total_amount'),
            'total_customers' => $tenant->customers->count(),
            'total_product_categories' => $tenant->productCategories->count(),
        ];

        // Get dukas with their own summary
        $dukas = $tenant->dukas->map(function($duka) {
            $salesCount = $duka->sales()->count();
            $totalRevenue = $duka->sales()->sum('total_amount');
            $customersCount = $duka->customers()->count();
            $productsCount = $duka->products()->count();

            // Get loans for this duka
            $loans = $duka->sales()->where('is_loan', true)->with(['customer', 'loanPayments'])->get();

            // Get today's sales
            $todaySales = $duka->sales()->whereDate('created_at', today())->get();

            // Calculate profit/loss
            $totalProfitLoss = $duka->sales()->sum('profit_loss');

            return [
                'id' => $duka->id,
                'name' => $duka->name,
                'location' => $duka->location,
                'manager_name' => $duka->manager_name,
                'latitude' => $duka->latitude,
                'longitude' => $duka->longitude,
                'status' => $duka->status,
                'created_at' => $duka->created_at,
                'updated_at' => $duka->updated_at,
                'summary' => [
                    'total_sales' => $salesCount,
                    'total_revenue' => $totalRevenue,
                    'total_customers' => $customersCount,
                    'total_products' => $productsCount,
                    'total_loans' => $loans->count(),
                    'today_sales_count' => $todaySales->count(),
                    'today_sales_revenue' => $todaySales->sum('total_amount'),
                    'total_profit_loss' => $totalProfitLoss,
                ],
                'loans' => $loans->map(function($loan) {
                    return [
                        'id' => $loan->id,
                        'customer_name' => $loan->customer->name ?? 'N/A',
                        'total_amount' => $loan->total_amount,
                        'remaining_balance' => $loan->remaining_balance,
                        'payment_status' => $loan->payment_status,
                        'due_date' => $loan->due_date,
                        'created_at' => $loan->created_at,
                    ];
                }),
                'today_sales' => $todaySales->map(function($sale) {
                    return [
                        'id' => $sale->id,
                        'customer_name' => $sale->customer->name ?? 'N/A',
                        'total_amount' => $sale->total_amount,
                        'profit_loss' => $sale->profit_loss,
                        'is_loan' => $sale->is_loan,
                        'created_at' => $sale->created_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                    'status' => $tenant->status,
                    'created_at' => $tenant->created_at,
                    'updated_at' => $tenant->updated_at,
                ],
                'dukas' => $dukas,
                'summary' => $dukasSummary,
            ]
        ]);
    }

    /**
     * Get products and related data for a specific duka.
     */
    public function apiGetDukaProducts(Request $request, $duka_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the duka belonging to the tenant
        $duka = $tenant->dukas()->find($duka_id);

        if (!$duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.'
            ], 404);
        }

        // Load related data
        $tenant->load(['productCategories']);
        $duka->load([
            'products.category',
            'products.items',
            'products.stocks',
            'customers',
            'sales.saleItems.product'
        ]);

        // Prepare the response data
        $data = [
            'duka' => [
                'id' => $duka->id,
                'name' => $duka->name,
                'location' => $duka->location,
                'status' => $duka->status,
            ],
            'categories' => $tenant->productCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'created_at' => $category->created_at,
                ];
            }),
            'products' => $duka->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'description' => $product->description,
                    'unit' => $product->unit,
                    'base_price' => $product->base_price,
                    'selling_price' => $product->selling_price,
                    'is_active' => $product->is_active,
                    'image' => $product->image_url,
                    'barcode' => $product->barcode,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'current_stock' => $product->current_stock,
                    'created_at' => $product->created_at,
                ];
            }),
            'customers' => $duka->customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'status' => $customer->status,
                    'created_at' => $customer->created_at,
                ];
            }),
            'product_items' => $duka->products->pluck('items')->flatten()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'qr_code' => $item->qr_code,
                    'status' => $item->status,
                    'stock_amount' => $item->stock_amount,
                    'created_at' => $item->created_at,
                ];
            }),
            'product_stock' => $duka->products->pluck('stocks')->flatten()->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'quantity' => $stock->quantity,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date?->format('Y-m-d'),
                    'created_at' => $stock->created_at,
                ];
            }),
            'sales' => $duka->sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'customer_name' => $sale->customer->name ?? 'N/A',
                    'total_amount' => $sale->total_amount,
                    'profit_loss' => $sale->profit_loss,
                    'is_loan' => $sale->is_loan,
                    'payment_status' => $sale->payment_status,
                    'due_date' => $sale->due_date,
                    'created_at' => $sale->created_at,
                    'sale_items' => $sale->saleItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total,
                        ];
                    }),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Create a new duka for the tenant.
     */
    public function apiCreateDuka(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can create dukas.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:active,inactive',
        ]);

        // Create the duka
        $duka = Duka::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'location' => $validated['location'],
            'manager_name' => $validated['manager_name'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Duka created successfully.',
            'data' => [
                'duka' => [
                    'id' => $duka->id,
                    'name' => $duka->name,
                    'location' => $duka->location,
                    'manager_name' => $duka->manager_name,
                    'latitude' => $duka->latitude,
                    'longitude' => $duka->longitude,
                    'status' => $duka->status,
                    'created_at' => $duka->created_at,
                ]
            ]
        ], 201);
    }

    /**
     * Get the plan for a specific duka.
     */
    public function apiGetDukaPlan(Request $request, $duka_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the duka belonging to the tenant
        $duka = $tenant->dukas()->find($duka_id);

        if (!$duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.'
            ], 404);
        }

        // Get the current plan for the duka
        $currentPlan = $duka->currentPlan();

        if (!$currentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'No active plan found for this duka.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'duka' => [
                    'id' => $duka->id,
                    'name' => $duka->name,
                ],
                'plan' => [
                    'id' => $currentPlan->id,
                    'name' => $currentPlan->name,
                    'description' => $currentPlan->description,
                    'price' => $currentPlan->price,
                    'duration_days' => $currentPlan->duration_days,
                    'features' => $currentPlan->features ?? [],
                ]
            ]
        ]);
    }

    /**
     * Create an officer and assign to a duka.
     */
    public function apiCreateOfficer(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can create officers.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'duka_id' => 'required|exists:dukas,id',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|max:50',
        ]);

        // Check if duka belongs to tenant
        $duka = $tenant->dukas()->find($validated['duka_id']);
        if (!$duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.'
            ], 404);
        }

        // Create the officer user
        $officer = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'officer',
            'status' => 'active',
        ]);

        // Assign officer role
        $officer->assignRole('officer');

        // Create tenant officer assignment
        TenantOfficer::create([
            'tenant_id' => $tenant->id,
            'duka_id' => $validated['duka_id'],
            'officer_id' => $officer->id,
            'role' => 'officer',
            'status' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Officer created and assigned successfully.',
            'data' => [
                'officer' => [
                    'id' => $officer->id,
                    'name' => $officer->name,
                    'email' => $officer->email,
                    'phone' => $officer->phone,
                    'status' => $officer->status,
                    'created_at' => $officer->created_at,
                ],
                'assignment' => [
                    'duka_id' => $validated['duka_id'],
                    'duka_name' => $duka->name,
                    'role' => $validated['role'] ?? 'officer',
                ]
            ]
        ], 201);
    }

    /**
     * Get all officers for the tenant.
     */
    public function apiGetOfficers(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Get all officers assigned to this tenant
        $officers = TenantOfficer::where('tenant_id', $tenant->id)
            ->with(['officer', 'duka'])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->officer->id,
                    'name' => $assignment->officer->name,
                    'email' => $assignment->officer->email,
                    'phone' => $assignment->officer->phone,
                    'status' => $assignment->officer->status,
                    'assignment' => [
                        'duka_id' => $assignment->duka_id,
                        'duka_name' => $assignment->duka->name ?? 'N/A',
                        'role' => $assignment->role,
                        'assignment_status' => $assignment->status,
                        'assigned_at' => $assignment->created_at,
                    ],
                    'created_at' => $assignment->officer->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'officers' => $officers,
                'total' => $officers->count(),
            ]
        ]);
    }

    /**
     * Update an officer's information and/or assignment.
     */
    public function apiUpdateOfficer(Request $request, $officer_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can update officers.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the officer
        $officer = \App\Models\User::find($officer_id);

        if (!$officer || !$officer->hasRole('officer')) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found.'
            ], 404);
        }

        // Check if officer is assigned to this tenant
        $assignment = TenantOfficer::where('tenant_id', $tenant->id)
            ->where('officer_id', $officer_id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Officer is not assigned to this tenant.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $officer_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'duka_id' => 'nullable|exists:dukas,id',
            'role' => 'nullable|string|max:50',
            'status' => 'nullable|boolean',
        ]);

        // Check if new duka belongs to tenant (if provided)
        if (isset($validated['duka_id'])) {
            $duka = $tenant->dukas()->find($validated['duka_id']);
            if (!$duka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duka not found or does not belong to this tenant.'
                ], 404);
            }
        }

        // Update officer user data
        $updateData = array_filter([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => isset($validated['status']) ? ($validated['status'] ? 'active' : 'inactive') : null,
        ]);

        if (!empty($updateData)) {
            $officer->update($updateData);
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $officer->update(['password' => Hash::make($validated['password'])]);
        }

        // Update assignment
        $assignmentUpdate = array_filter([
            'duka_id' => $validated['duka_id'] ?? null,
            'role' => $validated['role'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        if (!empty($assignmentUpdate)) {
            $assignment->update($assignmentUpdate);
        }

        return response()->json([
            'success' => true,
            'message' => 'Officer updated successfully.',
            'data' => [
                'officer' => [
                    'id' => $officer->id,
                    'name' => $officer->name,
                    'email' => $officer->email,
                    'phone' => $officer->phone,
                    'status' => $officer->status,
                ],
                'assignment' => [
                    'duka_id' => $assignment->duka_id,
                    'duka_name' => $assignment->duka->name ?? 'N/A',
                    'role' => $assignment->role,
                    'assignment_status' => $assignment->status,
                ]
            ]
        ]);
    }

    /**
     * Delete/remove an officer from the tenant.
     */
    public function apiDeleteOfficer(Request $request, $officer_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can delete officers.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the officer
        $officer = \App\Models\User::find($officer_id);

        if (!$officer || !$officer->hasRole('officer')) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found.'
            ], 404);
        }

        // Check if officer is assigned to this tenant
        $assignment = TenantOfficer::where('tenant_id', $tenant->id)
            ->where('officer_id', $officer_id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Officer is not assigned to this tenant.'
            ], 404);
        }

        // Remove the assignment (soft delete or deactivate)
        $assignment->update(['status' => false]);

        // Optionally deactivate the officer user
        $officer->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Officer removed successfully.',
            'data' => [
                'officer_id' => $officer_id,
                'status' => 'inactive',
            ]
        ]);
    }

    /**
     * Get tenant account information.
     */
    public function apiGetTenantAccount(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Get or create tenant account
        $tenantAccount = TenantAccount::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'company_name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_account' => [
                    'id' => $tenantAccount->id,
                    'tenant_id' => $tenantAccount->tenant_id,
                    'company_name' => $tenantAccount->company_name,
                    'logo_url' => $tenantAccount->logo_url,
                    'phone' => $tenantAccount->phone,
                    'email' => $tenantAccount->email,
                    'address' => $tenantAccount->address,
                    'currency' => $tenantAccount->currency,
                    'timezone' => $tenantAccount->timezone,
                    'website' => $tenantAccount->website,
                    'description' => $tenantAccount->description,
                    'created_at' => $tenantAccount->created_at,
                    'updated_at' => $tenantAccount->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Create or update tenant account.
     */
    public function apiCreateOrUpdateTenantAccount(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can manage their account.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ]);

        // Update or create tenant account
        $tenantAccount = TenantAccount::updateOrCreate(
            ['tenant_id' => $tenant->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Tenant account updated successfully.',
            'data' => [
                'tenant_account' => [
                    'id' => $tenantAccount->id,
                    'tenant_id' => $tenantAccount->tenant_id,
                    'company_name' => $tenantAccount->company_name,
                    'logo_url' => $tenantAccount->logo_url,
                    'phone' => $tenantAccount->phone,
                    'email' => $tenantAccount->email,
                    'address' => $tenantAccount->address,
                    'currency' => $tenantAccount->currency,
                    'timezone' => $tenantAccount->timezone,
                    'website' => $tenantAccount->website,
                    'description' => $tenantAccount->description,
                    'updated_at' => $tenantAccount->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Get detailed product information including history, profit, sales, stock, and product items.
     */
    public function getproudctinfindetails(Request $request, $productId)
    {
        \Log::info('Product details request started', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        \Log::info('Tenant authenticated successfully', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'product_id' => $productId
        ]);

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)
            ->with([
                'duka',
                'category',
                'stocks',
                'stocks.movements.user',
                'items',
                'stockTransfers'
            ])
            ->find($productId);

        if (!$product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.'
            ], 404);
        }

        \Log::info('Product found and loaded', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'duka_id' => $product->duka_id,
            'current_stock' => $product->current_stock
        ]);

        // Get sales history for this product
        $salesHistory = SaleItem::where('product_id', $productId)
            ->with(['sale.customer', 'sale'])
            ->get();

        \Log::info('Sales history loaded', [
            'product_id' => $productId,
            'total_sales_records' => $salesHistory->count()
        ]);

        $salesHistoryData = $salesHistory->map(function ($saleItem) use ($product) {
            return [
                'sale_id' => $saleItem->sale->id,
                'sale_date' => $saleItem->sale->created_at,
                'customer_name' => $saleItem->sale->customer->name ?? 'N/A',
                'quantity' => $saleItem->quantity,
                'unit_price' => $saleItem->unit_price,
                'total_amount' => $saleItem->total,
                'profit_per_unit' => $saleItem->unit_price - $product->base_price,
                'total_profit' => ($saleItem->unit_price - $product->base_price) * $saleItem->quantity,
                'is_loan' => $saleItem->sale->is_loan,
                'payment_status' => $saleItem->sale->payment_status,
            ];
        });

        // Calculate profit metrics
        $totalSold = $salesHistoryData->sum('quantity');
        $totalRevenue = $salesHistoryData->sum('total_amount');
        $totalCost = $totalSold * $product->base_price;
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalCost > 0 ? ($totalProfit / $totalCost) * 100 : 0;

        \Log::info('Profit metrics calculated', [
            'product_id' => $productId,
            'total_sold' => $totalSold,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin
        ]);

        // Get stock movements history
        $stockMovements = collect();
        $totalMovements = 0;

        foreach ($product->stocks as $stock) {
            $movements = $stock->movements->map(function ($movement) use ($product) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'quantity_change' => $movement->formatted_quantity_change,
                    'previous_quantity' => $movement->previous_quantity,
                    'new_quantity' => $movement->new_quantity,
                    'batch_number' => $movement->batch_number,
                    'expiry_date' => $movement->expiry_date?->format('Y-m-d'),
                    'notes' => $movement->notes,
                    'reason' => $movement->reason,
                    'user_name' => $movement->user->name ?? 'System',
                    'created_at' => $movement->created_at->format('Y-m-d H:i:s'),
                ];
            });
            $stockMovements = $stockMovements->concat($movements);
            $totalMovements += $movements->count();
        }
        $stockMovements = $stockMovements->sortByDesc('created_at')->values();

        \Log::info('Stock movements processed', [
            'product_id' => $productId,
            'total_movements' => $totalMovements,
            'stocks_count' => $product->stocks->count()
        ]);

        // Get stock transfer history
        $stockTransfers = $product->stockTransfers->map(function ($transfer) {
            return [
                'id' => $transfer->id,
                'from_duka_id' => $transfer->from_duka_id,
                'to_duka_id' => $transfer->to_duka_id,
                'status' => $transfer->status,
                'quantity' => $transfer->quantity,
                'created_at' => $transfer->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $transfer->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        \Log::info('Stock transfers processed', [
            'product_id' => $productId,
            'total_transfers' => $stockTransfers->count()
        ]);

        // Prepare comprehensive product data
        $productData = [
            'basic_info' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'unit' => $product->unit,
                'barcode' => $product->barcode,
                'image_url' => $product->image_url,
                'is_active' => $product->is_active,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ],
            'pricing' => [
                'base_price' => $product->base_price,
                'selling_price' => $product->selling_price,
                'profit_per_unit' => $product->profit_per_unit,
                'profit_margin' => $product->profit_margin,
                'formatted_base_price' => $product->formatted_base_price,
                'formatted_selling_price' => $product->formatted_selling_price,
            ],
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'description' => $product->category->description,
            ] : null,
            'duka' => $product->duka ? [
                'id' => $product->duka->id,
                'name' => $product->duka->name,
                'location' => $product->duka->location,
            ] : null,
            'stock_summary' => [
                'current_stock' => $product->current_stock,
                'stock_cost_value' => $product->stock_cost_value,
                'stock_selling_value' => $product->stock_selling_value,
                'total_profit_potential' => $product->total_profit,
                'stock_status' => $product->current_stock > 0 ?
                    ($product->current_stock < 10 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
            ],
            'profit_analysis' => [
                'total_sold' => $totalSold,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => round($profitMargin, 2),
                'average_selling_price' => $totalSold > 0 ? $totalRevenue / $totalSold : 0,
            ],
            'current_stock_details' => $product->stocks->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'quantity' => $stock->quantity,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date?->format('Y-m-d'),
                    'notes' => $stock->notes,
                    'value' => $stock->formatted_value,
                    'status' => $stock->status,
                    'last_updated_by' => $stock->lastUpdatedBy->name ?? 'System',
                    'updated_at' => $stock->updated_at->format('Y-m-d H:i:s'),
                ];
            }),
            'product_items' => $product->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'qr_code' => $item->qr_code,
                    'status' => $item->status,
                    'stock_amount' => $item->stock_amount,
                    'sold_at' => $item->sold_at?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'sales_history' => $salesHistoryData->sortByDesc('sale_date')->values(),
            'stock_movements' => $stockMovements,
            'stock_transfers' => $stockTransfers,
        ];

        \Log::info('Product details response prepared successfully', [
            'product_id' => $productId,
            'tenant_id' => $tenant->id,
            'response_data_size' => count($productData),
            'sales_history_count' => $salesHistoryData->count(),
            'stock_movements_count' => $stockMovements->count(),
            'product_items_count' => $product->items->count(),
            'stock_transfers_count' => $stockTransfers->count()
        ]);

        \Log::info($productData);

        return response()->json([
            'success' => true,
            'data' => $productData
        ]);
    }

    public function tenantaccount()
    {
        $user = Auth::user();
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can manage their account.'
            ], 403);
        }

        $tenantid = $user->id;
        $tenantaccount = TenantAccount::where('tenant_id', $tenantid)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_account' => $tenantaccount
            ]
        ]);
    }

    /**
     * Display a listing of tenants (for super admin).
     */
    public function apiIndex(Request $request)
    {
        \Log::info('Tenant list request', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->getRoleNames()->first(),
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (!$user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant index', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can access this endpoint.'
            ], 403);
        }

        $tenants = Tenant::with(['dukas', 'customers'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        \Log::info('Tenant list retrieved', [
            'total_tenants' => $tenants->total(),
            'per_page' => $tenants->perPage()
        ]);

        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }

    /**
     * Display the specified tenant.
     */
    public function apiShow(Request $request, $id)
    {
        \Log::info('Tenant show request', [
            'user_id' => Auth::id(),
            'requested_tenant_id' => $id,
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role or is the tenant themselves
        if (!$user->hasRole('super_admin') && $user->tenant_id != $id) {
            \Log::warning('Unauthorized access to tenant show', [
                'user_id' => $user->id,
                'user_tenant_id' => $user->tenant_id,
                'requested_tenant_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only access your own tenant information.'
            ], 403);
        }

        $tenant = Tenant::with(['dukas', 'customers', 'productCategories'])
            ->find($id);

        if (!$tenant) {
            \Log::warning('Tenant not found', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        \Log::info('Tenant retrieved successfully', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name
        ]);

        return response()->json([
            'success' => true,
            'data' => $tenant
        ]);
    }

    /**
     * Store a newly created tenant.
     */
    public function apiStore(Request $request)
    {
        \Log::info('Tenant store request', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->getRoleNames()->first(),
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (!$user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant store', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can create tenants.'
            ], 403);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        // Create the tenant
        $tenant = Tenant::create($validated);

        \Log::info('Tenant created successfully', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_email' => $tenant->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully.',
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                    'slug' => $tenant->slug,
                    'status' => $tenant->status,
                    'created_at' => $tenant->created_at->format('Y-m-d H:i:s'),
                ]
            ]
        ], 201);
    }

    /**
     * Update the specified tenant.
     */
    public function apiUpdate(Request $request, $id)
    {
        \Log::info('Tenant update request', [
            'user_id' => Auth::id(),
            'updated_tenant_id' => $id,
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role or is the tenant themselves
        if (!$user->hasRole('super_admin') && $user->tenant_id != $id) {
            \Log::warning('Unauthorized access to tenant update', [
                'user_id' => $user->id,
                'user_tenant_id' => $user->tenant_id,
                'updated_tenant_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own tenant information.'
            ], 403);
        }

        // Find the tenant
        $tenant = Tenant::find($id);

        if (!$tenant) {
            \Log::warning('Tenant not found for update', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:tenants,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:tenants,slug,' . $id,
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        // Update the tenant
        $tenant->update($validated);

        \Log::info('Tenant updated successfully', [
            'tenant_id' => $tenant->id,
            'updated_fields' => array_keys($validated)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully.',
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                    'slug' => $tenant->slug,
                    'status' => $tenant->status,
                    'updated_at' => $tenant->updated_at->format('Y-m-d H:i:s'),
                ]
            ]
        ]);
    }

    /**
     * Remove the specified tenant.
     */
    public function apiDestroy(Request $request, $id)
    {
        \Log::info('Tenant destroy request', [
            'user_id' => Auth::id(),
            'deleted_tenant_id' => $id,
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (!$user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant destroy', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can delete tenants.'
            ], 403);
        }

        // Find the tenant
        $tenant = Tenant::find($id);

        if (!$tenant) {
            \Log::warning('Tenant not found for deletion', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Check if tenant has associated data
        $hasDukas = $tenant->dukas()->count() > 0;
        $hasCustomers = $tenant->customers()->count() > 0;

        if ($hasDukas || $hasCustomers) {
            \Log::warning('Attempt to delete tenant with existing data', [
                'tenant_id' => $id,
                'has_dukas' => $hasDukas,
                'has_customers' => $hasCustomers
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tenant with existing dukas or customers. Please remove all associated data first.'
            ], 422);
        }

        // Delete the tenant
        $tenant->delete();

        \Log::info('Tenant deleted successfully', ['tenant_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully.',
            'data' => [
                'tenant_id' => $id
            ]
        ]);
    }

    /**
     * List all products for the tenant.
     */
    public function apiListProducts(Request $request)
    {
        \Log::info('Product list request', [
            'user_id' => Auth::id(),
            'request_ip' => $request->ip(),
            'query_params' => $request->all()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product list', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Build query with filters
        $query = Product::where('tenant_id', $tenant->id)
            ->with(['duka', 'category', 'stocks']);

        // Apply filters if provided
        if ($request->has('duka_id')) {
            $query->where('duka_id', $request->duka_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['name', 'sku', 'base_price', 'selling_price', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
        $products = $query->paginate($perPage);

        \Log::info('Product list retrieved successfully', [
            'tenant_id' => $tenant->id,
            'total_products' => $products->total(),
            'per_page' => $products->perPage()
        ]);

        // Format the response data
        $formattedProducts = $products->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'unit' => $product->unit,
                'base_price' => $product->base_price,
                'selling_price' => $product->selling_price,
                'profit_per_unit' => $product->profit_per_unit,
                'profit_margin' => $product->profit_margin,
                'is_active' => $product->is_active,
                'image_url' => $product->image_url,
                'barcode' => $product->barcode,
                'current_stock' => $product->current_stock,
                'stock_value' => $product->stock_cost_value,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'duka' => $product->duka ? [
                    'id' => $product->duka->id,
                    'name' => $product->duka->name,
                    'location' => $product->duka->location,
                ] : null,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $formattedProducts,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Show a specific product for the tenant.
     */
    public function apiShowProduct(Request $request, $productId)
    {
        \Log::info('Product show request', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product show', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)
            ->with([
                'duka',
                'category',
                'stocks',
                'stocks.movements.user',
                'items',
                'stockTransfers'
            ])
            ->find($productId);

        if (!$product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.'
            ], 404);
        }

        \Log::info('Product found and loaded', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'duka_id' => $product->duka_id
        ]);

        // Get sales history for this product
        $salesHistory = SaleItem::where('product_id', $productId)
            ->with(['sale.customer', 'sale'])
            ->latest()
            ->limit(10)
            ->get();

        // Prepare comprehensive product data
        $productData = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'unit' => $product->unit,
            'barcode' => $product->barcode,
            'image_url' => $product->image_url,
            'is_active' => $product->is_active,
            'base_price' => $product->base_price,
            'selling_price' => $product->selling_price,
            'profit_per_unit' => $product->profit_per_unit,
            'profit_margin' => $product->profit_margin,
            'current_stock' => $product->current_stock,
            'stock_cost_value' => $product->stock_cost_value,
            'stock_selling_value' => $product->stock_selling_value,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'description' => $product->category->description,
            ] : null,
            'duka' => $product->duka ? [
                'id' => $product->duka->id,
                'name' => $product->duka->name,
                'location' => $product->duka->location,
            ] : null,
            'recent_sales' => $salesHistory->map(function ($saleItem) use ($product) {
                return [
                    'sale_id' => $saleItem->sale->id,
                    'sale_date' => $saleItem->sale->created_at->format('Y-m-d H:i:s'),
                    'customer_name' => $saleItem->sale->customer->name ?? 'N/A',
                    'quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'total_amount' => $saleItem->total,
                    'profit_per_unit' => $saleItem->unit_price - $product->base_price,
                    'total_profit' => ($saleItem->unit_price - $product->base_price) * $saleItem->quantity,
                ];
            }),
            'stock_details' => $product->stocks->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'quantity' => $stock->quantity,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date?->format('Y-m-d'),
                    'notes' => $stock->notes,
                    'created_at' => $stock->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data' => $productData
        ]);
    }

    /**
     * Create a new product for the tenant.
     */
    public function apiCreateProduct(Request $request)
    {
        \Log::info('Product create request', [
            'user_id' => Auth::id(),
            'request_ip' => $request->ip(),
            'request_data' => $request->all()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product create', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can create products.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'duka_id' => 'required|exists:dukas,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'required|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Check if duka belongs to tenant
        $duka = $tenant->dukas()->find($validated['duka_id']);
        if (!$duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.'
            ], 404);
        }

        // Handle image upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $imagePath = $imageName;
        }

        // Create the product
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'duka_id' => $validated['duka_id'],
            'category_id' => $validated['category_id'] ?? null,
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit' => $validated['unit'],
            'base_price' => $validated['base_price'],
            'selling_price' => $validated['selling_price'],
            'barcode' => $validated['barcode'] ?? null,
            'image' => $imagePath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        \Log::info('Product created successfully', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'tenant_id' => $tenant->id,
            'duka_id' => $product->duka_id
        ]);

        // Load relationships for response
        $product->load(['duka', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'description' => $product->description,
                    'unit' => $product->unit,
                    'base_price' => $product->base_price,
                    'selling_price' => $product->selling_price,
                    'profit_per_unit' => $product->profit_per_unit,
                    'profit_margin' => $product->profit_margin,
                    'is_active' => $product->is_active,
                    'image_url' => $product->image_url,
                    'barcode' => $product->barcode,
                    'current_stock' => $product->current_stock,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'duka' => [
                        'id' => $product->duka->id,
                        'name' => $product->duka->name,
                        'location' => $product->duka->location,
                    ],
                    'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                ]
            ]
        ], 201);
    }

    /**
     * Update a product for the tenant.
     */
    public function apiUpdateProduct(Request $request, $productId)
    {
        \Log::info('Product update request', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip(),
            'request_data' => $request->all()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product update', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can update products.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)->find($productId);

        if (!$product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.'
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'duka_id' => 'nullable|exists:dukas,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $productId,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'base_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $productId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Check if duka belongs to tenant (if provided)
        if (isset($validated['duka_id'])) {
            $duka = $tenant->dukas()->find($validated['duka_id']);
            if (!$duka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duka not found or does not belong to this tenant.'
                ], 404);
            }
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $validated['image'] = $imageName;
        }

        // Update the product
        $product->update($validated);

        \Log::info('Product updated successfully', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'updated_fields' => array_keys($validated)
        ]);

        // Load relationships for response
        $product->load(['duka', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'description' => $product->description,
                    'unit' => $product->unit,
                    'base_price' => $product->base_price,
                    'selling_price' => $product->selling_price,
                    'profit_per_unit' => $product->profit_per_unit,
                    'profit_margin' => $product->profit_margin,
                    'is_active' => $product->is_active,
                    'image_url' => $product->image_url,
                    'barcode' => $product->barcode,
                    'current_stock' => $product->current_stock,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'duka' => [
                        'id' => $product->duka->id,
                        'name' => $product->duka->name,
                        'location' => $product->duka->location,
                    ],
                    'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
                ]
            ]
        ]);
    }

    /**
     * Delete a product for the tenant.
     */
    public function apiDeleteProduct(Request $request, $productId)
    {
        \Log::info('Product delete request', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product delete', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can delete products.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)->find($productId);

        if (!$product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.'
            ], 404);
        }

        // Check if product has associated sales
        $hasSales = SaleItem::where('product_id', $productId)->exists();
        $hasStock = $product->stocks()->exists();
        $hasItems = $product->items()->exists();

        if ($hasSales) {
            \Log::warning('Attempt to delete product with existing sales', [
                'product_id' => $productId,
                'tenant_id' => $tenant->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product with existing sales. Consider deactivating it instead.'
            ], 422);
        }

        // If product has stock or items, we can still delete but log it
        if ($hasStock || $hasItems) {
            \Log::warning('Deleting product with existing stock or items', [
                'product_id' => $productId,
                'tenant_id' => $tenant->id,
                'has_stock' => $hasStock,
                'has_items' => $hasItems
            ]);
        }

        // Delete the product (this will also delete related stock, items, etc. due to foreign key constraints)
        $productName = $product->name;
        $product->delete();

        \Log::info('Product deleted successfully', [
            'product_id' => $productId,
            'product_name' => $productName,
            'tenant_id' => $tenant->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data' => [
                'product_id' => $productId,
                'product_name' => $productName
            ]
        ]);
    }

    /**
     * Get all features.
     */
    public function apiGetFeatures(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get all features
        $features = Feature::orderBy('name', 'asc')->get();

        // Format the response
        $formattedFeatures = $features->map(function ($feature) {
            return [
                'id' => $feature->id,
                'code' => $feature->code,
                'name' => $feature->name,
                'description' => $feature->description,
                'created_at' => $feature->created_at,
                'updated_at' => $feature->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'features' => $formattedFeatures,
                'total' => $formattedFeatures->count(),
            ]
        ]);
    }

    /**
     * Get all plans with their features.
     */
   public function apiGetPlans(Request $request)
{
    try {
        $plans = Plan::where('is_active', true)
                     ->with('features')
                     ->orderBy('price', 'asc')
                     ->get();

        return response()->json([
            'success' => true,
            'data'    => $plans
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve plans.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get comprehensive duka overview with analytics and growth metrics.
     */
    public function apiGetDukaOverview(Request $request, $dukaId)
    {
        \Log::info('Duka overview request', [
            'user_id' => Auth::id(),
            'duka_id' => $dukaId,
            'request_ip' => $request->ip(),
            'query_params' => $request->all()
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (!$user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to duka overview', [
                'user_id' => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'duka_id' => $dukaId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.'
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (!$tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        // Find the duka that belongs to the tenant
        $duka = $tenant->dukas()->find($dukaId);

        if (!$duka) {
            \Log::warning('Duka not found or access denied', [
                'tenant_id' => $tenant->id,
                'duka_id' => $dukaId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.'
            ], 404);
        }

        \Log::info('Duka found and loading analytics', [
            'duka_id' => $duka->id,
            'duka_name' => $duka->name
        ]);

        // Get date range for analytics (default: last 30 days)
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));

        // Previous period for comparison
        $daysDiff = \Carbon\Carbon::parse($endDate)->diffInDays(\Carbon\Carbon::parse($startDate));
        $previousEndDate = \Carbon\Carbon::parse($startDate)->subDays(1)->format('Y-m-d');
        $previousStartDate = \Carbon\Carbon::parse($startDate)->subDays($daysDiff + 1)->format('Y-m-d');

        // Load relationships
        $duka->load([
            'products.category',
            'products.stocks',
            'customers',
            'sales.customer',
            'sales.saleItems.product'
        ]);

        // ==========================
        // FINANCIAL OVERVIEW
        // ==========================

        // Sales Data
        $currentSales = Sale::where('duka_id', $dukaId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('saleItems')
            ->get();

        $previousSales = Sale::where('duka_id', $dukaId)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->with('saleItems')
            ->get();

        // Current Period Metrics
        $currentRevenue = $currentSales->sum('total_amount');
        $currentProfit = $currentSales->sum('profit_loss');
        $currentTransactions = $currentSales->count();
        $currentAverageOrderValue = $currentTransactions > 0 ? $currentRevenue / $currentTransactions : 0;

        // Previous Period Metrics
        $previousRevenue = $previousSales->sum('total_amount');
        $previousProfit = $previousSales->sum('profit_loss');
        $previousTransactions = $previousSales->count();
        $previousAverageOrderValue = $previousTransactions > 0 ? $previousRevenue / $previousTransactions : 0;

        // Growth Calculations
        $revenueGrowth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        $profitGrowth = $previousProfit > 0 ? (($currentProfit - $previousProfit) / $previousProfit) * 100 : 0;
        $transactionGrowth = $previousTransactions > 0 ? (($currentTransactions - $previousTransactions) / $previousTransactions) * 100 : 0;
        $aovGrowth = $previousAverageOrderValue > 0 ? (($currentAverageOrderValue - $previousAverageOrderValue) / $previousAverageOrderValue) * 100 : 0;

        // ==========================
        // CASH FLOW ANALYSIS
        // ==========================

        $currentCashFlows = CashFlow::where('duka_id', $dukaId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $previousCashFlows = CashFlow::where('duka_id', $dukaId)
            ->whereBetween('transaction_date', [$previousStartDate, $previousEndDate])
            ->get();

        // Traditional cash flow from CashFlow model
        $currentIncome = $currentCashFlows->where('type', 'income')->sum('amount');
        $currentExpenses = $currentCashFlows->where('type', 'expense')->sum('amount');
        $currentNetCashFlow = $currentIncome - $currentExpenses;

        $previousIncome = $previousCashFlows->where('type', 'income')->sum('amount');
        $previousExpenses = $previousCashFlows->where('type', 'expense')->sum('amount');
        $previousNetCashFlow = $previousIncome - $previousExpenses;

        // Sales-based income calculation (more accurate for business performance)
        $currentSalesIncome = $currentSales->sum('total_amount');
        $previousSalesIncome = $previousSales->sum('total_amount');

        // Calculate cost of goods sold (COGS) from sales
        $currentCOGS = $currentSales->sum(function($sale) {
            return $sale->saleItems->sum(function($item) {
                return $item->quantity * $item->product->base_price;
            });
        });

        $previousCOGS = $previousSales->sum(function($sale) {
            return $sale->saleItems->sum(function($item) {
                return $item->quantity * $item->product->base_price;
            });
        });

        // Gross profit from sales
        $currentGrossProfit = $currentSalesIncome - $currentCOGS;
        $previousGrossProfit = $previousSalesIncome - $previousCOGS;

        // Sales-based growth metrics
        $salesIncomeGrowth = $previousSalesIncome > 0 ? (($currentSalesIncome - $previousSalesIncome) / $previousSalesIncome) * 100 : 0;
        $grossProfitGrowth = $previousGrossProfit > 0 ? (($currentGrossProfit - $previousGrossProfit) / $previousGrossProfit) * 100 : 0;

        $cashFlowGrowth = $previousNetCashFlow != 0 ? (($currentNetCashFlow - $previousNetCashFlow) / abs($previousNetCashFlow)) * 100 : 0;

        // ==========================
        // PRODUCT ANALYTICS
        // ==========================

        $products = $duka->products;
        $totalProducts = $products->count();
        $activeProducts = $products->where('is_active', true)->count();

        // Stock Analysis
        $totalStockCostValue = $products->sum('stock_cost_value');
        $totalStockSellingValue = $products->sum('stock_selling_value');
        $totalPotentialProfit = $totalStockSellingValue - $totalStockCostValue;

        // Low Stock Alert
        $lowStockProducts = $products->filter(function($product) {
            return $product->current_stock > 0 && $product->current_stock <= 10;
        })->count();

        $outOfStockProducts = $products->filter(function($product) {
            return $product->current_stock <= 0;
        })->count();

        // Top Selling Products
        $topSellingProducts = SaleItem::whereHas('sale', function($query) use ($dukaId, $startDate, $endDate) {
                $query->where('duka_id', $dukaId)
                      ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // ==========================
        // CUSTOMER ANALYTICS
        // ==========================

        $totalCustomers = $duka->customers->count();
        $activeCustomers = $currentSales->unique('customer_id')->count();
        $newCustomers = Customer::where('duka_id', $dukaId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $returningCustomers = $currentSales->filter(function($sale) use ($dukaId, $startDate) {
            $oldCustomers = Customer::where('duka_id', $dukaId)
                ->where('created_at', '<', $startDate)
                ->pluck('id')
                ->toArray();
            return in_array($sale->customer_id, $oldCustomers);
        })->unique('customer_id')->count();

        // ==========================
        // LOAN ANALYTICS
        // ==========================

        $totalLoans = $currentSales->where('is_loan', true)->count();
        $totalLoanAmount = $currentSales->where('is_loan', true)->sum('total_amount');

        // Get outstanding loans - fix the whereHas issue
        $outstandingLoanSales = Sale::where('duka_id', $dukaId)
            ->where('is_loan', true)
            ->whereHas('loanPayments')
            ->with(['loanPayments' => function($query) {
                $query->selectRaw('sale_id, SUM(amount) as total_paid')
                      ->groupBy('sale_id');
            }])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $outstandingLoans = $outstandingLoanSales->filter(function($sale) {
            return $sale->remaining_balance > 0;
        })->count();

        $totalOutstandingLoanAmount = $currentSales->where('is_loan', true)->sum('remaining_balance');

        // ==========================
        // TREND ANALYSIS
        // ==========================

        // Daily sales for trend
        $dailySales = Sale::where('duka_id', $dukaId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Weekly comparison
        $currentWeekSales = Sale::where('duka_id', $dukaId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total_amount');

        $previousWeekSales = Sale::where('duka_id', $dukaId)
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->sum('total_amount');

        $weeklyGrowth = $previousWeekSales > 0 ? (($currentWeekSales - $previousWeekSales) / $previousWeekSales) * 100 : 0;

        // ==========================
        // PERFORMANCE INDICATORS
        // ==========================

        // Calculate growth score (0-100) - Enhanced with sales-based metrics
        $growthScore = 0;
        $growthFactors = [
            max(0, min(100, ($revenueGrowth + 100) / 2)) * 0.25,        // Revenue growth (25%)
            max(0, min(100, ($profitGrowth + 100) / 2)) * 0.20,          // Profit growth (20%)
            max(0, min(100, ($salesIncomeGrowth + 100) / 2)) * 0.20,     // Sales income growth (20%)
            max(0, min(100, ($grossProfitGrowth + 100) / 2)) * 0.15,     // Gross profit growth (15%)
            max(0, min(100, ($transactionGrowth + 100) / 2)) * 0.10,     // Transaction growth (10%)
            max(0, min(100, ($cashFlowGrowth + 100) / 2)) * 0.05,        // Cash flow growth (5%)
            max(0, min(100, ($weeklyGrowth + 100) / 2)) * 0.05           // Weekly growth (5%)
        ];
        $growthScore = array_sum($growthFactors);

        // Determine growth status
        if ($growthScore >= 75) {
            $growthStatus = 'Excellent Growth';
            $growthStatusColor = 'success';
        } elseif ($growthScore >= 50) {
            $growthStatus = 'Good Growth';
            $growthStatusColor = 'info';
        } elseif ($growthScore >= 25) {
            $growthStatus = 'Moderate Growth';
            $growthStatusColor = 'warning';
        } else {
            $growthStatus = 'Needs Attention';
            $growthStatusColor = 'danger';
        }

        // ==========================
        // CASH FLOW BY CATEGORY
        // ==========================

        $cashFlowByCategory = $currentCashFlows->groupBy('category')->map(function($items) {
            return [
                'income' => $items->where('type', 'income')->sum('amount'),
                'expense' => $items->where('type', 'expense')->sum('amount'),
                'net' => $items->where('type', 'income')->sum('amount') - $items->where('type', 'expense')->sum('amount')
            ];
        });

        \Log::info('Duka overview analytics completed', [
            'duka_id' => $dukaId,
            'period' => "{$startDate} to {$endDate}",
            'growth_score' => $growthScore,
            'revenue_growth' => $revenueGrowth
        ]);

        // Prepare comprehensive response
        $overviewData = [
            'duka_info' => [
                'id' => $duka->id,
                'name' => $duka->name,
                'location' => $duka->location,
                'manager_name' => $duka->manager_name,
                'status' => $duka->status,
                'created_at' => $duka->created_at->format('Y-m-d'),
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'days' => $daysDiff + 1
                ]
            ],

            'financial_summary' => [
                'current_period' => [
                    'revenue' => $currentRevenue,
                    'profit' => $currentProfit,
                    'profit_margin' => $currentRevenue > 0 ? ($currentProfit / $currentRevenue) * 100 : 0,
                    'transactions' => $currentTransactions,
                    'average_order_value' => $currentAverageOrderValue,
                    'cash_flow' => [
                        'recorded_income' => $currentIncome,
                        'recorded_expenses' => $currentExpenses,
                        'recorded_net' => $currentNetCashFlow
                    ],
                    'sales_based_income' => [
                        'total_sales_income' => $currentSalesIncome,
                        'cost_of_goods_sold' => $currentCOGS,
                        'gross_profit' => $currentGrossProfit,
                        'gross_profit_margin' => $currentSalesIncome > 0 ? ($currentGrossProfit / $currentSalesIncome) * 100 : 0
                    ]
                ],
                'previous_period' => [
                    'revenue' => $previousRevenue,
                    'profit' => $previousProfit,
                    'profit_margin' => $previousRevenue > 0 ? ($previousProfit / $previousRevenue) * 100 : 0,
                    'transactions' => $previousTransactions,
                    'average_order_value' => $previousAverageOrderValue,
                    'cash_flow' => [
                        'recorded_income' => $previousIncome,
                        'recorded_expenses' => $previousExpenses,
                        'recorded_net' => $previousNetCashFlow
                    ],
                    'sales_based_income' => [
                        'total_sales_income' => $previousSalesIncome,
                        'cost_of_goods_sold' => $previousCOGS,
                        'gross_profit' => $previousGrossProfit,
                        'gross_profit_margin' => $previousSalesIncome > 0 ? ($previousGrossProfit / $previousSalesIncome) * 100 : 0
                    ]
                ],
                'growth_metrics' => [
                    'revenue_growth' => round($revenueGrowth, 2),
                    'profit_growth' => round($profitGrowth, 2),
                    'transaction_growth' => round($transactionGrowth, 2),
                    'aov_growth' => round($aovGrowth, 2),
                    'cash_flow_growth' => round($cashFlowGrowth, 2),
                    'sales_income_growth' => round($salesIncomeGrowth, 2),
                    'gross_profit_growth' => round($grossProfitGrowth, 2),
                    'weekly_growth' => round($weeklyGrowth, 2)
                ]
            ],

            'product_analytics' => [
                'summary' => [
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                    'inactive_products' => $totalProducts - $activeProducts,
                    'low_stock_products' => $lowStockProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'stock_health_score' => $totalProducts > 0 ? (($activeProducts - $lowStockProducts - $outOfStockProducts) / $totalProducts) * 100 : 0
                ],
                'inventory_value' => [
                    'total_cost_value' => $totalStockCostValue,
                    'total_selling_value' => $totalStockSellingValue,
                    'potential_profit' => $totalPotentialProfit,
                    'profit_margin_potential' => $totalStockCostValue > 0 ? ($totalPotentialProfit / $totalStockCostValue) * 100 : 0
                ],
                'top_selling_products' => $topSellingProducts->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Unknown',
                        'total_quantity_sold' => $item->total_quantity,
                        'total_revenue' => $item->total_revenue,
                        'average_selling_price' => $item->total_quantity > 0 ? $item->total_revenue / $item->total_quantity : 0
                    ];
                })
            ],

            'customer_analytics' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'new_customers' => $newCustomers,
                'returning_customers' => $returningCustomers,
                'customer_retention_rate' => $activeCustomers > 0 ? ($returningCustomers / $activeCustomers) * 100 : 0,
                'customer_acquisition_rate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0
            ],

            'loan_analytics' => [
                'total_loans' => $totalLoans,
                'total_loan_amount' => $totalLoanAmount,
                'outstanding_loans' => $outstandingLoans,
                'outstanding_amount' => $totalOutstandingLoanAmount,
                'loan_collection_rate' => $totalLoanAmount > 0 ? (($totalLoanAmount - $totalOutstandingLoanAmount) / $totalLoanAmount) * 100 : 0,
                'average_loan_size' => $totalLoans > 0 ? $totalLoanAmount / $totalLoans : 0
            ],

            'performance_indicators' => [
                'growth_score' => round($growthScore, 2),
                'growth_status' => $growthStatus,
                'status_color' => $growthStatusColor,
                'key_insights' => [
                    $revenueGrowth > 0 ? 'Revenue is growing' : 'Revenue is declining',
                    $profitGrowth > 0 ? 'Profitability is improving' : 'Profitability is declining',
                    $salesIncomeGrowth > 0 ? 'Sales income is increasing' : 'Sales income is decreasing',
                    $grossProfitGrowth > 0 ? 'Gross profit margins are improving' : 'Gross profit margins are declining',
                    $currentNetCashFlow > 0 ? 'Positive recorded cash flow' : 'Negative recorded cash flow',
                    $currentSalesIncome > $currentCOGS ? 'Healthy sales profitability' : 'Sales profitability needs attention',
                    $lowStockProducts > 0 ? "{$lowStockProducts} products need restocking" : 'Stock levels are healthy',
                    $weeklyGrowth > 0 ? 'Weekly performance is improving' : 'Weekly performance needs attention'
                ]
            ],

            'trend_analysis' => [
                'daily_sales' => $dailySales->map(function($day) {
                    return [
                        'date' => $day->date,
                        'revenue' => $day->revenue,
                        'transactions' => $day->transactions,
                        'average_transaction' => $day->transactions > 0 ? $day->revenue / $day->transactions : 0
                    ];
                }),
                'cash_flow_by_category' => $cashFlowByCategory->map(function($category, $name) {
                    return [
                        'category' => $name,
                        'income' => $category['income'],
                        'expenses' => $category['expense'],
                        'net' => $category['net']
                    ];
                })->values()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $overviewData
        ]);
    }
}

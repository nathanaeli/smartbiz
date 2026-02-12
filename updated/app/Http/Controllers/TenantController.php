<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Duka;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\TenantOfficer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\StockTransfer;
use App\Models\StockMovement;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\Stock;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// Au kama ipo kwenye Controllers:



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
     * Delete a tenant account and all related data.
     */
    public function apiDeleteTenantAccount(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        DB::beginTransaction();

        try {
            $dukaIds = $tenant->dukas()->pluck('id');
            Sale::whereIn('duka_id', $dukaIds)->delete();
            SaleItem::whereIn('sale_id', Sale::whereIn('duka_id', $dukaIds)->pluck('id'))->delete();
            Product::whereIn('duka_id', $dukaIds)->delete();
            Customer::whereIn('duka_id', $dukaIds)->delete();
            $officerIds = TenantOfficer::where('tenant_id', $tenant->id)->pluck('officer_id');
            TenantOfficer::where('tenant_id', $tenant->id)->delete();
            User::whereIn('id', $officerIds)->delete();
            Duka::whereIn('id', $dukaIds)->delete();
            TenantAccount::where('tenant_id', $tenant->id)->delete();
            DukaSubscription::where('tenant_id', $tenant->id)->delete();
            ProductCategory::where('tenant_id', $tenant->id)->delete();
            StockTransfer::whereIn('from_duka_id', $dukaIds)
                ->orWhereIn('to_duka_id', $dukaIds)
                ->delete();
            StockMovement::whereHas('stock', function ($query) use ($dukaIds) {
                $query->whereIn('duka_id', $dukaIds);
            })->delete();
            Stock::whereIn('duka_id', $dukaIds)->delete();
            Transaction::whereIn('duka_id', $dukaIds)->delete();
            Message::where('tenant_id', $tenant->id)->delete();
            LoanPayment::whereHas('sale', function ($query) use ($dukaIds) {
                $query->whereIn('duka_id', $dukaIds);
            })->delete();
            StaffPermission::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant account and all related data deleted successfully.',
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant account.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiInventoryAndLoanAnalysis(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);
        $dukaId = $request->query('duka_id');

        // --- 1. STOCK MOVEMENT ANALYSIS (NET FLOW) ---
        $movementQuery = StockMovement::whereHas('stock.duka', function ($q) use ($tenantId, $dukaId) {
            $q->where('tenant_id', $tenantId);
            if ($dukaId) $q->where('duka_id', $dukaId);
        });

        // Use specific reasons to differentiate between actual sales and returns
        $stockStats = (clone $movementQuery)
            ->selectRaw("
            SUM(CASE WHEN type = 'in' AND reason = 'purchase' THEN quantity_change ELSE 0 END) as total_in,
            SUM(CASE WHEN type = 'out' AND reason = 'sale' THEN quantity_change ELSE 0 END) as raw_sold,
            SUM(CASE WHEN type = 'in' AND reason = 'sale_return' THEN quantity_change ELSE 0 END) as total_returned,
            SUM(CASE WHEN type = 'in' AND reason = 'purchase' THEN total_value ELSE 0 END) as stock_purchase_value
        ")
            ->first();

        // Calculate the TRUE items sold (Sales - Returns)
        $netItemsSold = (int)($stockStats->raw_sold - $stockStats->total_returned);

        // --- 2. LOAN AGING ANALYSIS ---
        $now = now();
        // Sales are deleted on void, so this list only contains active/unpaid sales
        $loans = Sale::where('tenant_id', $tenantId)
            ->where('is_loan', true)
            ->when($dukaId, fn($q) => $q->where('duka_id', $dukaId))
            ->get()
            ->filter(fn($sale) => $sale->remaining_balance > 0);

        $aging = [
            'current'      => 0,
            '1_30_days'    => 0,
            '31_60_days'   => 0,
            '61_90_days'   => 0,
            'over_90_days' => 0
        ];

        foreach ($loans as $loan) {
            $balance = $loan->remaining_balance;
            if (!$loan->due_date || $loan->due_date->isFuture()) {
                $aging['current'] += $balance;
            } else {
                $daysOverdue = $loan->due_date->diffInDays($now);
                if ($daysOverdue <= 30) $aging['1_30_days'] += $balance;
                elseif ($daysOverdue <= 60) $aging['31_60_days'] += $balance;
                elseif ($daysOverdue <= 90) $aging['61_90_days'] += $balance;
                else $aging['over_90_days'] += $balance;
            }
        }

        // --- 3. DUKA SUMMARY BREAKDOWN ---
        $dukaSummary = DB::table('dukas')
            ->where('dukas.tenant_id', $tenantId)
            ->leftJoin('sales', 'dukas.id', '=', 'sales.duka_id')
            ->select(
                'dukas.name as duka_name',
                DB::raw('SUM(COALESCE(sales.total_amount, 0)) as total_revenue'),
                DB::raw('SUM(CASE WHEN sales.is_loan = 1 THEN COALESCE(sales.remaining_balance, 0) ELSE 0 END) as total_debt')
            )
            ->when($dukaId, fn($q) => $q->where('dukas.id', $dukaId))
            ->groupBy('dukas.id', 'dukas.name')
            ->get();

        return response()->json([
            'success' => true,
            'summary' => [
                'stock_flow' => [
                    'total_items_received' => (int)$stockStats->total_in,
                    'total_items_sold'     => max(0, $netItemsSold), // Ensure never negative
                    'inventory_turnover_ratio' => $stockStats->total_in > 0
                        ? round($netItemsSold / $stockStats->total_in, 2)
                        : 0,
                ],
                'loan_aging' => [
                    'total_receivables' => (float)array_sum($aging),
                    'aging_groups' => $aging,
                ],
            ],
            'duka_breakdown' => $dukaSummary
        ]);
    }

    public function apiTransactionReport(Request $request)
    {
        $user = auth()->user();

        $tenantId = $user->tenant_id ?? optional($user->tenant)->id;

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 403);
        }

        // --------------------------------------------------
        // 1. Filters
        // --------------------------------------------------
        $dukaId = $request->query('duka_id');

        $startDate = $request->query('start_date')
            ? Carbon::parse($request->query('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))->endOfDay()
            : now();

        // --------------------------------------------------
        // 2. Base Query (Tenant scoped + exclude loan sales)
        // --------------------------------------------------
        $query = Transaction::query()
            ->whereHas('duka', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->whereBetween('transaction_date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ])
            ->where('status', '!=', 'void')
            ->where(function ($q) {
                // Include:
                // - Non-sale transactions (expenses, loan payments, etc.)
                // - Cash sales (sale but NOT loan)
                $q->where('category', '!=', 'sale')
                    ->orWhereNotIn('reference_id', function ($sub) {
                        $sub->select('id')
                            ->from('sales')
                            ->where('is_loan', true);
                    });
            });

        if ($dukaId) {
            $query->where('duka_id', $dukaId);
        }

        // --------------------------------------------------
        // 3. Financial Summary
        // --------------------------------------------------
        $summary = (clone $query)
            ->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
            COUNT(*) as transaction_count
        ")
            ->first();

        $netCashFlow = (float) $summary->total_income - (float) $summary->total_expense;

        // --------------------------------------------------
        // 4. Category Breakdown
        // --------------------------------------------------
        $categoryBreakdown = (clone $query)
            ->select('type', 'category', DB::raw('SUM(amount) as total'))
            ->groupBy('type', 'category')
            ->get()
            ->groupBy('type');

        // --------------------------------------------------
        // 5. Paginated Transactions List
        // --------------------------------------------------
        $transactions = $query
            ->with([
                'duka:id,name',
                'user:id,name'
            ])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        // --------------------------------------------------
        // 6. Response
        // --------------------------------------------------
        return response()->json([
            'success' => true,
            'period' => [
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
            ],
            'summary' => [
                'total_income'      => (float) $summary->total_income,
                'total_expense'     => (float) $summary->total_expense,
                'net_cash_flow'     => $netCashFlow,
                'transaction_count' => (int) $summary->transaction_count,
            ],
            'breakdown' => [
                'income_by_category'  => $categoryBreakdown->get('income', []),
                'expense_by_category' => $categoryBreakdown->get('expense', []),
            ],
            'transactions' => $transactions,
        ]);
    }


    public function apiGetLowStockProducts(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        $threshold = $request->query('threshold', 10);

        // Fetch products where stock is low in any Duka
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->whereHas('stocks', function ($query) use ($threshold) {
                $query->where('quantity', '<=', $threshold);
            })
            ->with([
                'category:id,name',
                'duka:id,name',
                'stocks',
                // We count items grouped by status to show exactly what's available
                'items' => function ($q) {
                    $q->select('id', 'product_id', 'status');
                }
            ])
            ->get()
            ->map(function ($product) {
                // Group the individual QR items by their status
                $itemStats = $product->items->groupBy('status')->map->count();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'N/A',
                    'image' => $product->image_url,

                    // Bulk Stock Data
                    'total_bulk_quantity' => $product->current_stock,

                    // Individual QR Item Data
                    'unit_tracking' => [
                        'available_units' => $itemStats->get('available', 0),
                        'damaged_units'   => $itemStats->get('damaged', 0),
                        'sold_units'      => $itemStats->get('sold', 0),
                    ],

                    // Breakdown per Duka (Where the shortage is)
                    'locations' => $product->stocks->map(function ($stock) {
                        return [
                            'duka_name' => $stock->duka->name ?? 'Main',
                            'quantity' => $stock->quantity,
                            'status' => $stock->status, // Low Stock / Out of Stock
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'threshold' => (int)$threshold,
            'data' => $lowStockProducts
        ]);
    }

    /**
     * Get tenant details including dukas and summary for API.
     */
    public function apiGetDetails(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        $tenant = Tenant::with(['tenantAccount', 'activeSubscription'])->find($tenantId);
        $today = now()->toDateString();

        // 1. Overall Financial Totals (Sales/Profit/Debt)
        $salesData = Sale::where('tenant_id', $tenantId)
            ->selectRaw('SUM(total_amount) as total_sales, SUM(profit_loss) as total_profit, SUM(remaining_balance) as total_debt')
            ->first();

        // 2. Today's Summary by Duka
        $dukaSummaries = Duka::where('tenant_id', $tenantId)
            ->withCount(['sales as today_sales_count' => function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            }])
            ->get()
            ->map(function ($duka) use ($today) {
                $todayStats = Sale::where('duka_id', $duka->id)
                    ->whereDate('created_at', $today)
                    ->selectRaw('SUM(total_amount) as amount, SUM(profit_loss) as profit')
                    ->first();

                $stockValue = Stock::where('stocks.duka_id', $duka->id)
                    ->join('products', 'stocks.product_id', '=', 'products.id')
                    ->selectRaw('SUM(stocks.quantity * products.base_price) as stock_cost_value')
                    ->first();

                return [
                    'id'           => $duka->id,
                    'name'         => $duka->name,
                    'location'     => $duka->location,
                    'today_sales'  => (float) ($todayStats->amount ?? 0),
                    'today_profit' => (float) ($todayStats->profit ?? 0),
                    'sales_count'  => (int) $duka->today_sales_count,
                    'stock_value'  => (float) ($stockValue->stock_cost_value ?? 0),
                ];
            });

        // 3. Fixed Stock Valuation (Quantity * Base Price)
        $productStats = Stock::whereHas('duka', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('
            COUNT(DISTINCT products.id) as total_items,
            SUM(stocks.quantity * products.base_price) as stock_cost_value
        ')
            ->first();

        $expenseTotal = Transaction::whereHas('duka', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('type', 'expense')->sum('amount');

        // 4. Final Response
        return response()->json([
            'success'     => true,
            'sync_date'   => now()->toDateTimeString(),
            'tenant_name' => $tenant->name,
            'overview'    => [
                'today_by_duka' => $dukaSummaries,
                'financials' => [
                    'total_sales'    => (float) ($salesData->total_sales ?? 0),
                    'total_profit'   => (float) ($salesData->total_profit ?? 0),
                    'total_expenses' => (float) $expenseTotal,
                    'net_income'     => (float) (($salesData->total_profit ?? 0) - $expenseTotal),
                ],
                'inventory' => [
                    'total_products'  => (int) ($productStats->total_items ?? 0),
                    'stock_valuation' => (float) ($productStats->stock_cost_value ?? 0),
                ],
                'business_stats' => [
                    'total_dukas'      => $dukaSummaries->count(),
                    'active_customers' => $tenant->customers()->count(),
                    'account_balance'  => $tenant->tenantAccount ? (float) $tenant->tenantAccount->balance : 0.00,
                ]
            ]
        ]);
    }


    public function getDetailedSummary($startDate = null, $endDate = null)
    {
        $querySales = Sale::where('tenant_id', $this->id);
        $queryTransactions = Transaction::whereHas('duka', function ($q) {
            $q->where('tenant_id', $this->id);
        });
        $queryMovements = StockMovement::whereHas('stock.duka', function ($q) {
            $q->where('tenant_id', $this->id);
        });

        if ($startDate && $endDate) {
            $querySales->whereBetween('created_at', [$startDate, $endDate]);
            $queryTransactions->whereBetween('transaction_date', [$startDate, $endDate]);
            $queryMovements->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'overview' => [
                'total_sales'      => (float) $querySales->sum('total_amount'),
                'total_profit'     => (float) $querySales->sum('profit_loss'),
                'total_collected'  => (float) $querySales->sum('total_payments'),
            ],
            'debt' => [
                'outstanding_debt' => (float) $querySales->where('is_loan', true)->sum('remaining_balance'),
                'loan_repayments'  => (float) LoanPayment::whereHas('sale', function ($q) {
                    $q->where('tenant_id', $this->id);
                })->sum('amount'),
            ],
            'expenses' => [
                'stock_purchases'  => (float) $queryMovements->where('reason', 'purchase')->sum('total_value'),
                'operating_costs'  => (float) $queryTransactions->where('type', 'expense')->sum('amount'),
                'stock_damages'    => (float) $queryMovements->where('reason', 'damage')->sum('total_value'),
            ],
            'counts' => [
                'total_customers'  => $this->customers()->count(),
                'total_dukas'      => $this->dukas()->count(),
                'active_products'  => Product::where('tenant_id', $this->id)->where('is_active', true)->count(),
            ]
        ];
    }
    /**
     * Get products and related data for a specific duka.
     */
    public function apiGetDukaProducts(Request $request, $duka_id)
    {
        $user = Auth::user();

        // Check for Tenant context
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant context not found.'], 403);
        }

        // Find the duka and verify ownership in one query
        $duka = Duka::where('id', $duka_id)->where('tenant_id', $tenantId)->first();

        if (!$duka) {
            return response()->json(['success' => false, 'message' => 'Duka not found.'], 404);
        }

        // Eager load with specific columns to save memory
        $duka->load([
            'products' => function ($q) {
                $q->with(['category:id,name', 'stocks', 'items']);
            },
            'customers',
            'sales.saleItems.product:id,name',
            'sales.customer:id,name'
        ]);

        $categories = \App\Models\ProductCategory::where('tenant_id', $tenantId)->get(['id', 'name', 'description']);

        return response()->json([
            'success' => true,
            'sync_date' => now()->toDateTimeString(),
            'data'    => [
                'duka_info' => [
                    'id' => $duka->id,
                    'name' => $duka->name,
                    'location' => $duka->location,
                ],
                'categories' => $categories,
                'products'   => $duka->products->map(function ($product) {
                    return [
                        'id'            => $product->id,
                        'sku'           => $product->sku,
                        'name'          => $product->name,
                        'base_price'    => (float)$product->base_price,
                        'selling_price' => (float)$product->selling_price,
                        'unit'          => $product->unit,
                        'category_name' => $product->category->name ?? 'Uncategorized',
                        'image'         => $product->image_url,
                        'current_stock' => $product->stocks->sum('quantity'),
                        'is_active'     => $product->is_active,
                    ];
                }),
                'inventory_details' => [
                    'stocks' => $duka->products->pluck('stocks')->flatten(),
                    'individual_items' => $duka->products->pluck('items')->flatten(),
                ],
                'customers' => $duka->customers,
                'recent_sales' => $duka->sales->take(50)->map(function ($sale) {
                    return [
                        'id'             => $sale->id,
                        'customer'       => $sale->customer->name ?? 'Walk-in',
                        'total'          => (float)$sale->total_amount,
                        'status'         => $sale->payment_status,
                        'is_loan'        => $sale->is_loan,
                        'remaining'      => (float)$sale->remaining_balance,
                        'date'           => $sale->created_at->format('d M Y H:i'),
                        'items_count'    => $sale->saleItems->count(),
                    ];
                }),
            ]
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
        if (! $user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can create dukas.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'location'     => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'status'       => 'nullable|in:active,inactive',
        ]);

        // Create the duka
        $duka = Duka::create([
            'tenant_id'    => $tenant->id,
            'name'         => $validated['name'],
            'location'     => $validated['location'],
            'manager_name' => $validated['manager_name'] ?? null,
            'latitude'     => $validated['latitude'] ?? null,
            'longitude'    => $validated['longitude'] ?? null,
            'status'       => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Duka created successfully.',
            'data'    => [
                'duka' => [
                    'id'           => $duka->id,
                    'name'         => $duka->name,
                    'location'     => $duka->location,
                    'manager_name' => $duka->manager_name,
                    'latitude'     => $duka->latitude,
                    'longitude'    => $duka->longitude,
                    'status'       => $duka->status,
                    'created_at'   => $duka->created_at,
                ],
            ],
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
        if (! $user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the duka belonging to the tenant
        $duka = $tenant->dukas()->find($duka_id);

        if (! $duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.',
            ], 404);
        }

        // Get the current plan for the duka
        $currentPlan = $duka->currentPlan();

        if (! $currentPlan) {
            return response()->json([
                'success' => false,
                'message' => 'No active plan found for this duka.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'duka' => [
                    'id'   => $duka->id,
                    'name' => $duka->name,
                ],
                'plan' => [
                    'id'            => $currentPlan->id,
                    'name'          => $currentPlan->name,
                    'description'   => $currentPlan->description,
                    'price'         => $currentPlan->price,
                    'duration_days' => $currentPlan->duration_days,
                    'features'      => $currentPlan->features ?? [],
                ],
            ],
        ]);
    }

    /**
     * Create an officer and assign to a duka.
     */
    public function apiCreateOfficer(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();


        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'duka_id'  => 'required|exists:dukas,id',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|string|max:50',
        ]);

        // Check if duka belongs to tenant
        $duka = $tenant->dukas()->find($validated['duka_id']);
        if (! $duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.',
            ], 404);
        }

        // Create the officer user
        $officer = \App\Models\User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role'     => 'officer',
            'status'   => 'active',

        ]);

        // Assign officer role
        $officer->assignRole('officer');

        // Create tenant officer assignment
        TenantOfficer::create([
            'tenant_id'  => $tenant->id,
            'duka_id'    => $validated['duka_id'],
            'officer_id' => $officer->id,
            'role'       => 'officer',
            'status'     => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Officer created and assigned successfully.',
            'data'    => [
                'officer'    => [
                    'id'         => $officer->id,
                    'name'       => $officer->name,
                    'email'      => $officer->email,
                    'phone'      => $officer->phone,
                    'status'     => $officer->status,
                    'created_at' => $officer->created_at,
                ],
                'assignment' => [
                    'duka_id'   => $validated['duka_id'],
                    'duka_name' => $duka->name,
                    'role'      => $validated['role'] ?? 'officer',
                ],
            ],
        ], 201);
    }

    /**
     * Get all officers for the tenant.
     */
    public function apiGetOfficers(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();


        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Get all officers assigned to this tenant
        $officers = TenantOfficer::where('tenant_id', $tenant->id)
            ->with(['officer', 'duka'])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id'         => $assignment->officer->id,
                    'name'       => $assignment->officer->name,
                    'email'      => $assignment->officer->email,
                    'phone'      => $assignment->officer->phone,
                    'status'     => $assignment->officer->status,
                    'assignment' => [
                        'duka_id'           => $assignment->duka_id,
                        'duka_name'         => $assignment->duka->name ?? 'N/A',
                        'role'              => $assignment->role,
                        'assignment_status' => $assignment->status,
                        'assigned_at'       => $assignment->created_at,
                    ],
                    'created_at' => $assignment->officer->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'officers' => $officers,
                'total'    => $officers->count(),
            ],
        ]);
    }

    /**
     * Update an officer's information and/or assignment.
     */
    public function apiUpdateOfficer(Request $request, $officer_id)
    {
        // Get the authenticated user
        $user = Auth::user();


        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the officer
        $officer = \App\Models\User::find($officer_id);

        if (! $officer || ! $officer->hasRole('officer')) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found.',
            ], 404);
        }

        // Check if officer is assigned to this tenant
        $assignment = TenantOfficer::where('tenant_id', $tenant->id)
            ->where('officer_id', $officer_id)
            ->first();

        if (! $assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Officer is not assigned to this tenant.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|unique:users,email,' . $officer_id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'duka_id'  => 'nullable|exists:dukas,id',
            'role'     => 'nullable|string|max:50',
            'status'   => 'nullable|boolean',
        ]);

        // Check if new duka belongs to tenant (if provided)
        if (isset($validated['duka_id'])) {
            $duka = $tenant->dukas()->find($validated['duka_id']);
            if (! $duka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duka not found or does not belong to this tenant.',
                ], 404);
            }
        }

        // Update officer user data
        $updateData = array_filter([
            'name'   => $validated['name'] ?? null,
            'email'  => $validated['email'] ?? null,
            'phone'  => $validated['phone'] ?? null,
            'status' => isset($validated['status']) ? ($validated['status'] ? 'active' : 'inactive') : null,
        ]);

        if (! empty($updateData)) {
            $officer->update($updateData);
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $officer->update(['password' => Hash::make($validated['password'])]);
        }

        // Update assignment
        $assignmentUpdate = array_filter([
            'duka_id' => $validated['duka_id'] ?? null,
            'role'    => $validated['role'] ?? null,
            'status'  => $validated['status'] ?? null,
        ]);

        if (! empty($assignmentUpdate)) {
            $assignment->update($assignmentUpdate);
        }

        return response()->json([
            'success' => true,
            'message' => 'Officer updated successfully.',
            'data'    => [
                'officer'    => [
                    'id'     => $officer->id,
                    'name'   => $officer->name,
                    'email'  => $officer->email,
                    'phone'  => $officer->phone,
                    'status' => $officer->status,
                ],
                'assignment' => [
                    'duka_id'           => $assignment->duka_id,
                    'duka_name'         => $assignment->duka->name ?? 'N/A',
                    'role'              => $assignment->role,
                    'assignment_status' => $assignment->status,
                ],
            ],
        ]);
    }



    public function apiShowOfficerPermissions($officerId)
    {
        $user = auth()->user();

        // 1. Resolve Tenant context (Owner or Staff)
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);
        $tenant = Tenant::with(['activeSubscription.plan.features'])->find($tenantId);

        if (!$tenant || !$tenant->activeSubscription || !$tenant->activeSubscription->plan) {
            return response()->json([
                'success' => false,
                'message' => 'Active plan or subscription not found.'
            ], 404);
        }

        $activePlan = $tenant->activeSubscription->plan;
        $officer = User::find($officerId);

        if (!$officer) {
            return response()->json(['success' => false, 'message' => 'Officer not found.'], 404);
        }

        // 2. Verify Assignment
        $isAssigned = \App\Models\TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to officer data.'], 403);
        }

        // 3. Fetch Feature-based Permissions
        $allowedFeatureIds = $activePlan->features()->pluck('features.id')->toArray();

        $availablePermissions = \App\Models\AvailablePermission::where('is_active', true)
            ->whereIn('feature_id', $allowedFeatureIds)
            ->get()
            ->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => ucwords(str_replace('_', ' ', $perm->name)),
                    'feature' => $perm->feature->name ?? 'General',
                ];
            });

        // 4. Get Current Permissions (Granted status)
        $grantedPermissions = \App\Models\StaffPermission::where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->where('is_granted', true)
            ->pluck('permission_name')
            ->toArray();

        // 5. Get Assigned Dukas
        $assignedDukas = \App\Models\TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->with('duka:id,name,location')
            ->get()
            ->map(function ($assignment) {
                return [
                    'duka_id' => $assignment->duka->id ?? null,
                    'name'    => $assignment->duka->name ?? 'Deleted Duka',
                    'role'    => $assignment->role,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'officer' => [
                    'id' => $officer->id,
                    'name' => $officer->name,
                    'email' => $officer->email,
                ],
                'plan_info' => [
                    'name' => $activePlan->name,
                    'max_dukas' => $activePlan->max_dukas,
                ],
                'assigned_dukas' => $assignedDukas,
                'permissions_matrix' => $availablePermissions->map(function ($p) use ($grantedPermissions) {
                    return [
                        'permission_id' => $p['id'],
                        'permission_name' => $p['name'],
                        'display_name' => $p['display_name'],
                        'feature' => $p['feature'],
                        'is_granted' => in_array($p['name'], $grantedPermissions),
                    ];
                }),
            ]
        ]);
    }


    public function apiUpdateOfficerPermissions(Request $request, $officerId)
    {
        $user = auth()->user();
        // Resolve Tenant Context
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        $request->validate([
            'permissions'   => 'present|array', // 'present' ensures empty array is accepted to revoke all
            'permissions.*' => 'string',
        ]);

        $officer = User::find($officerId);
        if (!$officer) {
            return response()->json(['success' => false, 'message' => 'Officer not found'], 404);
        }

        // Verify officer assignment
        $isAssigned = \App\Models\TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $selectedPermissions = $request->input('permissions', []);

        try {
            DB::transaction(function () use ($tenantId, $officerId, $selectedPermissions) {
                // 1. Get officer's assigned duka IDs
                $dukaIds = \App\Models\TenantOfficer::where('tenant_id', $tenantId)
                    ->where('officer_id', $officerId)
                    ->pluck('duka_id');

                // 2. Remove all existing permissions
                \App\Models\StaffPermission::where('tenant_id', $tenantId)
                    ->where('officer_id', $officerId)
                    ->delete();

                // 3. Prepare Bulk Insert Data
                $insertData = [];
                foreach ($dukaIds as $dukaId) {
                    foreach ($selectedPermissions as $permissionName) {
                        $insertData[] = [
                            'tenant_id'       => $tenantId,
                            'officer_id'      => $officerId,
                            'duka_id'         => $dukaId,
                            'permission_name' => $permissionName,
                            'is_granted'      => true,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                }

                // 4. Perform Bulk Insert if there are permissions selected
                if (!empty($insertData)) {
                    \App\Models\StaffPermission::insert($insertData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully',
                'updated_count' => count($selectedPermissions)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/remove an officer from the tenant.
     */
    public function apiDeleteOfficer(Request $request, $officer_id)
    {
        // Get the authenticated user
        $user = Auth::user();


        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the officer
        $officer = \App\Models\User::find($officer_id);

        if (! $officer || ! $officer->hasRole('officer')) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found.',
            ], 404);
        }

        // Check if officer is assigned to this tenant
        $assignment = TenantOfficer::where('tenant_id', $tenant->id)
            ->where('officer_id', $officer_id)
            ->first();

        if (! $assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Officer is not assigned to this tenant.',
            ], 404);
        }

        // Remove the assignment (soft delete or deactivate)
        $assignment->update(['status' => false]);

        // Optionally deactivate the officer user
        $officer->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Officer removed successfully.',
            'data'    => [
                'officer_id' => $officer_id,
                'status'     => 'inactive',
            ],
        ]);
    }

    /**
     * Get tenant account information.
     */
    public function apiGetTenantAccount(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();


        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Get or create tenant account
        $tenantAccount = TenantAccount::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'company_name' => $tenant->name,
                'email'        => $tenant->email,
                'phone'        => $tenant->phone,
                'address'      => $tenant->address,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'tenant_account' => [
                    'id'           => $tenantAccount->id,
                    'tenant_id'    => $tenantAccount->tenant_id,
                    'company_name' => $tenantAccount->company_name,
                    'logo_url'     => $tenantAccount->logo_url,
                    'phone'        => $tenantAccount->phone,
                    'email'        => $tenantAccount->email,
                    'address'      => $tenantAccount->address,
                    'currency'     => $tenantAccount->currency,
                    'timezone'     => $tenantAccount->timezone,
                    'website'      => $tenantAccount->website,
                    'description'  => $tenantAccount->description,
                    'created_at'   => $tenantAccount->created_at,
                    'updated_at'   => $tenantAccount->updated_at,
                ],
            ],
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
        if (! $user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can manage their account.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email',
            'address'      => 'nullable|string',
            'currency'     => 'nullable|string|max:10',
            'timezone'     => 'nullable|string|max:50',
            'website'      => 'nullable|url',
            'description'  => 'nullable|string|max:1000',
        ]);

        // Update or create tenant account
        $tenantAccount = TenantAccount::updateOrCreate(
            ['tenant_id' => $tenant->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Tenant account updated successfully.',
            'data'    => [
                'tenant_account' => [
                    'id'           => $tenantAccount->id,
                    'tenant_id'    => $tenantAccount->tenant_id,
                    'company_name' => $tenantAccount->company_name,
                    'logo_url'     => $tenantAccount->logo_url,
                    'phone'        => $tenantAccount->phone,
                    'email'        => $tenantAccount->email,
                    'address'      => $tenantAccount->address,
                    'currency'     => $tenantAccount->currency,
                    'timezone'     => $tenantAccount->timezone,
                    'website'      => $tenantAccount->website,
                    'description'  => $tenantAccount->description,
                    'updated_at'   => $tenantAccount->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Get detailed product information including history, profit, sales, stock, and product items.
     */
    public function getproudctinfindetails(Request $request, $productId)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        // 1. Log the entry into the function
        Log::info("Product detailed view requested", [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id ?? 'N/A',
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
                'stockTransfers.fromDuka',
                'stockTransfers.toDuka',
                'stockTransfers.user',
            ])
            ->find($productId);

        // 2. Log if the product was not found or access was denied
        if (!$product) {
            Log::error("Product not found or unauthorized access attempt", [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'attempted_product_id' => $productId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // Get all sales history for this product
        $salesHistoryData = \App\Models\SaleItem::where('product_id', $productId)
            ->with('sale.customer')
            ->get();

        // 3. Log the metrics calculation (Useful for debugging financial discrepancies)
        $totalSold    = $salesHistoryData->sum('quantity');
        $totalRevenue = $salesHistoryData->sum('total');
        $totalCost    = $totalSold * $product->base_price;
        $totalProfit  = $totalRevenue - $totalCost;
        $profitMargin = $totalCost > 0 ? ($totalProfit / $totalCost) * 100 : 0;

        Log::info("Product metrics calculated", [
            'product_id' => $productId,
            'total_sold' => $totalSold,
            'current_stock' => $product->current_stock,
            'margin_percentage' => round($profitMargin, 2)
        ]);

        // Consolidate stock movements and transfers
        $stockMovements = $product->stocks->pluck('movements')->flatten();
        $stockTransfers = $product->stockTransfers;

        // Prepare comprehensive product data
        $productData = [
            'id'                  => $product->id,
            'sku'                 => $product->sku,
            'name'                => $product->name,
            'description'         => $product->description,
            'unit'                => $product->unit,
            'barcode'             => $product->barcode,
            'image_url'           => $product->image_url,
            'is_active'           => $product->is_active,
            'base_price'          => (float) $product->base_price,
            'selling_price'       => (float) $product->selling_price,
            'profit_per_unit'     => $product->profit_per_unit,
            'profit_margin'       => $product->profit_margin,
            'current_stock'       => $product->current_stock,
            'stock_cost_value'    => $product->stock_cost_value,
            'stock_selling_value' => $product->stock_selling_value,
            'category'            => $product->category ? [
                'id'          => $product->category->id,
                'name'        => $product->category->name,
                'description' => $product->category->description,
            ] : null,
            'duka'                => $product->duka ? [
                'id'       => $product->duka->id,
                'name'     => $product->duka->name,
                'location' => $product->duka->location,
            ] : null,
            'analytics' => [
                'total_units_sold' => $totalSold,
                'total_revenue'    => $totalRevenue,
                'total_cost'       => $totalCost,
                'total_profit'     => $totalProfit,
                'overall_profit_margin' => round($profitMargin, 2),
            ],
            'sales_history'        => $salesHistoryData->map(function ($saleItem) use ($product) {
                return [
                    'sale_id'         => $saleItem->sale->id,
                    'sale_date'       => $saleItem->sale->created_at->format('Y-m-d H:i:s'),
                    'customer_name'   => $saleItem->sale->customer->name ?? 'N/A',
                    'quantity'        => $saleItem->quantity,
                    'unit_price'      => (float) $saleItem->unit_price,
                    'total_amount'    => (float) $saleItem->total,
                    'profit_per_unit' => (float) $saleItem->unit_price - $product->base_price,
                    'total_profit'    => ((float) $saleItem->unit_price - $product->base_price) * $saleItem->quantity,
                ];
            }),
            'stock_details'       => $product->stocks->map(function ($stock) {
                return [
                    'id'           => $stock->id,
                    'duka_id'      => $stock->duka_id,
                    'quantity'     => $stock->quantity,
                    'batch_number' => $stock->batch_number,
                    'expiry_date'  => $stock->expiry_date?->format('Y-m-d'),
                    'notes'        => $stock->notes,
                    'created_at'   => $stock->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'stock_movements' => $stockMovements->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                    'reason' => $movement->reason,
                    'notes' => $movement->notes,
                    'user' => $movement->user->name ?? 'System',
                    'date' => $movement->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'stock_transfers' => $stockTransfers->map(function ($transfer) {
                return [
                    'id' => $transfer->id,
                    'from_duka' => $transfer->fromDuka->name,
                    'to_duka' => $transfer->toDuka->name,
                    'quantity' => $transfer->quantity,
                    'status' => $transfer->status,
                    'user' => $transfer->user->name ?? 'System',
                    'date' => $transfer->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'product_items' => $product->items,
            'created_at'          => $product->created_at->format('Y-m-d H:i:s'),
            'updated_at'          => $product->updated_at->format('Y-m-d H:i:s'),
        ];

        // 4. Log the final successful delivery of data
        Log::info("Product details successfully dispatched", [
            'product_id' => $productId,
            'history_count' => $salesHistoryData->count(),
            'movement_count' => $stockMovements->count()
        ]);

        return response()->json([
            'success' => true,
            'data'    => $productData,
        ]);
    }


    public function tenantaccount()
    {
        $user = Auth::user();
        if (! $user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can manage their account.',
            ], 403);
        }

        $tenantid      = $user->id;
        $tenantaccount = TenantAccount::where('tenant_id', $tenantid)->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'tenant_account' => $tenantaccount,
            ],
        ]);
    }

    /**
     * Display a listing of tenants (for super admin).
     */
    public function apiIndex(Request $request)
    {
        \Log::info('Tenant list request', [
            'user_id'    => Auth::id(),
            'user_role'  => Auth::user()->getRoleNames()->first(),
            'request_ip' => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (! $user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant index', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can access this endpoint.',
            ], 403);
        }

        $tenants = Tenant::with(['dukas', 'customers'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        \Log::info('Tenant list retrieved', [
            'total_tenants' => $tenants->total(),
            'per_page'      => $tenants->perPage(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $tenants,
        ]);
    }

    /**
     * Display the specified tenant.
     */
    public function apiShow(Request $request, $id)
    {
        \Log::info('Tenant show request', [
            'user_id'             => Auth::id(),
            'requested_tenant_id' => $id,
            'request_ip'          => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role or is the tenant themselves
        if (! $user->hasRole('super_admin') && $user->tenant_id != $id) {
            \Log::warning('Unauthorized access to tenant show', [
                'user_id'             => $user->id,
                'user_tenant_id'      => $user->tenant_id,
                'requested_tenant_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only access your own tenant information.',
            ], 403);
        }

        $tenant = Tenant::with(['dukas', 'customers', 'productCategories'])
            ->find($id);

        if (! $tenant) {
            \Log::warning('Tenant not found', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        \Log::info('Tenant retrieved successfully', [
            'tenant_id'   => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $tenant,
        ]);
    }

    /**
     * Store a newly created tenant.
     */
    public function apiStore(Request $request)
    {
        \Log::info('Tenant store request', [
            'user_id'    => Auth::id(),
            'user_role'  => Auth::user()->getRoleNames()->first(),
            'request_ip' => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (! $user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant store', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can create tenants.',
            ], 403);
        }

        // Validate the request data
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:tenants,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'slug'    => 'nullable|string|max:255|unique:tenants,slug',
            'status'  => 'nullable|in:active,inactive,suspended',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        // Create the tenant
        $tenant = Tenant::create($validated);

        \Log::info('Tenant created successfully', [
            'tenant_id'    => $tenant->id,
            'tenant_name'  => $tenant->name,
            'tenant_email' => $tenant->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully.',
            'data'    => [
                'tenant' => [
                    'id'         => $tenant->id,
                    'name'       => $tenant->name,
                    'email'      => $tenant->email,
                    'phone'      => $tenant->phone,
                    'address'    => $tenant->address,
                    'slug'       => $tenant->slug,
                    'status'     => $tenant->status,
                    'created_at' => $tenant->created_at->format('Y-m-d H:i:s'),
                ],
            ],
        ], 201);
    }

    /**
     * Update the specified tenant.
     */
    public function apiUpdate(Request $request, $id)
    {
        \Log::info('Tenant update request', [
            'user_id'           => Auth::id(),
            'updated_tenant_id' => $id,
            'request_ip'        => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role or is the tenant themselves
        if (! $user->hasRole('super_admin') && $user->tenant_id != $id) {
            \Log::warning('Unauthorized access to tenant update', [
                'user_id'           => $user->id,
                'user_tenant_id'    => $user->tenant_id,
                'updated_tenant_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own tenant information.',
            ], 403);
        }

        // Find the tenant
        $tenant = Tenant::find($id);

        if (! $tenant) {
            \Log::warning('Tenant not found for update', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'name'    => 'nullable|string|max:255',
            'email'   => 'nullable|email|unique:tenants,email,' . $id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'slug'    => 'nullable|string|max:255|unique:tenants,slug,' . $id,
            'status'  => 'nullable|in:active,inactive,suspended',
        ]);

        // Update the tenant
        $tenant->update($validated);

        \Log::info('Tenant updated successfully', [
            'tenant_id'      => $tenant->id,
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully.',
            'data'    => [
                'tenant' => [
                    'id'         => $tenant->id,
                    'name'       => $tenant->name,
                    'email'      => $tenant->email,
                    'phone'      => $tenant->phone,
                    'address'    => $tenant->address,
                    'slug'       => $tenant->slug,
                    'status'     => $tenant->status,
                    'updated_at' => $tenant->updated_at->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    /**
     * Remove the specified tenant.
     */
    public function apiDestroy(Request $request, $id)
    {
        \Log::info('Tenant destroy request', [
            'user_id'           => Auth::id(),
            'deleted_tenant_id' => $id,
            'request_ip'        => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has super admin role
        if (! $user->hasRole('super_admin')) {
            \Log::warning('Unauthorized access to tenant destroy', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super admins can delete tenants.',
            ], 403);
        }

        // Find the tenant
        $tenant = Tenant::find($id);

        if (! $tenant) {
            \Log::warning('Tenant not found for deletion', ['tenant_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Check if tenant has associated data
        $hasDukas     = $tenant->dukas()->count() > 0;
        $hasCustomers = $tenant->customers()->count() > 0;

        if ($hasDukas || $hasCustomers) {
            \Log::warning('Attempt to delete tenant with existing data', [
                'tenant_id'     => $id,
                'has_dukas'     => $hasDukas,
                'has_customers' => $hasCustomers,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tenant with existing dukas or customers. Please remove all associated data first.',
            ], 422);
        }

        // Delete the tenant
        $tenant->delete();

        \Log::info('Tenant deleted successfully', ['tenant_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully.',
            'data'    => [
                'tenant_id' => $id,
            ],
        ]);
    }

    /**
     * List all products for the tenant.
     */
    public function apiListProducts(Request $request)
    {
        \Log::info('Product list request', [
            'user_id'      => Auth::id(),
            'request_ip'   => $request->ip(),
            'query_params' => $request->all(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product list', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Apply sorting
        $sortBy            = $request->get('sort_by', 'created_at');
        $sortOrder         = $request->get('sort_order', 'desc');
        $allowedSortFields = ['name', 'sku', 'base_price', 'selling_price', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage  = min($request->get('per_page', 15), 100); // Max 100 items per page
        $products = $query->paginate($perPage);

        \Log::info('Product list retrieved successfully', [
            'tenant_id'      => $tenant->id,
            'total_products' => $products->total(),
            'per_page'       => $products->perPage(),
        ]);

        // Format the response data
        $formattedProducts = $products->getCollection()->map(function ($product) {
            return [
                'id'              => $product->id,
                'sku'             => $product->sku,
                'name'            => $product->name,
                'description'     => $product->description,
                'unit'            => $product->unit,
                'base_price'      => $product->base_price,
                'selling_price'   => $product->selling_price,
                'profit_per_unit' => $product->profit_per_unit,
                'profit_margin'   => $product->profit_margin,
                'is_active'       => $product->is_active,
                'image_url'       => $product->image_url,
                'barcode'         => $product->barcode,
                'current_stock'   => $product->current_stock,
                'stock_value'     => $product->stock_cost_value,
                'category'        => $product->category ? [
                    'id'   => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'duka'            => $product->duka ? [
                    'id'       => $product->duka->id,
                    'name'     => $product->duka->name,
                    'location' => $product->duka->location,
                ] : null,
                'created_at'      => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at'      => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'products'   => $formattedProducts,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                    'from'         => $products->firstItem(),
                    'to'           => $products->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Show a specific product for the tenant.
     */
    public function apiShowProduct(Request $request, $productId)
    {
        \Log::info('Product show request', [
            'user_id'    => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product show', [
                'user_id'    => $user->id,
                'user_role'  => $user->getRoleNames()->first(),
                'product_id' => $productId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
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
                'stockTransfers',
            ])
            ->find($productId);

        if (! $product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id'  => $tenant->id,
                'product_id' => $productId,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.',
            ], 404);
        }

        \Log::info('Product found and loaded', [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'duka_id'      => $product->duka_id,
        ]);

        // Get sales history for this product
        $salesHistory = SaleItem::where('product_id', $productId)
            ->with(['sale.customer', 'sale'])
            ->latest()
            ->limit(10)
            ->get();

        // Prepare comprehensive product data
        $productData = [
            'id'                  => $product->id,
            'sku'                 => $product->sku,
            'name'                => $product->name,
            'description'         => $product->description,
            'unit'                => $product->unit,
            'barcode'             => $product->barcode,
            'image_url'           => $product->image_url,
            'is_active'           => $product->is_active,
            'base_price'          => $product->base_price,
            'selling_price'       => $product->selling_price,
            'profit_per_unit'     => $product->profit_per_unit,
            'profit_margin'       => $product->profit_margin,
            'current_stock'       => $product->current_stock,
            'stock_cost_value'    => $product->stock_cost_value,
            'stock_selling_value' => $product->stock_selling_value,
            'category'            => $product->category ? [
                'id'          => $product->category->id,
                'name'        => $product->category->name,
                'description' => $product->category->description,
            ] : null,
            'duka'                => $product->duka ? [
                'id'       => $product->duka->id,
                'name'     => $product->duka->name,
                'location' => $product->duka->location,
            ] : null,
            'recent_sales'        => $salesHistory->map(function ($saleItem) use ($product) {
                return [
                    'sale_id'         => $saleItem->sale->id,
                    'sale_date'       => $saleItem->sale->created_at->format('Y-m-d H:i:s'),
                    'customer_name'   => $saleItem->sale->customer->name ?? 'N/A',
                    'quantity'        => $saleItem->quantity,
                    'unit_price'      => $saleItem->unit_price,
                    'total_amount'    => $saleItem->total,
                    'profit_per_unit' => $saleItem->unit_price - $product->base_price,
                    'total_profit'    => ($saleItem->unit_price - $product->base_price) * $saleItem->quantity,
                ];
            }),
            'stock_details'       => $product->stocks->map(function ($stock) {
                return [
                    'id'           => $stock->id,
                    'quantity'     => $stock->quantity,
                    'batch_number' => $stock->batch_number,
                    'expiry_date'  => $stock->expiry_date?->format('Y-m-d'),
                    'notes'        => $stock->notes,
                    'created_at'   => $stock->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'created_at'          => $product->created_at->format('Y-m-d H:i:s'),
            'updated_at'          => $product->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $productData,
        ]);
    }

    /**
     * Create a new product for the tenant.
     */
    public function apiCreateProduct(Request $request)
    {
        \Log::info('Product create request', [
            'user_id'      => Auth::id(),
            'request_ip'   => $request->ip(),
            'request_data' => $request->all(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product create', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can create products.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'duka_id'       => 'required|exists:dukas,id',
            'category_id'   => 'nullable|exists:product_categories,id',
            'sku'           => 'required|string|max:255|unique:products,sku',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'required|string|max:50',
            'base_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active'     => 'nullable|boolean',
        ]);

        // Check if duka belongs to tenant
        $duka = $tenant->dukas()->find($validated['duka_id']);
        if (! $duka) {
            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.',
            ], 404);
        }

        // Handle image upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $imagePath = $imageName;
        }

        // Create the product
        $product = Product::create([
            'tenant_id'     => $tenant->id,
            'duka_id'       => $validated['duka_id'],
            'category_id'   => $validated['category_id'] ?? null,
            'sku'           => $validated['sku'],
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'unit'          => $validated['unit'],
            'base_price'    => $validated['base_price'],
            'selling_price' => $validated['selling_price'],
            'barcode'       => $validated['barcode'] ?? null,
            'image'         => $imagePath,
            'is_active'     => $validated['is_active'] ?? true,
        ]);

        \Log::info('Product created successfully', [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'tenant_id'    => $tenant->id,
            'duka_id'      => $product->duka_id,
        ]);

        // Load relationships for response
        $product->load(['duka', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data'    => [
                'product' => [
                    'id'              => $product->id,
                    'sku'             => $product->sku,
                    'name'            => $product->name,
                    'description'     => $product->description,
                    'unit'            => $product->unit,
                    'base_price'      => $product->base_price,
                    'selling_price'   => $product->selling_price,
                    'profit_per_unit' => $product->profit_per_unit,
                    'profit_margin'   => $product->profit_margin,
                    'is_active'       => $product->is_active,
                    'image_url'       => $product->image_url,
                    'barcode'         => $product->barcode,
                    'current_stock'   => $product->current_stock,
                    'category'        => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'duka'            => [
                        'id'       => $product->duka->id,
                        'name'     => $product->duka->name,
                        'location' => $product->duka->location,
                    ],
                    'created_at'      => $product->created_at->format('Y-m-d H:i:s'),
                ],
            ],
        ], 201);
    }

    /**
     * Update a product for the tenant.
     */
    public function apiUpdateProduct(Request $request, $productId)
    {
        \Log::info('Product update request', [
            'user_id'      => Auth::id(),
            'product_id'   => $productId,
            'request_ip'   => $request->ip(),
            'request_data' => $request->all(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product update', [
                'user_id'    => $user->id,
                'user_role'  => $user->getRoleNames()->first(),
                'product_id' => $productId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can update products.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)->find($productId);

        if (! $product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id'  => $tenant->id,
                'product_id' => $productId,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.',
            ], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'duka_id'       => 'nullable|exists:dukas,id',
            'category_id'   => 'nullable|exists:product_categories,id',
            'sku'           => 'nullable|string|max:255|unique:products,sku,' . $productId,
            'name'          => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'nullable|string|max:50',
            'base_price'    => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode,' . $productId,
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active'     => 'nullable|boolean',
        ]);

        // Check if duka belongs to tenant (if provided)
        if (isset($validated['duka_id'])) {
            $duka = $tenant->dukas()->find($validated['duka_id']);
            if (! $duka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duka not found or does not belong to this tenant.',
                ], 404);
            }
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $validated['image'] = $imageName;
        }

        // Update the product
        $product->update($validated);

        \Log::info('Product updated successfully', [
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'updated_fields' => array_keys($validated),
        ]);

        // Load relationships for response
        $product->load(['duka', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data'    => [
                'product' => [
                    'id'              => $product->id,
                    'sku'             => $product->sku,
                    'name'            => $product->name,
                    'description'     => $product->description,
                    'unit'            => $product->unit,
                    'base_price'      => $product->base_price,
                    'selling_price'   => $product->selling_price,
                    'profit_per_unit' => $product->profit_per_unit,
                    'profit_margin'   => $product->profit_margin,
                    'is_active'       => $product->is_active,
                    'image_url'       => $product->image_url,
                    'barcode'         => $product->barcode,
                    'current_stock'   => $product->current_stock,
                    'category'        => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'duka'            => [
                        'id'       => $product->duka->id,
                        'name'     => $product->duka->name,
                        'location' => $product->duka->location,
                    ],
                    'updated_at'      => $product->updated_at->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    /**
     * Delete a product for the tenant.
     */
    public function apiDeleteProduct(Request $request, $productId)
    {
        \Log::info('Product delete request', [
            'user_id'    => Auth::id(),
            'product_id' => $productId,
            'request_ip' => $request->ip(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to product delete', [
                'user_id'    => $user->id,
                'user_role'  => $user->getRoleNames()->first(),
                'product_id' => $productId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can delete products.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the product that belongs to the tenant
        $product = Product::where('tenant_id', $tenant->id)->find($productId);

        if (! $product) {
            \Log::warning('Product not found or access denied', [
                'tenant_id'  => $tenant->id,
                'product_id' => $productId,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found or does not belong to this tenant.',
            ], 404);
        }

        // Check if product has associated sales
        $hasSales = SaleItem::where('product_id', $productId)->exists();
        $hasStock = $product->stocks()->exists();
        $hasItems = $product->items()->exists();

        if ($hasSales) {
            \Log::warning('Attempt to delete product with existing sales', [
                'product_id' => $productId,
                'tenant_id'  => $tenant->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product with existing sales. Consider deactivating it instead.',
            ], 422);
        }

        // If product has stock or items, we can still delete but log it
        if ($hasStock || $hasItems) {
            \Log::warning('Deleting product with existing stock or items', [
                'product_id' => $productId,
                'tenant_id'  => $tenant->id,
                'has_stock'  => $hasStock,
                'has_items'  => $hasItems,
            ]);
        }

        // Delete the product (this will also delete related stock, items, etc. due to foreign key constraints)
        $productName = $product->name;
        $product->delete();

        \Log::info('Product deleted successfully', [
            'product_id'   => $productId,
            'product_name' => $productName,
            'tenant_id'    => $tenant->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data'    => [
                'product_id'   => $productId,
                'product_name' => $productName,
            ],
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
        if (! $user->hasRole('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.',
            ], 403);
        }

        // Get all features
        $features = Feature::orderBy('name', 'asc')->get();

        // Format the response
        $formattedFeatures = $features->map(function ($feature) {
            return [
                'id'          => $feature->id,
                'code'        => $feature->code,
                'name'        => $feature->name,
                'description' => $feature->description,
                'created_at'  => $feature->created_at,
                'updated_at'  => $feature->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'features' => $formattedFeatures,
                'total'    => $formattedFeatures->count(),
            ],
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
                'data'    => $plans,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plans.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comprehensive duka overview with analytics and growth metrics.
     */
    public function apiGetDukaOverview(Request $request, $dukaId)
    {
        \Log::info('Duka overview request', [
            'user_id'      => Auth::id(),
            'duka_id'      => $dukaId,
            'request_ip'   => $request->ip(),
            'query_params' => $request->all(),
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if user has tenant role
        if (! $user->hasRole('tenant')) {
            \Log::warning('Unauthorized access to duka overview', [
                'user_id'   => $user->id,
                'user_role' => $user->getRoleNames()->first(),
                'duka_id'   => $dukaId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only tenants can access this endpoint.',
            ], 403);
        }

        // Get the tenant associated with the user
        $tenant = $user->tenant;

        if (! $tenant) {
            \Log::error('Tenant not found for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        // Find the duka that belongs to the tenant
        $duka = $tenant->dukas()->find($dukaId);

        if (! $duka) {
            \Log::warning('Duka not found or access denied', [
                'tenant_id' => $tenant->id,
                'duka_id'   => $dukaId,
                'user_id'   => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Duka not found or does not belong to this tenant.',
            ], 404);
        }

        \Log::info('Duka found and loading analytics', [
            'duka_id'   => $duka->id,
            'duka_name' => $duka->name,
        ]);

        // Get date range for analytics (default: last 30 days)
        $endDate   = $request->get('end_date', now()->format('Y-m-d'));
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));

        // Previous period for comparison
        $daysDiff          = \Carbon\Carbon::parse($endDate)->diffInDays(\Carbon\Carbon::parse($startDate));
        $previousEndDate   = \Carbon\Carbon::parse($startDate)->subDays(1)->format('Y-m-d');
        $previousStartDate = \Carbon\Carbon::parse($startDate)->subDays($daysDiff + 1)->format('Y-m-d');

        // Load relationships
        $duka->load([
            'products.category',
            'products.stocks',
            'customers',
            'sales.customer',
            'sales.saleItems.product',
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
        $currentRevenue           = $currentSales->sum('total_amount');
        $currentProfit            = $currentSales->sum('profit_loss');
        $currentTransactions      = $currentSales->count();
        $currentAverageOrderValue = $currentTransactions > 0 ? $currentRevenue / $currentTransactions : 0;

        // Previous Period Metrics
        $previousRevenue           = $previousSales->sum('total_amount');
        $previousProfit            = $previousSales->sum('profit_loss');
        $previousTransactions      = $previousSales->count();
        $previousAverageOrderValue = $previousTransactions > 0 ? $previousRevenue / $previousTransactions : 0;

        // Growth Calculations
        $revenueGrowth     = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        $profitGrowth      = $previousProfit > 0 ? (($currentProfit - $previousProfit) / $previousProfit) * 100 : 0;
        $transactionGrowth = $previousTransactions > 0 ? (($currentTransactions - $previousTransactions) / $previousTransactions) * 100 : 0;
        $aovGrowth         = $previousAverageOrderValue > 0 ? (($currentAverageOrderValue - $previousAverageOrderValue) / $previousAverageOrderValue) * 100 : 0;

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
        $currentIncome      = $currentCashFlows->where('type', 'income')->sum('amount');
        $currentExpenses    = $currentCashFlows->where('type', 'expense')->sum('amount');
        $currentNetCashFlow = $currentIncome - $currentExpenses;

        $previousIncome      = $previousCashFlows->where('type', 'income')->sum('amount');
        $previousExpenses    = $previousCashFlows->where('type', 'expense')->sum('amount');
        $previousNetCashFlow = $previousIncome - $previousExpenses;

        // Sales-based income calculation (more accurate for business performance)
        $currentSalesIncome  = $currentSales->sum('total_amount');
        $previousSalesIncome = $previousSales->sum('total_amount');

        // Calculate cost of goods sold (COGS) from sales
        $currentCOGS = $currentSales->sum(function ($sale) {
            return $sale->saleItems->sum(function ($item) {
                return $item->quantity * $item->product->base_price;
            });
        });

        $previousCOGS = $previousSales->sum(function ($sale) {
            return $sale->saleItems->sum(function ($item) {
                return $item->quantity * $item->product->base_price;
            });
        });

        // Gross profit from sales
        $currentGrossProfit  = $currentSalesIncome - $currentCOGS;
        $previousGrossProfit = $previousSalesIncome - $previousCOGS;

        // Sales-based growth metrics
        $salesIncomeGrowth = $previousSalesIncome > 0 ? (($currentSalesIncome - $previousSalesIncome) / $previousSalesIncome) * 100 : 0;
        $grossProfitGrowth = $previousGrossProfit > 0 ? (($currentGrossProfit - $previousGrossProfit) / $previousGrossProfit) * 100 : 0;

        $cashFlowGrowth = $previousNetCashFlow != 0 ? (($currentNetCashFlow - $previousNetCashFlow) / abs($previousNetCashFlow)) * 100 : 0;

        // ==========================
        // PRODUCT ANALYTICS
        // ==========================

        $products       = $duka->products;
        $totalProducts  = $products->count();
        $activeProducts = $products->where('is_active', true)->count();

        // Stock Analysis
        $totalStockCostValue    = $products->sum('stock_cost_value');
        $totalStockSellingValue = $products->sum('stock_selling_value');
        $totalPotentialProfit   = $totalStockSellingValue - $totalStockCostValue;

        // Low Stock Alert
        $lowStockProducts = $products->filter(function ($product) {
            return $product->current_stock > 0 && $product->current_stock <= 10;
        })->count();

        $outOfStockProducts = $products->filter(function ($product) {
            return $product->current_stock <= 0;
        })->count();

        // Top Selling Products
        $topSellingProducts = SaleItem::whereHas('sale', function ($query) use ($dukaId, $startDate, $endDate) {
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

        $totalCustomers  = $duka->customers->count();
        $activeCustomers = $currentSales->unique('customer_id')->count();
        $newCustomers    = Customer::where('duka_id', $dukaId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $returningCustomers = $currentSales->filter(function ($sale) use ($dukaId, $startDate) {
            $oldCustomers = Customer::where('duka_id', $dukaId)
                ->where('created_at', '<', $startDate)
                ->pluck('id')
                ->toArray();
            return in_array($sale->customer_id, $oldCustomers);
        })->unique('customer_id')->count();

        // ==========================
        // LOAN ANALYTICS
        // ==========================

        $totalLoans      = $currentSales->where('is_loan', true)->count();
        $totalLoanAmount = $currentSales->where('is_loan', true)->sum('total_amount');

        // Get outstanding loans - fix the whereHas issue
        $outstandingLoanSales = Sale::where('duka_id', $dukaId)
            ->where('is_loan', true)
            ->whereHas('loanPayments')
            ->with(['loanPayments' => function ($query) {
                $query->selectRaw('sale_id, SUM(amount) as total_paid')
                    ->groupBy('sale_id');
            }])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $outstandingLoans = $outstandingLoanSales->filter(function ($sale) {
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
        $growthScore   = 0;
        $growthFactors = [
            max(0, min(100, ($revenueGrowth + 100) / 2)) * 0.25,     // Revenue growth (25%)
            max(0, min(100, ($profitGrowth + 100) / 2)) * 0.20,      // Profit growth (20%)
            max(0, min(100, ($salesIncomeGrowth + 100) / 2)) * 0.20, // Sales income growth (20%)
            max(0, min(100, ($grossProfitGrowth + 100) / 2)) * 0.15, // Gross profit growth (15%)
            max(0, min(100, ($transactionGrowth + 100) / 2)) * 0.10, // Transaction growth (10%)
            max(0, min(100, ($cashFlowGrowth + 100) / 2)) * 0.05,    // Cash flow growth (5%)
            max(0, min(100, ($weeklyGrowth + 100) / 2)) * 0.05,      // Weekly growth (5%)
        ];
        $growthScore = array_sum($growthFactors);

        // Determine growth status
        if ($growthScore >= 75) {
            $growthStatus      = 'Excellent Growth';
            $growthStatusColor = 'success';
        } elseif ($growthScore >= 50) {
            $growthStatus      = 'Good Growth';
            $growthStatusColor = 'info';
        } elseif ($growthScore >= 25) {
            $growthStatus      = 'Moderate Growth';
            $growthStatusColor = 'warning';
        } else {
            $growthStatus      = 'Needs Attention';
            $growthStatusColor = 'danger';
        }

        // ==========================
        // CASH FLOW BY CATEGORY
        // ==========================

        $cashFlowByCategory = $currentCashFlows->groupBy('category')->map(function ($items) {
            return [
                'income'  => $items->where('type', 'income')->sum('amount'),
                'expense' => $items->where('type', 'expense')->sum('amount'),
                'net'     => $items->where('type', 'income')->sum('amount') - $items->where('type', 'expense')->sum('amount'),
            ];
        });

        \Log::info('Duka overview analytics completed', [
            'duka_id' => $dukaId,
            'period'  => "{$startDate} to {$endDate}",
            'growth_score'   => $growthScore,
            'revenue_growth' => $revenueGrowth,
        ]);

        // Prepare comprehensive response
        $overviewData = [
            'duka_info'              => [
                'id'           => $duka->id,
                'name'         => $duka->name,
                'location'     => $duka->location,
                'manager_name' => $duka->manager_name,
                'status'       => $duka->status,
                'created_at'   => $duka->created_at->format('Y-m-d'),
                'period'       => [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'days'       => $daysDiff + 1,
                ],
            ],

            'financial_summary'      => [
                'current_period'  => [
                    'revenue'             => $currentRevenue,
                    'profit'              => $currentProfit,
                    'profit_margin'       => $currentRevenue > 0 ? ($currentProfit / $currentRevenue) * 100 : 0,
                    'transactions'        => $currentTransactions,
                    'average_order_value' => $currentAverageOrderValue,
                    'cash_flow'           => [
                        'recorded_income'   => $currentIncome,
                        'recorded_expenses' => $currentExpenses,
                        'recorded_net'      => $currentNetCashFlow,
                    ],
                    'sales_based_income'  => [
                        'total_sales_income'  => $currentSalesIncome,
                        'cost_of_goods_sold'  => $currentCOGS,
                        'gross_profit'        => $currentGrossProfit,
                        'gross_profit_margin' => $currentSalesIncome > 0 ? ($currentGrossProfit / $currentSalesIncome) * 100 : 0,
                    ],
                ],
                'previous_period' => [
                    'revenue'             => $previousRevenue,
                    'profit'              => $previousProfit,
                    'profit_margin'       => $previousRevenue > 0 ? ($previousProfit / $previousRevenue) * 100 : 0,
                    'transactions'        => $previousTransactions,
                    'average_order_value' => $previousAverageOrderValue,
                    'cash_flow'           => [
                        'recorded_income'   => $previousIncome,
                        'recorded_expenses' => $previousExpenses,
                        'recorded_net'      => $previousNetCashFlow,
                    ],
                    'sales_based_income'  => [
                        'total_sales_income'  => $previousSalesIncome,
                        'cost_of_goods_sold'  => $previousCOGS,
                        'gross_profit'        => $previousGrossProfit,
                        'gross_profit_margin' => $previousSalesIncome > 0 ? ($previousGrossProfit / $previousSalesIncome) * 100 : 0,
                    ],
                ],
                'growth_metrics'  => [
                    'revenue_growth'      => round($revenueGrowth, 2),
                    'profit_growth'       => round($profitGrowth, 2),
                    'transaction_growth'  => round($transactionGrowth, 2),
                    'aov_growth'          => round($aovGrowth, 2),
                    'cash_flow_growth'    => round($cashFlowGrowth, 2),
                    'sales_income_growth' => round($salesIncomeGrowth, 2),
                    'gross_profit_growth' => round($grossProfitGrowth, 2),
                    'weekly_growth'       => round($weeklyGrowth, 2),
                ],
            ],

            'product_analytics'      => [
                'summary'              => [
                    'total_products'        => $totalProducts,
                    'active_products'       => $activeProducts,
                    'inactive_products'     => $totalProducts - $activeProducts,
                    'low_stock_products'    => $lowStockProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'stock_health_score'    => $totalProducts > 0 ? (($activeProducts - $lowStockProducts - $outOfStockProducts) / $totalProducts) * 100 : 0,
                ],
                'inventory_value'      => [
                    'total_cost_value'        => $totalStockCostValue,
                    'total_selling_value'     => $totalStockSellingValue,
                    'potential_profit'        => $totalPotentialProfit,
                    'profit_margin_potential' => $totalStockCostValue > 0 ? ($totalPotentialProfit / $totalStockCostValue) * 100 : 0,
                ],
                'top_selling_products' => $topSellingProducts->map(function ($item) {
                    return [
                        'product_id'            => $item->product_id,
                        'product_name'          => $item->product->name ?? 'Unknown',
                        'total_quantity_sold'   => $item->total_quantity,
                        'total_revenue'         => $item->total_revenue,
                        'average_selling_price' => $item->total_quantity > 0 ? $item->total_revenue / $item->total_quantity : 0,
                    ];
                }),
            ],

            'customer_analytics'     => [
                'total_customers'           => $totalCustomers,
                'active_customers'          => $activeCustomers,
                'new_customers'             => $newCustomers,
                'returning_customers'       => $returningCustomers,
                'customer_retention_rate'   => $activeCustomers > 0 ? ($returningCustomers / $activeCustomers) * 100 : 0,
                'customer_acquisition_rate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
            ],

            'loan_analytics'         => [
                'total_loans'          => $totalLoans,
                'total_loan_amount'    => $totalLoanAmount,
                'outstanding_loans'    => $outstandingLoans,
                'outstanding_amount'   => $totalOutstandingLoanAmount,
                'loan_collection_rate' => $totalLoanAmount > 0 ? (($totalLoanAmount - $totalOutstandingLoanAmount) / $totalLoanAmount) * 100 : 0,
                'average_loan_size'    => $totalLoans > 0 ? $totalLoanAmount / $totalLoans : 0,
            ],

            'performance_indicators' => [
                'growth_score'  => round($growthScore, 2),
                'growth_status' => $growthStatus,
                'status_color'  => $growthStatusColor,
                'key_insights'  => [
                    $revenueGrowth > 0 ? 'Revenue is growing' : 'Revenue is declining',
                    $profitGrowth > 0 ? 'Profitability is improving' : 'Profitability is declining',
                    $salesIncomeGrowth > 0 ? 'Sales income is increasing' : 'Sales income is decreasing',
                    $grossProfitGrowth > 0 ? 'Gross profit margins are improving' : 'Gross profit margins are declining',
                    $currentNetCashFlow > 0 ? 'Positive recorded cash flow' : 'Negative recorded cash flow',
                    $currentSalesIncome > $currentCOGS ? 'Healthy sales profitability' : 'Sales profitability needs attention',
                    $lowStockProducts > 0 ? "{$lowStockProducts} products need restocking" : 'Stock levels are healthy',
                    $weeklyGrowth > 0 ? 'Weekly performance is improving' : 'Weekly performance needs attention',
                ],
            ],

            'trend_analysis' => [
                'daily_sales'           => $dailySales->map(function ($day) {
                    return [
                        'date'                => $day->date,
                        'revenue'             => $day->revenue,
                        'transactions'        => $day->transactions,
                        'average_transaction' => $day->transactions > 0 ? $day->revenue / $day->transactions : 0,
                    ];
                }),
                'cash_flow_by_category' => $cashFlowByCategory->map(function ($category, $name) {
                    return [
                        'category' => $name,
                        'income'   => $category['income'],
                        'expenses' => $category['expense'],
                        'net'      => $category['net'],
                    ];
                })->values(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data'    => $overviewData,
        ]);
    }




    public function apiGetDukas(Request $request)
    {
        $user = Auth::user();
        // Get tenant ID from user (Works for Owner or Officer)
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        $today = now()->toDateString();

        // Fetch Dukas with counts and today's stats
        $dukas = Duka::where('tenant_id', $tenantId)
            ->withCount(['sales', 'customers', 'stocks'])
            ->get()
            ->map(function ($duka) use ($today) {
                // Calculate today's performance for this specific Duka
                $todayStats = $duka->sales()
                    ->whereDate('created_at', $today)
                    ->selectRaw('SUM(total_amount) as total_sales, SUM(profit_loss) as total_profit')
                    ->first();

                return [
                    'id' => $duka->id,
                    'name' => $duka->name,
                    'location' => $duka->location,
                    'manager' => $duka->manager_name,
                    'status' => $duka->status,
                    'stats' => [
                        'total_customers' => $duka->customers_count,
                        'total_products_in_stock' => $duka->stocks_count,
                        'today_sales' => (float) ($todayStats->total_sales ?? 0),
                        'today_profit' => (float) ($todayStats->total_profit ?? 0),
                    ],
                    'created_at' => $duka->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $dukas->count(),
            'data' => $dukas
        ]);
    }


    public function apiGetSales(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);
        $dukaId = $request->query('duka_id');

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        Log::info("Fetching sales for Duka", [
            'tenant_id' => $tenantId,
            'duka_id' => $dukaId,
            'requested_by' => $user->id
        ]);

        // Build query
        $query = Sale::where('tenant_id', $tenantId)
            ->with([
                'customer:id,name,phone',
                'saleItems.product:id,name,sku,image,unit',
                'duka:id,name'
            ]);

        // Filter by Duka if provided
        if ($dukaId) {
            $query->where('duka_id', $dukaId);
        }

        $sales = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count' => $sales->count(),
            'data' => $sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_no' => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    'duka_name' => $sale->duka->name ?? 'N/A',
                    'customer_name' => $sale->customer->name ?? 'Walk-in Customer',
                    'total_amount' => (float) $sale->total_amount,
                    'remaining_balance' => (float) $sale->remaining_balance,
                    'payment_status' => $sale->payment_status,
                    'is_loan' => (bool) $sale->is_loan,
                    'sale_date' => $sale->created_at->format('Y-m-d H:i:s'),
                    'products' => $sale->saleItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product->name ?? 'Deleted Product',
                            'quantity' => $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'total' => (float) $item->total,
                        ];
                    }),
                ];
            })
        ]);
    }
    public function apiConsolidatedProfitLoss(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant not found'], 404);
        }

        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now();

        $dukaIds = Duka::where('tenant_id', $tenantId)->pluck('id');

        // 1. Sales Revenue (Direct sales)
        $salesRevenue = (float) Transaction::whereIn('duka_id', $dukaIds)
            ->where('category', 'sale')
            ->where('status', '!=', 'void')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        // 2. Loan Repayments (Debt collection)
        $loanRepayments = (float) Transaction::whereIn('duka_id', $dukaIds)
            ->where('category', 'loan_repayment')
            ->where('status', '!=', 'void')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        // Total Cash Inflow (Revenue)
        // FIX: Sales Revenue already includes full value of invoices (Accrual Basis).
        // Adding loan repayments (Cash Basis) would double-count revenue.
        // Loan repayments are cash flow, but not new revenue for P&L.
        $totalRevenue = $salesRevenue;

        // 3. Net COGS: (Cost of Sales) - (Cost of Returns)
        $movements = StockMovement::whereHas('stock', function ($q) use ($dukaIds) {
            $q->whereIn('duka_id', $dukaIds);
        })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $cogsOut = $movements->where('type', 'out')->where('reason', 'sale')->sum(function ($m) {
            return (float) $m->quantity_change * (float) $m->unit_cost;
        });

        $cogsIn = $movements->where('type', 'in')->where('reason', 'sale_return')->sum(function ($m) {
            return (float) $m->quantity_change * (float) $m->unit_cost;
        });

        $cogs = $cogsOut - $cogsIn;

        // 4. Operating Expenses
        $expenseGroups = Transaction::whereIn('duka_id', $dukaIds)
            ->where('type', 'expense')
            ->where('category', '!=', 'stock_purchase')
            ->where('status', '!=', 'void')
            ->whereBetween('transaction_date', [$start, $end])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->map(function ($exp) {
                return [
                    'category' => ucwords(str_replace('_', ' ', $exp->category)),
                    'amount' => (float) $exp->total
                ];
            });

        $totalExpenses = $expenseGroups->sum('amount');

        // 5. Calculations
        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        // 6. Inventory Asset Valuation
        $totalStockBuyingValue = (float) Product::where('tenant_id', $tenantId)
            ->join('stocks', 'products.id', '=', 'stocks.product_id')
            ->selectRaw('SUM(stocks.quantity * products.base_price) as total_value')
            ->value('total_value');

        return response()->json([
            'success'   => true,
            'period'    => ['from' => $start->toDateString(), 'to' => $end->toDateString()],
            'data' => [
                'summary' => [
                    'sales_revenue'   => $salesRevenue,
                    'loan_repayments' => $loanRepayments,
                    'total_revenue'   => $totalRevenue,
                    'cogs'            => $cogs,
                    'gross_profit'    => $grossProfit,
                    'total_expenses'  => $totalExpenses,
                    'net_profit'      => $netProfit,
                    'profit_margin'   => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0,
                ],
                'expense_breakdown'   => $expenseGroups,
                'assets' => [
                    'inventory_valuation' => $totalStockBuyingValue,
                ]
            ]
        ]);
    }



    private function getTenantId()
    {
        $user = auth()->user();
        return $user->tenant_id ?? ($user->tenant ? $user->tenant->id : null);
    }

    // 1. List Categories
    public function apiListCategories()
    {
        $tenantId = $this->getTenantId();
        $categories = ProductCategory::where('tenant_id', $tenantId)
            ->withCount('products')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    // 2. Show Single Category
    public function apiShowCategory($categoryId)
    {
        $tenantId = $this->getTenantId();
        $category = ProductCategory::where('tenant_id', $tenantId)->find($categoryId);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $category]);
    }

    // 3. Create Category
    public function apiCreateCategory(Request $request)
    {
        $tenantId = $this->getTenantId();
        $request->validate(['name' => 'required|string|max:255']);

        $category = ProductCategory::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $category, 'message' => 'Category created']);
    }

    // 4. Update Category
    public function apiUpdateCategory(Request $request, $categoryId)
    {
        $tenantId = $this->getTenantId();
        $category = ProductCategory::where('tenant_id', $tenantId)->find($categoryId);

        if (!$category) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $category->update($request->only(['name', 'description', 'status']));

        return response()->json(['success' => true, 'data' => $category, 'message' => 'Category updated']);
    }

    // 5. Delete Category
    public function apiDeleteCategory($categoryId)
    {
        $tenantId = $this->getTenantId();
        $category = ProductCategory::where('tenant_id', $tenantId)->find($categoryId);

        if ($category && $category->products()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: Category has products'], 400);
        }

        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }

    // 6. Get Product Count
    public function apiGetCategoryProductCount($categoryId)
    {
        $tenantId = $this->getTenantId();
        $count = \App\Models\Product::where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->count();

        return response()->json(['success' => true, 'product_count' => $count]);
    }
}

<?php
namespace App\Http\Controllers;

use App\Mail\OfficerAssignmentMail;
use App\Mail\OfficerUnassignmentMail;
use App\Models\Duka;
use App\Models\TenantOfficer;
use App\Models\User;
use App\Models\StaffPermission;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class OfficerController extends Controller
{

    public function dashboard()
    {
        $user = auth()->user();
        // Get tenant ID from officer's assignments
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();
        $tenantId = $assignment ? $assignment->tenant_id : null;

        if (!$tenantId) {
            abort(403, 'No active assignments found.');
        }

        // Get officer's assigned dukas
        $assignedDukas = TenantOfficer::with('duka')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        // Get officer's permissions
        $permissions = StaffPermission::where('tenant_id', $tenantId)
            ->where('officer_id', $user->id)
            ->where('is_granted', true)
            ->with('duka')
            ->get();

        // Recent sales from assigned dukas (if has sale_report permission)
        $recentSales = collect();
        if ($this->hasPermission('sale_report')) {
            $dukaIds = $assignedDukas->pluck('duka_id');
            $recentSales = Sale::with(['customer', 'duka'])
                ->whereIn('duka_id', $dukaIds)
                ->where('tenant_id', $tenantId)
                ->latest()
                ->take(5)
                ->get();
        }

        // Today's sales count and revenue (if has sale_report permission)
        $todaySalesCount = 0;
        $todayRevenue = 0;
        $grossVolume = 0;
        $totalPayments = 0;
        $lastTransaction = 0;
        $receivedAmount = 0;
        $transferredAmount = 0;
        $monthlySalesData = [];
        $dukaSalesData = [];

        if ($this->hasPermission('sale_report')) {
            $dukaIds = $assignedDukas->pluck('duka_id');

            // Today's metrics
            $todaySalesCount = Sale::whereIn('duka_id', $dukaIds)
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->count();

            $todayRevenue = Sale::whereIn('duka_id', $dukaIds)
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->sum('total_amount');

            // Gross volume (total sales from all time)
            $grossVolume = Sale::whereIn('duka_id', $dukaIds)
                ->where('tenant_id', $tenantId)
                ->sum('total_amount');

            // Total payments (loan payments)
            $totalPayments = \App\Models\LoanPayment::whereHas('sale', function($q) use ($dukaIds, $tenantId) {
                $q->whereIn('duka_id', $dukaIds)->where('tenant_id', $tenantId);
            })->count();

            // Last transaction
            $lastSale = Sale::whereIn('duka_id', $dukaIds)
                ->where('tenant_id', $tenantId)
                ->latest()
                ->first();
            $lastTransaction = $lastSale ? $lastSale->total_amount : 0;

            // Received and transferred (simplified - received could be payments received, transferred could be payouts)
            $receivedAmount = \App\Models\LoanPayment::whereHas('sale', function($q) use ($dukaIds, $tenantId) {
                $q->whereIn('duka_id', $dukaIds)->where('tenant_id', $tenantId);
            })->whereDate('payment_date', '>=', now()->startOfMonth())->sum('amount');

            $transferredAmount = $todayRevenue * 0.3; // Placeholder for transferred/payouts

            // Monthly sales data for line chart (last 12 months)
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthlySalesData[] = [
                    'month' => $date->format('M Y'),
                    'sales' => Sale::whereIn('duka_id', $dukaIds)
                        ->where('tenant_id', $tenantId)
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->sum('total_amount')
                ];
            }

            // Sales breakdown by duka for gross volume chart
            $dukaSalesData = Sale::selectRaw('dukas.name as duka_name, SUM(sales.total_amount) as total_sales')
                ->join('dukas', 'sales.duka_id', '=', 'dukas.id')
                ->whereIn('sales.duka_id', $dukaIds)
                ->where('sales.tenant_id', $tenantId)
                ->groupBy('dukas.name')
                ->get()
                ->toArray();
        }

        // Total stock level across all assigned dukas
        $totalStock = 0;
        if ($this->hasPermission('adding_product')) {
            $dukaIds = $assignedDukas->pluck('duka_id');
            $totalStock = Stock::whereIn('duka_id', $dukaIds)->sum('quantity');
        }

        // Total customers across all assigned dukas
        $totalCustomers = 0;
        if ($this->hasPermission('manage_customer')) {
            $dukaIds = $assignedDukas->pluck('duka_id');
            $totalCustomers = \App\Models\Customer::whereIn('duka_id', $dukaIds)->where('tenant_id', $tenantId)->count();
        }

        // Last stock transfer involving officer's assigned dukas
        $lastStockTransfer = null;
        $dukaIds = $assignedDukas->pluck('duka_id');
        $lastStockTransfer = \App\Models\StockTransferItem::with(['fromDuka', 'toDuka', 'transferredBy', 'items.product'])
            ->where('tenant_id', $tenantId)
            ->where(function($q) use ($dukaIds) {
                $q->whereIn('from_duka_id', $dukaIds)
                  ->orWhereIn('to_duka_id', $dukaIds);
            })
            ->latest()
            ->first();

        // Low stock products (if has adding_product permission)
        $lowStockProducts = collect();
        if ($this->hasPermission('adding_product')) {
            $dukaIds = $assignedDukas->pluck('duka_id');
            $lowStockProducts = Product::whereHas('stocks', function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds)
                  ->where('quantity', '<=', 10); // Low stock threshold
            })->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }])->take(5)->get();
        }

        return view('officer.dashboard', compact(
            'assignedDukas',
            'permissions',
            'recentSales',
            'todaySalesCount',
            'todayRevenue',
            'lowStockProducts',
            'grossVolume',
            'totalPayments',
            'lastTransaction',
            'receivedAmount',
            'transferredAmount',
            'monthlySalesData',
            'dukaSalesData',
            'totalStock',
            'totalCustomers',
            'lastStockTransfer'
        ));
    }


    public function manageproduct()
    {
        return view('officer.allproduct');
    }

    public function importProducts(Request $request)
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to import products.');
        }

        // Handle template download
        if ($request->has('download') && $request->download === 'template') {
            return $this->downloadTemplate();
        }

        return view('officer.products.import');
    }

    private function downloadTemplate()
    {
        $filename = 'product_import_template_' . now()->format('Y-m-d') . '.xlsx';

        // Create sample data
        $data = [
            [
                'name' => 'Rice 5kg',
                'buying_price' => 150.00,
                'selling_price' => 180.00,
                'unit' => 'pcs',
                'category' => 'Food & Beverages',
                'description' => 'Premium quality rice',
                'barcode' => '123456789',
                'initial_stock' => 50,
                'duka' => 'Main Store'
            ],
            [
                'name' => 'Sugar 1kg',
                'buying_price' => 80.00,
                'selling_price' => 95.00,
                'unit' => 'pcs',
                'category' => 'Food & Beverages',
                'description' => 'White sugar',
                'barcode' => '987654321',
                'initial_stock' => 100,
                'duka' => 'Branch A'
            ],
            [
                'name' => 'Cooking Oil 1L',
                'buying_price' => 120.00,
                'selling_price' => 140.00,
                'unit' => 'pcs',
                'category' => 'Food & Beverages',
                'description' => 'Vegetable oil',
                'barcode' => '456789123',
                'initial_stock' => 75,
                'duka' => 'Main Store'
            ]
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'name',
                    'buying_price',
                    'selling_price',
                    'unit',
                    'category',
                    'description',
                    'barcode',
                    'initial_stock',
                    'duka'
                ];
            }
        }, $filename);
    }

    public function processImport(Request $request)
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to import products.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120', // 5MB max
        ]);

        try {
            $user = auth()->user();
            $assignment = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->first();

            if (!$assignment) {
                return redirect()->back()->with('error', 'No active assignments found.');
            }

            $dukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
                ->where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id')
                ->toArray();

            $import = new \App\Imports\ProductImport(
                $assignment->tenant_id,
                $user->id,
                $dukaIds
            );

            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $errors = $import->getErrors();

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} products!";
                if ($skipCount > 0) {
                    $message .= " {$skipCount} rows were skipped due to errors.";
                }
                return redirect()->back()->with('success', $message)->with('import_errors', $errors);
            } else {
                return redirect()->back()->with('error', 'No products were imported. Please check the errors below.')->with('import_errors', $errors);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function showCreateProduct()
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to add products.');
        }

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

        $dukas = \App\Models\Duka::whereIn('id', $dukaIds)->get();

        // Get categories with smart data
        $categories = \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)
            ->where('status', 'active')
            ->with(['products' => function($query) use ($dukaIds) {
                $query->whereHas('stocks', function($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds);
                });
            }])
            ->get()
            ->map(function($category) {
                // Calculate averages for smart features
                $products = $category->products;
                $avgBuyingPrice = $products->avg('base_price') ?? 0;
                $avgSellingPrice = $products->avg('selling_price') ?? 0;

                // Get common units for this category
                $commonUnits = $products->pluck('unit')->countBy()->sortDesc()->keys()->take(3)->implode(',');

                $category->avg_buying_price = round($avgBuyingPrice, 2);
                $category->avg_selling_price = round($avgSellingPrice, 2);
                $category->common_units = $commonUnits;

                return $category;
            });

        // Get currency for the tenant
        $currency = \App\Models\TenantAccount::where('tenant_id', $assignment->tenant_id)
            ->first()->currency ?? 'TZS';

        // Get existing product names for auto-suggestions
        $existingProducts = \App\Models\Product::where('tenant_id', $assignment->tenant_id)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->take(100)
            ->pluck('name')
            ->toArray();

        return view('officer.products.create', compact('dukas', 'categories', 'currency', 'existingProducts'));
    }

    public function showEditProduct($id)
    {
        if (!$this->hasPermission('edit_product')) {
            abort(403, 'You do not have permission to edit products.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $product = Product::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        return view('officer.products.edit', compact('product'));
    }

    public function profile()
    {
        $user = auth()->user();
        // Get officer's assignments for profile display
        $assignments = TenantOfficer::with('duka')
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->get();

        return view('officer.profile', compact('user', 'assignments'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && file_exists(public_path('storage/profiles/' . $user->profile_picture))) {
                unlink(public_path('storage/profiles/' . $user->profile_picture));
            }

            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/profiles'), $filename);
            $updateData['profile_picture'] = $filename;
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    private function hasPermission($permissionName)
    {
        return auth()->user()->hasPermission($permissionName);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|max:255',
        ]);

        $user              = auth()->user();
        $tenantId          = $user->tenant->id;
        $officerAssignment = TenantOfficer::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $officerAssignment->update([
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Officer role updated successfully!');
    }
   public function index()
{
   $user     = auth()->user();
   $tenantId = Tenant::where('user_id', $user->id)->first()->id;



    $officers = TenantOfficer::with(['officer', 'duka'])
        ->where('tenant_id', $tenantId)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // 2. Dukas for this tenant
    $dukas = Duka::where('tenant_id', $tenantId)->get();

    // 3. All users who have officer role AND have tenant accounts for this tenant
    $allOfficers = User::where('tenant_id',$user->id)->whereHas('roles', function ($query) {
            $query->where('name', 'officer');
        })
        ->orderBy('name', 'asc')
        ->get();

    // 4. Available officers for this tenant (only staff in tenant accounts for this tenant)
    $availableUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'officer'); // must be officer
        })
        ->whereHas('tenantAccount', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }) // Only show officers that have tenant accounts for this tenant
        ->where('id', '!=', auth()->id())     // remove logged-in tenant admin
        ->whereNotIn('id', function ($query) use ($tenantId) {
            $query->select('officer_id')
                ->from('tenant_officers')
                ->where('tenant_id', $tenantId); // remove already assigned officers
        })
        ->orderBy('name', 'asc')
        ->get();

    return view('officers.index', compact(
        'officers',
        'dukas',
        'availableUsers',
        'allOfficers'
    ));
}

    public function assign(Request $request)
    {
        $request->validate([
            'officer_id' => 'required|exists:users,id',
            'duka_id'    => 'required|exists:dukas,id',
            'role'       => 'nullable|string|max:255',
        ]);

        $user     = auth()->user();
        $tenantId = $user->tenant->id;
        $duka     = Duka::where('id', $request->duka_id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Verify the user exists and is not a tenant
        $user = User::where('id', $request->officer_id)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'tenant');
            })
            ->where('id', '!=', $tenantId) // Can't assign self
            ->firstOrFail();

        // Check if already assigned
        $existing = TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $request->officer_id)
            ->where('duka_id', $request->duka_id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'This officer is already assigned to this duka.');
        }

        $assignment = TenantOfficer::create([
            'tenant_id'  => $tenantId,
            'duka_id'    => $request->duka_id,
            'officer_id' => $request->officer_id,
            'role'       => $request->role ?? 'Officer',
            'status'     => true, // Boolean true for active
        ]);

        // Get tenant information
        $tenant = Auth::user();

        // Send email notification to the officer
        try {
            Mail::to($user->email)->send(new OfficerAssignmentMail($user, $duka, $assignment, $tenant));
        } catch (\Exception $e) {
            // Log the error but don't fail the assignment
            \Log::error('Failed to send officer assignment email: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Officer assigned to duka successfully! An email notification has been sent.');
    }

    public function unassign($id)
    {
        $tenantId = auth()->user()->tenant->id;

        $officerAssignment = TenantOfficer::with(['officer', 'duka'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Store data before deletion for email
        $officer = $officerAssignment->officer;
        $duka    = $officerAssignment->duka;
        $tenant  = Auth::user();

        $officerAssignment->delete();

        // Send email notification to the officer
        try {
            Mail::to($officer->email)->send(new OfficerUnassignmentMail($officer, $duka, $tenant));
        } catch (\Exception $e) {
            // Log the error but don't fail the unassignment
            \Log::error('Failed to send officer unassignment email: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Officer unassigned from duka successfully! An email notification has been sent.');
    }

    // Officer CRUD Operations
    public function create()
    {
        return view('officers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tenantId = Auth::id();

        // Ensure officer role exists
        $officerRole = Role::firstOrCreate(['name' => 'officer']);

        // Create the officer user
        $officer = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'officer',
            'tenant_id'   => $tenantId,
        ]);

        // Assign officer role
        $officer->assignRole('officer');

        // Assign officer to all dukas of the tenant
        $dukas = Duka::where('tenant_id', $tenantId)->get();
        foreach ($dukas as $duka) {
            TenantOfficer::create([
                'tenant_id' => $tenantId,
                'duka_id' => $duka->id,
                'officer_id' => $officer->id,
                'role' => 'Officer',
                'status' => false, // Set to inactive initially, tenant can activate later
            ]);
        }

        return redirect()->route('officers.index')->with('success', 'Officer created successfully!');
    }

    public function show($id)
    {
          $user     = auth()->user();
          $tenantId = \App\Models\Tenant::where('user_id', $user->id)->first()->id;

        $officer = User::where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'officer');
            })
            ->firstOrFail();

        // Get officer assignments for this tenant
        $assignments = TenantOfficer::with('duka')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officer->id)
            ->get();

        return view('officers.show', compact('officer', 'assignments'));
    }

    public function edit($id)
    {
        $tenantId = Auth::id();

        $officer = User::where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'officer');
            })
            ->firstOrFail();

        return view('officers.edit', compact('officer'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $officer = User::where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'officer');
            })
            ->firstOrFail();

        $updateData = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $officer->update($updateData);

        return redirect()->route('officers.index')->with('success', 'Officer updated successfully!');
    }

    public function setDefaultPassword($id)
    {
        $tenantId = Auth::id();

        $officer = User::where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'officer');
            })
            ->firstOrFail();

        // Get tenant's default password
        $tenant = Auth::user()->tenant;
        $defaultPassword = $tenant->default_password ?? '123456';

        // Update officer's password to default
        $officer->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return redirect()->back()->with('success', 'Officer password has been reset to the default password.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::id();

        $officer = User::where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'officer');
            })
            ->firstOrFail();

        // Remove all assignments for this tenant
        TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officer->id)
            ->delete();

        // Check if officer is assigned to other tenants
        $otherAssignments = TenantOfficer::where('officer_id', $officer->id)->count();

        if ($otherAssignments == 0) {
            // If no other assignments, we can deactivate or keep the user
            // For now, we'll keep the user but remove officer role
            $officer->removeRole('officer');
        }

        return redirect()->route('officers.index')->with('success', 'Officer removed from your organization successfully!');
    }

    // Product Management Methods for Officers
    public function manageProducts($filter = 'all')
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to manage products.');
        }

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

        $query = Product::where('tenant_id', $assignment->tenant_id)
            ->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'category', 'items']);

        // Apply filters
        switch ($filter) {
            case 'low-stock':
                $query->whereHas('stocks', function($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds)
                      ->where('quantity', '<=', 10);
                });
                break;
            case 'out-of-stock':
                $query->whereHas('stocks', function($q) use ($dukaIds) {
                    $q->whereIn('duka_id', $dukaIds)
                      ->where('quantity', '=', 0);
                });
                break;
            case 'all':
            default:
                // Show all products
                break;
        }

        $products = $query->paginate(15);
        $dukas = \App\Models\Duka::whereIn('id', $dukaIds)->get();
        $categories = \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)
            ->where('status', 'active')
            ->get();

        return view('officer.products.manage', compact('products', 'dukas', 'categories', 'filter'));
    }

    public function storeProduct(Request $request)
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to add products.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'duka_id' => 'required|exists:dukas,id',
            'initial_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Verify the duka belongs to officer's tenant and is assigned to them
        $duka = \App\Models\Duka::where('id', $request->duka_id)
            ->where('tenant_id', $assignment->tenant_id)
            ->first();

        if (!$duka) {
            abort(403, 'Invalid duka selected.');
        }

        $officerDukas = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('duka_id', $request->duka_id)
            ->where('status', true)
            ->exists();

        if (!$officerDukas) {
            abort(403, 'You are not assigned to this duka.');
        }

        // Generate unique SKU
        $sku = $this->generateProductSKU($request->name, $request->initial_stock);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/products'), $imageName);
            $imagePath = $imageName;
        }

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'sku' => $sku,
            'description' => $request->description,
            'unit' => $request->unit,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'category_id' => $request->category_id,
            'image' => $imagePath,
            'tenant_id' => $assignment->tenant_id,
        ]);

        // Create initial stock
        if ($request->initial_stock > 0) {
            $stock = Stock::create([
                'product_id' => $product->id,
                'duka_id' => $request->duka_id,
                'quantity' => $request->initial_stock,
            ]);

            // Record stock movement
            StockMovement::create([
                'stock_id' => $stock->id,
                'user_id' => $user->id,
                'type' => 'add',
                'quantity_change' => $request->initial_stock,
                'previous_quantity' => 0,
                'new_quantity' => $request->initial_stock,
                'reason' => 'Initial stock',
            ]);
        }

        return redirect()->back()->with('success', 'Product created successfully!');
    }

    public function editProduct($id)
    {
        if (!$this->hasPermission('edit_product')) {
            abort(403, 'You do not have permission to edit products.');
        }

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

        $product = Product::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'category'])
            ->firstOrFail();

        $dukas = \App\Models\Duka::whereIn('id', $dukaIds)->get();
        $categories = \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)->get();

        return view('officer.products.edit', compact('product', 'dukas', 'categories'));
    }

    public function updateProduct(Request $request, $id)
    {
        if (!$this->hasPermission('edit_product')) {
            abort(403, 'You do not have permission to edit products.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $product = Product::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        $product->update($request->only([
            'name', 'description', 'unit', 'buying_price', 'selling_price', 'category_id'
        ]));

        return redirect()->route('officer.products.manage')->with('success', 'Product updated successfully!');
    }

    public function destroyProduct($id)
    {
        if (!$this->hasPermission('delete_product')) {
            abort(403, 'You do not have permission to delete products.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $product = Product::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        $product->delete();

        return redirect()->route('officer.products.manage')->with('success', 'Product deleted successfully!');
    }

    // Stock Management Methods for Officers
    public function addStock(Request $request)
    {
        if (!$this->hasPermission('adding_stock')) {
            abort(403, 'You do not have permission to add stock.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'duka_id' => 'required|exists:dukas,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Verify product belongs to tenant
        $product = Product::where('id', $request->product_id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Verify duka belongs to officer
        $officerDuka = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('duka_id', $request->duka_id)
            ->where('status', true)
            ->exists();

        if (!$officerDuka) {
            abort(403, 'You are not assigned to this duka.');
        }

        // Get or create stock record first
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $request->product_id,
                'duka_id' => $request->duka_id,
            ],
            [
                'quantity' => 0,
                'last_updated_by' => $user->id
            ]
        );

        // Store previous quantity for movement record
        $previousQuantity = $stock->quantity;

        // Update stock quantity
        $stock->increment('quantity', $request->quantity);
        $stock->update(['last_updated_by' => $user->id]);

        // Record stock movement
        StockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'type' => 'add',
            'quantity_change' => $request->quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $stock->quantity,
            'reason' => $request->reason ?: 'Stock added',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Stock added successfully!');
    }

    public function reduceStock(Request $request)
    {
        if (!$this->hasPermission('reduce_stock')) {
            abort(403, 'You do not have permission to reduce stock.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'duka_id' => 'required|exists:dukas,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Verify product belongs to tenant
        $product = Product::where('id', $request->product_id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Verify duka belongs to officer
        $officerDuka = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('duka_id', $request->duka_id)
            ->where('status', true)
            ->exists();

        if (!$officerDuka) {
            abort(403, 'You are not assigned to this duka.');
        }

        // Check current stock
        $stock = Stock::where('product_id', $request->product_id)
            ->where('duka_id', $request->duka_id)
            ->first();

        if (!$stock || $stock->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock available.');
        }

        // Store previous quantity for movement record
        $previousQuantity = $stock->quantity;

        // Update stock quantity
        $stock->decrement('quantity', $request->quantity);
        $stock->update(['last_updated_by' => $user->id]);

        // Record stock movement
        StockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'type' => 'remove',
            'quantity_change' => -$request->quantity, // Negative for reduction
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $stock->quantity,
            'reason' => $request->reason ?: 'Stock reduced',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Stock reduced successfully!');
    }

    public function updateStock(Request $request, $id)
    {
        if (!$this->hasPermission('adding_stock') && !$this->hasPermission('reduce_stock')) {
            abort(403, 'You do not have permission to update stock.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $stock = Stock::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Verify duka belongs to officer
        $officerDuka = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('duka_id', $stock->duka_id)
            ->where('status', true)
            ->exists();

        if (!$officerDuka) {
            abort(403, 'You are not assigned to this duka.');
        }

        $oldQuantity = $stock->quantity;
        $newQuantity = $request->quantity;
        $difference = $newQuantity - $oldQuantity;

        $stock->update(['quantity' => $newQuantity]);

        // Record stock movement if quantity changed
        if ($difference != 0) {
            StockMovement::create([
                'product_id' => $stock->product_id,
                'duka_id' => $stock->duka_id,
                'quantity' => abs($difference),
                'type' => $difference > 0 ? 'in' : 'out',
                'reason' => 'Stock adjustment',
                'tenant_id' => $assignment->tenant_id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()->back()->with('success', 'Stock updated successfully!');
    }

    // Customer Management Methods for Officers
    public function manageCustomers()
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

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

        $customers = \App\Models\Customer::where('tenant_id', $assignment->tenant_id)
            ->whereIn('duka_id', $dukaIds)
            ->with(['duka', 'creator'])
            ->paginate(15);

        $dukas = \App\Models\Duka::whereIn('id', $dukaIds)->get();

        // If officer has only one duka, pass it as a single object for auto-fill
        $singleDuka = $dukas->count() === 1 ? $dukas->first() : null;

        return view('officer.customers.manage', compact('customers', 'dukas', 'singleDuka'));
    }

    public function storeCustomer(Request $request)
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        // If officer has only one duka, auto-set it
        if ($dukaIds->count() === 1 && !$request->has('duka_id')) {
            $request->merge(['duka_id' => $dukaIds->first()]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'duka_id' => 'required|exists:dukas,id',
        ]);

        // Verify the duka belongs to officer
        $officerDuka = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('duka_id', $request->duka_id)
            ->where('status', true)
            ->exists();

        if (!$officerDuka) {
            abort(403, 'You are not assigned to this duka.');
        }

        \App\Models\Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'duka_id' => $request->duka_id,
            'tenant_id' => $assignment->tenant_id,
            'created_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Customer created successfully!');
    }

    public function updateCustomer(Request $request, $id)
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $customer = \App\Models\Customer::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        $customer->update($request->only(['name', 'email', 'phone', 'address']));

        return redirect()->back()->with('success', 'Customer updated successfully!');
    }

    public function destroyCustomer($id)
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $customer = \App\Models\Customer::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        $customer->delete();

        return redirect()->back()->with('success', 'Customer deleted successfully!');
    }

    // Customer Import Methods for Officers
    public function importCustomers(Request $request)
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

        // Handle template download
        if ($request->has('download') && $request->download === 'template') {
            return $this->downloadCustomerTemplate();
        }

        return view('officer.customers.import');
    }

    private function downloadCustomerTemplate()
    {
        $filename = 'customer_import_template_' . now()->format('Y-m-d') . '.xlsx';

        // Create sample data
        $data = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+255 123 456 789',
                'address' => '123 Main Street, Dar es Salaam',
                'duka' => 'Main Store'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+255 987 654 321',
                'address' => '456 Side Road, Arusha',
                'duka' => 'Branch A'
            ],
            [
                'name' => 'Bob Johnson',
                'email' => '',
                'phone' => '+255 555 123 456',
                'address' => '789 Center Ave, Mwanza',
                'duka' => 'Main Store'
            ]
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'name',
                    'email',
                    'phone',
                    'address',
                    'duka'
                ];
            }
        }, $filename);
    }

    public function processCustomerImport(Request $request)
    {
        if (!$this->hasPermission('manage_customer')) {
            abort(403, 'You do not have permission to manage customers.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120', // 5MB max
        ]);

        try {
            $user = auth()->user();
            $assignment = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->first();

            if (!$assignment) {
                return redirect()->back()->with('error', 'No active assignments found.');
            }

            $dukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
                ->where('officer_id', $user->id)
                ->where('status', true)
                ->pluck('duka_id')
                ->toArray();

            $import = new \App\Imports\CustomerImport(
                $assignment->tenant_id,
                $user->id,
                $dukaIds
            );

            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $errors = $import->getErrors();

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} customers!";
                if ($skipCount > 0) {
                    $message .= " {$skipCount} rows were skipped due to errors.";
                }
                return redirect()->route('officer.customers.manage')->with('success', $message)->with('import_errors', $errors);
            } else {
                return redirect()->back()->with('error', 'No customers were imported. Please check the errors below.')->with('import_errors', $errors);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // Category Management Methods for Officers
    public function manageCategories(Request $request)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $query = \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)
            ->with(['parent', 'children', 'products', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        // Summary Statistics for the Smart View
        $stats = [
            'total' => \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)->count(),
            'active' => \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)->where('status', 'active')->count(),
            'inactive' => \App\Models\ProductCategory::where('tenant_id', $assignment->tenant_id)->where('status', 'inactive')->count(),
        ];

        return view('officer.categories.manage', compact('categories', 'stats'));
    }

    public function storeCategory(Request $request)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // If parent_id is provided, ensure it belongs to the same tenant
        if ($request->parent_id) {
            $parentCategory = \App\Models\ProductCategory::where('id', $request->parent_id)
                ->where('tenant_id', $assignment->tenant_id)
                ->first();

            if (!$parentCategory) {
                return redirect()->back()->with('error', 'Invalid parent category selected.');
            }
        }

        \App\Models\ProductCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'status' => $request->status,
            'tenant_id' => $assignment->tenant_id,
            'created_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function updateCategory(Request $request, $id)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $category = \App\Models\ProductCategory::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Prevent setting self as parent
        if ($request->parent_id == $category->id) {
            return redirect()->back()->with('error', 'Category cannot be its own parent.');
        }

        // If parent_id is provided, ensure it belongs to the same tenant and prevent circular references
        if ($request->parent_id) {
            $parentCategory = \App\Models\ProductCategory::where('id', $request->parent_id)
                ->where('tenant_id', $assignment->tenant_id)
                ->first();

            if (!$parentCategory) {
                return redirect()->back()->with('error', 'Invalid parent category selected.');
            }

            // Check for circular reference
            if ($this->wouldCreateCircularReference($category->id, $request->parent_id)) {
                return redirect()->back()->with('error', 'Cannot set this parent category as it would create a circular reference.');
            }
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    private function wouldCreateCircularReference($categoryId, $parentId)
    {
        $currentId = $parentId;
        while ($currentId) {
            if ($currentId == $categoryId) {
                return true;
            }
            $parent = \App\Models\ProductCategory::find($currentId);
            $currentId = $parent ? $parent->parent_id : null;
        }
        return false;
    }

    public function destroyCategory($id)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        $category = \App\Models\ProductCategory::where('id', $id)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Check if category has children or products
        if ($category->children->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with subcategories. Please delete or reassign subcategories first.');
        }

        if ($category->products->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with associated products. Please reassign products to another category first.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    // Category Import Methods for Officers
    public function importCategories(Request $request)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        // Handle template download
        if ($request->has('download') && $request->download === 'template') {
            return $this->downloadCategoryTemplate();
        }

        return view('officer.categories.import');
    }

    private function downloadCategoryTemplate()
    {
        $filename = 'category_import_template_' . now()->format('Y-m-d') . '.xlsx';

        // Create sample data
        $data = [
            [
                'name' => 'Food & Beverages',
                'description' => 'All food and beverage products',
                'parent_category' => '',
                'status' => 'active'
            ],
            [
                'name' => 'Rice & Grains',
                'description' => 'Rice, wheat, and other grains',
                'parent_category' => 'Food & Beverages',
                'status' => 'active'
            ],
            [
                'name' => 'Beverages',
                'description' => 'Soft drinks, juices, and other beverages',
                'parent_category' => 'Food & Beverages',
                'status' => 'active'
            ],
            [
                'name' => 'Household Items',
                'description' => 'Cleaning supplies and household products',
                'parent_category' => '',
                'status' => 'active'
            ],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'parent_category' => '',
                'status' => 'inactive'
            ]
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'name',
                    'description',
                    'parent_category',
                    'status'
                ];
            }
        }, $filename);
    }

    public function processCategoryImport(Request $request)
    {
        if (!$this->hasPermission('manage_category')) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120', // 5MB max
        ]);

        try {
            $user = auth()->user();
            $assignment = TenantOfficer::where('officer_id', $user->id)
                ->where('status', true)
                ->first();

            if (!$assignment) {
                return redirect()->back()->with('error', 'No active assignments found.');
            }

            $import = new \App\Imports\CategoryImport(
                $assignment->tenant_id,
                $user->id
            );

            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $errors = $import->getErrors();

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} categories!";
                if ($skipCount > 0) {
                    $message .= " {$skipCount} rows were skipped due to errors.";
                }
                return redirect()->route('officer.categories.manage')->with('success', $message)->with('import_errors', $errors);
            } else {
                return redirect()->back()->with('error', 'No categories were imported. Please check the errors below.')->with('import_errors', $errors);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function manageStock($productId)
    {
        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Verify product belongs to tenant
        $product = Product::where('id', $productId)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Get officer's assigned dukas
        $dukaIds = TenantOfficer::where('tenant_id', $assignment->tenant_id)
            ->where('officer_id', $user->id)
            ->where('status', true)
            ->pluck('duka_id');

        $dukas = Duka::whereIn('id', $dukaIds)->get();

        // Get current stocks for each duka
        $stocks = Stock::where('product_id', $productId)
            ->whereIn('duka_id', $dukaIds)
            ->pluck('quantity', 'duka_id');

        return view('officer.products.stock', compact('product', 'dukas', 'stocks'));
    }

    public function viewProductItems($productId)
    {
        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            abort(403, 'No active assignments found.');
        }

        // Verify product belongs to tenant
        $product = Product::where('id', $productId)
            ->where('tenant_id', $assignment->tenant_id)
            ->firstOrFail();

        // Get product items
        $productItems = ProductItem::where('product_id', $productId)
            ->with('product')
            ->paginate(15);

        return view('officer.products.items', compact('product', 'productItems'));
    }

    public function updateProductItemStatus(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:product_items,id',
            'status' => 'required|in:available,sold,damaged',
        ]);

        $user = auth()->user();
        $assignment = TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No active assignments found.'], 403);
        }

        $item = ProductItem::where('id', $request->item_id)
            ->whereHas('product', function($q) use ($assignment) {
                $q->where('tenant_id', $assignment->tenant_id);
            })
            ->firstOrFail();

        // Check permissions
        if (!$this->hasPermission('adding_product')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update product items.'], 403);
        }

        if ($request->status === 'sold') {
            $item->markAsSold();
        } elseif ($request->status === 'damaged') {
            $item->markAsDamaged();
        } elseif ($request->status === 'available') {
            $item->markAsAvailable();
        }

        return response()->json(['success' => true, 'message' => 'Item status updated successfully.']);
    }

    public function exportProducts(Request $request)
    {
        if (!$this->hasPermission('adding_product')) {
            abort(403, 'You do not have permission to export products.');
        }

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

        $query = Product::where('tenant_id', $assignment->tenant_id)
            ->with(['stocks' => function($q) use ($dukaIds) {
                $q->whereIn('duka_id', $dukaIds);
            }, 'category']);

        // Apply same filters as Livewire component
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filterCategory) {
            $query->where('category_id', $request->filterCategory);
        }

        if ($request->filterDuka) {
            $query->whereHas('stocks', function($q) use ($request) {
                $q->where('duka_id', $request->filterDuka);
            });
        }

        if ($request->filterStockStatus) {
            switch ($request->filterStockStatus) {
                case 'out_of_stock':
                    $query->whereHas('stocks', function($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)
                          ->where('quantity', 0);
                    });
                    break;
                case 'low_stock':
                    $query->whereHas('stocks', function($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)
                          ->where('quantity', '>', 0)
                          ->where('quantity', '<=', 10);
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('stocks', function($q) use ($dukaIds) {
                        $q->whereIn('duka_id', $dukaIds)
                          ->where('quantity', '>', 10);
                    });
                    break;
            }
        }

        $products = $query->get();

        $filename = 'products_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'SKU',
                'Name',
                'Category',
                'Unit',
                'Buying Price',
                'Selling Price',
                'Profit Margin',
                'Total Stock',
                'Stock Value',
                'Status'
            ]);

            // CSV data
            foreach ($products as $product) {
                $stockQuantity = $product->stocks->sum('quantity');
                $availableItems = $product->items->where('status', 'available')->count();
                $totalStock = $stockQuantity + $availableItems;
                $status = $totalStock > 0 ? ($totalStock <= 10 ? 'Low Stock' : 'In Stock') : 'Out of Stock';

                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->category->name ?? 'No Category',
                    $product->unit,
                    $product->base_price,
                    $product->selling_price,
                    $product->base_price > 0 ? round((($product->selling_price - $product->base_price) / $product->base_price) * 100, 2) . '%' : '0%',
                    $totalStock,
                    $totalStock * $product->base_price,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

     /**
      * Generate a unique SKU for a product based on name and stock level
      */
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
         $counter = 1;
         $originalSku = $sku;
         while (Product::where('sku', $sku)->exists()) {
             $sku = $originalSku . '-' . $counter;
             $counter++;
         }

         return $sku;
     }

     // API Methods for Officers
     public function apiGetProducts(Request $request)
     {
         $user = auth()->user();

         // Verify user is an officer
         if (!$user->hasRole('officer')) {
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

         // Get tenant account information
         $tenants = \App\Models\User::whereIn('id', $tenantIds)
             ->with('tenantAccount')
             ->get()
             ->map(function ($tenant) {
                 $data = [
                     'id' => $tenant->id,
                     'name' => $tenant->name,
                     'email' => $tenant->email,
                 ];

                 if ($tenant->tenantAccount) {
                     $data['account'] = [
                         'company_name' => $tenant->tenantAccount->company_name,
                         'logo_url' => $tenant->tenantAccount->logo_url,
                         'phone' => $tenant->tenantAccount->phone,
                         'email' => $tenant->tenantAccount->email,
                         'address' => $tenant->tenantAccount->address,
                         'currency' => $tenant->tenantAccount->currency,
                         'timezone' => $tenant->tenantAccount->timezone,
                         'website' => $tenant->tenantAccount->website,
                         'description' => $tenant->tenantAccount->description,
                     ];
                 }

                 return $data;
             });

         // Get officer's assigned dukas for stock filtering
         $dukaIds = TenantOfficer::where('officer_id', $user->id)
             ->where('status', true)
             ->pluck('duka_id')
             ->toArray();

         $query = Product::whereIn('tenant_id', $tenantIds)
             ->with(['category', 'duka', 'items']);

         // Apply filters
         if ($request->has('search') && !empty($request->search)) {
             $query->where(function($q) use ($request) {
                 $q->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('sku', 'like', '%' . $request->search . '%')
                   ->orWhere('description', 'like', '%' . $request->search . '%');
             });
         }

         if ($request->has('category_id') && !empty($request->category_id)) {
             $query->where('category_id', $request->category_id);
         }

         if ($request->has('duka_id') && !empty($request->duka_id)) {
             // Verify officer is assigned to this duka
             if (in_array($request->duka_id, $dukaIds)) {
                 $query->where('duka_id', $request->duka_id);
             }
         }

         // Stock status filter
         if ($request->has('stock_status')) {
             switch ($request->stock_status) {
                 case 'out_of_stock':
                     $query->whereDoesntHave('stocks', function($q) use ($dukaIds) {
                         $q->whereIn('duka_id', $dukaIds);
                     })->orWhereHas('stocks', function($q) use ($dukaIds) {
                         $q->whereIn('duka_id', $dukaIds)->where('quantity', 0);
                     });
                     break;
                 case 'low_stock':
                     $query->whereHas('stocks', function($q) use ($dukaIds) {
                         $q->whereIn('duka_id', $dukaIds)
                           ->where('quantity', '>', 0)
                           ->where('quantity', '<=', 10);
                     });
                     break;
                 case 'in_stock':
                     $query->whereHas('stocks', function($q) use ($dukaIds) {
                         $q->whereIn('duka_id', $dukaIds)->where('quantity', '>', 10);
                     });
                     break;
             }
         }

         $products = $query->paginate($request->get('per_page', 15));

         // Format response with stock information
         $formattedProducts = $products->getCollection()->map(function ($product) use ($dukaIds) {
             $stocks = Stock::where('product_id', $product->id)
                 ->whereIn('duka_id', $dukaIds)
                 ->with('duka')
                 ->get();

             return [
                 'id' => $product->id,
                 'tenant_id' => $product->tenant_id,
                 'duka_id' => $product->duka_id,
                 'category_id' => $product->category_id,
                 'sku' => $product->sku,
                 'name' => $product->name,
                 'description' => $product->description,
                 'unit' => $product->unit,
                 'base_price' => $product->base_price,
                 'selling_price' => $product->selling_price,
                 'is_active' => $product->is_active,
                 'image' => $product->image,
                 'barcode' => $product->barcode,
                 'image_url' => $product->image_url,
                 'category' => $product->category ? [
                     'id' => $product->category->id,
                     'name' => $product->category->name,
                 ] : null,
                 'duka' => $product->duka ? [
                     'id' => $product->duka->id,
                     'name' => $product->duka->name,
                     'location' => $product->duka->location,
                 ] : null,
                 'stocks' => $stocks->map(function ($stock) {
                     return [
                         'duka_id' => $stock->duka_id,
                         'duka_name' => $stock->duka->name,
                         'quantity' => $stock->quantity,
                         'last_updated' => $stock->updated_at,
                     ];
                 }),
                 'total_stock' => $stocks->sum('quantity') + $product->items->where('status', 'available')->count(),
                 'created_at' => $product->created_at,
                 'updated_at' => $product->updated_at,
             ];
         });

         return response()->json([
             'success' => true,
             'data' => [
                 'products' => $formattedProducts,
                 'tenants' => $tenants,
                 'pagination' => [
                     'current_page' => $products->currentPage(),
                     'last_page' => $products->lastPage(),
                     'per_page' => $products->perPage(),
                     'total' => $products->total(),
                     'from' => $products->firstItem(),
                     'to' => $products->lastItem(),
                 ],
             ],
         ]);
     }







     public function proformaInvoice()
     {
         return view('officer.proforma-invoice');
     }

     public function proformaInvoicePreview()
     {
         $invoiceData = session('proforma_invoice_preview');

         if (!$invoiceData) {
             return redirect()->route('officer.proformainvoice')->with('error', 'No proforma invoice preview data found.');
         }

         return view('officer.proforma-invoice-preview', compact('invoiceData'));
     }






}

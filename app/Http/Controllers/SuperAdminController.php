<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Sale;
use App\Models\Duka;
use App\Models\Customer;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\DukaSubscription;
use App\Models\Message;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        // Basic counts
        $totalTenants = Tenant::count();
        $totalUsers = User::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $totalDukas = Duka::count();
        // Financial metrics
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('total_amount');

        // Recent data
        $recentTenants = Tenant::with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // System statistics
        $systemStats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'environment' => app()->environment(),
            'total_sales_this_month' => Sale::whereMonth('created_at', Carbon::now()->month)->count(),
            'total_revenue_this_month' => Sale::whereMonth('created_at', Carbon::now()->month)->sum('total_amount'),
            'new_tenants_this_month' => Tenant::whereMonth('created_at', Carbon::now()->month)->count(),
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        return view('super-admin.dashboard', compact(
            'totalTenants',
            'totalUsers',
            'activeTenants',
            'totalDukas',
            'totalSales',
            'totalRevenue',
            'recentTenants',
            'recentUsers',
            'systemStats'
        ));
    }

    public function tenants(Request $request)
    {
        $query = Tenant::with(['user', 'dukas']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $tenants = $query->orderBy('created_at', 'desc')
                         ->paginate(10);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function showTenant($id)
    {
        $tenant = Tenant::with(['user', 'dukas', 'customers'])->findOrFail($id);
        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function users(Request $request)
    {
        $query = User::with('roles', 'tenant.dukas.dukaSubscriptions.plan');
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Role filter
        if ($request->has('role') && !empty($request->role)) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate(10);

        return view('super-admin.users.index', compact('users'));
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->hasRole('superadmin') && auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own super admin account.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function resetUserPassword($userId)
    {
        $user = User::findOrFail($userId);

        // Prevent resetting super admin password
        if ($user->hasRole('superadmin')) {
            return redirect()->back()->with('error', 'Super admin passwords cannot be reset.');
        }

        // Reset password to default "123456"
        $user->password = bcrypt('123456');
        $user->save();

        return redirect()->back()->with('success', "Password for user '{$user->name}' has been reset to '123456'.");
    }

    public function editUser($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        return view('super-admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'role' => 'required|in:superadmin,tenant,officer,admin',
            'status' => 'required|in:active,inactive',
        ]);

        // Prevent super admin from changing their own role or status
        if ($user->hasRole('superadmin') && auth()->id() === $user->id) {
            if ($request->role !== 'superadmin' || $request->status !== 'active') {
                return redirect()->back()->with('error', 'You cannot modify your own super admin account.');
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ]);

        // Update role
        $user->syncRoles([$request->role]);

        return redirect()->route('super-admin.users.index')->with('success', 'User updated successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        // Prevent deletion of super admin accounts
        if ($user->hasRole('superadmin')) {
            return redirect()->back()->with('error', 'Super admin accounts cannot be deleted.');
        }

        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('super-admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function bulkDeleteUsers(Request $request)
    {
        if ($request->has('delete_all') && $request->delete_all == '1') {
            // Delete all users matching current filters
            $query = User::with('roles');

            // Apply same filters as in users() method
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Role filter
            if ($request->has('role') && !empty($request->role)) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Status filter
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $usersToDelete = $query->get();
            $deletedCount = 0;
            $skippedUsers = [];

            foreach ($usersToDelete as $user) {
                // Skip super admin accounts
                if ($user->hasRole('superadmin')) {
                    $skippedUsers[] = $user->name . ' (Super Admin)';
                    continue;
                }

                // Skip self-deletion
                if (auth()->id() === $user->id) {
                    $skippedUsers[] = $user->name . ' (Current User)';
                    continue;
                }

                $user->delete();
                $deletedCount++;
            }

            $message = "Successfully deleted {$deletedCount} user(s) across all pages.";

            if (!empty($skippedUsers)) {
                $message .= " Skipped: " . implode(', ', $skippedUsers) . " (protected accounts).";
            }
        } else {
            // Delete selected users
            $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
            ]);

            $userIds = $request->user_ids;
            $deletedCount = 0;
            $skippedUsers = [];

            foreach ($userIds as $userId) {
                $user = User::find($userId);

                if (!$user) {
                    continue;
                }

                // Skip super admin accounts
                if ($user->hasRole('superadmin')) {
                    $skippedUsers[] = $user->name . ' (Super Admin)';
                    continue;
                }

                // Skip self-deletion
                if (auth()->id() === $user->id) {
                    $skippedUsers[] = $user->name . ' (Current User)';
                    continue;
                }

                $user->delete();
                $deletedCount++;
            }

            $message = "Successfully deleted {$deletedCount} user(s).";

            if (!empty($skippedUsers)) {
                $message .= " Skipped: " . implode(', ', $skippedUsers) . " (protected accounts).";
            }
        }

        return redirect()->route('super-admin.users.index')->with('success', $message);
    }

    public function features(Request $request)
    {
        $query = Feature::with('plans');
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $features = $query->orderBy('created_at', 'desc')->paginate(10);
        $plans = Plan::all(); // For assignment modal

        return view('super-admin.features.index', compact('features', 'plans'));
    }

    public function createFeature()
    {
        return view('super-admin.features.create');
    }

    public function storeFeature(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:features,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Feature::create($request->only(['code', 'name', 'description']));

        return redirect()->route('super-admin.features.index')->with('success', 'Feature created successfully.');
    }

    public function showFeature($id)
    {
        $feature = Feature::findOrFail($id);
        return view('super-admin.features.show', compact('feature'));
    }

    public function editFeature($id)
    {
        $feature = Feature::findOrFail($id);
        return view('super-admin.features.edit', compact('feature'));
    }

    public function updateFeature(Request $request, $id)
    {
        $feature = Feature::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:features,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $feature->update($request->only(['code', 'name', 'description']));

        return redirect()->route('super-admin.features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroyFeature($id)
    {
        $feature = Feature::findOrFail($id);
        $feature->delete();

        return redirect()->route('super-admin.features.index')->with('success', 'Feature deleted successfully.');
    }

    public function assignFeatureToPlans(Request $request, $id)
    {
        $request->validate([
            'plan_ids' => 'nullable|array',
            'plan_ids.*' => 'exists:plans,id',
        ]);

        $feature = Feature::findOrFail($id);

        // Sync the plans for this feature
        $feature->plans()->sync($request->plan_ids ?? []);

        return redirect()->back()->with('success', 'Feature plans updated successfully.');
    }

    // Plans Management
    public function plans(Request $request)
    {
        $query = Plan::with('planFeatures');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $plans = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('super-admin.plans.index', compact('plans'));
    }

    public function createPlan()
    {
        $features = Feature::all();
        return view('super-admin.plans.create', compact('features'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_dukas' => 'required|integer|min:0',
            'max_products' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*.feature_id' => 'exists:features,id',
            'features.*.value' => 'nullable|string',
        ]);

        $plan = Plan::create($request->only([
            'name', 'description', 'price', 'billing_cycle', 'max_dukas', 'max_products', 'is_active'
        ]));

        // Attach features with values
        if ($request->has('features')) {
            foreach ($request->features as $featureData) {
                $plan->planFeatures()->attach($featureData['feature_id'], ['value' => $featureData['value'] ?? null]);
            }
        }

        return redirect()->route('super-admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function showPlan($id)
    {
        $plan = Plan::with('planFeatures')->findOrFail($id);
        return view('super-admin.plans.show', compact('plan'));
    }

    public function editPlan($id)
    {
        $plan = Plan::with('planFeatures')->findOrFail($id);
        $features = Feature::all();
        return view('super-admin.plans.edit', compact('plan', 'features'));
    }

    public function updatePlan(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_dukas' => 'required|integer|min:0',
            'max_products' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*.feature_id' => 'exists:features,id',
            'features.*.value' => 'nullable|string',
        ]);

        $plan->update($request->only([
            'name', 'description', 'price', 'billing_cycle', 'max_dukas', 'max_products', 'is_active'
        ]));

        // Sync features
        $featuresData = [];
        if ($request->has('features')) {
            foreach ($request->features as $featureData) {
                $featuresData[$featureData['feature_id']] = ['value' => $featureData['value'] ?? null];
            }
        }
        $plan->planFeatures()->sync($featuresData);

        return redirect()->route('super-admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroyPlan($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return redirect()->route('super-admin.plans.index')->with('success', 'Plan deleted successfully.');
    }

    // Dukas Management
    public function dukas(Request $request)
    {
        $query = Duka::with(['tenant.user', 'dukaSubscriptions.plan']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%')
                  ->orWhereHas('tenant.user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Plan expiry filter
        if ($request->has('plan_status') && !empty($request->plan_status)) {
            if ($request->plan_status === 'expired') {
                $query->whereHas('dukaSubscriptions', function($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '<', Carbon::now());
                });
            } elseif ($request->plan_status === 'active') {
                $query->whereHas('dukaSubscriptions', function($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '>=', Carbon::now());
                });
            } elseif ($request->plan_status === 'no_plan') {
                $query->whereDoesntHave('dukaSubscriptions', function($q) {
                    $q->where('status', 'active');
                });
            }
        }

        $dukas = $query->orderBy('created_at', 'desc')->paginate(10);

        // Calculate expiry status for each duka using DukaSubscription model methods
        foreach ($dukas as $duka) {
            $activeSubscription = $duka->dukaSubscriptions->where('status', 'active')->first();
            if ($activeSubscription) {
                $statusInfo = $activeSubscription->getStatusWithDays();
                $duka->plan_status = $statusInfo['status'];
                $duka->days_remaining = $statusInfo['days_remaining'];
            } else {
                $duka->plan_status = 'no_plan';
                $duka->days_remaining = 0;
            }
        }

        return view('super-admin.dukas.index', compact('dukas'));
    }

    public function showDuka($id)
    {
        $duka = Duka::with(['tenant.user', 'dukaSubscriptions.plan', 'customers', 'sales'])->findOrFail($id);

        // Get subscription history
        $subscriptionHistory = $duka->dukaSubscriptions()->with('plan')->orderBy('created_at', 'desc')->get();

        // Calculate total revenue from this duka
        $totalRevenue = $duka->sales()->sum('total_amount');

        return view('super-admin.dukas.show', compact('duka', 'subscriptionHistory', 'totalRevenue'));
    }

    public function destroyDuka($id)
    {
        $duka = Duka::findOrFail($id);

        // Check if duka has active subscriptions
        $activeSubscriptions = $duka->dukaSubscriptions()->where('status', 'active')->count();
        if ($activeSubscriptions > 0) {
            return redirect()->back()->withErrors(['general' => 'Cannot delete duka with active subscriptions. Please cancel all active subscriptions first.']);
        }

        // Check if duka has sales
        $salesCount = $duka->sales()->count();
        if ($salesCount > 0) {
            return redirect()->back()->withErrors(['general' => 'Cannot delete duka with existing sales records. Please archive instead.']);
        }

        try {
            $dukaName = $duka->name;
            $duka->delete();

            Log::info('Duka deleted successfully', [
                'duka_id' => $id,
                'duka_name' => $dukaName,
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'timestamp' => now()
            ]);

            return redirect()->route('super-admin.dukas.index')->with('success', "Duka '{$dukaName}' has been deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to delete duka', [
                'duka_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['general' => 'Failed to delete duka. Please try again.']);
        }
    }

    // Subscriptions Analytics
    public function subscriptions(Request $request)
    {
        $query = DukaSubscription::with(['duka.tenant.user', 'plan']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('duka', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('plan', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('super-admin.subscriptions.index', compact('subscriptions'));
    }

    public function subscriptionAnalytics()
    {
        // Total subscription revenue (all successful payments)
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        // Monthly revenue for the last 12 months
        $monthlyRevenue = Payment::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Active subscriptions by plan
        $activeSubscriptionsByPlan = DukaSubscription::where('status', 'active')
            ->with('plan')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->get();

        // Expiring subscriptions (next 30 days)
        $expiringSubscriptions = DukaSubscription::where('status', 'active')
            ->where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays(30))
            ->with(['duka.tenant.user', 'plan'])
            ->orderBy('end_date')
            ->get();

        // Expired subscriptions
        $expiredSubscriptions = DukaSubscription::where('status', 'active')
            ->where('end_date', '<', Carbon::now())
            ->with(['duka.tenant.user', 'plan'])
            ->orderBy('end_date', 'desc')
            ->take(10)
            ->get();

        return view('super-admin.subscriptions.analytics', compact(
            'totalRevenue',
            'monthlyRevenue',
            'activeSubscriptionsByPlan',
            'expiringSubscriptions',
            'expiredSubscriptions'
        ));
    }

    // Messages Management
    public function messages(Request $request)
    {
        $query = Message::with(['sender', 'tenant.user']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%')
                  ->orWhereHas('sender', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('tenant.user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('super-admin.messages.index', compact('messages'));
    }

    public function createMessage()
    {
        $tenants = Tenant::with('user')->where('status', 'active')->whereHas('user')->get();
        return view('super-admin.messages.create', compact('tenants'));
    }

    public function storeMessage(Request $request)
    {
        try {
            Log::info('Message creation started', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'recipient_type' => $request->recipient_type,
                'tenant_id' => $request->tenant_id,
                'has_attachment' => $request->hasFile('attachment'),
                'has_video_url' => !empty($request->video_url),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'all_request_data' => $request->all()
            ]);

            Log::info('Starting validation process');

            // Validate based on recipient type
            $rules = [
                'recipient_type' => 'required|in:single,all',
                'subject' => 'required|string|max:255',
                'body' => 'required|string',
                'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif,xls,xlsx,ppt,pptx,zip,rar',
                'video_url' => 'nullable|url',
            ];

            // Add tenant_id validation only for single recipient
            if ($request->recipient_type === 'single') {
                $rules['tenant_id'] = 'required|exists:tenants,id';
            } else {
                $rules['tenant_id'] = 'nullable';
            }

            $request->validate($rules);

            Log::info('Message validation passed', [
                'subject_length' => strlen($request->subject),
                'body_length' => strlen($request->body),
                'recipient_type' => $request->recipient_type,
                'tenant_id' => $request->tenant_id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during validation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        try {
            $sender = auth()->user();

            Log::info('Sender authenticated successfully', [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'sender_email' => $sender->email
            ]);

            // Handle file upload
            $attachmentData = null;
            if ($request->hasFile('attachment')) {
                Log::info('File upload processing started', [
                    'has_file' => true,
                    'file_info' => $request->file('attachment')
                ]);

                $file = $request->file('attachment');

                Log::info('File details extracted', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_size_human' => $this->formatBytes($file->getSize()),
                    'is_valid' => $file->isValid()
                ]);

                if (!$file->isValid()) {
                    Log::error('Invalid file uploaded', [
                        'error_code' => $file->getError(),
                        'error_message' => $file->getErrorMessage()
                    ]);
                    return back()->withErrors(['attachment' => 'Invalid file uploaded.'])->withInput();
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                Log::info('Attempting to store file', [
                    'filename' => $filename,
                    'storage_disk' => 'public',
                    'directory' => 'message_attachments'
                ]);

                $path = $file->storeAs('message_attachments', $filename, 'public');

                if ($path) {
                    $attachmentData = [
                        'attachment_path' => $path,
                        'attachment_name' => $file->getClientOriginalName(),
                        'attachment_type' => $file->getMimeType(),
                        'attachment_size' => $file->getSize(),
                    ];

                    Log::info('File upload successful', [
                        'stored_path' => $path,
                        'full_url' => asset('storage/' . $path),
                        'attachment_data' => $attachmentData
                    ]);
                } else {
                    Log::error('File upload failed - no path returned', [
                        'filename' => $filename,
                        'file_object' => $file
                    ]);
                    return back()->withErrors(['attachment' => 'Failed to upload attachment.'])->withInput();
                }
            } else {
                Log::info('No attachment file provided');
            }

            // Validate video URL if provided
            $videoUrl = null;
            if ($request->video_url) {
                Log::info('Video URL provided, starting validation', [
                    'video_url' => $request->video_url
                ]);

                $videoUrl = $request->video_url;
                $url = strtolower($videoUrl);
                $isValidVideo = preg_match('/youtube\.com\/watch\?v=|youtu\.be\/|vimeo\.com\/|dailymotion\.com\/video\/|dai\.ly\//', $url);

                Log::info('Video URL validation result', [
                    'video_url' => $videoUrl,
                    'url_lower' => $url,
                    'is_valid' => $isValidVideo,
                    'detected_platform' => $this->detectVideoPlatform($videoUrl),
                    'regex_match' => preg_match('/youtube\.com\/watch\?v=|youtu\.be\/|vimeo\.com\/|dailymotion\.com\/video\/|dai\.ly\//', $url)
                ]);

                if (!$isValidVideo) {
                    Log::warning('Invalid video URL provided', [
                        'video_url' => $videoUrl,
                        'reason' => 'Not from supported platform',
                        'supported_platforms' => ['YouTube', 'Vimeo', 'Dailymotion']
                    ]);
                    return back()->withErrors(['video_url' => 'Please provide a valid video URL from YouTube, Vimeo, or Dailymotion.'])->withInput();
                }
            } else {
                Log::info('No video URL provided');
            }
        } catch (\Exception $e) {
            Log::error('Error during file/video processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->withErrors(['general' => 'An error occurred while processing your request.'])->withInput();
        }

        try {
            $messageData = [
                'sender_id' => $sender->id,
                'subject' => $request->subject,
                'body' => $request->body,
                'is_broadcast' => $request->recipient_type === 'all',
                'sent_at' => now(),
                'video_url' => $videoUrl,
            ];

            // Add attachment data if exists
            if ($attachmentData) {
                $messageData = array_merge($messageData, $attachmentData);
            }

            Log::info('Message data prepared', [
                'is_broadcast' => $messageData['is_broadcast'],
                'has_attachment' => isset($messageData['attachment_path']),
                'has_video' => !empty($messageData['video_url']),
                'data_size' => strlen(json_encode($messageData)),
                'final_message_data_keys' => array_keys($messageData)
            ]);

            if ($request->recipient_type === 'all') {
                // Send to all tenants
                $tenants = Tenant::where('status', 'active')->whereHas('user')->get();

                Log::info('Broadcast message preparation', [
                    'total_active_tenants' => $tenants->count(),
                    'tenant_ids' => $tenants->pluck('id')->toArray(),
                    'tenants_found' => $tenants->isNotEmpty()
                ]);

                if ($tenants->isEmpty()) {
                    Log::warning('No active tenants found for broadcast message');
                    return back()->withErrors(['general' => 'No active tenants found to send the message to.'])->withInput();
                }

                $successCount = 0;
                $failedTenants = [];

                foreach ($tenants as $tenant) {
                    try {
                        Log::info('Creating broadcast message for tenant', [
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name
                        ]);

                        $message = Message::create(array_merge($messageData, [
                            'tenant_id' => $tenant->id,
                        ]));

                        Log::info('Broadcast message sent to tenant', [
                            'message_id' => $message->id,
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'tenant_email' => $tenant->email
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to send broadcast message to tenant', [
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);

                        $failedTenants[] = $tenant->name;
                    }
                }

                $message = "Message sent to {$successCount} tenants successfully.";
                if (!empty($failedTenants)) {
                    $message .= " Failed to send to: " . implode(', ', $failedTenants);
                    Log::warning('Broadcast message partially failed', [
                        'success_count' => $successCount,
                        'failed_count' => count($failedTenants),
                        'failed_tenants' => $failedTenants
                    ]);
                } else {
                    Log::info('Broadcast message completed successfully', [
                        'total_sent' => $successCount
                    ]);
                }
            } else {
                // Send to single tenant
                try {
                    Log::info('Processing direct message', [
                        'target_tenant_id' => $request->tenant_id
                    ]);

                    $tenant = Tenant::findOrFail($request->tenant_id);

                    Log::info('Target tenant found', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'tenant_email' => $tenant->email,
                        'tenant_status' => $tenant->status
                    ]);

                    $message = Message::create(array_merge($messageData, [
                        'tenant_id' => $request->tenant_id,
                    ]));

                    Log::info('Direct message sent successfully', [
                        'message_id' => $message->id,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'tenant_email' => $tenant->email
                    ]);

                    $message = 'Message sent successfully.';
                } catch (\Exception $e) {
                    Log::error('Failed to send direct message', [
                        'tenant_id' => $request->tenant_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'error_line' => $e->getLine(),
                        'error_file' => $e->getFile()
                    ]);

                    return back()->withErrors(['general' => 'Failed to send message. Please try again.'])->withInput();
                }
            }

            Log::info('Message creation process completed', [
                'process_duration_ms' => now()->diffInMilliseconds($request->server('REQUEST_TIME')),
                'final_status' => 'success',
                'redirect_route' => 'super-admin.messages.index'
            ]);

            return redirect()->route('super-admin.messages.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Critical error during message creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request_data' => $request->all(),
                'sender_id' => auth()->id()
            ]);

            return back()->withErrors(['general' => 'A critical error occurred while sending the message. Please check the logs for details.'])->withInput();
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function detectVideoPlatform($url)
    {
        if (strpos(strtolower($url), 'youtube.com') !== false || strpos(strtolower($url), 'youtu.be') !== false) {
            return 'YouTube';
        } elseif (strpos(strtolower($url), 'vimeo.com') !== false) {
            return 'Vimeo';
        } elseif (strpos(strtolower($url), 'dailymotion.com') !== false || strpos(strtolower($url), 'dai.ly') !== false) {
            return 'Dailymotion';
        }
        return 'Unknown';
    }

    public function showMessage($id)
    {
        $message = Message::findOrFail($id);

        // Load relationships conditionally to avoid null reference errors
        $message->load(['sender', 'replies.sender']);

        // Only load tenant relationship if it's not a broadcast message
        if (!$message->is_broadcast) {
            $message->load(['tenant.user']);
        }

        return view('super-admin.messages.show', compact('message'));
    }

    public function markMessageAsRead($id)
    {
        Log::info('Mark message as read request', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'message_id' => $id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            $message = Message::findOrFail($id);

            $wasUnread = !$message->read_at;

            $message->markAsRead();

            Log::info('Message marked as read successfully', [
                'message_id' => $message->id,
                'was_previously_unread' => $wasUnread,
                'marked_read_at' => $message->read_at,
                'time_to_read' => $wasUnread ? now()->diffInSeconds($message->created_at) : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read',
                'marked_read_at' => $message->read_at->format('M d, Y H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark message as read', [
                'message_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark message as read'
            ], 500);
        }
    }

    public function replyToMessage(Request $request, $id)
    {
        Log::info('Reply creation started', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'original_message_id' => $id,
            'has_attachment' => $request->hasFile('attachment'),
            'has_video_url' => !empty($request->video_url),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif,xls,xlsx,ppt,pptx,zip,rar',
            'video_url' => 'nullable|url',
        ]);

        $originalMessage = Message::findOrFail($id);
        $sender = auth()->user();

        Log::info('Reply validation passed', [
            'original_message_id' => $originalMessage->id,
            'original_sender_id' => $originalMessage->sender_id,
            'original_tenant_id' => $originalMessage->tenant_id,
            'is_broadcast_reply' => $originalMessage->is_broadcast,
            'subject_length' => strlen($request->subject),
            'body_length' => strlen($request->body)
        ]);

        // Handle file upload for reply
        $attachmentData = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            Log::info('Reply file upload processing started', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_size_human' => $this->formatBytes($file->getSize())
            ]);

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('message_attachments', $filename, 'public');

            if ($path) {
                $attachmentData = [
                    'attachment_path' => $path,
                    'attachment_name' => $file->getClientOriginalName(),
                    'attachment_type' => $file->getMimeType(),
                    'attachment_size' => $file->getSize(),
                ];

                Log::info('Reply file upload successful', [
                    'stored_path' => $path,
                    'full_url' => asset('storage/' . $path)
                ]);
            } else {
                Log::error('Reply file upload failed', [
                    'filename' => $filename,
                    'error' => 'Storage failed'
                ]);
                return back()->withErrors(['attachment' => 'Failed to upload attachment.'])->withInput();
            }
        }

        // Validate video URL if provided
        $videoUrl = null;
        if ($request->video_url) {
            $videoUrl = $request->video_url;
            $url = strtolower($videoUrl);
            $isValidVideo = preg_match('/youtube\.com\/watch\?v=|youtu\.be\/|vimeo\.com\/|dailymotion\.com\/video\/|dai\.ly\//', $url);

            Log::info('Reply video URL validation', [
                'video_url' => $videoUrl,
                'is_valid' => $isValidVideo,
                'detected_platform' => $this->detectVideoPlatform($videoUrl)
            ]);

            if (!$isValidVideo) {
                Log::warning('Invalid reply video URL provided', [
                    'video_url' => $videoUrl,
                    'reason' => 'Not from supported platform'
                ]);
                return back()->withErrors(['video_url' => 'Please provide a valid video URL from YouTube, Vimeo, or Dailymotion.'])->withInput();
            }
        }

        $messageData = [
            'sender_id' => $sender->id,
            'tenant_id' => $originalMessage->is_broadcast ? null : $originalMessage->tenant_id,
            'parent_id' => $originalMessage->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_broadcast' => false,
            'sent_at' => now(),
            'video_url' => $videoUrl,
        ];

        // Add attachment data if exists
        if ($attachmentData) {
            $messageData = array_merge($messageData, $attachmentData);
        }

        Log::info('Reply message data prepared', [
            'parent_message_id' => $originalMessage->id,
            'target_tenant_id' => $messageData['tenant_id'],
            'has_attachment' => isset($messageData['attachment_path']),
            'has_video' => !empty($messageData['video_url']),
            'data_size' => strlen(json_encode($messageData))
        ]);

        try {
            // Create reply message
            $reply = Message::create($messageData);

            Log::info('Reply message created successfully', [
                'reply_message_id' => $reply->id,
                'parent_message_id' => $originalMessage->id,
                'recipient_tenant_id' => $reply->tenant_id,
                'recipient_tenant_name' => $reply->tenant?->name,
                'has_attachment' => $reply->hasAttachment(),
                'has_video' => $reply->hasVideo(),
                'video_platform' => $reply->hasVideo() ? $reply->getVideoPlatform() : null
            ]);

            // Log conversation thread update
            $threadDepth = $this->getMessageThreadDepth($reply);
            Log::info('Message thread updated', [
                'thread_root_id' => $this->getRootMessageId($reply),
                'thread_depth' => $threadDepth,
                'total_replies_in_thread' => $this->getTotalRepliesInThread($reply)
            ]);

            return redirect()->route('super-admin.messages.show', $reply->id)
                            ->with('success', 'Reply sent successfully');

        } catch (\Exception $e) {
            Log::error('Failed to create reply message', [
                'original_message_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message_data' => $messageData
            ]);

            return back()->withErrors(['general' => 'Failed to send reply. Please try again.'])->withInput();
        }
    }

    private function getMessageThreadDepth($message)
    {
        $depth = 0;
        $current = $message;

        while ($current->parent) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    private function getRootMessageId($message)
    {
        $current = $message;
        while ($current->parent) {
            $current = $current->parent;
        }
        return $current->id;
    }

    private function getTotalRepliesInThread($message)
    {
        $rootId = $this->getRootMessageId($message);
        return Message::where('id', $rootId)
                      ->orWhere('parent_id', $rootId)
                      ->orWhereHas('parent', function($q) use ($rootId) {
                          $q->where('parent_id', $rootId);
                      })
                      ->count();
    }

    // Backup Management
    public function backups()
    {
        // Get backups from public/backups directory
        $publicBackupsPath = public_path('backups');

        // Ensure the backup directory exists
        if (!file_exists($publicBackupsPath)) {
            mkdir($publicBackupsPath, 0755, true);
        }

        $backups = [];
        $files = scandir($publicBackupsPath);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $publicBackupsPath . '/' . $file;
                $backups[] = (object) [
                    'path' => $filePath,
                    'date' => \Carbon\Carbon::createFromTimestamp(filemtime($filePath)),
                    'size' => filesize($filePath),
                ];
            }
        }

        // Sort backups by date (newest first)
        usort($backups, function($a, $b) {
            return $b->date <=> $a->date;
        });

        // Convert to collection for easier handling in view
        $backups = collect($backups);

        // Get backup statistics
        $stats = [
            'total_backups' => $backups->count(),
            'total_size' => $backups->sum(function ($backup) {
                return $backup->size;
            }),
            'oldest_backup' => $backups->isNotEmpty() ? $backups->last()->date : null,
            'newest_backup' => $backups->isNotEmpty() ? $backups->first()->date : null,
        ];

        return view('super-admin.backups.index', compact('backups', 'stats'));
    }

    public function createBackup()
    {
        try {
            Log::info('Manual backup initiated by user', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'timestamp' => now()
            ]);

            // Create backup directory if it doesn't exist
            $publicPath = public_path('backups');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }

            // Generate backup filename
            $timestamp = now()->format('Y-m-d-H-i-s');
            $backupFileName = 'smartbiz-backup-' . $timestamp . '.sql';
            $backupFilePath = $publicPath . '/' . $backupFileName;

            // Database connection details
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE', 'smartbiz');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');

            // Create database dump using mariadb-dump directly
            $command = sprintf(
                '/usr/bin/mariadb-dump --host=%s --port=%s --user=%s --password=%s --skip-ssl --single-transaction %s > %s 2>/dev/null',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($backupFilePath)
            );

            Log::info('Executing mariadb-dump command', [
                'command' => preg_replace('/--password=[^ ]+/', '--password=***', $command),
                'backup_path' => $backupFilePath
            ]);

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                Log::error('mariadb-dump command failed', [
                    'return_code' => $returnCode,
                    'command' => preg_replace('/--password=[^ ]+/', '--password=***', $command),
                    'output' => $output
                ]);

                return redirect()->route('super-admin.backups.index')
                                ->with('error', 'Database backup failed. Please check database connection settings.');
            }

            // Check if backup file was created and has content
            if (!file_exists($backupFilePath) || filesize($backupFilePath) === 0) {
                Log::error('Backup file was not created or is empty', [
                    'file_path' => $backupFilePath,
                    'file_exists' => file_exists($backupFilePath),
                    'file_size' => file_exists($backupFilePath) ? filesize($backupFilePath) : 'N/A'
                ]);

                return redirect()->route('super-admin.backups.index')
                                ->with('error', 'Backup file was not created properly.');
            }

            $fileSize = filesize($backupFilePath);

            Log::info('Database backup created successfully', [
                'file_path' => $backupFilePath,
                'file_size' => $fileSize,
                'file_size_human' => number_format($fileSize / 1024 / 1024, 2) . ' MB'
            ]);

            // Send email with backup link
            $this->sendBackupEmail($backupFileName, $backupFilePath);

            return redirect()->route('super-admin.backups.index')
                            ->with('success', 'Database backup created successfully and sent to email! File size: ' . number_format($fileSize / 1024 / 1024, 2) . ' MB');

        } catch (\Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return redirect()->route('super-admin.backups.index')
                            ->with('error', 'Backup creation failed: ' . $e->getMessage());
        }
    }

    private function sendBackupEmail($fileName, $filePath)
    {
        try {
            $fileSize = filesize($filePath);
            $downloadUrl = url('backups/' . $fileName);

            \Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                        <h1 style='margin: 0; font-size: 24px;'>🗄️ SMARTBIZ Database Backup</h1>
                        <p style='margin: 10px 0 0 0; opacity: 0.9;'>Backup Created Successfully</p>
                    </div>

                    <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #333; margin-top: 0;'>📋 Backup Details</h2>

                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 120px;'>📁 File Name:</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$fileName}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>📊 Size:</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . number_format($fileSize / 1024 / 1024, 2) . " MB</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>📅 Created:</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . now()->format('M d, Y \a\t H:i:s') . "</td>
                            </tr>
                        </table>

                        <div style='background: #e8f5e8; border: 1px solid #c8e6c9; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                            <h3 style='color: #2e7d32; margin-top: 0;'>✅ Backup Ready for Download</h3>
                            <p style='margin-bottom: 15px; color: #333;'>Your database backup has been created and is ready for download. Click the button below to access it:</p>
                            <a href='{$downloadUrl}' style='background: #4caf50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>⬇️ Download Backup</a>
                        </div>

                        <div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                            <h4 style='color: #856404; margin-top: 0;'>ℹ️ Important Information</h4>
                            <ul style='color: #333; margin: 0; padding-left: 20px;'>
                                <li>This backup contains all database tables and data in SQL format</li>
                                <li>Use this file to restore the system: <code>mysql -u username -p database_name < backup.sql</code></li>
                                <li>Store this backup in a secure location</li>
                                <li>The backup file is also available in your admin panel</li>
                            </ul>
                        </div>

                        <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>

                        <div style='text-align: center; color: #666;'>
                            <p style='margin: 0;'><strong>SMARTBIZ System</strong></p>
                            <p style='margin: 5px 0 0 0; font-size: 14px;'>Automated Database Backup Service</p>
                            <p style='margin: 5px 0 0 0; font-size: 12px; color: #999;'>Generated on " . now()->format('l, F j, Y \a\t g:i A T') . "</p>
                        </div>
                    </div>
                </div>
            ", function ($message) use ($fileName, $filePath) {
                $message->to('petsonvedastuskisenya1997@gmail.com')
                        ->subject('🗄️ SMARTBIZ Database Backup - ' . now()->format('Y-m-d H:i:s'))
                        ->attach($filePath, [
                            'as' => $fileName,
                            'mime' => 'application/zip'
                        ]);
            });

            Log::info('Backup email sent successfully', [
                'recipient' => 'petsonvedastuskisenya1997@gmail.com',
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'email_type' => 'HTML'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send backup email', [
                'error' => $e->getMessage(),
                'file_name' => $fileName,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function downloadBackup($filename)
    {
        $filePath = public_path('backups/' . $filename);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->route('super-admin.backups.index')
                        ->with('error', 'Backup file not found');
    }

    public function deleteBackup($filename)
    {
        $filePath = public_path('backups/' . $filename);

        if (file_exists($filePath)) {
            unlink($filePath);

            Log::info('Backup file deleted', [
                'file_name' => $filename,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('super-admin.backups.index')
                            ->with('success', 'Backup deleted successfully');
        }

        return redirect()->route('super-admin.backups.index')
                        ->with('error', 'Backup file not found');
    }

    // Customers Overview
    public function customers(Request $request)
    {
        $query = Customer::with(['tenant.user', 'duka']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        // Calculate total customers and statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();

        return view('super-admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers'
        ));
    }
public function showCustomer($id)

{

    $customer = Customer::with(['tenant.user.tenantAccount', 'duka', 'sales'])->findOrFail($id);
        // Get customer's purchase history
        $purchaseHistory = $customer->sales()->with(['duka', 'saleItems.product'])->orderBy('created_at', 'desc')->get();

        // Calculate total spent
        $totalSpent = $customer->sales()->sum('total_amount');
// Calculate total loans

$totalLoans = $customer->sales()->where('is_loan', true)->sum('total_amount');

        // Get currency from tenant account
        $currency = $customer->tenant->user->tenantAccount->currency ?? 'TZS';

        return view('super-admin.customers.show', compact('customer', 'purchaseHistory', 'totalSpent', 'totalLoans', 'currency'));
    }

    // Telescope Management
    public function telescope(Request $request)
    {
        $query = DB::table('telescope_entries');

        // Type filter
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in content
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get enhanced statistics
        $stats = [
            'total_entries' => DB::table('telescope_entries')->count(),
            'today_entries' => DB::table('telescope_entries')->whereDate('created_at', today())->count(),
            'yesterday_entries' => DB::table('telescope_entries')->whereDate('created_at', today()->subDay())->count(),
            'week_entries' => DB::table('telescope_entries')->where('created_at', '>=', now()->startOfWeek())->count(),
            'month_entries' => DB::table('telescope_entries')->where('created_at', '>=', now()->startOfMonth())->count(),

            // Type breakdown
            'query_count' => DB::table('telescope_entries')->where('type', 'query')->count(),
            'model_count' => DB::table('telescope_entries')->where('type', 'model')->count(),
            'request_count' => DB::table('telescope_entries')->where('type', 'request')->count(),
            'command_count' => DB::table('telescope_entries')->where('type', 'command')->count(),
            'job_count' => DB::table('telescope_entries')->where('type', 'job')->count(),
            'event_count' => DB::table('telescope_entries')->where('type', 'event')->count(),
            'cache_count' => DB::table('telescope_entries')->where('type', 'cache')->count(),
            'log_count' => DB::table('telescope_entries')->where('type', 'log')->count(),

            // Performance metrics
            'avg_response_time' => $this->getAverageResponseTime(),
            'slow_queries' => $this->getSlowQueriesCount(),
            'error_count' => $this->getErrorCount(),

            // Storage info
            'oldest_entry' => DB::table('telescope_entries')->min('created_at'),
            'newest_entry' => DB::table('telescope_entries')->max('created_at'),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('super-admin.telescope.index', compact('entries', 'stats'));
    }

    public function showTelescopeEntry($id)
    {
        $entry = DB::table('telescope_entries')->where('uuid', $id)->first();

        if (!$entry) {
            return redirect()->route('super-admin.telescope.index')->with('error', 'Telescope entry not found');
        }

        // Decode content based on type
        $decodedContent = json_decode($entry->content, true);

        return view('super-admin.telescope.show', compact('entry', 'decodedContent'));
    }

    public function clearTelescopeEntries(Request $request)
    {
        $type = $request->get('type', 'all');
        $cleanupMode = $request->get('cleanup_mode', 'time');
        $days = $request->get('days', 7);
        $keepCount = $request->get('keep_count', 1000);

        $query = DB::table('telescope_entries');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($cleanupMode === 'time') {
            // Time-based cleanup
            if ($days > 0) {
                $query->where('created_at', '<', now()->subDays($days));
                $count = $query->delete();
                $message = "Cleared {$count} telescope entries older than {$days} days";
            } else {
                return redirect()->route('super-admin.telescope.index')
                                ->with('error', 'Invalid cleanup parameters');
            }
        } else {
            // Count-based cleanup - keep only the most recent N entries
            $totalEntries = DB::table('telescope_entries')->count();

            if ($totalEntries <= $keepCount) {
                return redirect()->route('super-admin.telescope.index')
                                ->with('info', 'No cleanup needed. Current entries (' . $totalEntries . ') are within the limit (' . $keepCount . ').');
            }

            // Get the IDs of entries to keep (most recent)
            $entriesToKeep = DB::table('telescope_entries')
                ->orderBy('created_at', 'desc')
                ->limit($keepCount)
                ->pluck('id');

            // Delete all entries except the ones to keep
            $count = DB::table('telescope_entries')
                ->whereNotIn('id', $entriesToKeep)
                ->delete();

            $message = "Smart cleanup completed. Kept {$keepCount} most recent entries, deleted {$count} older entries";
        }

        return redirect()->route('super-admin.telescope.index')
                        ->with('success', $message);
    }

    public function bulkDeleteTelescopeEntries(Request $request)
    {
        $request->validate([
            'entry_ids' => 'required|array|min:1',
            'entry_ids.*' => 'string|exists:telescope_entries,uuid',
        ]);

        $count = DB::table('telescope_entries')
            ->whereIn('uuid', $request->entry_ids)
            ->delete();

        return redirect()->route('super-admin.telescope.index')
                        ->with('success', "Successfully deleted {$count} telescope entries");
    }

    public function exportTelescopeEntries(Request $request)
    {
        $query = DB::table('telescope_entries');

        // Apply same filters as index method
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $entries = $query->orderBy('created_at', 'desc')->get();

        $filename = 'telescope-entries-' . now()->format('Y-m-d-H-i-s') . '.json';

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->json($entries, 200, $headers);
    }

    public function getTelescopeStats()
    {
        $stats = [
            'total_entries' => DB::table('telescope_entries')->count(),
            'today_entries' => DB::table('telescope_entries')->whereDate('created_at', today())->count(),
            'yesterday_entries' => DB::table('telescope_entries')->whereDate('created_at', today()->subDay())->count(),
            'week_entries' => DB::table('telescope_entries')->where('created_at', '>=', now()->startOfWeek())->count(),
            'month_entries' => DB::table('telescope_entries')->where('created_at', '>=', now()->startOfMonth())->count(),

            // Type breakdown
            'query_count' => DB::table('telescope_entries')->where('type', 'query')->count(),
            'model_count' => DB::table('telescope_entries')->where('type', 'model')->count(),
            'request_count' => DB::table('telescope_entries')->where('type', 'request')->count(),
            'command_count' => DB::table('telescope_entries')->where('type', 'command')->count(),
            'job_count' => DB::table('telescope_entries')->where('type', 'job')->count(),
            'event_count' => DB::table('telescope_entries')->where('type', 'event')->count(),
            'cache_count' => DB::table('telescope_entries')->where('type', 'cache')->count(),
            'log_count' => DB::table('telescope_entries')->where('type', 'log')->count(),

            // Performance metrics
            'avg_response_time' => $this->getAverageResponseTime(),
            'slow_queries' => $this->getSlowQueriesCount(),
            'error_count' => $this->getErrorCount(),

            // Storage info
            'oldest_entry' => DB::table('telescope_entries')->min('created_at'),
            'newest_entry' => DB::table('telescope_entries')->max('created_at'),
            'database_size' => $this->getDatabaseSize(),
        ];

        return response()->json($stats);
    }

    private function getAverageResponseTime()
    {
        $requests = DB::table('telescope_entries')
            ->where('type', 'request')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $totalTime = 0;
        $count = 0;

        foreach ($requests as $request) {
            $content = json_decode($request->content, true);
            if (isset($content['duration'])) {
                $totalTime += $content['duration'];
                $count++;
            }
        }

        return $count > 0 ? round($totalTime / $count, 2) : 0;
    }

    private function getSlowQueriesCount()
    {
        return DB::table('telescope_entries')
            ->where('type', 'query')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereRaw("JSON_EXTRACT(content, '$.duration') > 1000")
            ->count();
    }

    private function getErrorCount()
    {
        return DB::table('telescope_entries')
            ->where('type', 'log')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereRaw("JSON_EXTRACT(content, '$.level') IN ('error', 'critical', 'emergency')")
            ->count();
    }

    private function getDatabaseSize()
    {
        try {
            $result = DB::select("SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()");

            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Profile Management
    public function profile()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email']);

        // Handle profile picture removal
        if ($request->input('remove_profile_picture') == '1') {
            if ($user->profile_picture && \Storage::disk('public')->exists('profiles/' . $user->profile_picture)) {
                \Storage::disk('public')->delete('profiles/' . $user->profile_picture);
            }
            $data['profile_picture'] = null;
        }
        // Handle profile picture upload
        elseif ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && \Storage::disk('public')->exists('profiles/' . $user->profile_picture)) {
                \Storage::disk('public')->delete('profiles/' . $user->profile_picture);
            }

            $profilePath = $request->file('profile_picture')->store('profiles', 'public');
            $data['profile_picture'] = basename($profilePath);
        }

        $user->update($data);

        return redirect()->route('super-admin.profile')->with('success', 'Profile updated successfully!');
    }

    // Settings Management
    public function settings(Request $request)
    {
        $query = Tenant::with('user');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $tenants = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('super-admin.settings.index', compact('tenants'));
    }

    public function bulkSetPassword(Request $request)
    {
        $request->validate([
            'bulk_default_password' => 'required|string|min:4|max:50',
        ]);

        $count = Tenant::count();
        Tenant::query()->update([
            'default_password' => $request->bulk_default_password,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Default password has been set to '{$request->bulk_default_password}' for all {$count} tenants.");
    }

    public function setTenantPassword(Request $request, $tenantId)
    {
        $request->validate([
            'default_password' => 'required|string|min:4|max:50',
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update([
            'default_password' => $request->default_password,
        ]);

        return redirect()->back()->with('success', "Default password for '{$tenant->name}' has been updated to '{$request->default_password}'.");
    }

}

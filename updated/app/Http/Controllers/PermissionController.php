<?php
namespace App\Http\Controllers;

use App\Models\Duka;
use App\Models\Plan;
use App\Models\StaffPermission;
use App\Models\TenantOfficer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    // Get available permissions from database
    private function getAvailablePermissionsFromDb()
    {
        return \App\Models\AvailablePermission::where('is_active', true)
            ->pluck('display_name', 'name')
            ->toArray();
    }

    public function index()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant->id;

        // Get all dukas for this tenant with their plan information
        $dukas = Duka::where('tenant_id', $tenantId)
            ->with(['activeSubscription.plan'])
            ->get();

        // Officers with role = 'officer'
        $officers = TenantOfficer::where('tenant_id', $tenantId)
            ->where('role', 'officer')
            ->with(['officer.officerAssignments.duka', 'duka.activeSubscription.plan'])
            ->get();

        return view('permissions.index', compact('dukas', 'officers'));
    }
     public function updateOfficerPermissions(Request $request, $officerId)
    {
           $user     = auth()->user();
     $tenantId = $user->tenant->id;


        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);

        $officer = User::findOrFail($officerId);

        // Check if officer belongs to tenant's dukas
        $isAssigned = TenantOfficer::where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->exists();

        if (! $isAssigned) {
            return redirect()->back()->with('error', 'Officer not found in your organization');
        }

        $selectedPermissions = $request->input('permissions', []);

        DB::transaction(function () use ($tenantId, $officerId, $selectedPermissions) {
            // Get officer's dukas
            $officerDukas = TenantOfficer::where('tenant_id', $tenantId)
                ->where('officer_id', $officerId)
                ->pluck('duka_id');

            // Remove all existing permissions for this officer
            StaffPermission::where('tenant_id', $tenantId)
                ->where('officer_id', $officerId)
                ->delete();

            // Add selected permissions for each duka
            foreach ($officerDukas as $dukaId) {
                foreach ($selectedPermissions as $permission) {
                    // Check if permission exists in available permissions
                    if (\App\Models\AvailablePermission::where('name', $permission)->where('is_active', true)->exists()) {
                        StaffPermission::create([
                            'tenant_id'       => $tenantId,
                            'officer_id'      => $officerId,
                            'duka_id'         => $dukaId,
                            'permission_name' => $permission,
                            'is_granted'      => true,
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Officer permissions updated successfully');
    }

    /**
     * Show permissions for a specific officer
     */
public function showOfficerPermissions($officerId)
{
    $user = auth()->user();

    // 1. Load Tenant with the full relationship tree
    $tenant = $user->tenant()
        ->with(['activeSubscription.plan.features'])
        ->first();

    // 2. Comprehensive check to avoid "pluck on null" errors
    if (!$tenant || !$tenant->activeSubscription || !$tenant->activeSubscription->plan) {
        return redirect()->back()->with('error', 'Active plan or subscription not found for this organization.');
    }

    $activePlan = $tenant->activeSubscription->plan;
    $officer = User::findOrFail($officerId);

    // 3. Verify officer belongs to this tenant
    $isAssigned = \App\Models\TenantOfficer::where('tenant_id', $tenant->id)
        ->where('officer_id', $officerId)
        ->exists();

    if (!$isAssigned) {
        abort(403, 'This officer is not assigned to your organization.');
    }

    // 4. Safe Pluck: Using features() method ensures we get a query builder
    // This prevents the error if the collection is null
    $allowedFeatureIds = $activePlan->features()->pluck('features.id')->toArray();

    // 5. Fetch permissions based on IDs 12, 13, 14 (as per your DB data)
    $availablePermissions = \App\Models\AvailablePermission::where('is_active', true)
        ->whereIn('feature_id', $allowedFeatureIds)
        ->with('feature')
        ->get();

    // 6. Get officer's current permissions (to show checkmarks)
    $permissions = \App\Models\StaffPermission::where('tenant_id', $tenant->id)
        ->where('officer_id', $officerId)
        ->get()
        ->keyBy('permission_name');

    // 7. Get officer's assigned dukas
    $officerDukas = \App\Models\TenantOfficer::where('tenant_id', $tenant->id)
        ->where('officer_id', $officerId)
        ->with(['duka'])
        ->get();

    return view('permissions.officer-permissions', compact(
        'officer',
        'permissions',
        'officerDukas',
        'availablePermissions',
        'activePlan'
    ));
}
    /**
     * Assign or revoke permissions for an officer
     */


    /**
     * Get plan details for a specific duka
     */
    public function checkDukaPlan($dukaId)
    {
        $user     = auth()->user();
        $tenantId = $user->tenant->id;

        $duka = Duka::where('tenant_id', $tenantId)
            ->where('id', $dukaId)
            ->with(['activeSubscription.plan'])
            ->firstOrFail();

        $plan = $duka->activeSubscription?->plan;

        return response()->json([
            'duka'     => $duka,
            'plan'     => $plan,
            'features' => $plan?->features ?? [],
        ]);
    }

    /**
     * Get available permissions
     */
    public function getAvailablePermissions()
    {
        return $this->getAvailablePermissionsFromDb();
    }
}

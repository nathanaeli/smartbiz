<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$officerId = 116;
$user = \App\Models\User::find($officerId);

if (!$user) {
    echo "User 116 not found\n";
    exit;
}

echo "User: " . $user->name . " (ID: $officerId)\n";

$assignment = \App\Models\TenantOfficer::where('officer_id', $officerId)
    ->where('status', true)
    ->first();

if (!$assignment) {
    echo "No active assignment found in TenantOfficer for this user.\n";

    // Check inactive assignments
    $inactive = \App\Models\TenantOfficer::where('officer_id', $officerId)->count();
    echo "Found $inactive inactive assignments.\n";
    exit;
}

echo "Active Assignment Found:\n";
echo "Tenant ID: " . $assignment->tenant_id . "\n";
echo "Assigned Duka ID (primary): " . $assignment->duka_id . "\n";

// Get all duka IDs for this officer and tenant
$dukaIds = \App\Models\TenantOfficer::where('tenant_id', $assignment->tenant_id)
    ->where('officer_id', $officerId)
    ->where('status', true)
    ->pluck('duka_id');

echo "All Assigned Duka IDs: " . $dukaIds->implode(', ') . "\n";

// Check Sales matching these Duka IDs
$salesCount = \App\Models\Sale::where('tenant_id', $assignment->tenant_id)
    ->whereIn('duka_id', $dukaIds)
    ->count();

echo "Sales count matching Tenant & Duka IDs: $salesCount\n";

// Check Sales created by this user
$salesByUserCount = \App\Models\Sale::where('created_by', $officerId)->count();
echo "Sales created by user (created_by = $officerId): $salesByUserCount\n";

if ($salesByUserCount > 0) {
    $sampleSales = \App\Models\Sale::where('created_by', $officerId)->limit(5)->get();
    echo "\nSample Sales created by User:\n";
    foreach ($sampleSales as $sale) {
        $matchesTenant = $sale->tenant_id == $assignment->tenant_id ? 'Yes' : 'No';
        $matchesDuka = $dukaIds->contains($sale->duka_id) ? 'Yes' : 'No';
        echo "Sale ID: {$sale->id}, Tenant: {$sale->tenant_id} (Matches: $matchesTenant), Duka: {$sale->duka_id} (Matches: $matchesDuka)\n";
    }
} else {
    echo "User has created 0 sales.\n";
}

// Check if user has sales but linked via officer_id (if column exists)
// Note: Sale model doesn't have officer_id in fillable, but let's check schema/columns just in case
try {
    $hasOfficerId = \Schema::hasColumn('sales', 'officer_id');
    if ($hasOfficerId) {
        $salesByOfficerId = \DB::table('sales')->where('officer_id', $officerId)->count();
        echo "Sales with officer_id = $officerId: $salesByOfficerId\n";
    } else {
        echo "Sales table does not have 'officer_id' column.\n";
    }
} catch (\Exception $e) {
    echo "Error checking schema: " . $e->getMessage() . "\n";
}

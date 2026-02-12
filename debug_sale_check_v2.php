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
    echo "No active assignment found.\n";
    exit;
}

$tenantId = $assignment->tenant_id;
$dukaIds = \App\Models\TenantOfficer::where('tenant_id', $tenantId)
    ->where('officer_id', $officerId)
    ->where('status', true)
    ->pluck('duka_id');

echo "Tenant ID: $tenantId\n";
echo "Assigned Duka IDs: " . $dukaIds->implode(', ') . "\n";

// Check Sales (With Trashed)
$salesCount = \App\Models\Sale::withTrashed()
    ->where('tenant_id', $tenantId)
    ->whereIn('duka_id', $dukaIds)
    ->count();

echo "Sales (including trashed) for checks: $salesCount\n";

if ($salesCount == 0) {
    // Check if there are ANY sales for this tenant
    $tenantSales = \App\Models\Sale::withTrashed()->where('tenant_id', $tenantId)->count();
    echo "Total Sales for Tenant $tenantId (any duka): $tenantSales\n";

    if ($tenantSales > 0) {
        // Show distribution by duka
        $salesByDuka = \App\Models\Sale::withTrashed()
            ->where('tenant_id', $tenantId)
            ->select('duka_id', \DB::raw('count(*) as total'))
            ->groupBy('duka_id')
            ->get();

        echo "Sales by Duka for Tenant $tenantId:\n";
        foreach ($salesByDuka as $stat) {
            echo "  Duka ID {$stat->duka_id}: {$stat->total}\n";
        }
    }
}

// Check if user created sales regardless of assignment
$userSales = \App\Models\Sale::withTrashed()->where('created_by', $officerId)->count();
echo "Sales created by User $officerId (created_by): $userSales\n";

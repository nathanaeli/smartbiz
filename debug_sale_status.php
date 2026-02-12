<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$officerId = 116;
$tenantId = 71;
$dukaIds = [42];

echo "Checking Sales for Tenant $tenantId and Duka IDs: " . implode(', ', $dukaIds) . "\n";

$sales = \App\Models\Sale::withTrashed()
    ->where('tenant_id', $tenantId)
    ->whereIn('duka_id', $dukaIds)
    ->get();

if ($sales->isEmpty()) {
    echo "No sales found (active or deleted).\n";
} else {
    foreach ($sales as $sale) {
        $status = $sale->deleted_at ? "DELETED at " . $sale->deleted_at : "ACTIVE";
        echo "Sale ID: {$sale->id}, Amount: {$sale->total_amount}, Created By: {$sale->created_by}, Status: $status\n";
    }
}

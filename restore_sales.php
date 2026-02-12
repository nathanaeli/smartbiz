<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$tenantId = 71;
$dukaId = 42;
$saleIds = [140, 141, 142, 143];

echo "Attempting to restore sales for Tenant $tenantId, Duka $dukaId...\n";

$sales = \App\Models\Sale::withTrashed()
    ->where('tenant_id', $tenantId)
    ->where('duka_id', $dukaId)
    ->whereIn('id', $saleIds)
    ->get();

$count = 0;
foreach ($sales as $sale) {
    if ($sale->trashed()) {
        $sale->restore();
        echo "Restored Sale ID: {$sale->id}\n";
        $count++;
    } else {
        echo "Sale ID: {$sale->id} was already active.\n";
    }
}

echo "Total sales restored: $count\n";

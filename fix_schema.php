<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Running schema fix for 'sales' table...\n";

try {
    Schema::table('sales', function (Blueprint $table) {
        // Drop the current column first? Or modify?
        // Let's modify it to be nullable.
        // If strict mode is on, we might need DB::statement
        $table->timestamp('deleted_at')->nullable()->default(null)->change();
    });

    echo "Schema updated successfully using Blueprint change().\n";
} catch (\Exception $e) {
    echo "Blueprint change failed: " . $e->getMessage() . "\n";
    echo "Attempting RAW SQL fix...\n";

    try {
        // If using MariaDB/MySQL
        \DB::statement("ALTER TABLE sales MODIFY COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "Schema updated using RAW SQL.\n";
    } catch (\Exception $e2) {
        echo "RAW SQL failed too: " . $e2->getMessage() . "\n";
    }
}

// Verify the change
$columns = \DB::select('SHOW COLUMNS FROM sales WHERE Field = "deleted_at"');
foreach ($columns as $col) {
    echo "Field: " . $col->Field . " | Null: " . $col->Null . " | Default: " . ($col->Default === null ? 'NULL' : $col->Default) . "\n";
}

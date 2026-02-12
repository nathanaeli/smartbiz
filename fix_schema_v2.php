<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Running data cleanup for 'sales' table...\n";

// 1. Check for invalid dates (0000-00-00)
// Using raw query because Eloquent might choke on invalid dates
try {
    // We can't check for '0000-00-00' easily in strict mode select, but we can try updating blind
    // Or we can disable strict mode for the connection
    DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

    // Update invalid dates to a safe placeholder
    $affected = DB::update("UPDATE sales SET deleted_at = '2000-01-01 00:00:01' WHERE CAST(deleted_at AS CHAR) LIKE '0000-00-00%'");
    echo "Fixed $affected rows with invalid '0000-00-00' dates.\n";

    // 2. Now try to alter the table
    echo "Attempting to Modify Column to Nullable...\n";
    DB::statement("ALTER TABLE sales MODIFY COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");

    echo "Schema updated successfully!\n";

    // 3. Set the placeholder dates to NULL (restoring them effectively, or just cleaning up)
    // If they were 0000-00-00, they were likely meant to be NULL (active)
    $restored = DB::update("UPDATE sales SET deleted_at = NULL WHERE deleted_at = '2000-01-01 00:00:01'");
    echo "Converted $restored placeholder rows to NULL (Active).\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Verify Schema
$columns = DB::select('SHOW COLUMNS FROM sales WHERE Field = "deleted_at"');
foreach ($columns as $col) {
    echo "Field: " . $col->Field . " | Null: " . $col->Null . " | Default: " . ($col->Default === null ? 'NULL' : $col->Default) . "\n";
}

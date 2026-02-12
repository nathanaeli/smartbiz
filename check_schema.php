<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$columns = \DB::select('SHOW COLUMNS FROM sales WHERE Field = "deleted_at"');

if (empty($columns)) {
    echo "Column 'deleted_at' does not exist in 'sales' table.\n";
} else {
    foreach ($columns as $col) {
        echo "Field: " . $col->Field . "\n";
        echo "Type: " . $col->Type . "\n";
        echo "Null: " . $col->Null . "\n"; // Should be YES
        echo "Default: " . ($col->Default === null ? 'NULL' : $col->Default) . "\n";
    }
}

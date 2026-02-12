<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('import_batch')->nullable()->change();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->string('import_batch')->nullable()->change();
        });
    }

    public function down(): void
    {
        // No down action as we don't want to revert to not-nullable
    }
};

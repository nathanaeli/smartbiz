<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'import_batch')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('import_batch')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('stock_movements', 'import_batch')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('import_batch')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('import_batch');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('import_batch');
        });
    }
};

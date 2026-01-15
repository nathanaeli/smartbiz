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
        Schema::table('stock_movements', function (Blueprint $table) {
           $table->foreignId('product_item_id')
                  ->after('user_id')
                  ->nullable()
                  ->constrained('product_items')
                  ->onDelete('set null');

            // 2. Add Financial Columns for Stock Flow
            $table->decimal('unit_cost', 15, 2)->default(0)->after('new_quantity');
            $table->decimal('unit_price', 15, 2)->default(0)->after('unit_cost');
            $table->decimal('total_value', 15, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            //
        });
    }
};

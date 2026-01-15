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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            // Core Relationships
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');

            // Link to individual item (Optional: for one-by-one scanning)
            $table->foreignId('product_item_id')->nullable()->constrained('product_items')->onDelete('set null');

            // Movement Definition
            // Changed to 'in'/'out' for standard accounting logic
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity_change');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');

            // Financial Flow (StockFlow)
            // unit_cost = Expense (Buying price)
            // unit_price = Income (Selling price)
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0); // qty * relevant price

            // Metadata
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('reason')->nullable(); // e.g., 'purchase', 'sale', 'damage', 'return'

            $table->timestamps();

            // Indexing for faster reporting
            $table->index(['type', 'reason']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

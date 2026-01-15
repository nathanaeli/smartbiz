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
        Schema::create('product_stock_items', function (Blueprint $table) {
            $table->id();

            // Relationship to stocks table
            $table->foreignId('stock_id')
                ->constrained('stocks')
                ->onDelete('cascade');

            // Relationship to products table
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // Unique QR code per item
            $table->string('qr_code')->unique();

            // Track if sold or not
            $table->enum('status', ['available', 'sold'])
                ->default('available');

            // Optional for pharmacy/grocery items
            $table->date('expiry_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_items');
    }
};

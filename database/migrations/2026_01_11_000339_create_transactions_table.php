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
        Schema::create('transactions', function (Blueprint $table) {
          $table->id();
        $table->foreignId('duka_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained();
        $table->string('type');     // 'income' or 'expense'
        $table->string('category'); // 'sale', 'stock_purchase', 'rent', etc.
        $table->decimal('amount', 15, 2);
        $table->string('status')->default('active');
        $table->string('payment_method')->default('cash');
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('description')->nullable();
        $table->date('transaction_date');
        $table->softDeletes();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

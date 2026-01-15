<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duka_id')->constrained('dukas')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');

            // Optional additional fields
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['duka_id', 'product_id']); // Prevent duplicates per duka
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};

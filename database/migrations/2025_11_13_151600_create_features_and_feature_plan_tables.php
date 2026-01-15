<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. max_products, barcode_scanning
            $table->string('name'); // visible name e.g. "Barcode Scanning"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot table for plan-feature mapping
        Schema::create('feature_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->string('value')->nullable(); // TRUE/FALSE or numbers like 1000 products
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_plan');
        Schema::dropIfExists('features');
    }
};

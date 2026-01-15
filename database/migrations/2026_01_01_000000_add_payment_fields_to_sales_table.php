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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total_payments', 15, 2)->default(0)->after('profit_loss');
            $table->decimal('remaining_balance', 15, 2)->default(0)->after('total_payments');
            $table->string('payment_status')->default('pending')->after('remaining_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['total_payments', 'remaining_balance', 'payment_status']);
        });
    }
};

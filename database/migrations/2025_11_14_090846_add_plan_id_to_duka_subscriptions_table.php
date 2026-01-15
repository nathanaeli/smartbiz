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
        Schema::table('duka_subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->after('duka_id')->constrained('plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duka_subscriptions', function (Blueprint $table) {
            //
        });
    }
};

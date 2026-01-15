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
        Schema::create('staff_permissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('officer_id');
            $table->unsignedBigInteger('duka_id');
            $table->string('permission_name');
            $table->boolean('is_granted')->default(true);

            $table->timestamps();

            // Indexes and foreign keys
            $table->index(['tenant_id', 'officer_id', 'duka_id']);
            $table->index('permission_name');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('duka_id')->references('id')->on('dukas')->onDelete('cascade');

            // Unique constraint to prevent duplicate permissions
            $table->unique(['tenant_id', 'officer_id', 'duka_id', 'permission_name'], 'unique_staff_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_permissions');
    }
};

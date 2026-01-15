<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->unique(); // One record per tenant

            $table->string('company_name');
            $table->string('logo')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('currency', 20)->default('TZS');
            $table->string('timezone', 100)->default('Africa/Dar_es_Salaam');
            $table->string('website')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_accounts');
    }
};

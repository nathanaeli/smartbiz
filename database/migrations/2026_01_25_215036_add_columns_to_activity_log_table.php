<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('causer_id')->index();
            $table->unsignedBigInteger('duka_id')->nullable()->after('tenant_id')->index();
            $table->string('ip_address', 45)->nullable()->after('properties');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'duka_id', 'ip_address', 'user_agent']);
        });
    }
};

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
        Schema::table('available_permissions', function (Blueprint $table) {
           $table->foreignId('feature_id')
                  ->after('id') // Optional: places it after the ID column
                  ->nullable()
                  ->constrained('features')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_permissions', function (Blueprint $table) {
           $table->dropForeign(['feature_id']);
            $table->dropColumn('feature_id');
        });
    }
};

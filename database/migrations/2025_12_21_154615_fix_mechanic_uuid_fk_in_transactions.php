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
        // Try to drop old FK if exists (might already be dropped or not exist)
        try {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['mechanic_uuid']);
            });
        } catch (\Exception $e) {
            // FK might not exist, continue
        }

        // Add new FK to employments
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('mechanic_uuid')
                  ->references('id')
                  ->on('employments')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};

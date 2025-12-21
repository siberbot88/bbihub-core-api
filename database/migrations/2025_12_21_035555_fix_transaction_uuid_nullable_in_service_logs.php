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
        // First drop the foreign key constraint
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropForeign(['transaction_uuid']);
        });

        // Then modify the column to be nullable
        Schema::table('service_logs', function (Blueprint $table) {
            $table->uuid('transaction_uuid')->nullable()->change();
        });

        // Re-add foreign key as nullable
        Schema::table('service_logs', function (Blueprint $table) {
            $table->foreign('transaction_uuid')
                  ->references('id')
                  ->on('transactions')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            //
        });
    }
};

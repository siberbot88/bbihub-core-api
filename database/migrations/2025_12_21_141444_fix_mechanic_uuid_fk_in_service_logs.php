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
        // Drop old foreign key constraint (references users.id)
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropForeign(['mechanic_uuid']);
        });

        // Add new foreign key constraint (references employments.id)
        Schema::table('service_logs', function (Blueprint $table) {
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
        Schema::table('service_logs', function (Blueprint $table) {
            //
        });
    }
};

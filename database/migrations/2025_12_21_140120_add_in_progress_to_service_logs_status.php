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
        // Update ENUM to match services table status values
        // Using raw SQL since Laravel doesn't support ENUM modification well
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE service_logs 
            MODIFY COLUMN status ENUM(
                'pending',
                'accepted',
                'rejected',
                'in progress',
                'completed',
                'menunggu pembayaran',
                'lunas',
                'cancelled'
            ) DEFAULT 'pending'
        ");
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `services`
            MODIFY COLUMN `status` ENUM(
                'pending',
                'in progress',
                'completed',
                'menunggu pembayaran',
                'lunas'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // kalau mau rollback ke versi lama (3 status saja):
        DB::statement("
            ALTER TABLE `services`
            MODIFY COLUMN `status` ENUM(
                'pending',
                'in progress',
                'completed'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};

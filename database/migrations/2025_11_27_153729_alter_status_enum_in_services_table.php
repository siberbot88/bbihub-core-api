<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite-compatible: Skip ALTER TABLE for SQLite
        // MySQL: Update ENUM values
        if (DB::connection()->getDriverName() !== 'sqlite') {
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
        // SQLite uses string, no action needed
    }

    public function down(): void
    {
        // Rollback for MySQL only
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE `services`
                MODIFY COLUMN `status` ENUM(
                    'pending',
                    'in progress',
                    'completed'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }
};

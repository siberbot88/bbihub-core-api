<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1 — Drop foreign key pada service_uuid (apapun namanya)
        try {
            DB::statement("ALTER TABLE transaction_items DROP FOREIGN KEY fk_transaction_items_service");
        } catch (\Exception $e) {
            // FK tidak ada → lewati
        }

        // STEP 2 — Drop index jika masih ada
        try {
            DB::statement("ALTER TABLE transaction_items DROP INDEX fk_transaction_items_service");
        } catch (\Exception $e) {
            // index tidak ada → lewati
        }

        // STEP 3 — Drop kolom service_uuid (jika masih ada)
        if (Schema::hasColumn('transaction_items', 'service_uuid')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->dropColumn('service_uuid');
            });
        }

        // STEP 4 — Tambahkan FK transaction_uuid → transactions.id
        // cek apakah FK sudah ada
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'transaction_items'
            AND COLUMN_NAME = 'transaction_uuid'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (empty($fkExists)) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->foreign('transaction_uuid')
                    ->references('id')
                    ->on('transactions')
                    ->onDelete('cascade');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

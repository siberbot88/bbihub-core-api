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
        // Tambah kolom baru
        Schema::table('transaction_items', function (Blueprint $table) {
            if (! Schema::hasColumn('transaction_items', 'name')) {
                $table->string('name')->after('transaction_uuid');
            }

            if (! Schema::hasColumn('transaction_items', 'service_type')) {
                $table->enum('service_type', [
                    'servis ringan',
                    'servis sedang',
                    'servis berat',
                    'sparepart',
                    'biaya tambahan',
                    'lainnya',
                ])->after('name');
            }
        });

        // ===============================
        // HAPUS FK & KOLOM service_uuid
        // ===============================
        if (Schema::hasColumn('transaction_items', 'service_uuid')) {

            // Drop foreign key-nya (kalau masih ada)
            try {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->dropForeign(['service_uuid']);
                });
            } catch (\Throwable $e) {
                // Kalau FK sudah nggak ada, di-skip aja
            }

            // Drop kolomnya
            Schema::table('transaction_items', function (Blueprint $table) {
                if (Schema::hasColumn('transaction_items', 'service_uuid')) {
                    $table->dropColumn('service_uuid');
                }
            });
        }

        // =====================================
        // HAPUS FK & KOLOM service_type_uuid
        // =====================================
        if (Schema::hasColumn('transaction_items', 'service_type_uuid')) {

            // Drop foreign key-nya
            try {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->dropForeign(['service_type_uuid']);
                });
            } catch (\Throwable $e) {
                // Kalau FK sudah nggak ada, di-skip
            }

            // Drop kolomnya
            Schema::table('transaction_items', function (Blueprint $table) {
                if (Schema::hasColumn('transaction_items', 'service_type_uuid')) {
                    $table->dropColumn('service_type_uuid');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Balikin kolom lama
        Schema::table('transaction_items', function (Blueprint $table) {
            if (! Schema::hasColumn('transaction_items', 'service_uuid')) {
                $table->foreignUuid('service_uuid')
                    ->nullable()
                    ->constrained('services');
            }

            if (! Schema::hasColumn('transaction_items', 'service_type_uuid')) {
                $table->foreignUuid('service_type_uuid')
                    ->nullable()
                    ->constrained('service_types');
            }
        });

        // Hapus kolom baru yang ditambahkan
        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('transaction_items', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });
    }
};

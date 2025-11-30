<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // hanya tambahkan jika belum ada
            if (! Schema::hasColumn('transaction_items', 'service_uuid')) {
                // tambahkan kolom service_uuid nullable (aman untuk baris lama)
                $table->foreignUuid('service_uuid')->nullable()->after('id');

                // buat foreign key constraint mengarah ke services.id
                $table->foreign('service_uuid')
                    ->references('id')
                    ->on('services')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'service_uuid')) {
                // drop foreign key dulu lalu kolom
                $table->dropForeign(['service_uuid']);
                $table->dropColumn('service_uuid');
            }
        });
    }
};

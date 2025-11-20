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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('name')->after('transaction_uuid');
            $table->enum('service_type', ['servis ringan', 'servis sedang', 'servis berat','sparepart', 'biaya tambahan', 'lainnya'])->after('name');

            // Hapus kolom yang tidak dibutuhkan
            $table->dropForeign(['service_uuid']);
            $table->dropColumn('service_uuid');

            $table->dropForeign(['service_type_uuid']);
            $table->dropColumn('service_type_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignUuid('service_uuid')->constrained('services');
            $table->foreignUuid('service_type_uuid')->constrained('service_types');

            // Hapus kolom baru yang ditambahkan
            $table->dropColumn(['name', 'service_type']);
        });
    }
};

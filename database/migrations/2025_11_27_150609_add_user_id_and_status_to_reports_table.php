<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // JANGAN tambahin user_id lagi, karena sudah ada
            // $table->unsignedBigInteger('user_id')->nullable()->after('workshop_uuid');

            // Tambah kolom status kalau belum ada
            if (!Schema::hasColumn('reports', 'status')) {
                $table->enum('status', ['baru', 'diproses', 'diterima', 'selesai'])
                      ->default('baru')
                      ->after('report_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Jangan drop user_id, karena tadi kita juga tidak menambahkannya di up()
            if (Schema::hasColumn('reports', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

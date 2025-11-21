<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_status_to_workshops_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->enum('status', ['pending','active','suspended','rejected'])
                  ->default('pending')
                  ->index()
                  ->after('id'); // atur posisinya sesuai kebutuhan
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

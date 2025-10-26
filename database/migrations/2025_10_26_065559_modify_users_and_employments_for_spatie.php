<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Modifikasi ini bertujuan untuk:
     * 1. Menghapus kolom 'role' dari 'users' (karena akan ditangani Spatie).
     * 2. Menghapus semua kolom login dari 'employments' (karena login pindah ke 'users').
     * 3. Menambah 'user_uuid' di 'employments' sebagai foreign key ke 'users'.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom 'role' karena Spatie akan menggantikannya
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });

        // 2. Modifikasi tabel 'employments'
        Schema::table('employments', function (Blueprint $table) {

            // Tambahkan kolom user_uuid untuk relasi ke tabel 'users'
            // Dibuat nullable dulu agar tidak error jika tabel sudah ada isinya
            $table->foreignUuid('user_uuid')
                ->nullable()
                ->after('workshop_uuid')
                ->constrained('users') // Membuat foreign key ke users.id
                ->onDelete('cascade'); // Jika user dihapus, data employment ikut terhapus

            // Kolom-kolom ini akan dihapus karena datanya pindah ke tabel 'users'
            $columnsToDrop = [
                'name',
                'role',
                'email',
                'email_verified_at',
                'password',
                'remember_token',
                'photo'
            ];

            // Cek & hapus kolom satu per satu
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('employments', $column)) {
                    // Perlu drop index unik di 'email' dulu sebelum drop kolomnya
                    if ($column === 'email') {
                        // Nama index defaultnya adalah: nama_tabel_nama_kolom_unique
                        $table->dropUnique('employments_email_unique');
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     * (Untuk mengembalikan database jika terjadi kesalahan)
     */
    public function down(): void
    {
        // 1. Kembalikan tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'admin', 'mechanic'])->default('owner')->after('username');
        });

        // 2. Kembalikan tabel 'employments'
        Schema::table('employments', function (Blueprint $table) {
            // Hapus foreign key dan kolom user_uuid
            $table->dropForeign(['user_uuid']);
            $table->dropColumn('user_uuid');

            // Tambahkan kembali kolom-kolom yang tadi dihapus
            $table->string('name')->after('code');
            $table->enum('role', ['admin', 'mechanic'])->default('mechanic')->after('name');
            $table->string('email')->unique()->after('description');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->after('email_verified_at');
            $table->rememberToken()->after('password');
            $table->string('photo')->nullable()->after('remember_token');
        });
    }
};

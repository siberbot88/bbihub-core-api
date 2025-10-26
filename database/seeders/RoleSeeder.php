<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus cache role/permission Spatie agar tidak error
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat 3 role utama Anda
        // firstOrCreate() akan membuat jika belum ada, atau mengambil jika sudah ada.
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mechanic', 'guard_name' => 'web']);

        // Catatan: 'guard_name' => 'web' adalah default.
        // Jika Anda menggunakan guard 'api' untuk Sanctum, Anda bisa ganti ke 'api'
        // Tapi biasanya 'web' sudah cukup untuk Sanctum.
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // pastikan role super-admin ada
        Role::firstOrCreate([
            'name'       => 'super-admin',
            'guard_name' => 'web',
        ]);

        // data dasar user admin
        $data = [
            'name'              => 'Super Admin',
            'email'             => 'admin@bbihub.com',
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
        ];

        // isi kolom-kolom yang wajib bila ada di tabel users kamu
        if (Schema::hasColumn('users', 'username')) {
            $data['username'] = 'admin';         // ganti kalau sudah terpakai
        }
        if (Schema::hasColumn('users', 'status')) {
            $data['status'] = 'active';
        }

        // buat/ubah user berdasarkan email
        $user = User::updateOrCreate(['email' => $data['email']], $data);

        // role
        $user->syncRoles(['super-admin']);
    }
}

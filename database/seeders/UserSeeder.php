<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Pastikan ini ada
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

// Hapus 'use Has' yang tidak perlu

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Panggil RoleSeeder dulu jika belum
        // $this->call(RoleSeeder::class);

        // Langkah 1: Buat user-nya
        $owner = User::create([
            'id' => Str::uuid(),
            'name' => 'Mohammad Bayu Rizki',
            'email' => 'mohammadbayurizki22@gmail.com',
            'username' => 'Owner bengkel',
            'email_verified_at' => now(),
            // Ganti 'bcrypt()' dengan 'Hash::make()' agar konsisten
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        // Langkah 2: Gunakan Spatie untuk menetapkan role
        $owner->assignRole(Role::findByName('owner', 'web'));
    }
}

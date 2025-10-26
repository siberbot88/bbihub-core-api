<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'budi.hartono@gmail.com'], // Kunci unik untuk mengecek
            [
                'id' => Str::uuid(), // ID di-generate otomatis
                'name' => 'Budi Hartono',
                'username' => 'budihartono',
                'role' => 'owner', // Sesuai permintaan "rolenya wajib owner"
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Password default: "password123"
                'remember_token' => Str::random(10),
                'photo' => 'https://placehold.co/400x400/4F46E5/FFFFFF?text=BH',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}

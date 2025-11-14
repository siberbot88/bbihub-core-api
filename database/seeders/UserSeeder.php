<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// Kita tidak perlu 'Spatie\Permission\Models\Role' jika pakai string

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat user 'owner'
        $owner = User::create([
            'id' => Str::uuid(),
            'name' => 'Mohammad Bayu Rizki',
            'email' => 'mohammadbayurizki22@gmail.com',
            'username' => 'Owner bengkel',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $owner->guard_name = 'sanctum';

        $owner->assignRole('owner');


        // (Opsional) Jika Anda ingin membuat superadmin untuk 'web'
        // $superadmin = User::create([
        //     'id' => Str::uuid(),
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'username' => 'superadmin',
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('password'),
        // ]);

        // $superadmin->guard_name = 'web'; // Atur guard ke 'web'
        // $superadmin->assignRole('superadmin'); // Beri role 'superadmin' (guard 'web')
    }
}

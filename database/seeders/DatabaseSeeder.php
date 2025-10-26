<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Mohammad Bayu Rizki',
//            'username' => 'Owner bengkel',
//            'role' => 'owner',
//            'email' => 'mohammadbayurizki22@gmail.com',
//            'password' => bcrypt('password'),
//        ]);

        $this->call([
            CustomerSeeder::class,
            ServiceTypeSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $guards = ['web', 'sanctum'];
        $roles  = ['owner', 'admin', 'mechanic'];

        foreach ($guards as $guard) {
            foreach ($roles as $name) {
                Role::firstOrCreate([
                    'name'       => $name,
                    'guard_name' => $guard,
                ]);
            }
        }
    }
}

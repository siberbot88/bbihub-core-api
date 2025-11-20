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

        // --- Roles untuk Mobile/API (Guard: sanctum) ---
        $apiRoles = ['owner', 'admin', 'mechanic', 'user'];
        foreach ($apiRoles as $name) {
            Role::firstOrCreate([
                'name'       => $name,
                'guard_name' => 'sanctum', // Guard khusus API
            ]);
        }

        // --- Roles untuk Web Dashboard (Guard: web) ---
        Role::firstOrCreate([
            'name'       => 'superadmin',
            'guard_name' => 'web', // Guard khusus Web
        ]);
    }
}

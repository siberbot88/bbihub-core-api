<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds untuk development/testing.
     * Data ini match dengan SQL dump tanggal 4 Des 2025.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Seed Roles
            $this->seedRoles();

            // 2. Seed Users (Owner, Admin, Mechanic, Superadmin)
            $this->seedUsers();

            // 3. Seed Workshops
            $this->seedWorkshops();

            // 4. Seed Workshop Documents
            $this->seedWorkshopDocuments();

            // 5. Seed Employments (Staff)
            $this->seedEmployments();

            // 6. Seed Customers (10 sample customers)
            $this->seedCustomers();

            // 7. Seed Subscription Plans (Owner SaaS Plans)
            $this->seedSubscriptionPlans();

            // 8. Seed Memberships (Customer Loyalty)
            $this->seedMemberships();

            DB::commit();

            $this->command->info('✅ Development data seeded successfully!');
            $this->command->info('📋 Seeded:');
            $this->command->info('   - Roles & Users (Idempotent)');
            $this->command->info('   - Workshop & Staff');
            $this->command->info('   - Customers');
            $this->command->info('   - 2 Subscription Plans (Starter, BBI HUB Plus) -> table: subscription_plans');
            $this->command->info('   - 3 Membership Tiers (Bronze, Silver, Gold) -> table: memberships');
            $this->command->info('');
            $this->command->info('🔐 Login Credentials:');
            $this->command->info('   Owner: dian_owner / password');
            $this->command->info('   Admin: lina_admin_medan / password');
            $this->command->info('   Superadmin: superadmin / password');
            $this->command->info('');
            $this->command->info('💡 Sekarang Anda bisa coba checkout di mobile app!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function seedRoles()
    {
        $roles = [
            ['id' => 1, 'name' => 'owner', 'guard_name' => 'sanctum'],
            ['id' => 2, 'name' => 'admin', 'guard_name' => 'sanctum'],
            ['id' => 3, 'name' => 'mechanic', 'guard_name' => 'sanctum'],
            ['id' => 4, 'name' => 'user', 'guard_name' => 'sanctum'],
            ['id' => 5, 'name' => 'superadmin', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                array_merge($role, ['updated_at' => now()])
            );
        }
    }

    private function seedUsers()
    {
        $users = [
            [
                'id' => 'caa8be80-583f-4e02-ac39-2b1effdec082',
                'name' => 'Dian Sastro',
                'username' => 'dian_owner',
                'email' => 'dian.sastro@example.com',
                'password' => Hash::make('password'),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=DI',
                'role_id' => 1,
            ],
            [
                'id' => '0524617e-26c0-4839-a7dd-a120daebe024',
                'name' => 'Lina Marlina',
                'username' => 'lina_admin_medan',
                'email' => 'lina.marlina@medanjayamotor.com',
                'password' => Hash::make('password'),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=LI',
                'password_changed_at' => now(),
                'role_id' => 2,
            ],
            [
                'id' => '25d70510-c01b-4a30-b0b4-9b788f634a6d',
                'name' => 'Indodaya',
                'username' => 'indo',
                'email' => 'indo@gmail.com',
                'password' => Hash::make('password'),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=IN',
                'role_id' => 2,
            ],
            [
                'id' => '86ff6c25-8dea-425f-9430-187893a04f42',
                'name' => 'Togar Siregar',
                'username' => 'togar_mekanik',
                'email' => 'togar.siregar@medanjayamotor.com',
                'password' => Hash::make('password'),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=TO',
                'role_id' => 3,
            ],
            [
                'id' => '019ab41e-b66f-7308-ad23-8abcdaf13206',
                'name' => 'Superadmin',
                'username' => 'superadmin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('password'),
                'photo' => null,
                'role_id' => 5,
            ],
        ];

        foreach ($users as $userData) {
            $roleId = $userData['role_id'];
            unset($userData['role_id']);

            // Update or create user
            DB::table('users')->updateOrInsert(
                ['id' => $userData['id']],
                array_merge($userData, ['updated_at' => now()])
            );

            // Assign role (delete existing first to be safe, or just insert ignore)
            // For simplicity in seeder with fixed IDs, we can check existence
            $exists = DB::table('model_has_roles')
                ->where('model_id', $userData['id'])
                ->where('role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userData['id'],
                ]);
            }
        }
    }

    private function seedWorkshops()
    {
        DB::table('workshops')->updateOrInsert(
            ['id' => '019a9d4b-4695-73a7-a832-d7926deb73f3'],
            [
                'status' => 'active',
                'user_uuid' => 'caa8be80-583f-4e02-ac39-2b1effdec082',
                'code' => 'BKL-PHW9LGGM',
                'name' => 'Surabaya Auto Mandiri',
                'description' => 'Bengkel umum Surabaya Auto Mandiri melayani servis rutin, ganti oli, tune-up, dan perbaikan ringan untuk semua merek mobil. Buka setiap hari.',
                'address' => 'Jl. Raya Darmo Permai III No. 77',
                'phone' => '085712345678',
                'email' => 'info@surabayaauto.com',
                'photo' => 'https://placehold.co/600x400/D72B1C/FFFFFF?text=Surabaya+Auto+Mandiri',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'country' => 'Indonesia',
                'postal_code' => '60226',
                'latitude' => -7.29050000,
                'longitude' => 112.68650000,
                'maps_url' => 'https://maps.google.com/?q=-7.29050000,112.68650000',
                'opening_time' => '08:30:00',
                'closing_time' => '17:30:00',
                'operational_days' => 'Senin - Jumat',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );
    }

    private function seedWorkshopDocuments()
    {
        // Check by workshop_uuid instead of random ID since ID is random uuid in original
        // But original used Str::uuid(), so let's check by workshop_uuid uniqueness
        DB::table('workshop_documents')->updateOrInsert(
            ['workshop_uuid' => '019a9d4b-4695-73a7-a832-d7926deb73f3'],
            [
                'id' => Str::uuid(), // Note: this will only be set on insert, on update it's ignored basically
                'nib' => '1204000654321',
                'npwp' => '08.123.456.7-618.000',
                'updated_at' => now(),
            ]
        );
    }

    private function seedEmployments()
    {
        $employments = [
            [
                'id' => '84061424-5fde-41e7-94d0-cbb6af663ae0',
                'workshop_uuid' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'user_uuid' => '0524617e-26c0-4839-a7dd-a120daebe024',
                'code' => 'ST00001',
                'specialist' => 'Kasir & Administrasi',
                'jobdesk' => 'Melayani pembayaran, input data servis, dan booking customer via telepon.',
                'status' => 'active',
            ],
            [
                'id' => 'b54b461b-7255-4a59-a24e-91f982fca9f8',
                'workshop_uuid' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'user_uuid' => '86ff6c25-8dea-425f-9430-187893a04f42',
                'code' => 'ST00002',
                'specialist' => 'Diagnostik Mercedes & BMW',
                'jobdesk' => 'Kepala mekanik, menangani kasus sulit, dan supervisor mekanik junior.',
                'status' => 'active',
            ],
            [
                'id' => '720c3acd-c058-4808-beaa-affce5cdd1b1',
                'workshop_uuid' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'user_uuid' => '25d70510-c01b-4a30-b0b4-9b788f634a6d',
                'code' => 'ST00003',
                'specialist' => 'Kasir',
                'jobdesk' => 'Kasir bengkel',
                'status' => 'active',
            ],
        ];

        foreach ($employments as $employment) {
            DB::table('employments')->updateOrInsert(
                ['id' => $employment['id']],
                array_merge($employment, ['updated_at' => now()])
            );
        }
    }

    private function seedCustomers()
    {
        $customers = [
            ['055d26cb-019a-4de4-90f9-c631fde6dde0', 'CUS20251119067', 'Zulham Salim', '081563238052', 'Jl. Hayam Wuruk No. 20, Bogor', 'zulham@gmail.com'],
            ['38f4385e-65db-437f-8812-6a17af3737fa', 'CUS20251119072', 'Rizky Purnomo', '081155855600', 'Jl. Diponegoro No. 81, Jakarta', ''],
            ['1c1412e0-7d0b-4011-ad82-240e99f3887f', 'CUS20251119095', 'Dewi Wahyudi', '089413318262', 'Jl. Pemuda No. 36, Bekasi', ''],
            ['1c092f0d-2de6-46a1-ad19-3b77e6cb9b82', 'CUS20251119082', 'Rini Maulana', '084890933294', 'Jl. Gajah Mada No. 44, Bekasi', ''],
            ['702f9679-9cc9-4904-932f-aed42d8a671b', 'CUS20251119050', 'Kiki Gunawan', '084726781721', 'Jl. Diponegoro No. 78, Bogor', ''],
            ['08c9356a-bc30-4fea-941b-2437961f87df', 'CUS20251119015', 'Deden Maulana', '085974269026', 'Jl. Mawar No. 21, Bekasi', ''],
            ['b8056b57-21ab-4bdf-8407-1b1383fd0e2e', 'CUS20251119001', 'Siti Kusuma', '089531684683', 'Jl. Merdeka No. 96, Makassar', ''],
            ['715d6bb4-1155-434c-8360-84c0c2631da7', 'CUS20251119003', 'Utami Gunawan', '088817357957', 'Jl. Melati No. 23, Surabaya', ''],
            ['b3af3f99-5602-49cb-8b7d-bb392feec648', 'CUS20251119002', 'Teguh Hidayat', '088727579629', 'Jl. Diponegoro No. 19, Tangerang', ''],
            ['be515261-8705-48a5-89c4-8dd07dd3b83b', 'CUS20251119005', 'Zulham Hidayat', '089720145830', 'Jl. Anggrek No. 47, Tangerang', ''],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->updateOrInsert(
                ['id' => $customer[0]],
                [
                    'code' => $customer[1],
                    'name' => $customer[2],
                    'phone' => $customer[3],
                    'address' => $customer[4],
                    'email' => $customer[5],
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedSubscriptionPlans()
    {
        $plans = [
            [
                'code' => 'starter',
                'name' => 'Starter',
                'description' => 'Mulai kelola bengkel Anda dengan mudah.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => json_encode([
                    'Manajemen Bengkel Dasar',
                    '1 Admin',
                    'Maksimal 5 Mekanik',
                ]),
                'is_active' => true,
                'is_recommended' => false,
            ],
            [
                'code' => 'bbi_hub_plus',
                'name' => 'BBI HUB Plus',
                'description' => 'Kembangkan bisnis lebih cepat.',
                'price_monthly' => 120000,
                'price_yearly' => 1440000,
                'features' => json_encode([
                    'Semua fitur Starter',
                    'Dashboard Analitik Canggih',
                    'Unlimited Mekanik & Admin',
                    'Laporan Keuangan Detail',
                ]),
                'is_active' => true,
                'is_recommended' => true,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['code' => $plan['code']],
                array_merge($plan, [
                    'id' => Str::uuid(), // Only set on insert
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function seedMemberships()
    {
        $memberships = [
            [
                'workshop_id' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'name' => 'Bronze Member',
                'description' => 'Paket membership dasar dengan benefit eksklusif',
                'discount_percentage' => 5.00,
                'points_multiplier' => 1.00,
                'price' => 50000.00,
                'duration_months' => 1,
                'benefits' => json_encode([
                    'Diskon 5% untuk semua layanan servis',
                    'Points 1x dari nilai transaksi',
                    'Prioritas booking servis',
                    'Notifikasi reminder servis berkala',
                ]),
            ],
            [
                'workshop_id' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'name' => 'Silver Member',
                'description' => 'Paket membership populer dengan lebih banyak benefit',
                'discount_percentage' => 10.00,
                'points_multiplier' => 1.50,
                'price' => 150000.00,
                'duration_months' => 3,
                'benefits' => json_encode([
                    'Diskon 10% untuk semua layanan servis',
                    'Points 1.5x dari nilai transaksi',
                    'Gratis cuci mobil 2x per bulan',
                    'Prioritas booking + konsultasi gratis',
                    'Skip antrian untuk booking emergency',
                ]),
            ],
            [
                'workshop_id' => '019a9d4b-4695-73a7-a832-d7926deb73f3',
                'name' => 'Gold Member',
                'description' => 'Paket membership premium dengan benefit maksimal',
                'discount_percentage' => 15.00,
                'points_multiplier' => 2.00,
                'price' => 500000.00,
                'duration_months' => 12,
                'benefits' => json_encode([
                    'Diskon 15% untuk semua layanan servis',
                    'Points 2x dari nilai transaksi',
                    'Gratis cuci mobil unlimited',
                    'Gratis check-up rutin bulanan',
                    'Prioritas tertinggi + teknisi terbaik',
                    'Diskon tambahan 5% untuk sparepart',
                    'Layanan antar-jemput kendaraan',
                ]),
            ],
        ];

        foreach ($memberships as $membership) {
            DB::table('memberships')->updateOrInsert(
                [
                    'workshop_id' => $membership['workshop_id'],
                    'name' => $membership['name']
                ],
                array_merge($membership, [
                    'id' => Str::uuid(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

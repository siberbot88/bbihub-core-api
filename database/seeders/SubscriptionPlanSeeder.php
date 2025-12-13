<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            $exists = DB::table('subscription_plans')->where('code', $plan['code'])->exists();
            if (!$exists) {
                $plan['id'] = Str::uuid();
                $plan['created_at'] = now();
                $plan['updated_at'] = now();
                DB::table('subscription_plans')->insert($plan);
            } else {
                $plan['updated_at'] = now();
                DB::table('subscription_plans')->where('code', $plan['code'])->update($plan);
            }
        }
    }
}

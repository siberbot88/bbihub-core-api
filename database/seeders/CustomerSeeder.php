<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $firstNames = [
            'Budi', 'Agus', 'Eko', 'Dewi', 'Siti', 'Rini', 'Andi', 'Dodi', 'Fajar', 'Gita',
            'Hendra', 'Indah', 'Joko', 'Kiki', 'Lintang', 'Mega', 'Nina', 'Oscar', 'Putri', 'Rizky',
            'Sari', 'Teguh', 'Utami', 'Vino', 'Wati', 'Yoga', 'Zulham', 'Asep', 'Cecep', 'Deden'
        ];

        $lastNames = [
            'Santoso', 'Wijaya', 'Hartono', 'Lestari', 'Gunawan', 'Setiawan', 'Purnomo', 'Wati', 'Yulianto',
            'Nugroho', 'Susanto', 'Salim', 'Kusuma', 'Pratama', 'Hidayat', 'Maulana', 'Saputra', 'Wahyudi'
        ];

        $streetNames = [
            'Jl. Melati', 'Jl. Mawar', 'Jl. Kenanga', 'Jl. Anggrek', 'Jl. Sudirman', 'Jl. Thamrin',
            'Jl. Diponegoro', 'Jl. Gajah Mada', 'Jl. Hayam Wuruk', 'Jl. Pahlawan', 'Jl. Merdeka', 'Jl. Pemuda'
        ];

        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Yogyakarta',
            'Denpasar', 'Makassar', 'Palembang', 'Bogor', 'Tangerang', 'Bekasi'
        ];

        $dataToInsert = [];
        $now = now();
        $datePart = $now->format('Ymd');

        // Generate 100 customer
        for ($i = 1; $i <= 100; $i++) {

            $code = 'CUS' . $datePart . str_pad($i, 3, '0', STR_PAD_LEFT);

            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

            $phone = '08' . rand(10, 99) . rand(1000, 9999) . rand(1000, 9999);

            $address = $streetNames[array_rand($streetNames)] . ' No. ' . rand(1, 100) . ', ' . $cities[array_rand($cities)];

            $dataToInsert[] = [
                'id' => Str::uuid()->toString(),
                'code' => $code,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }


        DB::table('customers')->upsert(
            $dataToInsert,
            ['code'],
            ['name', 'phone', 'address', 'updated_at']
        );
    }
}

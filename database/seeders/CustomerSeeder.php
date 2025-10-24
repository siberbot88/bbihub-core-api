<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $customers = [
            [
                'name' => 'Ahmad Setiawan',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No.10, Bandung',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'phone' => '081298765432',
                'address' => 'Jl. Sudirman No.25, Jakarta Selatan',
            ],
            [
                'name' => 'Budi Santoso',
                'phone' => '085712345678',
                'address' => 'Jl. Diponegoro No.8, Yogyakarta',
            ],
            [
                'name' => 'Rina Kurniawati',
                'phone' => '081355667788',
                'address' => 'Jl. Ahmad Yani No.17, Surabaya',
            ],
            [
                'name' => 'Dewi Lestari',
                'phone' => '081267899000',
                'address' => 'Jl. Veteran No.14, Medan',
            ],
            [
                'name' => 'Hendra Gunawan',
                'phone' => '085822334455',
                'address' => 'Jl. Malioboro No.33, Yogyakarta',
            ],
            [
                'name' => 'Putri Ananda',
                'phone' => '081277889900',
                'address' => 'Jl. Imam Bonjol No.12, Semarang',
            ],
            [
                'name' => 'Agus Saputra',
                'phone' => '081388899900',
                'address' => 'Jl. Dipatiukur No.4, Bandung',
            ],
            [
                'name' => 'Kartika Sari',
                'phone' => '085766554433',
                'address' => 'Jl. Soekarno Hatta No.55, Malang',
            ],
            [
                'name' => 'Rudi Hartono',
                'phone' => '081322334455',
                'address' => 'Jl. Asia Afrika No.7, Bandung',
            ],
            [
                'name' => 'Lina Marlina',
                'phone' => '081245678912',
                'address' => 'Jl. Teuku Umar No.22, Denpasar',
            ],
            [
                'name' => 'Eko Prasetyo',
                'phone' => '085811122233',
                'address' => 'Jl. Gatot Subroto No.19, Jakarta Timur',
            ],
            [
                'name' => 'Nur Aini',
                'phone' => '081234998877',
                'address' => 'Jl. Kalimantan No.11, Palembang',
            ],
            [
                'name' => 'Andi Wijaya',
                'phone' => '081266778899',
                'address' => 'Jl. Hasanuddin No.2, Makassar',
            ],
            [
                'name' => 'Ratna Sari',
                'phone' => '081398877665',
                'address' => 'Jl. Gajah Mada No.30, Pontianak',
            ],
            [
                'name' => 'Tono Rahman',
                'phone' => '081377755544',
                'address' => 'Jl. Pahlawan No.9, Bogor',
            ],
            [
                'name' => 'Yuni Astuti',
                'phone' => '085677889900',
                'address' => 'Jl. Diponegoro No.18, Solo',
            ],
            [
                'name' => 'Arif Hidayat',
                'phone' => '081233344455',
                'address' => 'Jl. Slamet Riyadi No.20, Solo',
            ],
            [
                'name' => 'Nita Anggraini',
                'phone' => '081288899900',
                'address' => 'Jl. Sutomo No.8, Medan',
            ],
            [
                'name' => 'Fajar Maulana',
                'phone' => '081399988877',
                'address' => 'Jl. Sisingamangaraja No.5, Pekanbaru',
            ],
        ];

        $no = 1;
        foreach ($customers as $data) {
            $code = 'CS' . str_pad($no, 3, '0', STR_PAD_LEFT);
            DB::table('customers')->insert([
                'id' => Str::uuid(),
                'code' => $code,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $no++;
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            ['id'=>(string) Str::uuid(),'code'=> 'TP001', 'name'=> 'Service Ringan', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>(string) Str::uuid(),'code'=> 'TP002', 'name'=> 'Service Sedang', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>(string) Str::uuid(),'code'=> 'TP003', 'name'=> 'Service Berat', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>(string) Str::uuid(),'code'=> 'TP004', 'name'=> 'Service Lengkap', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('service_types')
            ->insert($serviceTypes);
    }
}

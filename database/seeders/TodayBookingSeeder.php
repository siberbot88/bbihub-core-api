<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Workshop;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TodayBookingSeeder extends Seeder
{
    public function run(): void
    {
        $workshopUuid = '019a9d4b-4695-73a7-a832-d7926deb73f3';
        $workshop = Workshop::find($workshopUuid);

        if (!$workshop) {
            $this->command->error('Workshop not found.');
            return;
        }

        $customer = Customer::first();
        if (!$customer) {
            $this->command->error('No customers found.');
            return;
        }
        
        $vehicle = Vehicle::where('customer_uuid', $customer->id)->first();
        if (!$vehicle) {
            $vehicle = Vehicle::create([
                'id' => Str::uuid(),
                'customer_uuid' => $customer->id,
                'plate_number' => 'B 9999 TEST',
                'name' => 'Honda CR-V',
                'brand' => 'Honda',
                'code' => 'V-TEST-02',
                'year' => 2022,
            ]);
        }
        
        // Create Booking Services for TODAY
        $services = [
            [
                'name' => 'HARI INI: Ganti Oli Mesin',
                'scheduled_date' => now()->setHour(10)->setMinute(0),
                'status' => 'pending',
                'acceptance_status' => 'pending',
            ],
            [
                'name' => 'HARI INI: Service AC',
                'scheduled_date' => now()->setHour(14)->setMinute(30),
                'status' => 'pending',
                'acceptance_status' => 'pending',
            ],
            [
                'name' => 'HARI INI: Tune Up',
                'scheduled_date' => now()->setHour(16)->setMinute(0),
                'status' => 'pending',
                'acceptance_status' => 'accepted', // One accepted
            ],
        ];

        foreach ($services as $s) {
            try {
                Service::create([
                    'id' => Str::uuid(),
                    'code' => 'TODAY-'.Str::upper(Str::random(5)),
                    'workshop_uuid' => $workshop->id,
                    'customer_uuid' => $customer->id,
                    'vehicle_uuid' => $vehicle->id,
                    'name' => $s['name'],
                    'category_service' => 'Service Rutin',
                    'description' => 'Booking untuk hari ini (testing)',
                    'type' => 'booking',
                    'scheduled_date' => $s['scheduled_date'],
                    'estimated_time' => $s['scheduled_date']->copy()->addHours(2),
                    'status' => $s['status'],
                    'acceptance_status' => $s['acceptance_status'],
                    'reason' => '',
                ]);
            } catch (\Exception $e) {
                $this->command->error("Failed: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Created 3 Booking Services for TODAY (' . now()->format('Y-m-d') . ')');
    }
}

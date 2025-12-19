<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$workshopUuid = '019a9d4b-4695-73a7-a832-d7926deb73f3';
$customerUuid = '055d26cb-019a-4de4-90f9-c631fde6dde0';
$vehicle = DB::table('vehicles')->where('customer_uuid', $customerUuid)->first();

if (!$vehicle) {
    echo "No vehicle found\n";
    exit(1);
}

$today = now()->format('Y-m-d');
$now = now();

DB::table('services')->insert([
    [
        'id' => Str::uuid(),
        'code' => 'BK-' . strtoupper(Str::random(5)),
        'workshop_uuid' => $workshopUuid,
        'customer_uuid' => $customerUuid,
        'vehicle_uuid' => $vehicle->id,
        'name' => 'Booking: Ganti Oli Hari Ini',
        'description' => 'Test booking',
        'category_service' => 'Service Rutin',
        'type' => 'booking',
        'price' => 0,
        'scheduled_date' => $today . ' 09:00:00',
        'estimated_time' => $today . ' 11:00:00',
        'status' => 'pending',
        'acceptance_status' => 'pending',
        'reason' => '',
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'id' => Str::uuid(),
        'code' => 'BK-' . strtoupper(Str::random(5)),
        'workshop_uuid' => $workshopUuid,
        'customer_uuid' => $customerUuid,
        'vehicle_uuid' => $vehicle->id,
        'name' => 'Booking: Service AC Hari Ini',
        'description' => 'Test booking',
        'category_service' => 'Service Rutin',
        'type' => 'booking',
        'price' => 0,
        'scheduled_date' => $today . ' 13:00:00',
        'estimated_time' => $today . ' 15:00:00',
        'status' => 'pending',
        'acceptance_status' => 'pending',
        'reason' => '',
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'id' => Str::uuid(),
        'code' => 'BK-' . strtoupper(Str::random(5)),
        'workshop_uuid' => $workshopUuid,
        'customer_uuid' => $customerUuid,
        'vehicle_uuid' => $vehicle->id,
        'name' => 'Booking: Tune Up Hari Ini (Terima)',
        'description' => 'Test booking - already accepted',
        'category_service' => 'Service Rutin',
        'type' => 'booking',
        'price' => 0,
        'scheduled_date' => $today . ' 15:00:00',
        'estimated_time' => $today . ' 17:00:00',
        'status' => 'pending',
        'acceptance_status' => 'accepted',
        'reason' => '',
        'created_at' => $now,
        'updated_at' => $now,
    ],
]);

echo "✅ Successfully inserted 3 booking services for today ($today)\n";

// Verify
$count = DB::table('services')
    ->where('type', 'booking')
    ->whereDate('scheduled_date', $today)
    ->count();
    
echo "✓ Total booking services for today: $count\n";

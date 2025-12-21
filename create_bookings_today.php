<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Support\Str;

$workshopUuid = '019a9d4b-4695-73a7-a832-d7926deb73f3';
$customerUuid = '055d26cb-019a-4de4-90f9-c631fde6dde0';
$vehicle = Vehicle::where('customer_uuid', $customerUuid)->first();

if (!$vehicle) {
    echo "No vehicle found for customer\n";
    exit(1);
}

$bookings = [
    ['name' => 'Booking: Ganti Oli Hari Ini', 'hour' => 9, 'acceptance' => 'pending'],
    ['name' => 'Booking: Service AC Hari Ini', 'hour' => 13, 'acceptance' => 'pending'],
    ['name' => 'Booking: Tune Up Hari Ini', 'hour' => 15, 'acceptance' => 'accepted'],
];

foreach ($bookings as $b) {
    $service = Service::create([
        'id' => Str::uuid(),
        'code' => 'BK-' . strtoupper(Str::random(5)),
        'workshop_uuid' => $workshopUuid,
        'customer_uuid' => $customerUuid,
        'vehicle_uuid' => $vehicle->id,
        'name' => $b['name'],
        'category_service' => 'Service Rutin',
        'description' => 'Booking dibuat via script untuk testing',
        'type' => 'booking',
        'scheduled_date' => now()->setHour($b['hour'])->setMinute(0)->setSecond(0),
        'estimated_time' => now()->setHour($b['hour'])->addHours(2)->setMinute(0)->setSecond(0),
        'status' => 'pending',
        'acceptance_status' => $b['acceptance'],
        'reason' => '',
    ]);
    
    echo "✓ Created: {$service->name} (ID: {$service->id})\n";
}

echo "\n✅ Successfully created " . count($bookings) . " booking services for TODAY\n";

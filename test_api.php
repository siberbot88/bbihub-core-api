<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get tomorrow's date (when our seeded bookings are)
$tomorrow = now()->addDay()->format('Y-m-d');

echo "=== Testing Services API ===\n";
echo "Date: $tomorrow\n\n";

// Get booking services for tomorrow
$services = \App\Models\Service::where('type', 'booking')
    ->whereDate('scheduled_date', $tomorrow)
    ->with(['workshop', 'customer', 'vehicle', 'mechanic.user'])
    ->get();

echo "Found " . $services->count() . " booking services for $tomorrow\n\n";

if ($services->isEmpty()) {
    echo "❌ No booking services found!\n";
    echo "Checking all services in database:\n";
    $all = \App\Models\Service::select('scheduled_date', 'type', 'name')->get();
    foreach ($all as $s) {
        echo "  - {$s->name} | Type: {$s->type} | Date: {$s->scheduled_date}\n";
    }
} else {
    echo "✅ Services found. Simulating API response:\n\n";
    
    foreach ($services as $service) {
        $resource = new \App\Http\Resources\ServiceResource($service);
        $fakeRequest = \Illuminate\Http\Request::create('/test');
        $data = $resource->resolve($fakeRequest);
        
        echo "Service: {$service->name}\n";
        echo "  - ID: {$data['id']}\n";
        echo "  - Type: " . ($data['type'] ?? 'NULL/MISSING') . "\n";
        echo "  - Scheduled: {$data['scheduled_date']}\n";
        echo "  - Status: {$data['status']}\n";
        echo "  - Acceptance: {$data['acceptance_status']}\n";
        echo "\n";
    }
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DETAILED CHECK: Services for 2025-12-19 ===\n\n";

$services = \App\Models\Service::whereDate('scheduled_date', '2025-12-19')->get();

echo "Total services today: {$services->count()}\n\n";

foreach ($services as $s) {
    echo "ID: {$s->id}\n";
    echo "Name: {$s->name}\n";
    echo "Type: " . ($s->type ?? 'NULL') . "\n";
    echo "Scheduled: {$s->scheduled_date}\n";
    echo "Acceptance: {$s->acceptance_status}\n";
    echo "---\n";
}

$bookingToday = $services->where('type', 'booking');
echo "\n✅ BOOKING services today: {$bookingToday->count()}\n";
echo "❌ NON-BOOKING services today: " . ($services->count() - $bookingToday->count()) . "\n";

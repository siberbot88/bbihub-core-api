<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$today = now()->format('Y-m-d');

echo "=== VERIFICATION: Booking Services for TODAY ===\n";
echo "Date: $today\n\n";

$services = \App\Models\Service::where('type', 'booking')
    ->whereDate('scheduled_date', $today)
    ->get();

echo "Found: {$services->count()} booking services\n\n";

foreach ($services as $s) {
    echo "✓ {$s->name}\n";
    echo "  Type: {$s->type}\n";
    echo "  Scheduled: {$s->scheduled_date}\n";
    echo "  Status: {$s->status}\n";
    echo "  Acceptance: {$s->acceptance_status}\n";
    echo "\n";
}

echo "\n=== Summary ===\n";
echo "Total Booking: {$services->count()}\n";
echo "Pending: " . $services->where('acceptance_status', 'pending')->count() . "\n";
echo "Accepted: " . $services->where('acceptance_status', 'accepted')->count() . "\n";

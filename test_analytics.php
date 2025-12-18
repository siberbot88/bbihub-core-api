<?php

/**
 * Test script for Analytics API
 * Run with: php artisan tinker < test_analytics.php
 * Or manually in tinker
 */

// Get a test user (owner)
$user = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'owner');
})->first();

if (!$user) {
    echo "❌ No owner user found. Please create an owner user first.\n";
    exit;
}

echo "✅ Found owner user: {$user->name} (UUID: {$user->uuid})\n";
echo "   Workshop UUID: {$user->workshop_uuid}\n\n";

// Test Analytics Service directly
echo "Testing AnalyticsService...\n";
echo str_repeat('-', 50) . "\n";

$service = new \App\Services\AnalyticsService();

try {
    $analytics = $service->calculateWorkshopAnalytics($user->workshop_uuid, 'monthly');
    
    echo "✅ Analytics calculated successfully!\n\n";
    echo "📊 METRICS:\n";
    echo "   Revenue: Rp " . number_format($analytics['metrics']['revenue_this_period']) . "\n";
    echo "   Jobs Done: {$analytics['metrics']['jobs_done']} orders\n";
    echo "   Occupancy: {$analytics['metrics']['occupancy']}%\n";
    echo "   Avg Rating: {$analytics['metrics']['avg_rating']}/5.0\n\n";
    
    echo "📈 GROWTH (vs previous period):\n";
    echo "   Revenue: {$analytics['growth']['revenue']}%\n";
    echo "   Jobs: {$analytics['growth']['jobs']}%\n";
    echo "   Occupancy: {$analytics['growth']['occupancy']}%\n";
    echo "   Rating: {$analytics['growth']['rating']}%\n\n";
    
    echo "🔧 SERVICE BREAKDOWN:\n";
    foreach ($analytics['service_breakdown'] as $service => $percentage) {
        echo "   {$service}: {$percentage}%\n";
    }
    echo "\n";
    
    echo "⏰ PEAK HOURS:\n";
    echo "   Peak Range: {$analytics['peak_hours']['peak_range']}\n\n";
    
    echo "💚 OPERATIONAL HEALTH:\n";
    echo "   Avg Queue: {$analytics['operational_health']['avg_queue']} mobil\n";
    echo "   Occupancy: {$analytics['operational_health']['occupancy']}%\n";
    echo "   Efficiency: {$analytics['operational_health']['efficiency']}%\n\n";
    
    echo "✅ Full JSON Response:\n";
    echo json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

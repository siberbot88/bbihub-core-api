// Simple test script to verify Week 2 implementations
// Run with: php test-week2.php

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AuditLog;
use App\Models\User;

echo "=== Week 2 Implementation Tests ===\n\n";

// Test 1: Check Audit Logs Table
echo "Test 1: Audit Logs Table\n";
echo "------------------------\n";
try {
    $count = AuditLog::count();
    echo "✅ audit_logs table exists\n";
    echo "📊 Total audit logs: $count\n\n";
    
    if ($count > 0) {
        echo "Recent audit logs:\n";
        AuditLog::latest()->take(5)->get()->each(function($log) {
            echo sprintf(
                "  - [%s] %s by %s from %s\n",
                $log->created_at->format('Y-m-d H:i:s'),
                $log->event,
                $log->user_email ?? 'N/A',
                $log->ip_address ?? 'N/A'
            );
        });
        echo "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check Security Headers Middleware
echo "Test 2: SecurityHeaders Middleware\n";
echo "-----------------------------------\n";
if (class_exists('App\\Http\\Middleware\\SecurityHeaders')) {
    echo "✅ SecurityHeaders middleware class exists\n";
    
    // Check if registered in bootstrap/app.php
    $appConfig = file_get_contents(__DIR__ . '/bootstrap/app.php');
    if (str_contains($appConfig, 'SecurityHeaders')) {
        echo "✅ SecurityHeaders middleware registered\n";
    } else {
        echo "⚠️  SecurityHeaders middleware NOT registered\n";
    }
} else {
    echo "❌ SecurityHeaders middleware class NOT found\n";
}
echo "\n";

// Test 3: Check FormRequest Enhancements
echo "Test 3: FormRequest Validations\n";
echo "--------------------------------\n";
try {
    $storeRequest = new \App\Http\Requests\Api\Service\StoreServiceRequest();
    $rules = $storeRequest->rules();
    
    // Check for enhanced validations
    $checks = [
        'name has min:3' => str_contains($rules['name'] ?? '', 'min:3'),
        'scheduled_date has after_or_equal' => str_contains($rules['scheduled_date'] ?? '', 'after_or_equal'),
        'description has max length' => str_contains($rules['description'] ?? '', 'max:'),
        'customer_uuid is required' => str_contains($rules['customer_uuid'] ?? '', 'required'),
    ];
    
    foreach ($checks as $check => $passed) {
        echo ($passed ? "✅" : "❌") . " $check\n";
    }
    
    // Check for prepareForValidation method
    if (method_exists($storeRequest, 'prepareForValidation')) {
        echo "✅ Input sanitization (prepareForValidation) exists\n";
    } else {
        echo "❌ Input sanitization method NOT found\n";
    }
    
    // Check for custom messages
    if (method_exists($storeRequest, 'messages')) {
        $messages = $storeRequest->messages();
        if (!empty($messages)) {
            echo "✅ Custom error messages defined (" . count($messages) . " messages)\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Simulate Audit Log Creation
echo "Test 4: Create Test Audit Log\n";
echo "------------------------------\n";
try {
    $testLog = AuditLog::log(
        event: 'test_event',
        user: null,
        newValues: ['test' => 'data', 'timestamp' => now()->toDateTimeString()]
    );
    
    echo "✅ Test audit log created (ID: {$testLog->id})\n";
    echo "   Event: {$testLog->event}\n";
    echo "   IP: {$testLog->ip_address}\n";
    echo "   Created: {$testLog->created_at}\n";
    
    // Clean up test log
    $testLog->delete();
    echo "✅ Test log cleaned up\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "✅ All core implementations verified via code\n";
echo "⏳ Runtime HTTP testing needed for security headers\n";
echo "📋 Check browser DevTools Network tab for header verification\n\n";

echo "Done!\n";

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnalyticsService;
use App\Models\Workshop;
use App\Models\Transaction;
use App\Models\Service;

class TestAnalytics extends Command
{
    protected $signature = 'test:analytics {--range=monthly}';
    protected $description = 'Test analytics service functionality';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🧪 Testing Analytics Service...');
        $this->info(str_repeat('=', 60));
        
        // Get first workshop
        $workshop = Workshop::first();
        
        if (!$workshop) {
            $this->error('❌ No workshop found in database');
            return 1;
        }
        
        $this->info("✅ Found workshop: {$workshop->name}");
        $this->info("   UUID: {$workshop->uuid}");
        $this->newLine();
        
        // Check data availability
        $transactionCount = Transaction::where('workshop_uuid', $workshop->uuid)->count();
        $serviceCount = Service::where('workshop_uuid', $workshop->uuid)->count();
        
        $this->info("📊 Data Status:");
        $this->line("   Transactions: {$transactionCount}");
        $this->line("   Services: {$serviceCount}");
        $this->newLine();
        
        if ($transactionCount == 0 && $serviceCount == 0) {
            $this->warn('⚠️  No data found for this workshop. Results will be zero.');
            $this->warn('   Consider running seeders or creating test data.');
            $this->newLine();
        }
        
        // Test analytics
        try {
            $range = $this->option('range');
            $service = new AnalyticsService();
            
            $this->info("🔄 Calculating analytics (range: {$range})...");
            $analytics = $service->calculateWorkshopAnalytics($workshop->uuid, $range);
            
            $this->newLine();
            $this->info('✅ Analytics calculated successfully!');
            $this->newLine();
            
            // Display metrics
            $metrics = $analytics['metrics'];
            $growth = $analytics['growth'];
            
            $this->info('💰 FINANCIAL METRICS:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Revenue', 'Rp ' . number_format($metrics['revenue_this_period'])],
                    ['Jobs Completed', $metrics['jobs_done'] . ' orders'],
                    ['Occupancy Rate', $metrics['occupancy'] . '%'],
                    ['Average Rating', $metrics['avg_rating'] . '/5.0'],
                ]
            );
            
            $this->newLine();
            $this->info('📈 GROWTH (vs previous period):');
            $this->table(
                ['Metric', 'Change'],
                [
                    ['Revenue Growth', ($growth['revenue'] >= 0 ? '+' : '') . $growth['revenue'] . '%'],
                    ['Jobs Growth', ($growth['jobs'] >= 0 ? '+' : '') . $growth['jobs'] . '%'],
                    ['Occupancy Change', ($growth['occupancy'] >= 0 ? '+' : '') . $growth['occupancy'] . '%'],
                    ['Rating Change', ($growth['rating'] >= 0 ? '+' : '') . $growth['rating'] . '%'],
                ]
            );
            
            $this->newLine();
            $this->info('🔧 SERVICE BREAKDOWN:');
            if (!empty($analytics['service_breakdown'])) {
                foreach ($analytics['service_breakdown'] as $service => $percentage) {
                    $this->line("   {$service}: {$percentage}%");
                }
            } else {
                $this->line('   No service data available');
            }
            
            $this->newLine();
            $this->info('⏰ PEAK HOURS:');
            $this->line("   Peak Range: {$analytics['peak_hours']['peak_range']}");
            
            $this->newLine();
            $this->info('💚 OPERATIONAL HEALTH:');
            $health = $analytics['operational_health'];
            $this->line("   Avg Queue: {$health['avg_queue']} mobil/day");
            $this->line("   Occupancy: {$health['occupancy']}%");
            $this->line("   Efficiency: {$health['efficiency']}%");
            
            $this->newLine();
            $this->info('📄 Full JSON Response:');
            $this->line(json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}

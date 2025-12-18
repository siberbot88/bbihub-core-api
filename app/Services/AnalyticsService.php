<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Service;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Calculate comprehensive analytics for workshop report dashboard
     */
    public function calculateWorkshopAnalytics(string $workshopUuid, string $range = 'monthly')
    {
        $dates = $this->getDateRange($range);
        $previousDates = $this->getPreviousDateRange($range);

        // Current period metrics
        $currentMetrics = $this->getMetrics($workshopUuid, $dates['start'], $dates['end']);
        
        // Previous period for comparison
        $previousMetrics = $this->getMetrics($workshopUuid, $previousDates['start'], $previousDates['end']);

        // Calculate growth percentages
        $growth = $this->calculateGrowth($currentMetrics, $previousMetrics);

        // Service breakdown
        $serviceBreakdown = $this->getServiceBreakdown($workshopUuid, $dates['start'], $dates['end']);

        // Peak hours analysis
        $peakHours = $this->getPeakHours($workshopUuid, $dates['start'], $dates['end']);

        // Operational health
        $health = $this->assessOperationalHealth($workshopUuid, $dates['start'], $dates['end']);

        return [
            'period' => [
                'range' => $range,
                'start' => $dates['start']->format('Y-m-d'),
                'end' => $dates['end']->format('Y-m-d'),
            ],
            'metrics' => [
                'revenue_this_period' => (int) $currentMetrics['revenue'],
                'jobs_done' => (int) $currentMetrics['jobs'],
                'occupancy' => (int) $currentMetrics['occupancy'],
                'avg_rating' => round($currentMetrics['avg_rating'], 1),
            ],
            'growth' => [
                'revenue' => $growth['revenue'],
                'jobs' => $growth['jobs'],
                'occupancy' => $growth['occupancy'],
                'rating' => $growth['rating'],
            ],
            'service_breakdown' => $serviceBreakdown,
            'peak_hours' => $peakHours,
            'operational_health' => $health,
        ];
    }

    /**
     * Get metrics for a specific date range
     */
    private function getMetrics(string $workshopUuid, Carbon $startDate, Carbon $endDate)
    {
        // Revenue and job count from transactions
        $transactionMetrics = Transaction::where('workshop_uuid', $workshopUuid)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'success')
            ->selectRaw('
                SUM(amount) as total_revenue,
                COUNT(*) as total_jobs
            ')
            ->first();

        // Average rating from feedback
        $avgRating = Feedback::whereHas('transaction', function ($query) use ($workshopUuid) {
                $query->where('workshop_uuid', $workshopUuid);
            })
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->avg('rating') ?? 0;

        // Occupancy rate (percentage of days with services)
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $daysWithServices = Service::where('workshop_uuid', $workshopUuid)
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->distinct('scheduled_date')
            ->count('scheduled_date');
        
        $occupancy = $totalDays > 0 ? ($daysWithServices / $totalDays) * 100 : 0;

        return [
            'revenue' => $transactionMetrics->total_revenue ?? 0,
            'jobs' => $transactionMetrics->total_jobs ?? 0,
            'avg_rating' => $avgRating,
            'occupancy' => $occupancy,
        ];
    }

    /**
     * Calculate growth percentages
     */
    private function calculateGrowth(array $current, array $previous)
    {
        $growth = [];
        
        foreach (['revenue', 'jobs', 'occupancy', 'avg_rating'] as $metric) {
            $currentValue = $current[$metric];
            $previousValue = $previous[$metric];
            
            if ($previousValue > 0) {
                $percentageChange = (($currentValue - $previousValue) / $previousValue) * 100;
                $growth[$metric === 'avg_rating' ? 'rating' : $metric] = round($percentageChange, 1);
            } else {
                $growth[$metric === 'avg_rating' ? 'rating' : $metric] = $currentValue > 0 ? 100 : 0;
            }
        }

        return $growth;
    }

    /**
     * Get service breakdown by category
     */
    private function getServiceBreakdown(string $workshopUuid, Carbon $startDate, Carbon $endDate)
    {
        $services = Service::where('workshop_uuid', $workshopUuid)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('category_service', DB::raw('COUNT(*) as count'))
            ->groupBy('category_service')
            ->get();

        $total = $services->sum('count');
        
        // Map to friendly names
        $categoryMap = [
            'ringan' => 'Service Rutin',
            'sedang' => 'Perbaikan',
            'berat' => 'Ganti Onderdil',
            'maintenance' => 'Maintenance',
        ];

        $breakdown = [];
        foreach ($services as $service) {
            $categoryName = $categoryMap[$service->category_service] ?? $service->category_service;
            $percentage = $total > 0 ? ($service->count / $total) * 100 : 0;
            $breakdown[$categoryName] = round($percentage, 1);
        }

        // Ensure we return an object, not array (for JSON consistency)
        return empty($breakdown) ? (object)[] : $breakdown;
    }

    /**
     * Analyze peak hours
     */
    private function getPeakHours(string $workshopUuid, Carbon $startDate, Carbon $endDate)
    {
        // Get all services grouped by hour
        $servicesByHour = Service::where('workshop_uuid', $workshopUuid)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->get();

        if ($servicesByHour->isEmpty()) {
            return [
                'peak_range' => '14:00 - 16:00', // Default
                'peak_hour' => 14,
                'hourly_distribution' => (object)[], // Cast to object for JSON
            ];
        }

        // Find peak hour
        $peakService = $servicesByHour->sortByDesc('count')->first();
        $peakHour = $peakService->hour;
        $startHour = str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ':00';
        $endHour = str_pad(($peakHour + 2) % 24, 2, '0', STR_PAD_LEFT) . ':00';

        // Build hourly distribution for visualization (8am - 10pm)
        $hourlyDistribution = [];
        $visualizationHours = [8, 10, 12, 14, 16, 18, 20, 22];
        
        foreach ($visualizationHours as $hour) {
            $service = $servicesByHour->firstWhere('hour', $hour);
            $hourlyDistribution["{$hour}:00"] = $service ? (int)$service->count : 0;
        }

        return [
            'peak_range' => "$startHour - $endHour",
            'peak_hour' => $peakHour,
            'hourly_distribution' => $hourlyDistribution,
        ];
    }

    /**
     * Assess operational health
     */
    private function assessOperationalHealth(string $workshopUuid, Carbon $startDate, Carbon $endDate)
    {
        // Average queue (services per day)
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $totalServices = Service::where('workshop_uuid', $workshopUuid)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $avgQueue = $totalDays > 0 ? round($totalServices / $totalDays, 1) : 0;

        // Efficiency (completed vs total services)
        $completedServices = Service::where('workshop_uuid', $workshopUuid)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();
        
        $efficiency = $totalServices > 0 ? round(($completedServices / $totalServices) * 100, 1) : 0;

        // Occupancy (reuse from metrics)
        $metrics = $this->getMetrics($workshopUuid, $startDate, $endDate);

        return [
            'avg_queue' => $avgQueue,
            'occupancy' => round($metrics['occupancy'], 1),
            'efficiency' => $efficiency,
        ];
    }

    /**
     * Get date range based on period selection
     */
    private function getDateRange(string $range)
    {
        $now = Carbon::now();
        
        return match($range) {
            'daily' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'weekly' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'monthly' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get previous period date range for comparison
     */
    private function getPreviousDateRange(string $range)
    {
        $now = Carbon::now();
        
        return match($range) {
            'daily' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            'weekly' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek(),
            ],
            'monthly' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            default => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
        };
    }
}

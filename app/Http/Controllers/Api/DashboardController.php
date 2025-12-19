<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Employment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/admins/dashboard/stats
     * Filter by date_from & date_to (default: start of month - now)
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        if (!$user->employment) {
             return response()->json(['message' => 'Admin tidak memiliki workshop.'], 403);
        }
        $workshopId = $user->employment->workshop_uuid;

        // Date Range
        $rawFrom = $request->input('date_from');
        $rawTo   = $request->input('date_to');

        // Parse with explicit error handling/logging
        try {
            $dateFrom = $rawFrom
                ? Carbon::parse($rawFrom)->startOfDay()
                : Carbon::now()->startOfMonth();

            $dateTo = $rawTo
                ? Carbon::parse($rawTo)->endOfDay()
                : Carbon::now()->endOfDay();

            \Illuminate\Support\Facades\Log::info("Dashboard Stats Filter: ", [
                'raw_from' => $rawFrom,
                'raw_to' => $rawTo,
                'parsed_from' => $dateFrom->toDateTimeString(),
                'parsed_to' => $dateTo->toDateTimeString(),
                'workshop_id' => $workshopId
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Dashboard Date Parse Error: " . $e->getMessage());
            return response()->json(['message' => 'Invalid date format'], 400);
        }

        // 1. SERVICES STATS
        $servicesQuery = Service::where('workshop_uuid', $workshopId)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalServices = (clone $servicesQuery)->count();

        // Group by status
        $servicesByStatus = (clone $servicesQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();


        // Acceptance Status
        $acceptanceStats = (clone $servicesQuery)
            ->select('acceptance_status', DB::raw('count(*) as count'))
            ->groupBy('acceptance_status')
            ->pluck('count', 'acceptance_status')
            ->toArray();

        // [Feature Upgrade] Service Trend (Daily) for Chart
        // Group by scheduled_date or created_at. Let's use created_at (Date)
        $serviceTrend = (clone $servicesQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // [Feature Upgrade] Service Type Breakdown (Booking vs Walk-in)
        $typeBreakdown = (clone $servicesQuery)
            ->select('type', DB::raw('count(*) as count'))
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // [Feature Upgrade] Most Frequent Services (Top 5)
        // Group by 'category_service' (or 'name' if consistent)
        $topServices = (clone $servicesQuery)
             ->select('category_service', DB::raw('count(*) as count'))
             ->whereNotNull('category_service')
             ->groupBy('category_service')
             ->orderByDesc('count')
             ->limit(5)
             ->get();


        // 3. MECHANIC PERFORMANCE
        // Get mechanics in this workshop
        $mechanics = Employment::mechanic()
            ->where('workshop_uuid', $workshopId)
            ->with(['user'])
            ->get();

        $mechanicStats = $mechanics->map(function ($emp) use ($dateFrom, $dateTo, $workshopId) {
             // Completed services count in range
             $completedCount = Service::where('workshop_uuid', $workshopId)
                 ->where('mechanic_uuid', $emp->id)
                 ->where('status', 'completed') // or lunas? usually completed is technical work done
                 ->whereBetween('created_at', [$dateFrom, $dateTo])
                 ->count();

             // Active services (in progress) - snapshot now (not range based usually, but let's stick to current active)
             $activeCount = Service::where('workshop_uuid', $workshopId)
                 ->where('mechanic_uuid', $emp->id)
                 ->whereIn('status', ['in progress', 'pending']) // pending might not have mechanic yet, but if assigned
                 ->count();

             return [
                 'name' => $emp->user->name,
                 'completed_services' => $completedCount,
                 'active_services' => $activeCount,
             ];
        })->sortByDesc('completed_services')->values();


        // 4. CUSTOMER INSIGHTS
        // Total customers who had service in this periode
        $totalCustomersServed = (clone $servicesQuery)
            ->distinct('customer_uuid')
            ->count('customer_uuid');


        return response()->json([
            'period' => [
                'from' => $dateFrom->toDateString(),
                'to'   => $dateTo->toDateString(),
            ],
            'services' => [
                'total' => $totalServices,
                'status_breakdown' => $servicesByStatus,
                'acceptance_breakdown' => $acceptanceStats,
                'type_breakdown' => $typeBreakdown, // New for Type Pie Chart
                'trend' => $serviceTrend, // New for Chart
                'top_services' => $topServices, // New for Top List
            ],
            // Revenue removed as per request
            'mechanics' => $mechanicStats,
            'customers' => [
                'served_count' => $totalCustomersServed,
            ]
        ]);
    }
}

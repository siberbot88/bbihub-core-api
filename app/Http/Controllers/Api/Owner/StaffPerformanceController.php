<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ServiceLog; // Assumption: ServiceLog tracks who did the job
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffPerformanceController extends Controller
{
    /**
     * Get staff performance metrics.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $owner = $request->user();
        
        // 1. Determine Workshop ID
        $workshopId = $owner->workshop_id;
        if (!$workshopId && $owner->workshop) {
            $workshopId = $owner->workshop->id;
        }
        
        if (!$workshopId) {
             return response()->json([
                 'success' => false,
                 'message' => 'Workshop not found',
             ], 404);
        }

        // 2. Parse Date Range
        $range = $request->input('range', 'today');
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        switch ($range) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
        }

        // 3. Get Staff (Employments) with User details
        // We look for employments in this workshop
        $employments = \App\Models\Employment::with(['user.roles'])
            ->where('workshop_uuid', $workshopId)
            ->get();

        $performanceData = $employments->map(function ($employment) use ($startDate, $endDate) {
            $user = $employment->user;
            if (!$user) return null; // Skip if user data missing

            // 4. Calculate Stats from Transactions
            // Using mechanic_uuid which links to employment id
            
            // Jobs Done: Status completed or done
            $jobsDone = Transaction::where('mechanic_uuid', $employment->id)
                ->whereIn('status', ['completed', 'done', 'paid']) // Adjust based on exact enum
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
            
            // Jobs In Progress: Status processing/working
            $jobsInProgress = Transaction::where('mechanic_uuid', $employment->id)
                ->whereIn('status', ['processing', 'working', 'pending']) 
                ->count();

            // Revenue: Sum of amount
            $revenue = Transaction::where('mechanic_uuid', $employment->id)
                ->whereIn('status', ['completed', 'done', 'paid'])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->sum('amount');

            // Safely get role
            $role = 'Mechanic';
            if ($user->roles && $user->roles->isNotEmpty()) {
                $role = $user->roles->first()->name;
            }

            return [
                'id' => $user->id, 
                'name' => $user->name,
                'role' => $role, 
                'avatar_url' => $user->photo ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name),
                'jobs_done' => $jobsDone,
                'jobs_in_progress' => $jobsInProgress,
                'estimated_revenue' => (int) $revenue,
            ];
        })->filter()->values(); // Remove nulls

        return response()->json([
            'success' => true,
            'data' => $performanceData,
            'meta' => [
                'range' => $range,
                'start_date' => $startDate->toIso8601String(),
                'end_date' => $endDate->toIso8601String(),
            ]
        ]);
    }
}

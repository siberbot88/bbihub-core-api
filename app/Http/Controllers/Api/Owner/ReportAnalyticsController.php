<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get workshop analytics report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'range' => 'sometimes|in:daily,weekly,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get authenticated user
            $user = $request->user();
            
            // Find workshop owned by this user
            $workshop = \App\Models\Workshop::where('user_uuid', $user->id)->first();
            
            if (!$workshop) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not associated with any workshop',
                ], 400);
            }

            $range = $request->input('range', 'monthly');
            
            $analytics = $this->analyticsService->calculateWorkshopAnalytics(
                $workshop->id, // Use workshop ID
                $range
            );

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate analytics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}

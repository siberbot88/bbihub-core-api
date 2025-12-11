<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Services\StaffPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StaffPerformanceController extends Controller
{
    protected $service;

    public function __construct(StaffPerformanceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'workshop_uuid' => 'required|uuid|exists:workshops,id',
            'range' => 'nullable|in:today,week,month',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2030',
        ]);

        $workshopUuid = $request->input('workshop_uuid');
        $range = $request->input('range', 'month');
        $filters = $request->only(['month', 'year']);

        $user = $request->user();
        
        // Verify workshop ownership
        if (!$user->workshops()->where('id', $workshopUuid)->exists()) {
             return response()->json(['message' => 'Unauthorized access to this workshop'], 403);
        }

        $result = $this->service->getStaffPerformance($workshopUuid, $range, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Staff performance retrieved successfully',
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function show(Request $request, string $userId): JsonResponse
    {
        $request->validate([
            'workshop_uuid' => 'required|uuid|exists:workshops,id',
            'range' => 'nullable|in:today,week,month',
        ]);

        $workshopUuid = $request->input('workshop_uuid');
        $range = $request->input('range', 'month');

        $user = $request->user();
        
        // Verify workshop ownership
        if (!$user->workshops()->where('id', $workshopUuid)->exists()) {
             return response()->json(['message' => 'Unauthorized access to this workshop'], 403);
        }

        $result = $this->service->getIndividualPerformance($workshopUuid, $userId, $range);

        if (!$result) {
            return response()->json(['message' => 'Staff not found or not in this workshop'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff performance retrieved successfully',
            'data' => $result,
        ]);
    }
}

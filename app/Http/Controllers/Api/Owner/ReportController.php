<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of owner's reports (paginated)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get workshop UUID from user
        $workshopUuid = $user->workshops?->first()?->id;

        if (!$workshopUuid) {
            return $this->errorResponse('No workshop found for this user', 404);
        }

        $perPage = $request->input('per_page', 10);
        $reports = Report::where('workshop_uuid', $workshopUuid)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($reports, 'Reports retrieved successfully');
    }

    /**
     * Store a newly created report
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get workshop UUID from user
        $workshopUuid = $user->workshops?->first()?->id;

        if (!$workshopUuid) {
            return $this->errorResponse('No workshop found for this user', 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string|in:Bug,Keluhan,Saran,Ulasan',
            'report_data' => 'required|string|min:10',
            'photo' => 'nullable|string', // base64 or URL
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            // Create report
            $report = Report::create([
                'workshop_uuid' => $workshopUuid,
                'report_type' => $request->report_type,
                'report_data' => $request->report_data,
                'photo' => $request->photo,
                'status' => 'baru', // New report status
            ]);

            return $this->successResponse(
                $report->load('workshop'),
                'Report submitted successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified report
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $workshopUuid = $user->workshops?->first()?->id;

        if (!$workshopUuid) {
            return $this->errorResponse('No workshop found for this user', 404);
        }

        $report = Report::where('id', $id)
            ->where('workshop_uuid', $workshopUuid)
            ->with('workshop')
            ->first();

        if (!$report) {
            return $this->errorResponse('Report not found', 404);
        }

        return $this->successResponse($report, 'Report retrieved successfully');
    }
}

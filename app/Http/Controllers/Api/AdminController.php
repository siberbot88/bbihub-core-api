<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(protected ServiceService $serviceService)
    {}

    /**
     * POST /api/v1/admins/services/{service}/accept
     */
    public function accept(Request $request, Service $service)
    {
        $data = $request->validate([
            'scheduled_date'   => ['nullable','date'],
            'estimated_time'   => ['nullable','date'],
            'price'            => ['nullable','numeric'],
            'category_service' => ['nullable', Rule::in(['ringan','sedang','berat','maintenance'])],
            'mechanic_uuid'    => ['nullable','string','exists:employments,id'],
        ]);

        try {
            $updated = $this->serviceService->acceptService(
                $service,
                $data,
                $request->user()
            );

            return new ServiceResource(
                $updated->fresh()->load([
                    'workshop',
                    'customer',
                    'vehicle',
                    'mechanic.user',
                    'transaction.items',
                    'log',
                    'task',
                ])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/admins/services/{service}/decline
     */
    public function decline(Request $request, Service $service)
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in([
                'antrian sedang full',
                'jadwal bentrok',
                'lokasi terlalu jauh',
                'kendaraan tidak sesuai',
                'lainnya',
            ])],
            'reason_description' => ['nullable','string'],
        ]);

        try {
            $updated = $this->serviceService->declineService(
                $service,
                $data,
                $request->user()
            );

            return new ServiceResource(
                $updated->fresh()->load([
                    'workshop',
                    'customer',
                    'vehicle',
                    'mechanic.user',
                    'transaction.items',
                    'log',
                    'task',
                ])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/admins/services/{service}/assign-mechanic
     */
    public function assignMechanic(Request $request, Service $service)
    {
        $data = $request->validate([
            'mechanic_uuid' => ['required', 'string', 'exists:employments,id'],
        ]);

        try {
            $updated = $this->serviceService->assignMechanic(
                $service,
                $data['mechanic_uuid'],
                $request->user()
            );

            return new ServiceResource(
                $updated->fresh()->load([
                    'workshop',
                    'customer',
                    'vehicle',
                    'mechanic.user',
                    'transaction.items',
                    'log',
                    'task',
                ])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }
}

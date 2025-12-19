<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
<<<<<<< HEAD
use App\Models\Employment;
=======
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
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
<<<<<<< HEAD
     * GET /api/v1/admins/employees
     */
    public function employees(Request $request)
    {
        $user = $request->user();

        // 1. Cari data employment admin yang sedang login
        $currentEmployment = Employment::where('user_uuid', $user->id)->first();

        if (!$currentEmployment) {
            return response()->json(['message' => 'Data employment tidak ditemukan.'], 403);
        }

        // 2. Query dasar: workshop yang sama
        $query = Employment::with(['user.roles'])
            ->where('workshop_uuid', $currentEmployment->workshop_uuid);

        // 3. Filter berdasarkan role jika diminta (e.g. ?role=mechanic)
        if ($request->has('role') && $request->role === 'mechanic') {
            $query->mechanic();
        }

        $employees = $query->get();

        return response()->json(['data' => $employees]);
    }
    /**
=======
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
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

<<<<<<< HEAD




=======
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\VehicleService;
use App\Http\Requests\Api\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Api\Vehicle\UpdateVehicleRequest;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Traits\ApiResponseTrait;

class VehicleController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected VehicleService $vehicleService)
    {
    }

    /**
     * GET /api/v1/vehicles
     * Jauh lebih bersih menggunakan spatie/laravel-query-builder
     */
    public function index(Request $request): JsonResponse
    {
        $query = QueryBuilder::for(Vehicle::class)
            ->allowedFilters([
                'customer_uuid',
                AllowedFilter::partial('brand'), // 'like %...%'
                AllowedFilter::partial('model'), // 'like %...%'
                AllowedFilter::callback('q', function ($query, $value) {
                    $query->where(function ($qq) use ($value) {
                        $qq->where('plate_number', 'like', "%{$value}%")
                            ->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('model', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedIncludes(['customer:id,name'])
            ->defaultSort('-created_at');

        if ($request->boolean('paginate', false)) {
            $perPage = (int) $request->input('per_page', 15);
            $vehicles = $query->paginate($perPage);
        } else {
            $vehicles = $query->get();
        }

        return $this->successResponse('success', $vehicles);
    }

    /**
     * POST /api/v1/vehicles
     * Logika dipindah ke Service, validasi ke Request
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->createVehicle(
                $request->validated(),
                $request->input('include')
            );

            $request->input(('include')
            );

            return $this->successResponse('kendaraan berhasil dibuat', $vehicle, 201);

        } catch (Exception $e) {
            return $this->errorResponse('Gagal membuat kendaraan', 500, $e->getMessage());
        }
    }

    /**
     * GET /api/v1/vehicles/{vehicle}
     * Disederhanakan dengan QueryBuilder agar konsisten
     */
    public function show(Vehicle $vehicle, Request $request): JsonResponse
    {
        $result = QueryBuilder::for(Vehicle::where('id', $vehicle->id))
            ->allowedIncludes(['customer:id,name'])
            ->first();

        return $this->successResponse('success', $result);
    }

    /**
     * PUT/PATCH /api/v1/vehicles/{vehicle}
     * Logika dipindah ke Service, validasi ke Request
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $updatedVehicle = $this->vehicleService->updateVehicle(
                $vehicle,
                $request->validated()
            );
            return $this->successResponse('updated', $updatedVehicle);

        } catch (Exception $e) {
            return $this->errorResponse('Gagal update kendaraan', 500, $e->getMessage());
        }
    }

    /**
     * DELETE /api/v1/vehicles/{vehicle}
     * (Sudah bersih, tidak perlu diubah)
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        try {
            $vehicle->delete();
            return $this->successResponse('Kendaraan berhasil dihapus');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menghapus kendaraan', 500, $e->getMessage());
        }
    }

}

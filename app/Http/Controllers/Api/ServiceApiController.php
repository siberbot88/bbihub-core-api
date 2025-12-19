<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Service\StoreServiceRequest;
use App\Http\Requests\Api\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ServiceApiController extends Controller
{
    public function __construct(protected ServiceService $serviceService)
    {
    }

    /**
     * GET /services
     * - owner: lihat service di semua workshop dia
     * - admin: lihat service di workshop tempat dia bekerja
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);

        $user = $request->user();

        $query = QueryBuilder::for(Service::class)
            ->allowedIncludes([
                'workshop',
                'customer',
                'vehicle',
                'mechanic',
                'mechanic.user',
                'transaction.items',
                'extras', // Dummy relation for mobile backward compatibility
            ])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('code'),
                AllowedFilter::exact('workshop_uuid'),
                // Keep these for standard compliant clients
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('scheduled_date', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('scheduled_date', '<=', $value);
                }),
            ])
            ->defaultSort('-created_at');

        // [Manual Filter Support] for Mobile App (flat params)
        if ($request->has('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->input('date_to'));
        }

        // Scope query based on role
        if ($user->hasRole('owner')) {
            $ownerWorkshopIds = $user->workshops()->pluck('id');
            $query->whereIn('workshop_uuid', $ownerWorkshopIds);

            // Jika filter workshop_uuid ada, pastikan milik owner
            if ($request->filled('filter.workshop_uuid')) {
                $requestedWorkshopId = $request->input('filter.workshop_uuid');
                abort_unless($ownerWorkshopIds->contains($requestedWorkshopId), 403, 'Workshop bukan milik Anda');
            }

        } elseif ($user->hasRole('admin')) {
            $employment = $user->employment;

            if (! $employment) {
                $query->whereRaw('1 = 0'); // No access if no employment
            } else {
                $query->where('workshop_uuid', $employment->workshop_uuid);

                // Jika filter workshop_uuid ada, pastikan sama dengan tempat kerja
                if ($request->filled('filter.workshop_uuid')) {
                    $requestedWorkshopId = $request->input('filter.workshop_uuid');
                    abort_unless($requestedWorkshopId === $employment->workshop_uuid, 403, 'Workshop bukan milik Anda');
                }
            }
        }

        // Always load essential relations for mobile app compatibility
        // QueryBuilder allowedIncludes will handle additional includes if requested
        $query->with(['customer', 'vehicle', 'mechanic.user', 'workshop']);

        $perPage = (int) $request->get('per_page', 15);
        $services = $query->paginate($perPage)->appends($request->query());

        return ServiceResource::collection($services);
    }

    /**
     * GET /services/{service}
     * owner + admin (policy view)
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        $this->authorize('view', $service);

        $service->load([
            'workshop',
            'customer',
            'vehicle',
            'mechanic.user',
            'transaction.items',
        ]);

        return response()->json([
            'message' => 'success',
            'data'    => new ServiceResource($service),
        ], 200);
    }

    /**
     * POST /services
     * HANYA admin (policy + middleware).
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $service = $this->serviceService->createService($request->validated(), $request->user());

            $service->load([
                'workshop',
                'customer',
                'vehicle',
                'mechanic.user',
            ]);

            return response()->json([
                'message' => 'created',
                'data'    => new ServiceResource($service),
            ], 201);
        } catch (\Exception $e) {
             return response()->json([
                'message' => $e->getMessage(),
            ], 422); // Or 500 depending on exception type, but ValidationException is usually 422 handled by Laravel
        }
    }

    /**
     * POST /admins/services/walk-in
     * Create Customer + Vehicle + Service in one flow
     */
    public function storeWalkIn(\App\Http\Requests\Api\Service\StoreWalkInServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Force workshop uuid from admin session
        $data['workshop_uuid'] = $user->employment->workshop_uuid;

        $service = $this->serviceService->createWalkInService($data, $user);
        
        $service->load([
             'workshop',
             'customer',
             'vehicle',
             'mechanic.user',
        ]);

        return response()->json([
            'message' => 'Walk-In Service created successfully',
            'data'    => new ServiceResource($service),
        ], 201);
    }

    /**
     * PUT/PATCH /services/{service}
     * HANYA admin (di UpdateServiceRequest@authorize + policy).
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        try {
            $updatedService = $this->serviceService->updateService($service, $request->validated(), $request->user());

            $updatedService->load([
                'workshop',
                'customer',
                'vehicle',

                'mechanic.user',
            ]);

            return response()->json([
                'message' => 'Service updated successfully',
                'data'    => new ServiceResource($updatedService),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /services/{service}
     * HANYA admin (policy delete).
     */
    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use App\Services\EmploymentService;
use App\Http\Requests\Api\Employment\StoreEmploymentRequest;
use App\Http\Requests\Api\Employment\UpdateEmploymentRequest;
use App\Http\Requests\Api\Employment\UpdateEmploymentStatusRequest;

class EmployementApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected EmploymentService $employmentService)
    {
    }

    /* =================== Endpoints =================== */

    /**
     * GET /v1/owners/employee
     * Mengambil daftar karyawan milik owner
     */
    public function index(Request $request): JsonResponse
    {
        $owner = $request->user();
        $workshopIds = $owner->workshops()->pluck('id');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $employees = $this->employmentService->getEmployees($workshopIds, $search, $perPage);

        return $this->successResponse('Data karyawan berhasil diambil', $employees);
    }

    /**
     * POST /v1/owners/employee
     * Membuat karyawan baru
     */
    public function store(StoreEmploymentRequest $request): JsonResponse
    {
        // 🔒 LIMIT CHECK for Free Tier
        $user = $request->user();
        if ($user->hasRole('owner', 'sanctum')) {
             $subscription = $user->ownerSubscription; 
             $isPremium = false;
             
             // Check if active premium plan (not starter)
             if ($subscription && $subscription->status === 'active') {
                 $plan = $subscription->plan; // using the alias we fixed earlier
                 if ($plan && $plan->code !== 'starter') {
                     $isPremium = true;
                 }
             }
             
             $limit = $isPremium ? 9999 : 5;
             
             // Count current employees
             $workshopIds = $user->workshops()->pluck('id');
             $currentCount = Employment::whereIn('workshop_uuid', $workshopIds)->count();
             
             if ($currentCount >= $limit) {
                 return $this->errorResponse(
                     'Batas staff tercapai (Maksimal 5). Upgrade ke Premium untuk menambah staff.',
                     403,
                     ['code' => 'LIMIT_REACHED']
                 );
             }
        }

        try {
            [$employment, $emailSent] = $this->employmentService->createEmployee(
                $request->validated()
            );

            return $this->successResponse('Karyawan berhasil dibuat', [
                'data'       => $employment,
                'email_sent' => $emailSent,
            ], 201);

        } catch (\Throwable $e) {
            \Log::error('Create employee failed', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                'Gagal membuat karyawan.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }

    /**
     * GET /v1/owners/employee/{employee}
     * Mengambil detail satu karyawan
     */
    public function show(Request $request, Employment $employee): JsonResponse
    {
        if (optional($employee->workshop)->user_uuid !== $request->user()->id) {
            return $this->errorResponse('Tidak diizinkan', 403);
        }

        $employee->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

        return $this->successResponse('Detail karyawan', $employee);
    }

    /**
     * PUT /v1/owners/employee/{employee}
     * Memperbarui data karyawan
     */
    public function update(UpdateEmploymentRequest $request, Employment $employee): JsonResponse
    {
        try {
            $updatedEmployee = $this->employmentService->updateEmployee(
                $employee,
                $request->validated()
            );

            return $this->successResponse('Karyawan berhasil diperbarui', $updatedEmployee);

        } catch (\Throwable $e) {
            \Log::error('Update employee failed', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                'Gagal update karyawan.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }

    /**
     * PATCH /v1/owners/employee/{employee}/status
     * Mengubah status aktif/non-aktif karyawan
     */
    public function updateStatus(UpdateEmploymentStatusRequest $request, Employment $employee): JsonResponse
    {
        try {
            $updatedEmployee = $this->employmentService->updateEmployeeStatus(
                $employee,
                $request->validated('status')
            );

            return $this->successResponse('Status updated', [
                'id'      => $updatedEmployee->id,
                'status'  => $updatedEmployee->status,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Update employee status failed', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                'Gagal update status karyawan.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }

    /**
     * DELETE /v1/owners/employee/{employee}
     * Menghapus karyawan
     */
    public function destroy(Request $request, Employment $employee): JsonResponse
    {
        if (optional($employee->workshop)->user_uuid !== $request->user()->id) {
            return $this->errorResponse('Tidak diizinkan', 403);
        }

        try {
            $this->employmentService->deleteEmployee($employee);

            return $this->successResponse('Karyawan berhasil dihapus', null, 204); // 204 No Content

        } catch (\Throwable $e) {
            \Log::error('Delete employee failed', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                'Gagal menghapus karyawan.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }
}

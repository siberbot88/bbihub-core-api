<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Workshop\StoreWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;

class WorkshopApiController extends Controller
{
    use ApiResponseTrait;

    /**
     *
     * @param StoreWorkshopRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreWorkshopRequest $request)
    {

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $dataToCreate = array_merge($validatedData, [
                'user_uuid' => $user->id,
            ]);

            $workshop = Workshop::create($dataToCreate);
            return $this->successResponse('Bengkel berhasil dibuat', $workshop, 201);

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal menyimpan bengkel.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\Workshop\UpdateWorkshopRequest $request
     * @param \App\Models\Workshop $workshop
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(\App\Http\Requests\Api\Workshop\UpdateWorkshopRequest $request, Workshop $workshop)
    {
        $this->authorize('update', $workshop);

        try {
            $data = $request->validated();

            if ($request->hasFile('photo')) {
                // Delete old photo if exists (optional)
                $path = $request->file('photo')->store('workshops', 'public');
                $data['photo'] = url('storage/' . $path);
            }

            $workshop->update($data);

            return $this->successResponse('Workshop updated successfully', $workshop);

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal mengupdate workshop.',
                500,
                $e->getMessage()
            );
        }
    }

}

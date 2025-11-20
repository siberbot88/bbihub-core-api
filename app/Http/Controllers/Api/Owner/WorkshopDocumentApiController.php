<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\WorkshopDocument;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Requests\Api\Document\StoreWorkshopDocumentRequest;

class WorkshopDocumentApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Menyimpan dokumen bengkel (Step 2)
     */
    public function store(StoreWorkshopDocumentRequest $request)
    {

        try {
            $validatedData = $request->validated();
            $document = WorkshopDocument::create($validatedData);

            return $this->successResponse('Dokumen berhasil disimpan', $document, 201);

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal menyimpan dokumen.',
                500,
                $e->getMessage()
            );
        }
    }
}

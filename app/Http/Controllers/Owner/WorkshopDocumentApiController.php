<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\WorkshopDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WorkshopDocumentApiController extends Controller
{
    /**
     * Menyimpan dokumen bengkel (Step 2)
     */
    public function store(Request $request)
    {
        // 1. Validasi data dari Flutter
        $validator = Validator::make($request->all(), [
            'workshop_uuid' => 'required|uuid|exists:workshops,id',
            'nib' => 'required|string|max:255',
            'npwp' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workshop = Workshop::where('id', $request->workshop_uuid)
                ->where('user_uuid', Auth::id())
                ->first();

            if (!$workshop) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda bukan pemilik bengkel ini.'
                ], 403);
            }

            $document = WorkshopDocument::create([
                'id' => Str::uuid(),
                'workshop_uuid' => $workshop->id,
                'nib' => $request->nib,
                'npwp' => $request->npwp,
            ]);

            return response()->json($document, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan dokumen.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

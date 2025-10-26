<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WorkshopApiController extends Controller
{
    /**
     * Menyimpan data bengkel baru (Step 1)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'city' => 'required|string',
            'province' => 'required|string',
            'country' => 'required|string',
            'postal_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'operational_days' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();
            $workshop = Workshop::create([
                'id' => Str::uuid(),
                'user_uuid' => $user->id,
                'code' => 'BKL-' . strtoupper(Str::random(8)),
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'operational_days' => $request->operational_days,
                'photo' => 'https://placehold.co/600x400/D72B1C/FFFFFF?text=' . $request->name, // Default
                'is_active' => true,
            ]);

            return response()->json($workshop, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan bengkel.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

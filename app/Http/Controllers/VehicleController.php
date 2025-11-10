<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * GET /api/v1/vehicles
     * Optional query:
     * - customer_uuid=... | brand=... | model=... | q=... (search plate/name/model)
     * - include=customer
     */
    public function index(Request $request): JsonResponse
    {
        $q = Vehicle::query();

        if ($request->filled('customer_uuid')) {
            $q->where('customer_uuid', $request->string('customer_uuid'));
        }
        if ($request->filled('brand')) {
            $q->where('brand', 'like', '%'.$request->string('brand').'%');
        }
        if ($request->filled('model')) {
            $q->where('model', 'like', '%'.$request->string('model').'%');
        }
        if ($request->filled('q')) {
            $s = $request->string('q');
            $q->where(function($qq) use ($s) {
                $qq->where('plate_number', 'like', '%'.$s.'%')
                    ->orWhere('name', 'like', '%'.$s.'%')
                    ->orWhere('model', 'like', '%'.$s.'%');
            });
        }

        if ($request->boolean('paginate', false)) {
            $perPage = (int) $request->input('per_page', 15);
            $vehicles = $q->orderBy('created_at','desc')->paginate($perPage);
        } else {
            $vehicles = $q->orderBy('created_at','desc')->get();
        }

        // include=customer => tambahkan data customer ringkas
        if ($request->filled('include') && $request->input('include') === 'customer') {
            $vehicles->loadMissing('customer:id,name');
        }

        return response()->json([
            'message' => 'success',
            'data'    => $vehicles,
        ], 200);
    }

    /**
     * POST /api/v1/vehicles
     * Auto-generate code: VH-{MODEL}-{NNN}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_uuid' => ['required', 'uuid', 'exists:customers,id'],
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'string', 'max:100'], // ex: car/motorcycle
            'brand'         => ['required', 'string', 'max:100'],
            'category'      => ['required', 'string', 'max:100'],
            'model'         => ['required', 'string', 'max:100'],
            'year'          => ['required', 'string', 'max:10'],  // kamu simpan string
            'color'         => ['required', 'string', 'max:50'],
            'plate_number'  => ['required', 'string', 'max:50', 'unique:vehicles,plate_number'],
            'odometer'      => ['required', 'string', 'max:50'],  // kolom kamu string
        ]);

        // Generate code aman race-condition
        $vehicle = DB::transaction(function () use ($validated) {
            $modelSegment = $this->modelSegment($validated['model']); // e.g. AVANZA
            $prefix = "VH-{$modelSegment}-";

            // Kunci baris terakhir untuk model ini
            $last = Vehicle::where('code', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderBy('code', 'desc')
                ->first();

            $next = 1;
            if ($last) {
                // Extract 3 digit di belakang
                if (preg_match('/^'.preg_quote($prefix, '/').'(\d{3})$/', $last->code, $m)) {
                    $next = ((int) $m[1]) + 1;
                }
            }
            $code = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);

            // Create
            return Vehicle::create([
                'id'            => (string) Str::uuid(),
                'customer_uuid' => $validated['customer_uuid'],
                'code'          => $code,
                'name'          => $validated['name'],
                'type'          => $validated['type'],
                'category'      => $validated['category'],
                'brand'         => $validated['brand'],
                'model'         => $validated['model'],
                'year'          => $validated['year'],
                'color'         => $validated['color'],
                'plate_number'  => $validated['plate_number'],
                'odometer'      => $validated['odometer'],
            ]);
        });

        // Tambahkan relasi customer sederhana jika diminta
        if ($request->input('include') === 'customer') {
            $vehicle->loadMissing('customer:id,name');
        }

        return response()->json([
            'message' => 'kendaraan berhasil dibuat',
            'data'    => $vehicle,
        ], 201);
    }

    /**
     * GET /api/v1/vehicles/{vehicle}
     * Optional ?include=customer
     */
    public function show(Vehicle $vehicle, Request $request): JsonResponse
    {
        if ($request->input('include') === 'customer') {
            $vehicle->loadMissing('customer:id,name');
        }

        return response()->json([
            'message' => 'success',
            'data'    => $vehicle,
        ], 200);
    }

    /**
     * PUT/PATCH /api/v1/vehicles/{vehicle}
     * Catatan: code immutable. Set `regenerate_code=true` + `model` untuk mengganti code mengikuti model baru.
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'customer_uuid' => ['sometimes', 'uuid', 'exists:customers,id'],
            'name'          => ['sometimes', 'string', 'max:255'],
            'type'          => ['sometimes', 'string', 'max:100'],
            'category'      => ['sometimes', 'string', 'max:100'],
            'brand'         => ['sometimes', 'string', 'max:100'],
            'model'         => ['sometimes', 'string', 'max:100'],
            'year'          => ['sometimes', 'string', 'max:10'],
            'color'         => ['sometimes', 'string', 'max:50'],
            'plate_number'  => [
                'sometimes', 'string', 'max:50',
                Rule::unique('vehicles','plate_number')->ignore($vehicle->id, 'id'),
            ],
            'odometer'      => ['sometimes', 'string', 'max:50'],
            'regenerate_code' => ['sometimes', 'boolean'],
        ]);

        // Jika diminta regenerate code (dan model ikut berubah / ada)
        if (($validated['regenerate_code'] ?? false) && !empty($validated['model'])) {
            DB::transaction(function () use (&$vehicle, $validated) {
                $vehicle->fill($validated);
                $vehicle->save();

                $modelSegment = $this->modelSegment($vehicle->model);
                $prefix = "VH-{$modelSegment}-";

                $last = Vehicle::where('code', 'like', $prefix.'%')
                    ->lockForUpdate()
                    ->orderBy('code','desc')
                    ->first();

                $next = 1;
                if ($last && preg_match('/^'.preg_quote($prefix,'/').'(\d{3})$/', $last->code, $m)) {
                    $next = ((int) $m[1]) + 1;
                }

                $vehicle->code = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
                $vehicle->save();
            });
        } else {
            // Update biasa tanpa menyentuh code
            $vehicle->fill($validated);
            $vehicle->save();
        }

        return response()->json([
            'message' => 'updated',
            'data'    => $vehicle,
        ], 200);
    }

    /**
     * DELETE /api/v1/vehicles/{vehicle}
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();
        return response()->json(null, 204);
    }

    /**
     * Ubah model jadi segmen code: uppercase + hanya A-Z0-9 (spasi & simbol dihilangkan).
     * "Avanza 1.3 G" -> "AVANZA13G"
     */
    private function modelSegment(string $model): string
    {
        $ascii = Str::ascii($model);
        $upper = Str::upper($ascii);
        return preg_replace('/[^A-Z0-9]/', '', $upper) ?: 'GEN';
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ServiceController extends Controller
{
// GET /api/v1/services
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $services = Service::with([
            'workshop', 'customer', 'vehicle', 'mechanic', 'items', 'log', 'task'
        ])->latest()->paginate($perPage);

        return ServiceResource::collection($services);
    }

    // GET /api/v1/services/{id}
    public function show($id)
    {
        $service = Service::with(['workshop','customer','vehicle','mechanic','items','log','task'])
            ->findOrFail($id);

        return new ServiceResource($service);
    }

    // POST /api/v1/services
    public function store(Request $request)
    {
        $data = $request->validate([
            'workshop_uuid' => ['required','string'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric'],
            'scheduled_date' => ['required','date'],
            'estimated_time' => ['required','date'],
            'customer_uuid' => ['required','string'],
            'vehicle_uuid' => ['required','string'],
            'mechanic_uuid' => ['nullable','string'],
        ]);

        // Business rule: 1 vehicle hanya boleh 1 service aktif (pending/in progress)
        $existing = Service::where('vehicle_uuid', $data['vehicle_uuid'])
            ->whereIn('status', ['pending', 'in progress'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Kendaraan ini sudah memiliki service aktif yang belum selesai.',
                'existing_service_id' => $existing->id
            ], 422);
        }

        $data['id'] = (string) Str::uuid();
        $data['code'] = $this->generateCode();
        // default status & acceptance jika tidak dikirim
        $data['status'] = $data['status'] ?? 'pending';
        $data['acceptance_status'] = $data['acceptance_status'] ?? 'pending';

        $service = Service::create($data);

        // (opsional) buat log awal & task via event/dispatch jika ingin otomatis
        // event(new ServiceCreated($service));

        return (new ServiceResource($service->load(['workshop','customer','vehicle','mechanic','items','log','task'])))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH /api/v1/services/{id}
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $data = $request->validate([
            'status' => ['nullable', Rule::in(['pending','in progress','completed'])],
            'acceptance_status' => ['nullable', Rule::in(['pending','accepted','decline'])],
            'mechanic_uuid' => ['nullable','string'],
            'scheduled_date' => ['nullable','date'],
            'estimated_time' => ['nullable','date'],
            'price' => ['nullable','numeric'],
            'description' => ['nullable','string'],
        ]);

        // Jika mengubah status menjadi 'in progress', pastikan vehicle tidak conflict
        if (isset($data['status']) && $data['status'] === 'in progress') {
            $conflict = Service::where('vehicle_uuid', $service->vehicle_uuid)
                ->where('id', '!=', $service->id)
                ->whereIn('status', ['pending','in progress'])
                ->first();

            if ($conflict) {
                return response()->json([
                    'message' => 'Tidak dapat set ke in progress karena ada service lain yang aktif untuk kendaraan ini.',
                    'conflict_service_id' => $conflict->id
                ], 422);
            }
        }

        $service->update($data);

        // Jika status berubah ke 'completed' bisa lakukan cleanup: hapus/selesaikan task, buat log akhir, dsb.
        // Contoh:
        // if (isset($data['status']) && $data['status'] === 'completed') { ... }

        return new ServiceResource($service->fresh()->load(['workshop','customer','vehicle','mechanic','items','log','task']));
    }

    // DELETE /api/v1/services/{id}
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service dihapus.'], 200);
    }

    protected function generateCode()
    {
        return 'SRV-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

}

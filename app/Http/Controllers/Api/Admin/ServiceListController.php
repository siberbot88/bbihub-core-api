<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceListController extends Controller
{
    public function index()
    {
        $services = Service::with(['workshop','customer','vehicle','mechanic','items','log','task'])
            ->latest()
            ->paginate(15);

        return ServiceResource::collection($services);
    }

    public function show($id)
    {
        $service = Service::with(['workshop','customer','vehicle','mechanic','items','log','task'])
            ->findOrFail($id);

        return new ServiceResource($service);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workshop_uuid' => ['required','string'],
            'name' => ['required','string'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric'],
            'scheduled_date' => ['required','date'],
            'estimated_time' => ['required','date'],
            'customer_uuid' => ['required','string'],
            'vehicle_uuid' => ['required','string'],
            'mechanic_uuid' => ['nullable','string'],
        ]);

        // RULE: cek apakah kendaraan sudah punya service aktif
        $existing = Service::where('vehicle_uuid', $data['vehicle_uuid'])
            ->whereIn('status', ['pending','in progress'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Kendaraan ini sudah memiliki service aktif yang belum selesai.',
                'existing_service_id' => $existing->id
            ], 422);
        }

        $data['id'] = (string) Str::uuid();
        $data['code'] = $this->generateCode();
        $data['status'] = 'pending';
        $data['acceptance_status'] = 'pending';

        $service = Service::create($data);

        // (opsional) buat log awal & task notifikasi di event/dispatch
        // ServiceLog::create([...]); Task::create([...]);

        return new ServiceResource($service->load(['workshop','customer','vehicle','mechanic','items','log','task']));
    }

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

        $service->update($data);

        // contoh: jika status jadi completed -> bisa set log / hapus task
        // if (isset($data['status']) && $data['status'] === 'completed') { ... }

        return new ServiceResource($service->fresh()->load(['workshop','customer','vehicle','mechanic','items','log','task']));
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted']);
    }
    protected function generateCode()
    {
        return 'SRV-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ServiceController extends Controller
{

    private function assertCanChangeAcceptance(Service $service, string $target)
    {
        $from = $service->acceptance_status;

        if ($from === $target) {
            return; // nggak ada perubahan, aman
        }

        $allowedTargets = [
            'pending'  => ['accepted', 'decline'],
            'accepted' => [],   // final
            'decline'  => [],   // final
        ];

        if (! isset($allowedTargets[$from]) || ! in_array($target, $allowedTargets[$from], true)) {
            abort(422, "Perubahan acceptance_status dari '{$from}' ke '{$target}' tidak diperbolehkan. Status 'accepted' / 'decline' bersifat final.");
        }
    }

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

        //  cek mekanik kalau diisi
        if (!empty($data['mechanic_uuid'])) {
            $employment = Employment::with('user')
                ->active()      // scope status active
                ->mechanic()    // scope role mechanic
                ->where('workshop_uuid', $data['workshop_uuid'])
                ->where('id', $data['mechanic_uuid'])
                ->first();

            if (!$employment) {
                return response()->json([
                    'message' => 'Mekanik tidak valid untuk bengkel ini.'
                ], 422);
            }
        }


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

            // ✅ alasan di update juga
            'reason'             => ['nullable', Rule::in(['antrian sedang full','jadwal bentrok','lokasi terlalu jauh','lainnya'])],
            'reason_description' => ['nullable','string'],

        ]);

        /* ========= 1. LOCK acceptance_status ========= */

        if (array_key_exists('acceptance_status', $data)) {
            $from = $service->acceptance_status;   // nilai sebelum diupdate
            $to   = $data['acceptance_status'];    // nilai yang diminta

            // kalau tidak berubah, ya udah biarin (no-op)
            if ($from !== $to) {
                // HANYA boleh dari 'pending' → 'accepted' atau 'decline'
                $allowedTargets = [
                    'pending' => ['accepted','decline'],
                    'accepted' => [],   // tidak boleh kemana-mana
                    'decline' => [],    // tidak boleh kemana-mana
                ];

                if (! isset($allowedTargets[$from]) || ! in_array($to, $allowedTargets[$from], true)) {
                    return response()->json([
                        'message' => "Perubahan acceptance_status dari '{$from}' ke '{$to}' tidak diperbolehkan. " .
                            "Status 'accepted' atau 'decline' bersifat final."
                    ], 422);
                }
            }
        }

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

        // ✅ kalau mekanik diubah / diisi
        if (array_key_exists('mechanic_uuid', $data) && !empty($data['mechanic_uuid'])) {
            $employment = Employment::with('user')
                ->active()
                ->mechanic()
                ->where('workshop_uuid', $service->workshop_uuid) // bengkel-nya service sekarang
                ->where('id', $data['mechanic_uuid'])
                ->first();

            if (!$employment) {
                return response()->json([
                    'message' => 'Mekanik tidak valid untuk bengkel ini.'
                ], 422);
            }
            //  langsung set status in progress kalau mau
            if ($service->status === 'pending' && !isset($data['status'])) {
                $data['status'] = 'in progress';
            }
        }

        $service->update($data);

        // Jika status berubah ke 'completed' bisa lakukan cleanup: hapus/selesaikan task, buat log akhir, dsb.
        // Contoh:
        // if (isset($data['status']) && $data['status'] === 'completed') { ... }

        return new ServiceResource($service->fresh()->load(['workshop','customer','vehicle','mechanic','items','log','task']));
    }

    public function accept(Request $request, Service $service)
    {
        // pastikan transition allowed
        $this->assertCanChangeAcceptance($service, 'accepted');

        // kalau mau, boleh update jadwal dan harga sekalian
        $data = $request->validate([
            'scheduled_date'  => ['nullable', 'date'],
            'estimated_time'  => ['nullable', 'date'],
            'price'           => ['nullable', 'numeric'],
            'category_service'=> ['nullable', Rule::in(['ringan','sedang','berat','maintenance'])],
        ]);

        $update = array_merge($data, [
            'acceptance_status' => 'accepted',
            'accepted_at'       => now(),
            // status pengerjaan tetap 'pending', nanti pindah ke 'in progress' lewat alur logging
        ]);

        $service->update($update);

        return new ServiceResource(
            $service->fresh()->load(['workshop','customer','vehicle','mechanic','transaction.items','log','task'])
        );
    }

    public function decline(Request $request, Service $service)
    {
        // pastikan transition allowed
        $this->assertCanChangeAcceptance($service, 'decline');

        $data = $request->validate([
            'reason' => [
                'required',
                Rule::in(['antrian sedang full', 'jadwal bentrok', 'lokasi terlalu jauh', 'lainnya']),
            ],
            'reason_description' => ['nullable', 'string'],
        ]);

        $service->update([
            'acceptance_status' => 'decline',
            'reason'            => $data['reason'],
            'reason_description'=> $data['reason_description'] ?? null,
            // status pengerjaan biasanya tetap 'pending' (artinya: request sudah ditolak)
        ]);

        return new ServiceResource(
            $service->fresh()->load(['workshop','customer','vehicle','mechanic','transaction.items','log','task'])
        );
    }

    public function assignMechanic(Request $request, Service $service)
    {
        // Service harus SUDAH accepted, dan BELUM decline
        if ($service->acceptance_status !== 'accepted') {
            return response()->json([
                'message' => "Service harus berstatus acceptance 'accepted' sebelum menetapkan mekanik."
            ], 422);
        }

        $data = $request->validate([
            'mechanic_uuid' => ['required', 'string', 'exists:employments,id'],
        ]);

        // Pastikan employment ada di workshop yg sama
        $employment = \App\Models\Employment::with('user.roles')
            ->where('id', $data['mechanic_uuid'])
            ->where('workshop_uuid', $service->workshop_uuid)
            ->first();

        if (! $employment) {
            return response()->json([
                'message' => 'Mekanik tidak ditemukan di workshop ini.'
            ], 422);
        }

        // Pastikan rolenya benar-benar mechanic (Spatie)
        if (! $employment->user || ! $employment->user->hasRole('mechanic')) {
            return response()->json([
                'message' => 'User ini bukan mekanik yang valid.'
            ], 422);
        }

        // Update service: tetapkan mechanic, boleh sekalian geser status ke "in progress" kalau mau
        $service->update([
            'mechanic_uuid' => $employment->id,
            // opsional: otomatis in progress ketika mekanik ditetapkan
            // 'status'        => 'in progress',
        ]);

        return new ServiceResource(
            $service->fresh()->load(['workshop','customer','vehicle','mechanic','transaction.items','log','task'])
        );
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

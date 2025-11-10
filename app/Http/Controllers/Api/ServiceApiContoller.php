<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceApiContoller extends Controller
{
    /**
     * Ambil daftar ID workshop milik owner yang login.
     */
    private function ownerWorkshopIds(Request $request)
    {
        return $request->user()->workshops()->pluck('id');
    }

    /**
     * Pastikan service milik salah satu workshop owner yang login.
     */
    private function assertOwned(Request $request, Service $service): void
    {
        $ids = $this->ownerWorkshopIds($request);
        abort_unless($ids->contains($service->workshop_uuid), 403, 'Tidak diizinkan');
    }

    /**
     * GET /services
     * Query optional:
     *   - workshop_uuid=...
     *   - status=pending,accept,in progress,completed,cancelled
     *   - code=WO-001
     *   - date_from=YYYY-MM-DD
     *   - date_to=YYYY-MM-DD
     *   - per_page=15 (max 100)
     */
    public function index(Request $request): JsonResponse
    {
        $ownerWorkshopIds = $this->ownerWorkshopIds($request);

        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage < 1 ? 15 : min($perPage, 100);

        $q = Service::query()
            ->whereIn('workshop_uuid', $ownerWorkshopIds) // <-- scope ke owner
            ->with([
                'workshop:id,name',
                'customer:id,name',
                'vehicle:id,plate_number,brand,model,name',
                'mechanic.user:id,name', // <-- ikutkan teknisi
            ])
            ->latest();

        // Validasi workshop_uuid jika dikirim: harus milik owner
        $q->when($request->filled('workshop_uuid'), function ($x) use ($request, $ownerWorkshopIds) {
            $wid = (string) $request->string('workshop_uuid');
            abort_unless($ownerWorkshopIds->contains($wid), 403, 'Workshop bukan milik Anda');
            $x->where('workshop_uuid', $wid);
        });

        $q->when($request->filled('status'), function ($x) use ($request) {
            $statuses = collect(explode(',', $request->string('status')))
                ->map(fn ($s) => trim($s))->filter()->values();
            if ($statuses->isNotEmpty()) $x->whereIn('status', $statuses);
        });

        $q->when($request->filled('code'), fn ($x) =>
        $x->where('code', 'like', '%'.$request->string('code').'%'));

        $q->when($request->filled('date_from'), fn ($x) =>
        $x->whereDate('scheduled_date', '>=', $request->date('date_from')));

        $q->when($request->filled('date_to'), fn ($x) =>
        $x->whereDate('scheduled_date', '<=', $request->date('date_to')));

        $p = $q->paginate($perPage)->appends($request->query());
        $p->getCollection()->transform(fn (Service $s) => $this->mapService($s));

        return response()->json($p, 200);
    }

    /**
     * GET /services/{service}
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        $this->assertOwned($request, $service);

        $service->load([
            'workshop:id,name',
            'customer:id,name',
            'vehicle:id,plate_number,brand,model,name',
            'mechanic.user:id,name',
        ]);

        return response()->json([
            'message' => 'success',
            'data'    => $this->mapService($service),
        ], 200);
    }

    /**
     * POST /services
     * Body minimal untuk dummy:
     * {
     *   "workshop_uuid": "uuid-ws",
     *   "name": "Tune Up",
     *   "description": "dummy",
     *   "price": 150000,
     *   "scheduled_date": "2025-11-01",
     *   "estimated_time": "2025-11-01",
     *   "status": "pending",             // optional (default pending)
     *   "customer_uuid": "uuid-cus",     // optional
     *   "vehicle_uuid": "uuid-veh",      // optional
     *   "mechanic_uuid": "uuid-emp"      // optional
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'workshop_uuid'  => 'required|uuid|exists:workshops,id',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'nullable|numeric|min:0|max:999999.99',
            'scheduled_date' => 'required|date',
            'estimated_time' => 'nullable|date',
            'status'         => 'nullable|in:pending,accept,in progress,completed,cancelled',
            'customer_uuid'  => 'nullable|uuid|exists:customers,id',
            'vehicle_uuid'   => 'nullable|uuid|exists:vehicles,id',
            'mechanic_uuid'  => 'nullable|uuid|exists:employments,id',
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        // Pastikan workshop adalah milik owner yang login
        $ownerWorkshopIds = $this->ownerWorkshopIds($request);
        if (! $ownerWorkshopIds->contains($data['workshop_uuid'])) {
            return response()->json(['message' => 'Workshop bukan milik Anda'], 403);
        }

        // Jika mechanic_uuid dikirim, pastikan employment berada di workshop yang sama
        if (! empty($data['mechanic_uuid'])) {
            $ok = Employment::where('id', $data['mechanic_uuid'])
                ->where('workshop_uuid', $data['workshop_uuid'])
                ->exists();
            if (! $ok) {
                return response()->json([
                    'message' => 'Mechanic tidak ditemukan pada workshop ini'
                ], 422);
            }
        }

        $data['status'] = $data['status'] ?? 'pending';

        // Generate code: WO-XXX-HHMMYY (XXX = urut 3 digit global)
        $service = DB::transaction(function () use ($data) {
            $last = Service::where('code', 'like', 'WO-%')
                ->orderBy('code', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($last && preg_match('/WO-(\d{3})-/', $last->code, $m)) {
                $seq = ((int) $m[1]) + 1;
            }

            $timeSuffix = now('Asia/Jakarta')->format('Hi') . now('Asia/Jakarta')->format('y'); // HHMMYY
            $data['code'] = 'WO-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$timeSuffix;

            return Service::create($data);
        });

        $service->load([
            'workshop:id,name',
            'customer:id,name',
            'vehicle:id,plate_number,brand,model,name',
            'mechanic.user:id,name',
        ]);

        return response()->json([
            'message' => 'created',
            'data'    => $this->mapService($service),
        ], 201);
    }

    /**
     * PUT/PATCH /services/{service}
     * Body: sebagian field (lihat rules)
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $this->assertOwned($request, $service);

        $v = Validator::make($request->all(), [
            'workshop_uuid'  => 'sometimes|required|uuid|exists:workshops,id',
            'name'           => 'sometimes|required|string|max:255',
            'description'    => 'sometimes|nullable|string',
            'price'          => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'scheduled_date' => 'sometimes|required|date',
            'estimated_time' => 'sometimes|nullable|date',
            'status'         => 'sometimes|required|in:pending,accept,in progress,completed,cancelled',
            'customer_uuid'  => 'sometimes|nullable|uuid|exists:customers,id',
            'vehicle_uuid'   => 'sometimes|nullable|uuid|exists:vehicles,id',
            'mechanic_uuid'  => 'sometimes|nullable|uuid|exists:employments,id',
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $v->errors()], 422);
        }

        $dataToUpdate = $v->validated();

        // Validasi transisi status
        if (isset($dataToUpdate['status'])) {
            $from = $service->status;
            $to   = $dataToUpdate['status'];

            $allowed = [
                'pending'     => ['accept', 'cancelled'],
                'accept'      => ['in progress', 'cancelled'],
                'in progress' => ['completed', 'cancelled'],
                'completed'   => [],
                'cancelled'   => [],
            ];

            if (isset($allowed[$from]) && $from !== $to && ! in_array($to, $allowed[$from], true)) {
                return response()->json([
                    'message' => "Transisi status dari '{$from}' ke '{$to}' tidak diperbolehkan."
                ], 422);
            }
        }

        // Jika workshop_uuid mau diubah, pastikan milik owner.
        $ownerWorkshopIds = $this->ownerWorkshopIds($request);
        if (isset($dataToUpdate['workshop_uuid']) && ! $ownerWorkshopIds->contains($dataToUpdate['workshop_uuid'])) {
            return response()->json(['message' => 'Workshop bukan milik Anda'], 403);
        }

        // Jika mechanic_uuid dikirim, pastikan employment ada di workshop target (baru atau lama)
        if (array_key_exists('mechanic_uuid', $dataToUpdate)) {
            $targetWorkshop = $dataToUpdate['workshop_uuid'] ?? $service->workshop_uuid;
            if (! empty($dataToUpdate['mechanic_uuid'])) {
                $ok = Employment::where('id', $dataToUpdate['mechanic_uuid'])
                    ->where('workshop_uuid', $targetWorkshop)
                    ->exists();
                if (! $ok) {
                    return response()->json([
                        'message' => 'Mechanic tidak ditemukan pada workshop ini'
                    ], 422);
                }
            }
        }

        $service->update($dataToUpdate);

        $service->load([
            'workshop:id,name',
            'customer:id,name',
            'vehicle:id,plate_number,brand,model,name',
            'mechanic.user:id,name',
        ]);

        return response()->json([
            'message' => 'Service updated successfully',
            'data'    => $this->mapService($service),
        ], 200);
    }

    /**
     * DELETE /services/{service}
     */
    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->assertOwned($request, $service);
        $service->delete();

        return response()->json(null, 204);
    }

    /**
     * Mapper untuk respon ringkas + relasi.
     */
    private function mapService(Service $s): array
    {
        return [
            'id'             => $s->id,
            'code'           => $s->code,
            'name'           => $s->name,
            'description'    => $s->description,
            'price'          => $s->price,
            'scheduled_date' => optional($s->scheduled_date)->toDateString(),
            'estimated_time' => optional($s->estimated_time)->toDateString(),
            'status'         => $s->status,

            'workshop'       => $s->workshop ? [
                'id'   => $s->workshop->id,
                'name' => $s->workshop->name,
            ] : null,

            'customer'       => $s->customer ? [
                'id'   => $s->customer->id,
                'name' => $s->customer->name,
            ] : null,

            'vehicle'        => $s->vehicle ? [
                'id'           => $s->vehicle->id,
                'plate_number' => $s->vehicle->plate_number,
                'name'         => trim(($s->vehicle->brand ?? '').' '.($s->vehicle->model ?? '').' '.($s->vehicle->name ?? '')),
            ] : null,

            // kirim teknisi (jika ada)
            'mechanic'       => $s->mechanic ? [
                'id'   => $s->mechanic->id,
                'name' => optional($s->mechanic->user)->name,
            ] : null,

            'created_at'     => optional($s->created_at)->toISOString(),
            'updated_at'     => optional($s->updated_at)->toISOString(),
        ];
    }
}

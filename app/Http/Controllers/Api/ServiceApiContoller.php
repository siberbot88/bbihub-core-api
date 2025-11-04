<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceApiContoller extends Controller
{
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
        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage < 1 ? 15 : min($perPage, 100);

        $q = Service::query()
            ->with([
                'workshop:id,name',
                'customer:id,name',
                'vehicle:id,plate_number,brand,model,name',
            ])
            ->latest();

        $q->when($request->filled('workshop_uuid'), fn ($x) =>
        $x->where('workshop_uuid', $request->string('workshop_uuid')));

        $q->when($request->filled('status'), function ($x) use ($request) {
            $statuses = collect(explode(',', $request->string('status')))
                ->map(fn ($s) => trim($s))->filter()->values();
            if ($statuses->isNotEmpty()) $x->whereIn('status', $statuses);
        });

        $q->when($request->filled('code'), fn ($x) =>
        $x->where('code', 'like', '%' . $request->string('code') . '%'));

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
    public function show(Service $service): JsonResponse
    {
        $service->load([
            'workshop:id,name',
            'customer:id,name',
            'vehicle:id,plate_number,brand,model,name',
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
     *   "vehicle_uuid": "uuid-veh"       // optional
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
        ], [
            // pesan custom opsional
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $data['status'] = $data['status'] ?? 'pending';

        // Generate code: WO-XXX-HHMMYY (XXX = urut 3 digit global)
        $service = DB::transaction(function () use ($data) {
            // lock untuk hindari race
            $last = Service::where('code', 'like', 'WO-%')
                ->orderBy('code', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($last && preg_match('/WO-(\d{3})-/', $last->code, $m)) {
                $seq = ((int) $m[1]) + 1;
            }

            $timeSuffix = now('Asia/Jakarta')->format('Hi') . now('Asia/Jakarta')->format('y'); // HHMMYY
            $data['code'] = 'WO-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT) . '-' . $timeSuffix;

            return Service::create($data);
        });

        $service->load(['workshop:id,name','customer:id,name','vehicle:id,plate_number,brand,model,name']);

        return response()->json([
            'message' => 'created',
            'data'    => $this->mapService($service),
        ], 201);
    }

    /**
     * PATCH /services/{service}/status
     * Body: { "status": "accept" | "in progress" | "completed" | "cancelled" | "pending" }
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'status' => 'required|in:pending,accept,in progress,completed,cancelled',
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $from = $service->status;
        $to   = $request->string('status');

        $allowed = [
            'pending'     => ['accept', 'cancelled'],
            'accept'      => ['in progress', 'cancelled'],
            'in progress' => ['completed', 'cancelled'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        if (isset($allowed[$from]) && $from !== $to && !in_array($to, $allowed[$from], true)) {
            return response()->json([
                'message' => "Transisi status dari '{$from}' ke '{$to}' tidak diperbolehkan."
            ], 422);
        }

        $service->update(['status' => $to]);

        return response()->json([
            'message' => 'Status updated',
            'data'    => ['id' => $service->id, 'status' => $service->status],
        ], 200);
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
                'id'    => $s->vehicle->id,
                'plate_number' => $s->vehicle->plate_number,
                'name'  => trim(($s->vehicle->brand ?? '') . ' ' . ($s->vehicle->model ?? '') . ' ' . ($s->vehicle->name ?? '')),
            ] : null,
            'created_at'     => optional($s->created_at)->toISOString(),
            'updated_at'     => optional($s->updated_at)->toISOString(),
        ];
    }
}

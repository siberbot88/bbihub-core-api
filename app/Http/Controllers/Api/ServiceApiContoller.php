<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Service\StoreServiceRequest;
use App\Http\Requests\Api\Service\UpdateServiceRequest;
use App\Models\Service;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceApiContoller extends Controller
{
    /**
     * Helper: untuk owner -> list workshop dia.
     */
    private function ownerWorkshopIds(Request $request)
    {
        return $request->user()->workshops()->pluck('id');
    }

    /**
     * GET /services
     * - owner: lihat service di semua workshop dia
     * - admin: lihat service di workshop tempat dia bekerja
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Service::class);

        $user = $request->user();
        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage < 1 ? 15 : min($perPage, 100);

        $q = Service::query()
            ->with([
                'workshop:id,name',
                'customer:id,name',
                'vehicle:id,plate_number,brand,model,name',
                'mechanic.user:id,name',
            ])
            ->latest();

        if ($user->hasRole('owner')) {
            $ownerWorkshopIds = $this->ownerWorkshopIds($request);
            $q->whereIn('workshop_uuid', $ownerWorkshopIds);

            $q->when($request->filled('workshop_uuid'), function ($x) use ($request, $ownerWorkshopIds) {
                $wid = (string) $request->string('workshop_uuid');
                abort_unless($ownerWorkshopIds->contains($wid), 403, 'Workshop bukan milik Anda');
                $x->where('workshop_uuid', $wid);
            });
        } elseif ($user->hasRole('admin')) {
            $employment = $user->employment;

            if (! $employment) {
                // admin tanpa employment: tidak punya workshop
                $q->whereRaw('1 = 0');
            } else {
                $q->where('workshop_uuid', $employment->workshop_uuid);

                $q->when($request->filled('workshop_uuid'), function ($x) use ($request, $employment) {
                    $wid = (string) $request->string('workshop_uuid');
                    abort_unless($wid === $employment->workshop_uuid, 403, 'Workshop bukan milik Anda');
                    $x->where('workshop_uuid', $wid);
                });
            }
        }

        // filter lain sama seperti sebelumnya
        $q->when($request->filled('status'), function ($x) use ($request) {
            $statuses = collect(explode(',', $request->string('status')))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->values();

            if ($statuses->isNotEmpty()) {
                $x->whereIn('status', $statuses);
            }
        });

        $q->when($request->filled('code'), fn ($x) =>
        $x->where('code', 'like', '%'.$request->string('code').'%')
        );

        $q->when($request->filled('date_from'), fn ($x) =>
        $x->whereDate('scheduled_date', '>=', $request->date('date_from'))
        );

        $q->when($request->filled('date_to'), fn ($x) =>
        $x->whereDate('scheduled_date', '<=', $request->date('date_to'))
        );

        $p = $q->paginate($perPage)->appends($request->query());
        $p->getCollection()->transform(fn (Service $s) => $this->mapService($s));

        return response()->json($p, 200);
    }

    /**
     * GET /services/{service}
     * owner + admin (policy view)
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        $this->authorize('view', $service);

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
     * HANYA admin (policy + middleware).
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Pastikan admin hanya buat di workshop tempat dia bekerja
        $employment = $user->employment;
        if (! $employment || $employment->workshop_uuid !== $data['workshop_uuid']) {
            return response()->json(['message' => 'Workshop bukan milik Anda'], 403);
        }

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

        $service = DB::transaction(function () use ($data) {
            $last = Service::where('code', 'like', 'WO-%')
                ->orderBy('code', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($last && preg_match('/WO-(\d{3})-/', $last->code, $m)) {
                $seq = ((int) $m[1]) + 1;
            }

            $nowJakarta = now('Asia/Jakarta');
            $timeSuffix = $nowJakarta->format('Hi') . $nowJakarta->format('y'); // HHMMYY

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

            'mechanic'       => $s->mechanic ? [
                'id'   => $s->mechanic->id,
                'name' => optional($s->mechanic->user)->name,
            ] : null,

            'created_at'     => optional($s->created_at)->toISOString(),
            'updated_at'     => optional($s->updated_at)->toISOString(),
            ];
        }

    /**
     * PUT/PATCH /services/{service}
     * HANYA admin (di UpdateServiceRequest@authorize + policy).
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $dataToUpdate = $request->validated();

        // Validasi transisi status (sama seperti sebelumnya)
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

        // Validasi workshop & mechanic sama seperti sebelumnya
        if (isset($dataToUpdate['workshop_uuid'])) {
            $user = $request->user();
            $employment = $user->employment;

            if (! $employment || $employment->workshop_uuid !== $dataToUpdate['workshop_uuid']) {
                return response()->json(['message' => 'Workshop bukan milik Anda'], 403);
            }
        }

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
     * HANYA admin (policy delete).
     */
    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return response()->json(null, 204);
    }

    // mapService() tetap sama
}

<?php

namespace App\Services;

use App\Models\Employment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceService
{
    /**
     * Create a new service with generated code.
     */
    public function createService(array $data, User $user): Service
    {
        // Validasi workshop & mechanic
        // Pastikan admin hanya buat di workshop tempat dia bekerja
        $employment = $user->employment;
        if (! $employment || $employment->workshop_uuid !== $data['workshop_uuid']) {
             throw ValidationException::withMessages(['workshop_uuid' => 'Workshop bukan milik Anda']);
        }

        // validasi mekanik (jika diisi)
        if (! empty($data['mechanic_uuid'])) {
            $this->ensureMechanicExistsInWorkshop($data['mechanic_uuid'], $data['workshop_uuid']);
        }

        $data['status'] = $data['status'] ?? 'pending';

        return DB::transaction(function () use ($data) {
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
    }

    /**
     * Update service data and handle status transitions.
     */
    public function updateService(Service $service, array $data, User $user): Service
    {
        if (isset($data['status'])) {
            $this->handleStatusTransition($service, $data['status']);
        }

        // Validasi workshop & mechanic
        if (isset($data['workshop_uuid'])) {
            $employment = $user->employment;

            if (! $employment || $employment->workshop_uuid !== $data['workshop_uuid']) {
                 throw ValidationException::withMessages(['workshop_uuid' => 'Workshop bukan milik Anda']);
            }
        }

        if (array_key_exists('mechanic_uuid', $data)) {
            $targetWorkshop = $data['workshop_uuid'] ?? $service->workshop_uuid;
            if (! empty($data['mechanic_uuid'])) {
                $this->ensureMechanicExistsInWorkshop($data['mechanic_uuid'], $targetWorkshop);
            }
        }

        $service->update($data);

        return $service;
    }

    private function handleStatusTransition(Service $service, string $to): void
    {
        $from = $service->status;
        $allowed = [
            'pending'     => ['accept', 'cancelled'],
            'accept'      => ['in progress', 'cancelled'],
            'in progress' => ['completed', 'cancelled'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        if (isset($allowed[$from]) && $from !== $to && ! in_array($to, $allowed[$from], true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status dari '{$from}' ke '{$to}' tidak diperbolehkan."
            ]);
        }

        $now = now('Asia/Jakarta');

        if ($to === 'accept' && $service->accepted_at === null) {
            $service->accepted_at = $now;
        }

        if ($to === 'completed' && $service->completed_at === null) {
            $service->completed_at = $now;
        }
    }

    private function ensureMechanicExistsInWorkshop(string $mechanicUuid, string $workshopUuid): void
    {
        $ok = Employment::where('id', $mechanicUuid)
            ->where('workshop_uuid', $workshopUuid)
            ->exists();

        if (! $ok) {
            throw ValidationException::withMessages(['mechanic_uuid' => 'Mechanic tidak ditemukan pada workshop ini']);
        }
    }
}

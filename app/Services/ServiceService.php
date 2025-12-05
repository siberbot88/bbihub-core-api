<?php

namespace App\Services;

use App\Models\Employment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceService
{
    /* =========================================================
       CRUD LOGIC (dipakai ServiceApiController)
    ========================================================== */

    /**
     * Create a new service with generated SRV code.
     */
    public function createService(array $data, User $user): Service
    {
        // Pastikan admin hanya create di workshop tempat dia bekerja
        $employment = $user->employment;
        if (! $employment || $employment->workshop_uuid !== $data['workshop_uuid']) {
            throw ValidationException::withMessages([
                'workshop_uuid' => 'Workshop bukan tempat kerja Anda'
            ]);
        }

        // Rule: 1 kendaraan hanya boleh punya 1 service aktif
        $existing = Service::where('vehicle_uuid', $data['vehicle_uuid'])
            ->whereIn('status', ['pending', 'in progress'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'vehicle_uuid' => 'Kendaraan ini sudah memiliki service aktif yang belum selesai.'
            ]);
        }

        // Validasi mechanic jika diisi
        if (! empty($data['mechanic_uuid'])) {
            $this->ensureMechanicExistsInWorkshop($data['mechanic_uuid'], $data['workshop_uuid']);
        }

        // Default value + SRV Code
        $data['id'] = (string) Str::uuid();
        $data['code'] = $this->generateSrvCode();
        $data['status'] = $data['status'] ?? 'pending';
        $data['acceptance_status'] = $data['acceptance_status'] ?? 'pending';

        return DB::transaction(fn () => Service::create($data));
    }

    /**
     * Update service data and handle status & acceptance transitions.
     */
    public function updateService(Service $service, array $data, User $user): Service
    {
        // Admin hanya boleh update service di workshop tempat dia bekerja
        $this->assertAdminWorkshopAccess($service, $user);

        // Validasi jika workshop_uuid mau diubah
        if (isset($data['workshop_uuid'])) {
            $employment = $user->employment;
            if (! $employment || $employment->workshop_uuid !== $data['workshop_uuid']) {
                throw ValidationException::withMessages([
                    'workshop_uuid' => 'Workshop bukan tempat kerja Anda'
                ]);
            }
        }

        // Handle acceptance_status transition kalau dikirim
        if (isset($data['acceptance_status'])) {
            $this->handleAcceptanceTransition($service, $data['acceptance_status'], $data);
        }

        // Validasi mechanic kalau diubah
        if (array_key_exists('mechanic_uuid', $data)) {
            $targetWorkshop = $data['workshop_uuid'] ?? $service->workshop_uuid;
            if (! empty($data['mechanic_uuid'])) {
                $this->ensureMechanicExistsInWorkshop($data['mechanic_uuid'], $targetWorkshop);
            }
        }

        // Handle status transition kalau dikirim
        if (isset($data['status'])) {
            $this->handleStatusTransition($service, $data['status']);
        }

        $service->update($data);

        /* ======================================================
      AUTO-BUAT / SINKRON TRANSAKSI BERDASARKAN STATUS
      ====================================================== */

        // Auto-create transaksi ketika status baru jadi completed
        if ($service->wasChanged('status') && $service->status === 'completed') {
            $this->ensureTransactionCreated($service, $user);
        }
        return $service;
    }

    /* =========================================================
       ADMIN FLOW LOGIC (dipakai AdminController)
    ========================================================== */

    public function acceptService(Service $service, array $data, User $user): Service
    {
        $this->assertAdminWorkshopAccess($service, $user);


        // Validasi mechanic kalau sekalian di-assign saat accept
        if (! empty($data['mechanic_uuid'])) {
            $this->ensureMechanicExistsInWorkshop($data['mechanic_uuid'], $service->workshop_uuid);
        }

        // ✅ accepted langsung auto in progress
        $this->handleStatusTransition($service, 'in progress');

        $patch = array_merge($data, [
            'acceptance_status' => 'accepted',
            'accepted_at'       => now(),
        ]);

        $service->update($patch);

        return $service;
    }

    public function declineService(Service $service, array $data, User $user): Service
    {
        $this->assertAdminWorkshopAccess($service, $user);

        $this->handleAcceptanceTransition($service, 'decline', $data);

        $service->update([
            'acceptance_status'  => 'decline',
            'reason'             => $data['reason'],
            'reason_description' => $data['reason_description'] ?? null,
        ]);

        return $service;
    }

    public function assignMechanic(Service $service, string $mechanicUuid, User $user): Service
    {
        $this->assertAdminWorkshopAccess($service, $user);

        if ($service->acceptance_status !== 'accepted') {
            throw ValidationException::withMessages([
                'acceptance_status' => 'Service harus accepted sebelum menetapkan mekanik.'
            ]);
        }

        $this->ensureMechanicExistsInWorkshop($mechanicUuid, $service->workshop_uuid);


        // ✅ begitu mekanik di-assign, status auto jadi in progress
        $this->handleStatusTransition($service, 'in progress'
        );

        $service->update([
            'mechanic_uuid' => $mechanicUuid,
            'status'        => 'in progress'

        ]);

        return $service;
    }

    /* =========================================================
       INTERNAL HELPERS (mirip Bayu tapi sesuai rules kamu)
    ========================================================== */

    private function assertAdminWorkshopAccess(Service $service, User $user): void
    {
        $employment = $user->employment;
        if (! $employment || $employment->workshop_uuid !== $service->workshop_uuid) {
            throw ValidationException::withMessages([
                'workshop_uuid' => 'Anda tidak punya akses ke service workshop ini'
            ]);
        }
    }

    private function handleAcceptanceTransition(Service $service, string $to, array $data): void
    {
        $from = $service->acceptance_status;

        $allowed = [
            'pending'  => ['accepted', 'decline'],
            'accepted' => [],
            'decline'  => [],
        ];

        if ($from !== $to && ! in_array($to, $allowed[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'acceptance_status' =>
                    "Transisi acceptance_status dari '{$from}' ke '{$to}' tidak diperbolehkan."
            ]);
        }

        if ($to === 'decline') {
            if (empty($data['reason'])) {
                throw ValidationException::withMessages([
                    'reason' => 'reason wajib jika decline'
                ]);
            }

            if (($data['reason'] ?? null) === 'lainnya' && empty($data['reason_description'])) {
                throw ValidationException::withMessages([
                    'reason_description' => 'reason_description wajib jika alasan lainnya'
                ]);
            }
        }

        if ($to === 'accepted' && $service->accepted_at === null) {
            $service->accepted_at = now();
        }
    }

    private function handleStatusTransition(Service $service, string $to): void
    {
        $from = $service->status;

        $allowed = [
            'pending'             => ['in progress', 'completed', 'menunggu pembayaran', 'cancelled'],
            'in progress'         => ['completed', 'cancelled'],
            'completed'           => ['menunggu pembayaran', 'lunas'],
            'menunggu pembayaran' => ['lunas'],
            'lunas'               => [],
            'cancelled'           => [],
        ];

        if ($from !== $to && ! in_array($to, $allowed[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status dari '{$from}' ke '{$to}' tidak diperbolehkan."
            ]);
        }

        if ($to === 'completed' && $service->completed_at === null) {
            $service->completed_at = now();
        }
    }

    private function ensureTransactionCreated(Service $service, User $user): void
    {
        if (empty($service->mechanic_uuid)) {
            throw ValidationException::withMessages([
                'mechanic_uuid' => 'Tidak bisa completed tanpa mekanik.'
            ]);
        }

        if (! $service->transaction) {
            Transaction::create([
                'id'            => (string) Str::uuid(),
                'service_uuid'  => $service->id,
                'customer_uuid' => $service->customer_uuid,
                'workshop_uuid' => $service->workshop_uuid,
                'mechanic_uuid' => $service->mechanic_uuid,
                'admin_uuid'    => $user->id,
                'status'        => 'pending',
                'amount'        => 0,
                'payment_method'=> null,
            ]);
        }
    }

    private function ensureMechanicExistsInWorkshop(string $mechanicUuid, string $workshopUuid): void
    {
        $ok = Employment::active()
            ->mechanic()
            ->where('id', $mechanicUuid)
            ->where('workshop_uuid', $workshopUuid)
            ->exists();

        if (! $ok) {
            throw ValidationException::withMessages([
                'mechanic_uuid' => 'Mekanik tidak ditemukan / tidak aktif di workshop ini.'
            ]);
        }
    }

    private function generateSrvCode(): string
    {
        return 'SRV-' . strtoupper(Str::random(6));
    }
}

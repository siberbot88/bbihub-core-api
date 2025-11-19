<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPolicy
{
    use HandlesAuthorization;

    /**
     * Superadmin full akses semua ability.
     */
    public function before(User $user, string $ability)
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return null;
    }

    /**
     * Bisa lihat daftar voucher?
     * Query tetap kita batasi di controller (by workshop),
     * di sini cuma cek boleh akses fitur voucher atau tidak.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'owner', 'superadmin']);
    }

    /**
     * Bisa lihat satu voucher?
     */
    public function view(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    /**
     * Bisa membuat voucher untuk workshop tertentu?
     *
     * Dipanggil dengan: Gate::authorize('create', [Voucher::class, $workshopUuid])
     */
    public function create(User $user, string $workshopUuid): bool
    {
        return $this->canAccessWorkshop($user, $workshopUuid);
    }

    /**
     * Bisa update voucher?
     */
    public function update(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    /**
     * Bisa delete voucher?
     */
    public function delete(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    /**
     * Helper: cek apakah user boleh akses workshop tertentu
     * berdasarkan role & relasi.
     */
    protected function canAccessWorkshop(User $user, string $workshopUuid): bool
    {
        if ($user->hasRole('owner')) {
            return $user->workshops()
                ->where('id', $workshopUuid)
                ->exists();
        }

        if ($user->hasRole('admin')) {
            $employment = $user->employment;

            return $employment
                && $employment->workshop_uuid === $workshopUuid;
        }

        return false;
    }
}

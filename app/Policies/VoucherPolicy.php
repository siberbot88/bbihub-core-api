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

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin', 'superadmin']);
    }

    public function view(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    public function create(User $user, string $workshopId): bool
    {
        return $this->canAccessWorkshop($user, $workshopId);
    }

    public function update(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    public function delete(User $user, Voucher $voucher): bool
    {
        return $this->canAccessWorkshop($user, $voucher->workshop_uuid);
    }

    protected function canAccessWorkshop(User $user, string $workshopId): bool
    {
        if ($user->hasRole('owner')) {
            return $user->workshops()
                ->where('id', $workshopId)
                ->exists();
        }

        if ($user->hasRole('admin')) {
            $employment = $user->employment;
            return $employment && $employment->workshop_uuid === $workshopId;
        }

        return false;
    }

}

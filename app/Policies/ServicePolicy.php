<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability)
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return null;
    }

    /**
     * Owner & admin boleh lihat daftar service (di workshop dia).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin']);
    }

    /**
     * Owner & admin boleh lihat detail service di workshop dia.
     */
    public function view(User $user, Service $service): bool
    {
        return $this->canAccessWorkshop($user, $service->workshop_uuid);
    }

    /**
     * HANYA admin boleh create service.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * HANYA admin boleh update service di workshop dia.
     */
    public function update(User $user, Service $service): bool
    {
        return $user->hasRole('admin')
            && $this->canAccessWorkshop($user, $service->workshop_uuid);
    }

    /**
     * HANYA admin boleh delete service di workshop dia.
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->hasRole('admin')
            && $this->canAccessWorkshop($user, $service->workshop_uuid);
    }

    /**
     * Helper: cek apakah user punya akses ke workshop service ini.
     *
     * - owner  -> lewat relasi workshops()
     * - admin  -> lewat employment->workshop_uuid
     */
    protected function canAccessWorkshop(User $user, string $workshopId): bool
    {
        if ($user->hasRole('owner')) {
            return $user->workshops()
                ->where('id', $workshopId)
                ->exists();
        }

        if ($user->hasRole('admin')) {
            $employment = $user->employment;

            return $employment
                && $employment->workshop_uuid === $workshopId;
        }

        return false;
    }
}

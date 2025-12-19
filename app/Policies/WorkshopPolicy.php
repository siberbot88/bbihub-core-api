<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workshop;

class WorkshopPolicy
{
    public function view(User $user, Workshop $workshop): bool
    {
        // Admin/Superadmin bisa lihat semua
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }

        // Owner hanya bisa lihat workshop miliknya
        return $user->id === $workshop->user_uuid;
    }

    public function update(User $user, Workshop $workshop): bool
    {
        if ($user->hasRole(['admin', 'superadmin']))
            return true;
        return $user->id === $workshop->user_uuid;
    }

    public function delete(User $user, Workshop $workshop): bool
    {
        if ($user->hasRole(['admin', 'superadmin']))
            return true;
        return $user->id === $workshop->user_uuid;
    }
}

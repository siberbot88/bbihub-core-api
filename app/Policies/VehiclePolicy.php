<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // Aplikasi Mitra Bengkel: Owner/Admin selalu boleh melihat data kendaraan customer
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // Aplikasi Mitra Bengkel: Owner/Admin boleh mengupdate data kendaraan customer
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }
        return false; // Only admin can delete
    }
}

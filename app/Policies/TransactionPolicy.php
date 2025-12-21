<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Admin/Superadmin/Mechanic/Owner akses luas
        if ($user->hasRole(['admin', 'superadmin', 'mechanic'])) {
            return true;
        }

        // Jika user adalah pemilik bengkel dari transaksi tersebut
        if ($user->hasRole('owner')) {
            // Cek apakah transaksi milik workshop yang dimiliki user
            return $user->workshops()->where('id', $transaction->workshop_uuid)->exists();
        }

        // Customer hanya bisa lihat transaksi miliknya -> vehicle -> user_id
        // Atau jika transaction punya user_id langsung
        return $user->id === $transaction->vehicle->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Hanya staff bengkel yang boleh update status/item
        if ($user->hasRole(['admin', 'superadmin', 'mechanic'])) {
            return true;
        }
        if ($user->hasRole('owner')) {
            return $user->workshops()->where('id', $transaction->workshop_uuid)->exists();
        }

        // Customer TIDAK BOLEH update transaksi
        return false;
    }
}

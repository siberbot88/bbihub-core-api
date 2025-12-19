<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionService
{

    public function __construct(
        protected \App\Services\Payment\MidtransService $midtransService
    ) {}

    /**
     * Buat transaksi baru dari service_uuid.
     * Alur sama persis seperti TransactionController lama kamu.
     */
    public function createTransaction(array $data, User $user): Transaction
    {
        $service = Service::with(['customer', 'workshop', 'mechanic.user', 'transaction'])
            ->findOrFail($data['service_uuid']);

        // 1) Pastikan belum ada transaksi
        if ($service->transaction) {
            throw ValidationException::withMessages([
                'service_uuid' => 'Transaksi untuk service ini sudah ada.',
                'transaction_id' => $service->transaction->id,
            ]);
        }

        // 2) Pastikan mekanik ada
        if (empty($service->mechanic_uuid)) {
            throw ValidationException::withMessages([
                'mechanic_uuid' => 'Service belum memiliki mekanik yang ditetapkan.',
            ]);
        }

        // 3) Pastikan admin login
        if (! $user?->id) {
            throw ValidationException::withMessages([
                'auth' => 'Tidak ada admin yang login.',
            ]);
        }

        // 4) Buat transaksi
        return Transaction::create([
            'id'             => (string) Str::uuid(),
            'service_uuid'   => $service->id,
            'customer_uuid'  => $service->customer_uuid,
            'workshop_uuid'  => $service->workshop_uuid,
            'mechanic_uuid'  => $service->mechanic_uuid,
            'admin_uuid'     => $user->id,
            'status'         => 'pending',
            'amount'         => 0,
            'payment_method' => $data['payment_method'] ?? null,
        ]);
    }

    /**
     * Update status transaksi.
     * Aturan tetap sama:
     * - sebelum process/success harus ada item & total > 0
     */

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        // Update payment method
        if (isset($data['payment_method'])) {
            $transaction->payment_method = $data['payment_method'];
        }

        // Update notes atau kolom lain jika ada
        if (isset($data['notes'])) {
            $transaction->notes = $data['notes'];
        }

        // Update amount manual (opsional)
        if (isset($data['amount'])) {
            $transaction->amount = $data['amount'];
        }

        $transaction->save();

        return $transaction;
    }

    public function updateStatus(Transaction $transaction, string $newStatus): Transaction
    {
        $oldStatus = $transaction->status;
        // === RULE: Tidak boleh success kalau belum ada metode pembayaran ===
        if ($newStatus === 'success' && $transaction->payment_method === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran wajib diisi sebelum menyelesaikan transaksi.'
            ]);
        }

        if (in_array($newStatus, ['process', 'success'], true)) {
            $hasItems = $transaction->items()->exists();

            if (! $hasItems) {
                throw ValidationException::withMessages([
                    'items' => 'Transaksi belum memiliki item. Tambahkan item dulu sebelum mengubah status.'
                ]);
            }

            $total = $transaction->items()->sum('subtotal');
            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Total transaksi masih 0. Pastikan harga & quantity item sudah benar.'
                ]);
            }

            if ($transaction->amount != $total) {
                $transaction->amount = $total;
            }
        }

        $transaction->status = $newStatus;
        $transaction->save();

        // === MIDTRANS INTEGRATION ===
        // Jika status naik ke process (Menunggu Pembayaran) dan belum ada token
        if ($newStatus === 'process' && $transaction->amount > 0) {
            // Generate token jika belum ada
            if (empty($transaction->snap_token)) {
                $snap = $this->midtransService->getSnapToken($transaction);
                $transaction->update([
                   'snap_token' => $snap->token,
                   'snap_redirect_url' => $snap->redirect_url
                ]);
            }
        }

        // === SINKRON KE SERVICE ===
        $service = $transaction->service;

        if ($service) {
            // Kalau invoice sudah siap dan menunggu bayar
            if ($newStatus === 'process') {
                // Jangan paksa kalau sudah lunas (misal diubah mundur)
                if ($service->status !== 'lunas') {
                    $service->update([
                        'status' => 'menunggu pembayaran',
                    ]);
                }
            }

            // Kalau transaksi sudah sukses dibayar
            if ($newStatus === 'success') {
                $service->update([
                    'status' => 'lunas',
                ]);
            }
        }


        return $transaction;
    }

    /**
     * Finalize transaksi -> success.
     * Sama seperti finalize lama kamu.
     */
    public function finalizeTransaction(Transaction $transaction): Transaction
    {
        $transaction->load(['items', 'service']);

        if ($transaction->status === 'success') {
            throw ValidationException::withMessages([
                'status' => 'Transaksi sudah berstatus success.'
            ]);
        }

        if ($transaction->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'Tidak bisa finalisasi transaksi tanpa item.'
            ]);
        }

        $transaction->update([
            'status' => 'success',
        ]);

        // sinkron status service (kalau kamu masih pakai enum waiting_payment/paid)
        if ($transaction->service && in_array($transaction->service->status, ['waiting_payment', 'completed'])) {
            $transaction->service->update([
                'status' => 'paid',
            ]);
        }

        return $transaction;
    }
}

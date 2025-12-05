<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionItemService
{
    /**
     * Tambah item ke transaksi.
     */
    public function addItem(Transaction $transaction, array $data): Transaction
    {
        $subtotal = $data['price'] * $data['quantity'];

        TransactionItem::create([
            'id'               => (string) Str::uuid(),
            'transaction_uuid' => $transaction->id,
            'service_uuid'     => $data['service_uuid'] ?? $transaction->service_uuid,
            'name'             => $data['name'],
            'service_type'     => $data['service_type'],
            'price'            => $data['price'],
            'quantity'         => $data['quantity'],
            'subtotal'         => $subtotal,
        ]);

        $this->recalculateAmount($transaction);

        return $transaction->fresh();
    }

    /**
     * Update item dalam transaksi.
     */
    public function updateItem(Transaction $transaction, TransactionItem $item, array $data): Transaction
    {
        // Pastikan item milik transaksi ini
        if ($item->transaction_uuid !== $transaction->id) {
            throw ValidationException::withMessages([
                'item' => 'Item tidak termasuk transaksi ini.',
            ]);
        }

        // kalau price/quantity berubah → hitung ulang subtotal
        if (array_key_exists('price', $data) || array_key_exists('quantity', $data)) {
            $price    = $data['price']    ?? $item->price;
            $quantity = $data['quantity'] ?? $item->quantity;
            $data['subtotal'] = $price * $quantity;
        }

        $item->update($data);

        $this->recalculateAmount($transaction);

        return $transaction->fresh();
    }

    /**
     * Hapus item dari transaksi.
     */
    public function deleteItem(Transaction $transaction, TransactionItem $item): Transaction
    {
        if ($item->transaction_uuid !== $transaction->id) {
            throw ValidationException::withMessages([
                'item' => 'Item tidak termasuk transaksi ini.',
            ]);
        }

        $item->delete();

        $this->recalculateAmount($transaction);

        return $transaction->fresh();
    }

    /**
     * Hitung ulang total amount transaksi dari semua item.
     */
    private function recalculateAmount(Transaction $transaction): void
    {
        $total = $transaction->items()->sum('subtotal');
        $transaction->update(['amount' => $total]);
    }
}

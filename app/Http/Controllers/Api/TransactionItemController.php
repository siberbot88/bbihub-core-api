<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransactionItemController extends Controller
{
    // POST /api/v1/transactions/{transaction}/items
    public function store(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'service_uuid' => 'nullable|string|exists:services,id',
            'service_type' => [
                'required',
                Rule::in([
                    'servis ringan',
                    'servis sedang',
                    'servis berat',
                    'sparepart',
                    'biaya tambahan',
                    'lainnya',
                ]),
            ],
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

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

        // Update total amount
        $total = $transaction->items()->sum('subtotal');
        $transaction->update(['amount' => $total]);

        return new TransactionResource(
            $transaction->fresh()->load(['items', 'service'])
        );
    }

    // PATCH /api/v1/transactions/{transaction}/items/{item}
    public function update(Request $request, Transaction $transaction, TransactionItem $item)
    {
        // Pastikan item memang milik transaksi ini
        if ($item->transaction_uuid !== $transaction->id) {
            return response()->json(['message' => 'Item tidak termasuk transaksi ini'], 404);
        }

        $data = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'service_type' => [
                'sometimes',
                'required',
                Rule::in([
                    'servis ringan',
                    'servis sedang',
                    'servis berat',
                    'sparepart',
                    'biaya tambahan',
                    'lainnya',
                ]),
            ],
            'price'    => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        // kalau price/quantity berubah, hitung lagi subtotal
        if (array_key_exists('price', $data) || array_key_exists('quantity', $data)) {
            $price    = $data['price']    ?? $item->price;
            $quantity = $data['quantity'] ?? $item->quantity;
            $data['subtotal'] = $price * $quantity;
        }

        $item->update($data);

        // update total amount transaksi
        $total = $transaction->items()->sum('subtotal');
        $transaction->update(['amount' => $total]);

        return new TransactionResource(
            $transaction->fresh()->load(['items', 'service'])
        );
    }

    // DELETE /api/v1/transactions/{transaction}/items/{item}
    public function destroy(Transaction $transaction, TransactionItem $item)
    {
        if ($item->transaction_uuid !== $transaction->id) {
            return response()->json(['message' => 'Item tidak termasuk transaksi ini'], 404);
        }

        $item->delete();

        $total = $transaction->items()->sum('subtotal');
        $transaction->update(['amount' => $total]);

        return new TransactionResource(
            $transaction->fresh()->load(['items', 'service'])
        );
    }
}

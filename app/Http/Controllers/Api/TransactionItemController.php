<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionItemResource;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class TransactionItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_uuid' => 'required|string|exists:transactions,id',
            'name' => 'required|string',
            'service_type' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
        ]);

        $item = TransactionItem::create([
            'id' => Str::uuid(),
            'transaction_uuid' => $data['transaction_uuid'],
            'name' => $data['name'],
            'service_type' => $data['service_type'],
            'price' => $data['price'],
            'quantity' => $data['quantity'],
            'subtotal' => $data['price'] * $data['quantity']
        ]);

        // update total transaction
        $transaction = Transaction::find($data['transaction_uuid']);
        $transaction->amount = $transaction->items()->sum('subtotal');
        $transaction->save();
        return new TransactionItemResource($item);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_uuid' => 'required|string|exists:services,id',
            'payment_method' => 'nullable|string'
        ]);

        $service = Service::findOrFail($data['service_uuid']);

        // Buat transaksi
        $trx = Transaction::create([
            'id' => Str::uuid(),
            'service_uuid' => $service->id,
            'customer_uuid' => $service->customer_uuid,
            'workshop_uuid' => $service->workshop_uuid,
            'mechanic_uuid' => $service->mechanic_uuid,
            'admin_uuid' => auth()->id(),
            'status' => 'pending',
            'amount' => 0,
            'payment_method' => $data['payment_method'] ?? null
        ]);

        return new TransactionResource($trx->load('items'));
    }


    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction->load('items'));
    }


    public function updateStatus(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,paid,cancelled'
        ]);

        $transaction->update($data);

        return new TransactionResource($transaction->load('items'));
    }
}

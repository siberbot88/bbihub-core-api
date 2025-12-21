<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * POST /admins/transactions/{transaction}/invoice
     * Create invoice from transaction
     */
    public function store(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        // Check if invoice already exists
        if ($transaction->invoice) {
            return response()->json([
                'message' => 'Invoice sudah ada untuk transaksi ini.',
                'data' => new InvoiceResource($transaction->invoice->load(['transaction.items', 'transaction.service.customer', 'transaction.service.vehicle'])),
            ], 200);
        }

        // Validate transaction has items and amount
        if ($transaction->items()->count() === 0) {
            return response()->json([
                'message' => 'Transaksi belum memiliki item.',
            ], 422);
        }

        // Calculate total from items
        $total = $transaction->items()->sum('subtotal');

        // Generate invoice code
        $invoiceCode = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        // Create invoice
        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'transaction_uuid' => $transaction->id,
            'code' => $invoiceCode,
            'amount' => $total,
            'due_date' => now()->addDays(7),
            'paid_at' => null,
        ]);

        // Update transaction amount if different
        if ($transaction->amount != $total) {
            $transaction->update(['amount' => $total]);
        }

        return response()->json([
            'message' => 'Invoice berhasil dibuat.',
            'data' => new InvoiceResource($invoice->load(['transaction.items', 'transaction.service.customer', 'transaction.service.vehicle', 'transaction.service.workshop'])),
        ], 201);
    }

    /**
     * GET /admins/invoices/{invoice}
     * Get invoice details
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'transaction.items',
            'transaction.service.customer',
            'transaction.service.vehicle',
            'transaction.service.workshop',
            'transaction.mechanic.user',
        ]);

        return response()->json([
            'message' => 'success',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * PATCH /admins/invoices/{invoice}/mark-paid
     * Mark invoice as paid
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:QRIS,Cash,Bank',
        ]);

        $invoice->update([
            'paid_at' => now(),
        ]);

        // Update transaction status
        $transaction = $invoice->transaction;
        if ($transaction) {
            $transaction->update([
                'status' => 'success',
                'payment_method' => $data['payment_method'],
            ]);

            // Update service status
            if ($transaction->service) {
                $transaction->service->update([
                    'status' => 'lunas',
                ]);
            }
        }

        return response()->json([
            'message' => 'Invoice telah ditandai lunas.',
            'data' => new InvoiceResource($invoice->fresh()->load(['transaction.items', 'transaction.service'])),
        ]);
    }
}

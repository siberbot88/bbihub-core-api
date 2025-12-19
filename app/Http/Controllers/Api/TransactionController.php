<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {
    }

    // POST /api/v1/transactions
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_uuid' => 'required|string|exists:services,id',
            'payment_method' => 'nullable|string',
        ]);

        try {
            $trx = $this->transactionService->createTransaction($data, $request->user());

            // Audit log - transaction created
            AuditLog::log(
                event: 'transaction_created',
                user: $request->user(),
                auditable: $trx,
                newValues: [
                    'service_id' => $trx->service_id,
                    'amount' => $trx->amount,
                    'status' => $trx->status
                ]
            );

            return (new TransactionResource($trx->load(['service', 'items'])))
                ->response()
                ->setStatusCode(201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // GET /api/v1/transactions/{transaction}
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource(
            $transaction->load(['service', 'items'])
        );
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $data = $request->validate([
            'payment_method' => 'nullable|in:QRIS,Cash,Bank',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->transactionService->updateTransaction($transaction, $data);

        return new TransactionResource(
            $updated->fresh()->load(['items', 'service'])
        );
    }


    // PATCH /api/v1/transactions/{transaction}/status
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $data = $request->validate([
            'status' => 'required|in:pending,process,success',
        ]);

        try {
            $oldStatus = $transaction->status;
            $updated = $this->transactionService->updateStatus($transaction, $data['status']);

            // Audit log - status changed
            AuditLog::log(
                event: 'transaction_status_changed',
                user: $request->user(),
                auditable: $updated,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $updated->status]
            );

            return new TransactionResource(
                $updated->fresh()->load(['items', 'service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // POST /api/v1/transactions/{transaction}/finalize
    public function finalize(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        try {
            $updated = $this->transactionService->finalizeTransaction($transaction);

            // Audit log - finalized
            AuditLog::log(
                event: 'transaction_finalized',
                user: auth()->user(),
                auditable: $updated,
                newValues: ['status' => $updated->status]
            );

            return new TransactionResource(
                $updated->fresh()->load(['items', 'service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }
}

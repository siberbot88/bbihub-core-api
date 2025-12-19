<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
<<<<<<< HEAD
=======
use App\Models\AuditLog;
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
<<<<<<< HEAD
    ) {}
=======
    ) {
    }
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac

    // POST /api/v1/transactions
    public function store(Request $request)
    {
        $data = $request->validate([
<<<<<<< HEAD
            'service_uuid'   => 'required|string|exists:services,id',
=======
            'service_uuid' => 'required|string|exists:services,id',
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            'payment_method' => 'nullable|string',
        ]);

        try {
            $trx = $this->transactionService->createTransaction($data, $request->user());

<<<<<<< HEAD
            return (new TransactionResource($trx->load(['service', 'items'])))
                ->response()
                ->setStatusCode(201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
=======
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
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            ], 422);
        }
    }

    // GET /api/v1/transactions/{transaction}
    public function show(Transaction $transaction)
    {
<<<<<<< HEAD
        return new TransactionResource(
            $transaction->load(['service', 'items'])
        );
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:QRIS,Cash,Bank',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->transactionService->updateTransaction($transaction, $data);

        return new TransactionResource(
            $updated->fresh()->load(['items','service'])
        );
    }


    // PATCH /api/v1/transactions/{transaction}/status
    public function updateStatus(Request $request, Transaction $transaction)
=======
        $this->authorize('view', $transaction);

        return new TransactionResource(
            $transaction->load(['service', 'items'])
        );
    }

    public function update(Request $request, Transaction $transaction)
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
    {
        $this->authorize('update', $transaction);

        $data = $request->validate([
<<<<<<< HEAD
            'status' => 'required|in:pending,process,success',
        ]);

        try {
            $updated = $this->transactionService->updateStatus($transaction, $data['status']);

            return new TransactionResource(
                $updated->fresh()->load(['items','service'])
=======
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
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
<<<<<<< HEAD
                'errors'  => $e->errors(),
=======
                'errors' => $e->errors(),
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            ], 422);
        }
    }

    // POST /api/v1/transactions/{transaction}/finalize
<<<<<<< HEAD
    public function finalize(Transaction $transaction)
    {
        try {
            $updated = $this->transactionService->finalize($transaction);

            return new TransactionResource(
                $updated->fresh()->load(['items','service'])
=======
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
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
<<<<<<< HEAD
                'errors'  => $e->errors(),
=======
                'errors' => $e->errors(),
>>>>>>> b84dd13a3bf307131c996e87af72f8b5dd8805ac
            ], 422);
        }
    }
}

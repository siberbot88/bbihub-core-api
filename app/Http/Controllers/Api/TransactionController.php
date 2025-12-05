<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    // POST /api/v1/transactions
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_uuid'   => 'required|string|exists:services,id',
            'payment_method' => 'nullable|string',
        ]);

        try {
            $trx = $this->transactionService->createTransaction($data, $request->user());

            return (new TransactionResource($trx->load(['service', 'items'])))
                ->response()
                ->setStatusCode(201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // GET /api/v1/transactions/{transaction}
    public function show(Transaction $transaction)
    {
        return new TransactionResource(
            $transaction->load(['service', 'items'])
        );
    }

    // PATCH /api/v1/transactions/{transaction}/status
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,process,success',
        ]);

        try {
            $updated = $this->transactionService->updateStatus($transaction, $data['status']);

            return new TransactionResource(
                $updated->fresh()->load(['items','service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // POST /api/v1/transactions/{transaction}/finalize
    public function finalize(Transaction $transaction)
    {
        try {
            $updated = $this->transactionService->finalize($transaction);

            return new TransactionResource(
                $updated->fresh()->load(['items','service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }
}

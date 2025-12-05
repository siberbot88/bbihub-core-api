<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionItemController extends Controller
{
    public function __construct(
        protected TransactionItemService $transactionItemService
    ) {}

    // POST /api/v1/transactions/{transaction}/items
    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_uuid' => 'required|string|exists:transactions,id',
            'service_uuid'     => 'nullable|string|exists:services,id',
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

        // Ambil transaksi yang valid
        $transaction = Transaction::findOrFail($data['transaction_uuid']);


        try {
            $trx = $this->transactionItemService->addItem($transaction, $data);

            return new TransactionResource(
                $trx->load(['items', 'service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // PATCH /api/v1/transactions/{transaction}/items/{item}
    public function update(Request $request, Transaction $transaction, TransactionItem $item)
    {
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

        try {
            $trx = $this->transactionItemService->updateItem($transaction, $item, $data);

            return new TransactionResource(
                $trx->load(['items', 'service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // DELETE /api/v1/transactions/{transaction}/items/{item}
    public function destroy(Transaction $transaction, TransactionItem $item)
    {
        try {
            $trx = $this->transactionItemService->deleteItem($transaction, $item);

            return new TransactionResource(
                $trx->load(['items', 'service'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }
}

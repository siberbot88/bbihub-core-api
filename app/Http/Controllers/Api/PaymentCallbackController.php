<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function __construct(protected MidtransService $midtransService)
    {
    }

    public function handle(Request $request)
    {
        try {
            $notification = $this->midtransService->handleNotification();

            $orderId = $notification['order_id'];
            $status  = $notification['status'];

            $transaction = Transaction::where('id', $orderId)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Update status based on Midtrans status
            // mapping:
            // success -> success
            // pending -> pending (or process in our app)
            // failed, expired, cancelled -> cancelled (maybe?)

            if ($status === 'success') {
                // Update to success
                $transaction->update([
                    'status' => 'success',
                ]);

                // Sync Service status
                if ($transaction->service) {
                    $transaction->service->update(['status' => 'lunas']);
                }

            } elseif (in_array($status, ['failed', 'expired', 'cancelled'])) {
                // Should we mark as cancelled or just ignored?
                // Let's mark as cancelled for clarity? Or keep as process?
                // For now, log it.
                Log::info("Payment for {$orderId} failed/expired: {$status}");
            }

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }
}

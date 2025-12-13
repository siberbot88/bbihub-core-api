<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OwnerSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Midtrans Webhook Received', $request->all());

        $payload = $request->all();
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $signatureKey = $payload['signature_key'];
        $transactionStatus = $payload['transaction_status'];
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        // Fraud status is optional
        $fraudStatus = $payload['fraud_status'] ?? null;

        // Verify Signature
        $serverKey = config('midtrans.server_key');
        $mySignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($mySignature !== $signatureKey) {
            Log::warning("Midtrans Invalid Signature: $orderId");
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Find Subscription
        // Order ID format: SUBS-{user_uuid}-{timestamp} OR we might store it in order_id column
        // In OwnerSubscriptionService we generated orderId. 
        // We should query by order_id column.
        $subscription = OwnerSubscription::where('order_id', $orderId)->first();

        if (!$subscription) {
            Log::error("Subscription not found for Order ID: $orderId");
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        // 🎁 TRIAL ACTIVATION LOGIC
        // Detect trial orders (starts with 'TRIAL-' or has gross_amount = 0)
        $isTrial = str_starts_with($orderId, 'TRIAL-') || (int)$grossAmount === 0;
        
        if ($isTrial && ($transactionStatus === 'settlement' || $transactionStatus === 'capture')) {
            // Activate trial for user
            $user = $subscription->user;
            if ($user) {
                $user->update([
                    'trial_used' => true,
                    'trial_ends_at' => now()->addDays(7),
                ]);
                Log::info("Trial activated for user {$user->id}, order: $orderId");
            }
        }

        // Current status logic
        $newStatus = null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $newStatus = 'pending'; // Challenge by FDS
            } else {
                $newStatus = 'active'; // Success
            }
        } else if ($transactionStatus == 'settlement') {
            $newStatus = 'active'; // Success
        } else if ($transactionStatus == 'pending') {
            $newStatus = 'pending';
        } else if ($transactionStatus == 'deny') {
            $newStatus = 'cancelled';
        } else if ($transactionStatus == 'expire') {
            $newStatus = 'expired';
        } else if ($transactionStatus == 'cancel') {
            $newStatus = 'cancelled';
        }

        if ($newStatus) {
            $subscription->status = $newStatus;
            $subscription->transaction_id = $transactionId;
            $subscription->payment_type = $paymentType;
            $subscription->gross_amount = $grossAmount;
            // $subscription->pdf_url = ... (Midtrans doesn't send PDF URL in webhook directly usually, but maybe we can construct it or ignore)
            
            $subscription->save();
            Log::info("Subscription $orderId updated to $newStatus");
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}

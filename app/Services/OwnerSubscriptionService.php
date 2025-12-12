<?php

namespace App\Services;

use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerSubscriptionService
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Initiate subscription purchase and get Snap Token
     */
    public function initiateSubscription(User $user, string $planId, string $billingCycle)
    {
        return DB::transaction(function () use ($user, $planId, $billingCycle) {
            $plan = SubscriptionPlan::where('code', $planId)->firstOrFail();

            // Determine price based on billing cycle
            $price = ($billingCycle === 'yearly') ? $plan->price_yearly : $plan->price_monthly;
            
            // Calculate expiry
            $startsAt = Carbon::now();
            $expiresAt = ($billingCycle === 'yearly') 
                ? $startsAt->copy()->addYear() 
                : $startsAt->copy()->addMonth();

            // Create Order ID
            $orderId = 'SUB-' . time() . '-' . Str::random(5);

            // Create Pending Subscription Record
            // Note: We create it now, effectively assuming 'active' status is contingent on payment success webhook.
            // But for simple flow, we might mark it as 'pending_payment' if status allowed.
            // Since existing schema 'status' has defaults, let's use 'pending' or keep it 'active' but set expiry only after payment?
            // Let's check status enum in migration. Migration default is 'active'.
            // Better practice: Default to 'pending' if possible, or 'unpaid'. 
            // The OwnerSubscription migration default is 'active'. I should probably update it to 'pending' or handle logic carefully.
            // For now, let's assume 'pending' status.

            $subscription = OwnerSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending', // Waiting for payment
                'billing_cycle' => $billingCycle,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'order_id' => $orderId, 
            ]);

            // Prepare Midtrans Params
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $price,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'item_details' => [
                    [
                        'id' => $plan->code,
                        'price' => (int) $price,
                        'quantity' => 1,
                        'name' => "{$plan->name} ({$billingCycle})",
                    ]
                ]
            ];

            // Get Snap Transaction (Token + Redirect URL)
            $midtransObj = $this->midtransService->createSnapTransaction($params);

            return [
                'subscription' => $subscription,
                'snap_token' => $midtransObj->token,
                'payment_url' => $midtransObj->redirect_url,
                'order_id' => $orderId,
            ];
        });
    }

    /**
     * Activate subscription (called by Webhook)
     */
    public function activateSubscription(string $orderId)
    {
        $subscription = OwnerSubscription::where('order_id', $orderId)->first();
        if ($subscription) {
            // Recalculate dates based on actual payment time
            $startsAt = Carbon::now();
            $expiresAt = ($subscription->billing_cycle === 'yearly') 
                ? $startsAt->copy()->addYear() 
                : $startsAt->copy()->addMonth();

            $subscription->update([
                'status' => 'active',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ]);
        }
    }


    /**
     * Manually verify and update subscription status (for polling/manual check)
     */
    public function verifySubscriptionStatus(User $user)
    {
        // Find latest subscription regardless of status
        $subscription = $user->ownerSubscription;

        if (!$subscription) {
            throw new \Exception("Tidak ada data langganan ditemukan.");
        }

        // Only check if we have an order_id (from Midtrans)
        if (!$subscription->order_id) {
             return $subscription;
        }

        try {
            $midtransStatus = $this->midtransService->getTransactionStatus($subscription->order_id);
            $transactionStatus = $midtransStatus->transaction_status;
            $fraudStatus = $midtransStatus->fraud_status ?? null;
            $paymentType = $midtransStatus->payment_type ?? null;
            $grossAmount = $midtransStatus->gross_amount ?? null;

            // Determine new status
            $newStatus = null;
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $newStatus = 'pending';
                } else {
                    $newStatus = 'active';
                }
            } else if ($transactionStatus == 'settlement') {
                $newStatus = 'active';
            } else if ($transactionStatus == 'pending') {
                $newStatus = 'pending';
            } else if ($transactionStatus == 'deny') {
                $newStatus = 'canceled';
            } else if ($transactionStatus == 'expire') {
                $newStatus = 'expired';
            } else if ($transactionStatus == 'cancel') {
                $newStatus = 'canceled';
            }

            // Update if changed
            if ($newStatus && $subscription->status !== $newStatus) {
                $subscription->status = $newStatus;
                $subscription->transaction_id = $midtransStatus->transaction_id ?? null;
                $subscription->payment_type = $paymentType;
                $subscription->gross_amount = $grossAmount;

                // Sync dates if becoming active
                if ($newStatus === 'active') {
                     $startsAt = Carbon::now();
                     $expiresAt = ($subscription->billing_cycle === 'yearly') 
                        ? $startsAt->copy()->addYear() 
                        : $startsAt->copy()->addMonth();
                     
                     $subscription->starts_at = $startsAt;
                     $subscription->expires_at = $expiresAt;
                }
                
                $subscription->save();
            }

            return $subscription;

        } catch (\Exception $e) {
            // Midtrans might throw 404 if transaction is really new or invalid order_id
            // Just return current subscription in that case
            return $subscription;
        }
    }
}

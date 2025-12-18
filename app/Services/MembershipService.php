<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerMembership;
use App\Models\Membership;
use App\Models\MembershipTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function __construct(
        private MidtransService $midtransService
    ) {}

    /**
     * Create membership purchase and get Midtrans Snap Token
     */
    public function initiatePurchase(
        Customer $customer,
        string $membershipId
    ): array {
        return DB::transaction(function () use ($customer, $membershipId) {
            $membership = Membership::findOrFail($membershipId);
            
            // Check if customer already has active membership
            $existingMembership = CustomerMembership::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();
                
            if ($existingMembership) {
                throw new \Exception('Customer already has an active membership');
            }
            
            // Create pending customer membership
            $startDate = now();
            $expiresAt = $startDate->copy()->addMonths($membership->duration_months);
            
            $customerMembership = CustomerMembership::create([
                'customer_id' => $customer->id,
                'membership_id' => $membership->id,
                'workshop_id' => $membership->workshop_id,
                'status' => 'pending', // Will be activated after payment
                'started_at' => $startDate,
                'expires_at' => $expiresAt,
                'auto_renew' => false,
                'total_points' => 0,
            ]);
            
            // Generate unique order ID
            $orderId = 'MEMBERSHIP-' . time() . '-' . Str::random(6);
            
            // Create transaction record
            $transaction = MembershipTransaction::create([
                'customer_membership_id' => $customerMembership->id,
                'customer_id' => $customer->id,
                'membership_id' => $membership->id,
                'amount' => $membership->price,
                'payment_status' => 'pending',
                'transaction_date' => now(),
                'midtrans_order_id' => $orderId,
            ]);
            
            // Prepare Midtrans transaction details
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $membership->price,
                ],
                'item_details' => [
                    [
                        'id' => $membership->id,
                        'price' => (int) $membership->price,
                        'quantity' => 1,
                        'name' => $membership->name . ' Membership (' . $membership->duration_months . ' months)',
                    ],
                ],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'email' => $customer->email ?? 'noemail@example.com',
                    'phone' => $customer->phone,
                ],
            ];
            
            // Get Snap Token
            $snapToken = $this->midtransService->createSnapToken($params);
            
            // Update transaction with snap token
            $transaction->update([
                'midtrans_snap_token' => $snapToken,
            ]);
            
            return [
                'transaction' => $transaction,
                'customer_membership' => $customerMembership,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ];
        });
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handleNotification(): array
    {
        $notification = $this->midtransService->verifyNotification();
        
        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentType = $notification->payment_type;
        
        // Find transaction
        $transaction = MembershipTransaction::where('midtrans_order_id', $orderId)->firstOrFail();
        
        // Update transaction with Midtrans response
        $transaction->update([
            'midtrans_transaction_id' => $notification->transaction_id ?? null,
            'payment_type' => $paymentType,
            'midtrans_response' => json_encode($notification),
        ]);
        
        $customerMembership = $transaction->customerMembership;
        
        // Handle different transaction statuses
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $this->activateMembership($transaction, $customerMembership);
            }
        } elseif ($transactionStatus == 'settlement') {
            $this->activateMembership($transaction, $customerMembership);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->cancelMembership($transaction, $customerMembership);
        } elseif ($transactionStatus == 'pending') {
            // Payment pending, do nothing
            $transaction->update(['payment_status' => 'pending']);
        }
        
        return [
            'status' => 'success',
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
        ];
    }

    /**
     * Activate membership after successful payment
     */
    private function activateMembership(
        MembershipTransaction $transaction,
        CustomerMembership $customerMembership
    ): void {
        DB::transaction(function () use ($transaction, $customerMembership) {
            // Update transaction status
            $transaction->update([
                'payment_status' => 'completed',
                'paid_at' => now(),
                'confirmed_at' => now(),
            ]);
            
            // Activate membership
            $customerMembership->update([
                'status' => 'active',
            ]);
            
            // TODO: Send notification to customer
            // TODO: Send welcome email with membership benefits
        });
    }

    /**
     * Cancel membership if payment failed
     */
    private function cancelMembership(
        MembershipTransaction $transaction,
        CustomerMembership $customerMembership
    ): void {
        DB::transaction(function () use ($transaction, $customerMembership) {
            $transaction->update([
                'payment_status' => 'failed',
            ]);
            
            $customerMembership->update([
                'status' => 'cancelled',
            ]);
        });
    }

    /**
     * Calculate discount for a given amount
     */
    public function calculateDiscount(Customer $customer, float $amount): float
    {
        $membership = $customer->activeMembership;
        
        if (!$membership || !$membership->isActive()) {
            return 0;
        }
        
        $discountPercentage = $membership->membership->discount_percentage;
        return $amount * ($discountPercentage / 100);
    }

    /**
     * Add points for transaction
     */
    public function addPointsForTransaction(Customer $customer, float $transactionAmount, string $transactionId): void
    {
        $membership = $customer->activeMembership;
        
        if (!$membership || !$membership->isActive()) {
            return;
        }
        
        $multiplier = $membership->membership->points_multiplier;
        $points = (int) floor($transactionAmount * $multiplier);
        
        if ($points > 0) {
            $membership->addPoints(
                $points,
                "Points earned from transaction",
                $transactionId
            );
        }
    }
}

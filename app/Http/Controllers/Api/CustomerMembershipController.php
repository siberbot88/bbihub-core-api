<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerMembership;
use App\Models\MembershipTransaction;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerMembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}
    
    /**
     * Get customer's active membership
     */
    public function show(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        
        $membership = CustomerMembership::with(['membership', 'workshop'])
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active membership found',
                'data' => null,
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'membership' => $membership,
                'days_remaining' => $membership->daysRemaining(),
                'is_active' => $membership->isActive(),
                'benefits' => $membership->membership->benefits,
                'total_points' => $membership->total_points,
            ],
        ]);
    }
    
    /**
     * Purchase membership (initiate payment)
     */
    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'membership_id' => 'required|exists:memberships,id',
        ]);
        
        $customer = $request->user()->customer;
        
        try {
            $result = $this->membershipService->initiatePurchase(
                $customer,
                $validated['membership_id']
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'order_id' => $result['order_id'],
                    'transaction_id' => $result['transaction']->id,
                    'amount' => $result['transaction']->amount,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Cancel active membership
     */
    public function cancel(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        
        $membership = CustomerMembership::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->first();
            
        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active membership to cancel',
            ], 404);
        }
        
        $membership->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'message' => 'Membership cancelled successfully',
        ]);
    }
    
    /**
     * Update auto-renew setting
     */
    public function updateAutoRenew(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_renew' => 'required|boolean',
        ]);
        
        $customer = $request->user()->customer;
        
        $membership = CustomerMembership::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->first();
            
        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active membership found',
            ], 404);
        }
        
        $membership->update(['auto_renew' => $validated['auto_renew']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Auto-renew setting updated',
            'data' => $membership,
        ]);
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, string $orderId): JsonResponse
    {
        $transaction = MembershipTransaction::where('midtrans_order_id', $orderId)
            ->firstOrFail();
            
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $transaction->midtrans_order_id,
                'payment_status' => $transaction->payment_status,
                'membership_status' => $transaction->customerMembership->status,
                'amount' => $transaction->amount,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OwnerSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OwnerSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(OwnerSubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Create subscription checkout
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,code',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        try {
            $user = Auth::user();
            $result = $this->subscriptionService->initiateSubscription(
                $user,
                $request->plan_id,
                $request->billing_cycle
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel active subscription
     */
    public function cancel(Request $request)
    {
        try {
            $user = Auth::user();
            $subscription = $user->ownerSubscription;

            if (!$subscription || $subscription->status !== 'active') {
                return response()->json([
                    'message' => 'Tidak ada langganan aktif yang ditemukan.',
                ], 404);
            }

            // Update status to canceled
            $subscription->update([
                'status' => 'canceled',
                'cancelled_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Langganan berhasil dibatalkan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan langganan: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Check current subscription status manually
     */
    public function checkStatus(Request $request)
    {
        try {
            $user = Auth::user();
            $subscription = $this->subscriptionService->verifySubscriptionStatus($user);

            return response()->json([
                'status' => 'success',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

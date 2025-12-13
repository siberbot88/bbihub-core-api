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
     * Start 7-day trial with payment method capture
     * POST /api/v1/owner/subscription/start-trial
     */
    public function startTrial(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validation 1: Check if already has active subscription
            $existingSubscription = $user->ownerSubscription;
            if ($existingSubscription && $existingSubscription->status === 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah memiliki subscription aktif.',
                ], 400);
            }
            
            // Validation 2: Check if trial already used
            if ($user->trial_used) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trial sudah pernah digunakan. Silakan subscribe langsung.',
                ], 400);
            }
            
            // Initiate trial subscription (Rp 0)
            $result = $this->subscriptionService->initiateTrial($user);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Trial checkout berhasil dibuat',
                'data' => $result,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memulai trial: ' . $e->getMessage(),
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

            // Check if user is in trial OR has active subscription
            $isInTrial = $user->trial_ends_at && $user->trial_ends_at->isFuture();
            $hasActiveSub = $subscription && $subscription->status === 'active';
            // Also allow cancelling if status is pending (e.g. trial just started but not active)
            $hasPendingSub = $subscription && $subscription->status === 'pending';

            if (!$isInTrial && !$hasActiveSub && !$hasPendingSub) {
                return response()->json([
                    'message' => 'Tidak ada langganan atau trial aktif yang ditemukan.',
                ], 404);
            }

            // Use transaction to ensure data integrity
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $subscription) {
                // 1. Revoke Trial Access immediately (set to past to guarantee expiry)
                if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
                    $user->update(['trial_ends_at' => now()->subHour()]);
                }

                // 2. Cancel Subscription Record if exists
                if ($subscription && $subscription->status !== 'canceled') {
                    $subscription->update([
                        'status' => 'canceled',
                        'cancelled_at' => now(),
                    ]);
                }
            });

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

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
}

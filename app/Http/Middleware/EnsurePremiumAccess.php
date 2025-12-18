<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure user has premium access (trial or active subscription)
 * 
 * This middleware should be applied to owner routes that require premium features.
 * Users must either:
 * - Have an active trial (trial_ends_at in the future)
 * - Have an active subscription (OwnerSubscription with status 'active')
 */
class EnsurePremiumAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Check if user has premium access (trial or active subscription)
        if (!$user->hasPremiumAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses premium diperlukan. Silakan mulai trial atau berlangganan.',
                'subscription_status' => $user->getSubscriptionStatus(),
                'trial_used' => $user->trial_used,
                'requires_action' => true,
                'action_type' => $user->trial_used ? 'subscribe' : 'start_trial',
            ], 403);
        }
        
        return $next($request);
    }
}

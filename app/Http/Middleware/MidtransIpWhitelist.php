<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class MidtransIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass jika di environment local/testing untuk kemudahan development
        if (app()->environment(['local'])) {
            return $next($request);
        }

        $allowedIps = config('services.midtrans.allowed_ips', []);
        $requestIp = $request->ip();

        if (!in_array($requestIp, $allowedIps)) {
            //            Log::warning('Midtrans Webhook: Blocked unauthorized IP', [
//                'ip' => $requestIp,
//                'user_agent' => $request->userAgent()
//            ]);

            return response()->json(['message' => 'Unauthorized IP address'], 403);
        }

        return $next($request);
    }
}

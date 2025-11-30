<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures that the authenticated user has the 'superadmin' role.
     * If not, logs out the user and redirects to login with error message.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('status', 'Silakan login terlebih dahulu.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user has superadmin role
        if (!$user->hasRole('superadmin', 'web')) {
            // Log the unauthorized access attempt
            \Log::warning('Unauthorized admin access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            // Logout the user
            Auth::logout();

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to login with error message
            return redirect()->route('login')
                ->with('error', 'Akses ditolak. Hanya superadmin yang dapat mengakses halaman ini.');
        }

        // User is superadmin, proceed
        return $next($request);
    }
}

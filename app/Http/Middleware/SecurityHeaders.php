<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Enable browser XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Force HTTPS (only in production)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        // Content Security Policy
        $csp = $this->getContentSecurityPolicy();
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
        }
        
        // Permissions Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }

    /**
     * Get Content Security Policy header value
     */
    protected function getContentSecurityPolicy(): ?string
    {
        // Skip CSP for API routes (JSON responses don't need CSP)
        if (request()->is('api/*')) {
            return null;
        }

        // For development: more permissive CSP to allow Vite HMR and inline styles
        if (config('app.env') !== 'production') {
            return implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 https://cdn.tailwindcss.com https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: https: http://localhost:5173",
                "connect-src 'self' ws://localhost:5173 http://localhost:5173 https:",
                "frame-ancestors 'none'",
            ]);
        }

        // Production: stricter CSP (adjust based on your actual assets)
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.tailwindcss.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}

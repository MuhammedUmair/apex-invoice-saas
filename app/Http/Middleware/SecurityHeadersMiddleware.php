<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and apply defense-in-depth security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Suppress PHP version fingerprinting at runtime
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
            header_remove('Server');
        }

        $response = $next($request);

        // Remove identification headers from the response
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Apply Ironclad Security Headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Enforce HSTS (HTTP Strict Transport Security) on HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}

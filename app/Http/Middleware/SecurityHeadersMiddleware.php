<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Menambahkan security response headers untuk perlindungan menyeluruh.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Cegah MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Perlindungan XSS lawas untuk browser kompatibel
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Kontrol Pengiriman Referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Nonaktifkan fitur perangkat keras berlebih
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy (CSP) aman yang mengizinkan WebSocket Reverb lokal
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
               "font-src 'self' https://fonts.bunny.net data:; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' ws://127.0.0.1:8080 wss://127.0.0.1:8080 ws://localhost:8080 wss://localhost:8080;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
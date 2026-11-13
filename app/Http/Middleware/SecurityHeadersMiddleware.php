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

        // Content Security Policy (CSP) aman yang mengizinkan WebSocket Reverb lokal, Vite dev server, dan Firebase/Google Auth
        $isLocal = app()->isLocal() || config('app.debug');
        $viteScript = $isLocal ? ' http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteStyle = $isLocal ? ' http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteConnect = $isLocal ? ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173' : '';

        $csp = "default-src 'self'; ".
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://apis.google.com https://www.gstatic.com{$viteScript}; ".
               "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com{$viteStyle}; ".
               "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com data:; ".
               "img-src 'self' data: https:; ".
               "connect-src 'self' ws://127.0.0.1:8080 wss://127.0.0.1:8080 ws://localhost:8080 wss://localhost:8080 https://*.googleapis.com https://*.firebaseio.com https://*.google.com https://accounts.google.com{$viteConnect}; ".
               "frame-src 'self' https://*.firebaseapp.com https://accounts.google.com;";

        return $response;
    }
}

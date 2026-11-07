<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_security_headers_are_present_on_http_responses(): void
    {
        $response = $this->get('/up');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $this->assertStringContainsString("default-src 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_content_security_policy_allows_reverb_websocket_connection(): void
    {
        $response = $this->get('/up');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('ws://127.0.0.1:8080', $csp);
        $this->assertStringContainsString('wss://127.0.0.1:8080', $csp);
    }

    public function test_rate_limiters_are_properly_configured(): void
    {
        $this->assertNotNull(RateLimiter::limiter('api'));
        $this->assertNotNull(RateLimiter::limiter('broadcasting'));
        $this->assertNotNull(RateLimiter::limiter('transaksi'));
    }
}

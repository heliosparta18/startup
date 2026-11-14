<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_prescription_upload_with_real_image_file_passes_magic_bytes_validation(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('resep-asli.jpg', 600, 800)->size(1024);

        $response = $this->postJson(route('customer.resep.upload', ['client' => 'banyuwangi-kota']), [
            'nama_dokter' => 'dr. Budi, Sp.PD',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Candi Chier',
            'catatan' => 'Resep valid asli',
            'foto_resep' => $file,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'client' => 'banyuwangi-kota',
        ]);
        $this->assertNotNull($response->json('url_gambar'));
        $this->assertNotNull($response->json('nama_berkas'));
    }

    public function test_prescription_upload_with_spoofed_file_fails_magic_bytes_validation(): void
    {
        Storage::fake('public');
        $spoofedFile = UploadedFile::fake()->createWithContent('malicious.jpg', "<?php echo 'hacked'; ?>");

        $response = $this->postJson(route('customer.resep.upload', ['client' => 'banyuwangi-kota']), [
            'nama_dokter' => 'dr. Budi, Sp.PD',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Candi Chier',
            'catatan' => 'Uji eksploitasi MIME spoofing',
            'foto_resep' => $spoofedFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['foto_resep']);
    }
}

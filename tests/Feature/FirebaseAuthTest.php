<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_firebase_session_creates_user_and_authenticates(): void
    {
        $payload = [
            'email' => 'sasa.firebase@gmail.com',
            'name' => 'Sasa Cantika',
            'uid' => 'firebase-uid-999',
            'provider' => 'google',
            'photo_url' => 'https://lh3.googleusercontent.com/a/test-avatar',
        ];

        $response = $this->postJson(route('auth.firebase.session'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('auth.complete-profile'),
            'user' => [
                'id' => auth()->id(),
                'name' => 'Sasa Cantika',
                'email' => 'sasa.firebase@gmail.com',
                'role' => 'pelanggan',
                'provider' => 'google',
            ],
        ]);

        // Verifikasi User terautentikasi di Laravel
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'sasa.firebase@gmail.com',
            'name' => 'Sasa Cantika',
            'role' => 'pelanggan',
        ]);

        // Verifikasi tabel Pengguna domain farmasi tersinkronisasi
        $this->assertDatabaseHas('pengguna', [
            'telepon_email' => 'sasa.firebase@gmail.com',
            'nama_lengkap' => 'Sasa Cantika',
            'peran' => 'pelanggan',
        ]);

        // Verifikasi session data
        $this->assertSame('firebase-uid-999', session('firebase_uid'));
        $this->assertSame('google', session('firebase_provider'));
    }

    public function test_firebase_session_redirects_super_admin_to_admin_portal(): void
    {
        // Admin Daffa sudah ada di seeder
        $user = User::factory()->create([
            'email' => 'daffa.admin@omnipharma.test',
            'name' => 'Admin Daffa',
            'role' => 'super_admin',
        ]);

        $payload = [
            'email' => 'daffa.admin@omnipharma.test',
            'name' => 'Admin Daffa',
            'uid' => 'firebase-admin-1',
            'provider' => 'password',
        ];

        $response = $this->postJson(route('auth.firebase.session'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('admin.dashboard'),
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_firebase_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('auth.firebase.logout'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('login'),
        ]);

        $this->assertGuest();
    }

    public function test_firebase_logout_redirects_for_html_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('auth.firebase.logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_page_allows_oauth_popups_and_blocks_inactive_firebase_user(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $csp = (string) $loginResponse->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('firebaseapp.com', $csp);
        $this->assertStringContainsString('accounts.google.com', $csp);

        User::factory()->create([
            'email' => 'nonaktif.google@gmail.com',
            'status' => 'nonaktif',
            'role' => 'pelanggan',
        ]);

        $denied = $this->postJson(route('auth.firebase.session'), [
            'email' => 'nonaktif.google@gmail.com',
            'name' => 'Nonaktif User',
            'uid' => 'uid-inactive-1',
            'provider' => 'google',
        ]);

        $denied->assertStatus(403);
        $this->assertGuest();
    }
}

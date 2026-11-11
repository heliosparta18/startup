<?php

namespace Tests\Feature;

use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EmailOtpAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_send_otp_generates_and_caches_code(): void
    {
        $response = $this->postJson(route('auth.otp.send'), [
            'email' => 'pasien.otp@gmail.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'email',
            'demo_otp',
            'expires_in',
        ]);

        $demoOtp = $response->json('demo_otp');
        $this->assertSame(6, strlen($demoOtp));
        $this->assertTrue(Cache::has('auth_otp:pasien.otp@gmail.com'));
        $this->assertSame($demoOtp, Cache::get('auth_otp:pasien.otp@gmail.com'));
    }

    public function test_verify_otp_authenticates_user_successfully(): void
    {
        $email = 'pasien.baru@gmail.com';
        Cache::put("auth_otp:{$email}", '654321', now()->addMinutes(5));

        $response = $this->postJson(route('auth.otp.verify'), [
            'email' => $email,
            'otp' => '654321',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'pelanggan']);
        $this->assertDatabaseHas('pengguna', ['telepon_email' => $email, 'peran' => 'pelanggan']);
        $this->assertFalse(Cache::has("auth_otp:{$email}"));
    }

    public function test_verify_otp_fails_with_invalid_code(): void
    {
        $email = 'pasien.salah@gmail.com';
        Cache::put("auth_otp:{$email}", '111222', now()->addMinutes(5));

        $response = $this->postJson(route('auth.otp.verify'), [
            'email' => $email,
            'otp' => '000000',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_register_with_otp_verifies_email_and_creates_account(): void
    {
        $email = 'registrasi.otp@gmail.com';
        Cache::put("auth_otp:{$email}", '333444', now()->addMinutes(5));

        $response = $this->postJson(route('auth.otp.register'), [
            'username' => 'pasienbaru',
            'email' => $email,
            'password' => 'aman123',
            'otp' => '333444',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('login'),
        ]);

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'name' => 'pasienbaru',
            'email' => $email,
            'role' => 'pelanggan',
        ]);
        $this->assertDatabaseHas('pengguna', [
            'nama_lengkap' => 'pasienbaru',
            'telepon_email' => $email,
            'peran' => 'pelanggan',
        ]);
        $this->assertFalse(Cache::has("auth_otp:{$email}"));
    }

    public function test_google_first_time_login_redirects_to_complete_profile(): void
    {
        $email = 'google.newuser@gmail.com';

        // 1. Login pertama kali via Google
        $response = $this->postJson(route('auth.firebase.session'), [
            'email' => $email,
            'name' => 'Google New User',
            'uid' => 'google-uid-888',
            'provider' => 'google',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('auth.complete-profile'),
        ]);

        $this->assertAuthenticated();

        // 2. Akses halaman formulir kelengkapan profil
        $viewResponse = $this->get(route('auth.complete-profile'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Lengkapi Profil Pasien');

        // 3. Simpan data profil (telepon, alamat, buat password)
        $submitResponse = $this->post(route('auth.complete-profile.save'), [
            'name' => 'Google User Sasa',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Ijen No. 10, Banyuwangi Kota',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $submitResponse->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Google User Sasa',
        ]);
        $this->assertDatabaseHas('pengguna', [
            'telepon_email' => $email,
            'nama_lengkap' => 'Google User Sasa',
        ]);
    }
}

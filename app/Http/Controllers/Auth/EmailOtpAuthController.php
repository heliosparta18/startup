<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailOtpAuthController extends Controller
{
    /**
     * Generate dan kirim kode OTP 6-digit ke email pengguna.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
        ]);

        $identifier = trim($validated['email']);
        $email = $identifier;

        // Jika memasukkan username tanpa @, format dengan domain apotek
        if (! str_contains($email, '@')) {
            $email = strtolower($email).'@omnipharma.test';
        }

        $email = strtolower($email);

        // Terbitkan kode OTP 6-digit acak
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan di Cache selama 5 menit
        Cache::put("auth_otp:{$email}", $otp, now()->addMinutes(5));

        // Catat di log sistem
        Log::info("Kode OTP OmniPharma diterbitkan untuk {$email}: {$otp}");

        // Coba kirim email via mail transport (jika SMTP terkonfigurasi)
        try {
            Mail::raw(
                "Halo,\n\nKode OTP Anda untuk masuk ke sistem OmniPharma Network adalah: {$otp}\n\nKode ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun demi keamanan rekam medis dan data transaksi Anda.\n\nSalam,\nTim Farmasi OmniPharma Network",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Kode Verifikasi OTP Masuk - OmniPharma Network');
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Pengiriman email OTP ke {$email} gagal via transport: ".$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Kode OTP 6-digit berhasil dikirimkan ke {$email}.",
            'email' => $email,
            'demo_otp' => $otp, // Disediakan untuk kemudahan uji coba langsung/demo
            'expires_in' => 300,
        ]);
    }

    /**
     * Verifikasi kode OTP dan masuk ke sesi aplikasi.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        if (! str_contains($email, '@')) {
            $email = strtolower($email).'@omnipharma.test';
        }

        $inputOtp = trim($validated['otp']);
        $cachedOtp = Cache::get("auth_otp:{$email}");

        if (! $cachedOtp || $cachedOtp !== $inputOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau telah kedaluwarsa. Silakan minta kode baru.',
            ], 422);
        }

        // Hapus OTP dari cache setelah berhasil digunakan (one-time use)
        Cache::forget("auth_otp:{$email}");

        // 1. Temukan atau buat User di tabel users
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => Str::before($email, '@'),
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'pelanggan',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]);
        }

        // 2. Temukan atau buat Pengguna di tabel domain farmasi
        $pengguna = Pengguna::where('telepon_email', $email)->first();
        if (! $pengguna) {
            Pengguna::create([
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => $user->name,
                'peran' => $user->role ?: 'pelanggan',
                'telepon_email' => $email,
                'aktif' => true,
            ]);
        }

        // 3. Masuk ke sesi Laravel
        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('firebase_provider', 'email_otp');
        $request->session()->flash('welcome_user', $user->name);

        $redirectUrl = route('dashboard');
        if ($user->hasRole('super_admin') || $user->role === 'super_admin') {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->hasRole('apoteker') || $user->role === 'apoteker') {
            $redirectUrl = route('tenant.dashboard', ['client' => 'banyuwangi-kota']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi OTP berhasil! Mengalihkan ke dashboard...',
            'redirect' => $redirectUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Verifikasi kode OTP dan selesaikan registrasi akun baru.
     */
    public function registerWithOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        $inputOtp = trim($validated['otp']);
        $cachedOtp = Cache::get("auth_otp:{$email}");

        if (! $cachedOtp || $cachedOtp !== $inputOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP verifikasi email salah atau telah kedaluwarsa. Silakan minta kode baru.',
            ], 422);
        }

        // Hapus OTP dari cache
        Cache::forget("auth_otp:{$email}");

        // Cek apakah email sudah terdaftar sebelumnya
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sudah terdaftar. Silakan langsung masuk ke akun Anda.',
            ], 422);
        }

        $username = trim($validated['username']);

        // 1. Buat User baru di tabel users
        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => bcrypt($validated['password']),
            'role' => 'pelanggan',
            'status' => 'aktif',
            'email_verified_at' => now(), // Terverifikasi via OTP email
        ]);

        // 2. Buat entitas Pengguna di tabel domain farmasi
        Pengguna::create([
            'id_pengguna' => (string) Str::uuid(),
            'nama_lengkap' => $username,
            'peran' => 'pelanggan',
            'telepon_email' => $email,
            'aktif' => true,
        ]);

        // Sesuai alur baru: setelah pendaftaran selesai, pengguna diarahkan untuk login terlebih dahulu
        session()->flash('status', 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan kata sandi yang baru Anda daftarkan.');

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan kata sandi Anda.',
            'redirect' => route('login'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}

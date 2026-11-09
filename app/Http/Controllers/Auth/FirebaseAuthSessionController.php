<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FirebaseAuthSessionController extends Controller
{
    /**
     * Sinkronkan sesi masuk Firebase dengan autentikasi Laravel.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'name' => ['nullable', 'string', 'max:150'],
            'uid' => ['nullable', 'string', 'max:128'],
            'provider' => ['nullable', 'string', 'max:50'],
            'photo_url' => ['nullable', 'string', 'max:500'],
        ]);

        $email = strtolower(trim($validated['email']));
        $name = $validated['name'] ?: Str::before($email, '@');
        $provider = $validated['provider'] ?? 'password';

        // 1. Temukan atau buat User pada tabel users
        $isFirstTime = false;
        $user = User::where('email', $email)->first();
        if (! $user) {
            $isFirstTime = true;
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'pelanggan',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]);
        } else {
            if (! $user->email_verified_at) {
                $user->email_verified_at = Carbon::now();
            }
            if ($validated['name'] && $user->name !== $name) {
                $user->name = $name;
            }
            $user->save();
        }
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

        // 4. Simpan metadata sesi Firebase
        $request->session()->put('firebase_uid', $validated['uid'] ?? null);
        $request->session()->put('firebase_provider', $provider);
        $request->session()->put('firebase_photo', $validated['photo_url'] ?? null);

        // 5. Tentukan tujuan redirect (jika Google pertama kali -> lengkapi profil)
        if ($isFirstTime && $provider === 'google') {
            $request->session()->put('google_first_time', true);
            $redirectUrl = route('auth.complete-profile');
        } else {
            $redirectUrl = route('dashboard');
            if ($user->hasRole('super_admin') || $user->role === 'super_admin') {
                $redirectUrl = route('admin.dashboard');
            } elseif ($user->hasRole('apoteker') || $user->role === 'apoteker') {
                $redirectUrl = route('tenant.dashboard', ['client' => 'banyuwangi-kota']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Autentikasi Firebase berhasil terhubung ke sistem OmniPharma.',
            'redirect' => $redirectUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'provider' => $provider,
            ],
        ]);
    }

    /**
     * Keluarkan sesi pengguna dari Laravel.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri.',
            'redirect' => route('login'),
        ]);
    }

    /**
     * Tampilkan formulir lengkapi data profil setelah login Google pertama kali.
     */
    public function showCompleteProfile(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        return view('pages.auth.complete-profile', [
            'user' => $user,
            'photoUrl' => session('firebase_photo'),
        ]);
    }

    /**
     * Simpan data formulir profil dan buat kata sandi akun.
     */
    public function saveCompleteProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'telepon' => ['required', 'string', 'max:30'],
            'alamat' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'password' => bcrypt($validated['password']),
        ]);

        $pengguna = Pengguna::where('telepon_email', $user->email)->first();
        if ($pengguna) {
            $pengguna->update([
                'nama_lengkap' => $validated['name'],
            ]);
        }

        $request->session()->forget('google_first_time');
        $request->session()->put('profile_completed', true);

        return redirect()->route('dashboard')->with('status', 'Profil pasien berhasil dilengkapi! Selamat datang di OmniPharma Network.');
    }
}

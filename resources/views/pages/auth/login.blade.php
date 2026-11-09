<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Portal Apotek Multi-Klien - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <style>[x-cloak] { display: none !important; }</style>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 antialiased flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="w-full max-w-xl mx-auto">
        <!-- Badge Prototipe Farmasi -->
        <div class="flex justify-center mb-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span>
                Autentikasi Terintegrasi Firebase & Multi-Tenancy
            </span>
        </div>

        <!-- Header Apotek -->
        <div class="text-center mb-6">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="text-left">
                    <span class="block text-xs font-bold tracking-widest text-emerald-700 uppercase">OmniPharma Network</span>
                    <span class="block text-sm font-medium text-slate-600">Sistem Farmasi Multi-Klien</span>
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0F172A]">Masuk Portal Apotek Multi-Klien</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-md mx-auto">Gunakan akun Google atau email/username untuk masuk ke portal apotek dan dashboard pelanggan.</p>
        </div>

        <!-- Kartu Kontainer Putih Utama -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-6 sm:p-8">
            <div x-data="{
                email: '{{ old('email', '') }}',
                password: '',
                showPassword: false,
                selectedRole: '',
                loadingFirebase: false,
                loadingGoogle: false,
                selectDemo(roleKey, userEmail, userPass) {
                    this.selectedRole = roleKey;
                    this.email = userEmail;
                    this.password = userPass;
                },
                async handleGoogleLogin() {
                    if (this.loadingGoogle || this.loadingFirebase) return;
                    this.loadingGoogle = true;
                    try {
                        if (!window.OmniFirebaseAuth || typeof window.OmniFirebaseAuth.loginWithGoogle !== 'function') {
                            throw new Error('Modul autentikasi Firebase belum siap. Silakan muat ulang halaman.');
                        }
                        const result = await window.OmniFirebaseAuth.loginWithGoogle();
                        const userName = result?.user?.displayName || result?.user?.email || 'Pelanggan';
                        if (window.OmniFirebaseAuth?.showToast) {
                            window.OmniFirebaseAuth.showToast(`Selamat datang, ${userName}!`, 'success');
                        }
                        setTimeout(() => {
                            window.location.href = result?.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } catch (err) {
                        console.error('Google Auth Error:', err);
                        const errorMsg = window.OmniFirebaseAuth?.formatFirebaseError
                            ? window.OmniFirebaseAuth.formatFirebaseError(err)
                            : (err?.message || 'Gagal masuk dengan akun Google.');
                        if (window.OmniFirebaseAuth?.showToast) {
                            window.OmniFirebaseAuth.showToast(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    } finally {
                        this.loadingGoogle = false;
                    }
                },
                async handleLoginSubmit(e) {
                    if (this.loadingFirebase || this.loadingGoogle) {
                        e.preventDefault();
                        return;
                    }

                    // Jika demo switcher dipilih, biarkan submit native Fortify berjalan
                    if (this.selectedRole) {
                        this.loadingFirebase = true;
                        return;
                    }

                    e.preventDefault();
                    if (!this.email || !this.password) {
                        const emptyMsg = 'Silakan masukkan email atau nama pengguna dan kata sandi.';
                        if (window.OmniFirebaseAuth?.showToast) {
                            window.OmniFirebaseAuth.showToast(emptyMsg, 'error');
                        } else {
                            alert(emptyMsg);
                        }
                        return;
                    }

                    this.loadingFirebase = true;
                    try {
                        if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.loginWithEmailOrUsername === 'function') {
                            const result = await window.OmniFirebaseAuth.loginWithEmailOrUsername(this.email, this.password);
                            if (window.OmniFirebaseAuth?.showToast) {
                                window.OmniFirebaseAuth.showToast('Login berhasil! Mengalihkan ke dashboard...', 'success');
                            }
                            setTimeout(() => {
                                window.location.href = result?.session?.redirect || '{{ route('dashboard') }}';
                            }, 600);
                        } else {
                            // Fallback jika modul Firebase belum siap
                            e.target.submit();
                        }
                    } catch (err) {
                        console.warn('Firebase login attempt fallback to Fortify:', err);
                        // Jika di Firebase belum ada atau error credentials, fallback ke login native Laravel form submit
                        const form = e.target;
                        form.submit();
                    } finally {
                        // Loading tetap aktif sampai halaman berpindah
                    }
                }
            }">
                <!-- Tombol Masuk dengan Akun Google (One-Click) -->
                <button
                    type="button"
                    @click="handleGoogleLogin()"
                    :disabled="loadingGoogle || loadingFirebase"
                    aria-label="Masuk Cepat dengan Akun Google"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.39 7.35 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.61 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span x-show="!loadingGoogle">Masuk Cepat dengan Akun Google</span>
                    <span x-show="loadingGoogle" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menghubungkan Akun Google...</span>
                    </span>
                </button>

                <!-- Pembatas Visual -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Atau Masuk dengan Kredensial</span>
                    </div>
                </div>

                <!-- Quick Demo Role Switcher -->
                <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Pilih Akun Demo (Uji Cepat)</span>
                        <span class="text-[11px] text-slate-500 font-medium">Klik untuk isi otomatis</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Super Admin -->
                        <button
                            type="button"
                            @click="selectDemo('super_admin', 'daffa@omnipharma.test', 'password')"
                            :class="selectedRole === 'super_admin' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:bg-slate-100/70 text-slate-800'"
                            class="flex flex-col text-left p-3 rounded-lg border transition min-h-[44px] justify-center focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                        >
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-900">Super Admin</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">Pusat</span>
                            </div>
                            <span class="text-xs font-medium text-emerald-700 mt-0.5 truncate">daffa@omnipharma.test</span>
                            <span class="text-[11px] text-slate-500">Muhammad Daffa Ariby</span>
                        </button>

                        <!-- Apoteker Banyuwangi -->
                        <button
                            type="button"
                            @click="selectDemo('apt_bwi', 'bambang@omnipharma.test', 'password')"
                            :class="selectedRole === 'apt_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:bg-slate-100/70 text-slate-800'"
                            class="flex flex-col text-left p-3 rounded-lg border transition min-h-[44px] justify-center focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                        >
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-900">Apoteker BWI</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">Cabang 01</span>
                            </div>
                            <span class="text-xs font-medium text-emerald-700 mt-0.5 truncate">bambang@omnipharma.test</span>
                            <span class="text-[11px] text-slate-500">apt. Bambang (Banyuwangi Kota)</span>
                        </button>

                        <!-- Apoteker Rogojampi -->
                        <button
                            type="button"
                            @click="selectDemo('apt_rogo', 'dewi@omnipharma.test', 'password')"
                            :class="selectedRole === 'apt_rogo' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:bg-slate-100/70 text-slate-800'"
                            class="flex flex-col text-left p-3 rounded-lg border transition min-h-[44px] justify-center focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                        >
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-900">Apoteker Rogojampi</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">Cabang 02</span>
                            </div>
                            <span class="text-xs font-medium text-emerald-700 mt-0.5 truncate">dewi@omnipharma.test</span>
                            <span class="text-[11px] text-slate-500">apt. Dewi Sartika (Rogojampi)</span>
                        </button>

                        <!-- Kasir Cabang -->
                        <button
                            type="button"
                            @click="selectDemo('kasir_bwi', 'kasir.bwi@omnipharma.test', 'password')"
                            :class="selectedRole === 'kasir_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:bg-slate-100/70 text-slate-800'"
                            class="flex flex-col text-left p-3 rounded-lg border transition min-h-[44px] justify-center focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                        >
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-900">Kasir Cabang</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">POS Staf</span>
                            </div>
                            <span class="text-xs font-medium text-emerald-700 mt-0.5 truncate">kasir.bwi@omnipharma.test</span>
                            <span class="text-[11px] text-slate-500">Kasir Staf Banyuwangi</span>
                        </button>
                    </div>

                    <!-- Notifikasi Pilihan Demo -->
                    <div x-show="selectedRole" x-cloak class="mt-3 flex items-center gap-2 p-2.5 rounded-lg bg-emerald-100/70 border border-emerald-300 text-xs text-emerald-900 font-medium">
                        <svg class="h-4 w-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Akun demo siap digunakan: <strong x-text="email"></strong> (kata sandi terisi otomatis).</span>
                    </div>
                </div>

                <!-- Notifikasi Sesi & Validasi Error -->
                @if (session('status'))
                    <div class="mb-4 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800">
                        <div class="font-semibold mb-1">Periksa kembali data masukan Anda:</div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Login (Firebase + Fallback Fortify) -->
                <form method="POST" action="{{ route('login.store') }}" @submit="handleLoginSubmit($event)" class="space-y-5">
                    @csrf

                    <!-- Kolom Email / Username -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Email atau Nama Pengguna
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                name="email"
                                type="text"
                                x-model="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="email@contoh.com atau username"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                            />
                        </div>
                    </div>

                    <!-- Kolom Sandi -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="inline-flex items-center min-h-[44px] py-1 text-xs font-medium text-emerald-700 hover:text-emerald-800 hover:underline">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi akun"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center cursor-pointer"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            >
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none min-h-[44px] py-2">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600 cursor-pointer"
                            />
                            <span class="text-xs text-slate-700">Ingat sesi login di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <div>
                        <button
                            type="submit"
                            :disabled="loadingFirebase || loadingGoogle"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition min-h-[44px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="!loadingFirebase" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <svg x-show="loadingFirebase" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-show="!loadingFirebase">Masuk ke Portal</span>
                            <span x-show="loadingFirebase" x-cloak>Memvalidasi Akun...</span>
                        </button>
                    </div>
                </form>

                <!-- Tautan Daftar Akun Baru -->
                <div class="mt-5 text-center flex items-center justify-center gap-1 min-h-[44px]">
                    <span class="text-xs text-slate-500">Belum memiliki akun pelanggan?</span>
                    <a href="{{ route('register') }}" class="inline-flex items-center min-h-[44px] px-1 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                        Daftar Akun Baru
                    </a>
                </div>

                <!-- Footer Dalam Kartu -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('home') }}" class="font-medium text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1 min-h-[44px] py-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <span class="text-[11px] text-slate-400">OmniPharma Core v2.4</span>
                </div>
            </div>
        </div>

        <!-- Catatan Kepatuhan Keamanan & Akses -->
        <p class="mt-6 text-center text-xs text-slate-500">
            Akses resmi farmasi OmniPharma Network terenkripsi & anti-tampering.
        </p>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Portal Apotek Multi-Klien - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-slate-100/90 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 lg:p-8 font-sans overflow-x-hidden">

    <!-- Container Utama Split Card Login (Sesuai Referensi Visual) -->
    <div class="w-full max-w-5xl bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row min-h-0 lg:min-h-[600px]">

        <!-- KOLOM KIRI: Visual Apotek Resmi & Jaminan Layanan -->
        <div class="lg:w-5/12 bg-slate-50 border-r border-slate-200/90 p-6 sm:p-8 lg:p-10 flex flex-col justify-between text-slate-800 relative">
            <div class="relative z-10">
                <!-- Brand Header -->
                <div class="flex items-center gap-3">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-10 w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.25)]">
                    <div>
                        <span class="block text-base font-extrabold text-slate-900 tracking-tight leading-none">OmniPharma</span>
                        <span class="block text-xs font-semibold text-emerald-700 mt-0.5">Jaringan Apotek Berlisensi</span>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <div class="inline-block text-[11px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200/80">
                        Akses Portal Resmi
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 leading-snug">
                        Pelayanan Apotek Resmi &amp; Tebus Resep Dokter
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Masuk untuk mengakses resep dokter, keranjang obat, atau kelola operasional kefarmasian cabang Anda.
                    </p>
                </div>

                <!-- Kartu Ringkasan Standar Farmasi -->
                <div class="my-6 space-y-3">
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-900">SIPA &amp; SIA Resmi Terdaftar</h4>
                            <p class="text-[11px] text-slate-500">Izin pelayanan farmasi Kemenkes RI</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-900">Cabang BWI Kota &amp; Rogojampi</h4>
                            <p class="text-[11px] text-slate-500">Stok obat fisik &amp; konsultasi apoteker</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Kolom Kiri -->
            <div class="relative z-10 text-[11px] text-slate-400 flex items-center justify-between pt-4 border-t border-slate-200/80">
                <span>&copy; 2026 Apotek OmniPharma</span>
                <a href="{{ route('home') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                    &larr; Beranda
                </a>
            </div>
        </div>

        <!-- KOLOM KANAN: Formulir Masuk Sederhana (Input 1: Email/Username, Input 2: Password) -->
        <div class="lg:w-7/12 p-5 sm:p-8 lg:p-12 flex flex-col justify-between bg-white" x-data="loginPageData()">
            <div>
                <!-- Mobile Brand Header -->
                <div class="flex items-center gap-2.5 mb-4 lg:hidden">
                    <img src="/logo.png" alt="OmniPharma" class="h-9 w-9 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.35)]">
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase">OmniPharma</span>
                </div>

                <!-- Heading Form -->
                <div class="mb-5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Masukkan email atau username dan kata sandi untuk mengakses portal.</p>
                </div>

                <!-- Notifikasi Status Sesi & Validasi Server -->
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                        <div class="font-semibold mb-1">Periksa kembali data masukan Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulir Masuk: 1 Input Email/Username + 1 Input Password -->
                <form x-ref="loginForm" method="POST" action="{{ route('login.store') }}" @submit="handleLoginSubmit($event)" class="space-y-4">
                    @csrf

                    <!-- Input 1: Email atau Username -->
                    <div>
                        <label for="login_identifier" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Email atau Username
                        </label>
                        <input
                            id="login_identifier"
                            name="email"
                            type="text"
                            x-model="identifier"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="nama@email.com atau username"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                        />
                    </div>

                    <!-- Input 2: Kata Sandi -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="login_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[44px] inline-flex items-center">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input
                                id="login_password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi akun"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center cursor-pointer"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            >
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between py-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none min-h-[44px]">
                            <input
                                type="checkbox"
                                name="remember"
                                id="login_remember"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                            />
                            <span class="text-xs text-slate-700 font-medium">Ingat sesi di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk Utama -->
                    <div>
                        <button
                            type="submit"
                            :disabled="loadingSubmit || loadingGoogle"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-xs hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="!loadingSubmit" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                            <svg x-show="loadingSubmit" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-show="!loadingSubmit">Masuk ke Portal Apotek</span>
                            <span x-show="loadingSubmit" x-cloak>Memvalidasi Kredensial...</span>
                        </button>
                    </div>
                </form>

                <!-- Pembatas Visual "Atau Masuk dengan" -->
                <div class="relative my-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Atau Masuk dengan</span>
                    </div>
                </div>

                <!-- Tombol Masuk dengan Akun Google (Di Bawah Form) -->
                <button
                    type="button"
                    @click="handleGoogleLogin()"
                    :disabled="loadingGoogle || loadingSubmit"
                    aria-label="Masuk dengan Akun Google"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.39 7.35 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.61 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span x-show="!loadingGoogle">Masuk dengan Akun Google</span>
                    <span x-show="loadingGoogle" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menghubungkan Akun Google...</span>
                    </span>
                </button>

                <!-- Tautan Daftar -->
                <div class="mt-4 flex flex-wrap items-center justify-center gap-1 text-center">
                    <span class="text-xs text-slate-500">Belum memiliki akun pelanggan?</span>
                    <a href="{{ route('register') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center min-h-[44px]">
                        Daftar Akun Baru
                    </a>
                </div>
            </div>

            <!-- Panel Bawah: Quick Demo Role Switcher (Praktikum Pak Devit) -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pilih Akun Demo Praktikum:</span>
                    <span class="text-[10px] text-slate-400">Klik untuk isi otomatis</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button
                        type="button"
                        @click="selectDemo('super_admin', 'daffa@omnipharma.test', 'password')"
                        :disabled="loadingSubmit || loadingGoogle"
                        :class="selectedRole === 'super_admin' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 sm:p-2.5 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer disabled:opacity-50"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold truncate">Super Admin</span>
                            <span x-show="selectedRole === 'super_admin'" class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                        </div>
                        <span class="text-[9px] text-slate-500 truncate">daffa@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('apt_bwi', 'bambang@omnipharma.test', 'password')"
                        :disabled="loadingSubmit || loadingGoogle"
                        :class="selectedRole === 'apt_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 sm:p-2.5 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer disabled:opacity-50"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold truncate">Apoteker BWI</span>
                            <span x-show="selectedRole === 'apt_bwi'" class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                        </div>
                        <span class="text-[9px] text-slate-500 truncate">bambang@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('apt_rogo', 'dinda@omnipharma.test', 'password')"
                        :disabled="loadingSubmit || loadingGoogle"
                        :class="selectedRole === 'apt_rogo' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 sm:p-2.5 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer disabled:opacity-50"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold truncate">Apoteker RGO</span>
                            <span x-show="selectedRole === 'apt_rogo'" class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                        </div>
                        <span class="text-[9px] text-slate-500 truncate">dinda@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('kasir_bwi', 'kasir.bwi@omnipharma.test', 'password')"
                        :disabled="loadingSubmit || loadingGoogle"
                        :class="selectedRole === 'kasir_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 sm:p-2.5 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer disabled:opacity-50"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold truncate">Kasir BWI</span>
                            <span x-show="selectedRole === 'kasir_bwi'" class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                        </div>
                        <span class="text-[9px] text-slate-500 truncate">kasir.bwi@omnipharma.test</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Overlay Animasi Loading Muter Menuju Dashboard -->
        <div
            x-show="isRedirecting || loadingSubmit || loadingGoogle"
            x-cloak
            class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
        >
            <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col items-center text-center max-w-xs mx-4 space-y-4">
                <!-- Spinner Muter dengan Logo Apotek di Tengah -->
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="animate-spin w-16 h-16 text-emerald-600" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <img src="/logo.png" alt="OmniPharma" class="w-7 h-7 object-contain absolute pointer-events-none">
                </div>

                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Memvalidasi Akun...</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Menyiapkan dashboard apotek Anda, mohon tunggu sebentar.</p>
                </div>
            </div>
        </div>

    </div>
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    <script>
        function loginPageData() {
            return {
                identifier: '{{ old('email', '') }}',
                password: '',
                showPassword: false,
                selectedRole: '',
                loadingSubmit: false,
                loadingGoogle: false,
                isRedirecting: false,

                selectDemo(roleKey, userEmail, userPass) {
                    this.selectedRole = roleKey;
                    this.identifier = userEmail;
                    this.password = userPass;
                    this.loadingSubmit = true;
                    this.isRedirecting = true;
                    if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.showToast === 'function') {
                        window.OmniFirebaseAuth.showToast('Memproses masuk akun demo...', 'info');
                    }
                    setTimeout(() => {
                        this.$refs.loginForm.submit();
                    }, 200);
                },

                async ensureFirebaseAuth() {
                    if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.loginWithGoogle === 'function') {
                        return window.OmniFirebaseAuth;
                    }
                    const scriptEl = document.querySelector("script[src*='app-']");
                    if (scriptEl && scriptEl.src) {
                        try {
                            await import(scriptEl.src);
                        } catch (e) {
                            console.warn('Dynamic import fallback note:', e);
                        }
                    }
                    for (let i = 0; i < 25; i++) {
                        if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.loginWithGoogle === 'function') {
                            return window.OmniFirebaseAuth;
                        }
                        await new Promise(r => setTimeout(r, 100));
                    }
                    throw new Error('Modul autentikasi Firebase belum siap. Silakan refresh halaman.');
                },

                async handleGoogleLogin() {
                    if (this.loadingGoogle || this.loadingSubmit) return;
                    this.loadingGoogle = true;
                    try {
                        const fb = await this.ensureFirebaseAuth();
                        const result = await fb.loginWithGoogle();
                        const userName = result.user.displayName || result.user.email;
                        if (fb.showToast) {
                            fb.showToast(`Selamat datang, ${userName}!`, 'success');
                        }
                        this.isRedirecting = true;
                        setTimeout(() => {
                            window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } catch (err) {
                        console.error('Google Auth Error:', err);
                        const errorMsg = window.OmniFirebaseAuth?.formatFirebaseError
                            ? window.OmniFirebaseAuth.formatFirebaseError(err)
                            : (err?.message || 'Gagal masuk dengan akun Google.');
                        if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.showToast === 'function') {
                            window.OmniFirebaseAuth.showToast(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    } finally {
                        this.loadingGoogle = false;
                    }
                },

                async handleLoginSubmit(e) {
                    if (this.selectedRole) {
                        return true;
                    }

                    e.preventDefault();
                    if (!this.identifier || !this.password) {
                        if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.showToast === 'function') {
                            window.OmniFirebaseAuth.showToast('Silakan masukkan email atau username beserta kata sandi.', 'error');
                        }
                        return;
                    }

                    this.loadingSubmit = true;
                    try {
                        await this.ensureFirebaseAuth();
                        if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.loginWithEmailOrUsername === 'function') {
                            const result = await window.OmniFirebaseAuth.loginWithEmailOrUsername(this.identifier, this.password);
                            if (window.OmniFirebaseAuth?.showToast) {
                                window.OmniFirebaseAuth.showToast('Login berhasil! Mengalihkan ke dashboard...', 'success');
                            }
                            this.isRedirecting = true;
                            setTimeout(() => {
                                window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                            }, 600);
                        } else {
                            this.isRedirecting = true;
                            e.target.submit();
                        }
                    } catch (err) {
                        console.warn('Firebase login attempt fallback to Fortify:', err);
                        this.isRedirecting = true;
                        e.target.submit();
                        this.loadingSubmit = false;
                    }
                }
            };
        }
    </script>
    @fluxScripts
</body>
</html>

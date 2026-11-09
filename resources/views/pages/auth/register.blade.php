<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun Pelanggan - OmniPharma Network</title>
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
        <!-- Badge Farmasi -->
        <div class="flex justify-center mb-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span>
                Pendaftaran Pasien & Pelanggan Farmasi
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
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0F172A]">Buat Akun Pelanggan Baru</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-md mx-auto">Daftar secara instan menggunakan akun Google atau lengkapi formulir di bawah ini.</p>
        </div>

        <!-- Kartu Kontainer Putih Utama -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-6 sm:p-8">
            <div x-data="{
                name: '{{ old('name', '') }}',
                email: '{{ old('email', '') }}',
                password: '',
                passwordConfirmation: '',
                showPassword: false,
                loadingGoogle: false,
                loadingRegister: false,
                errorMessage: '',
                async handleGoogleRegister() {
                    this.errorMessage = '';
                    this.loadingGoogle = true;
                    try {
                        if (!window.OmniFirebaseAuth) {
                            throw new Error('Layanan autentikasi Google belum siap. Silakan muat ulang halaman.');
                        }
                        const result = await window.OmniFirebaseAuth.loginWithGoogle();
                        window.OmniFirebaseAuth?.showToast(`Pendaftaran berhasil! Selamat datang, ${result.user.displayName || result.user.email}!`, 'success');
                        setTimeout(() => {
                            window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } catch (err) {
                        console.error('Google Register Error:', err);
                        const msg = window.OmniFirebaseAuth?.formatFirebaseError(err) || err.message || 'Pendaftaran dengan Google gagal.';
                        this.errorMessage = msg;
                        window.OmniFirebaseAuth?.showToast(msg, 'error');
                    } finally {
                        this.loadingGoogle = false;
                    }
                },
                async handleRegisterSubmit(e) {
                    this.errorMessage = '';
                    e.preventDefault();

                    if (!this.name.trim() || !this.email.trim() || !this.password) {
                        this.errorMessage = 'Silakan lengkapi seluruh kolom formulir pendaftaran.';
                        window.OmniFirebaseAuth?.showToast(this.errorMessage, 'error');
                        return;
                    }
                    if (this.password.length < 8) {
                        this.errorMessage = 'Kata sandi minimal harus 8 karakter sesuai standar keamanan.';
                        window.OmniFirebaseAuth?.showToast(this.errorMessage, 'error');
                        return;
                    }
                    if (this.password !== this.passwordConfirmation) {
                        this.errorMessage = 'Konfirmasi kata sandi tidak cocok dengan kata sandi yang dibuat.';
                        window.OmniFirebaseAuth?.showToast(this.errorMessage, 'error');
                        return;
                    }

                    // Jika Firebase SDK belum siap, langsung gunakan submit form native Fortify
                    if (!window.OmniFirebaseAuth) {
                        e.target.submit();
                        return;
                    }

                    this.loadingRegister = true;
                    try {
                        const result = await window.OmniFirebaseAuth.registerWithEmail(this.name.trim(), this.email.trim(), this.password);
                        window.OmniFirebaseAuth?.showToast(`Akun berhasil dibuat! Selamat datang, ${this.name}!`, 'success');
                        setTimeout(() => {
                            window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } catch (err) {
                        console.error('Firebase Register Error:', err);
                        const msg = window.OmniFirebaseAuth?.formatFirebaseError(err) || err.message || 'Gagal mendaftar akun baru.';
                        this.errorMessage = msg;
                        window.OmniFirebaseAuth?.showToast(msg, 'error');

                        // Fallback jika Firebase gagal koneksi: submit form native Laravel Fortify
                        if (err.code === 'auth/network-request-failed' || err.code === 'auth/internal-error') {
                            e.target.submit();
                        }
                    } finally {
                        this.loadingRegister = false;
                    }
                }
            }">
                <!-- Tombol Daftar Cepat dengan Akun Google -->
                <button
                    type="button"
                    @click="handleGoogleRegister()"
                    :disabled="loadingGoogle || loadingRegister"
                    aria-label="Daftar cepat dengan Akun Google"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.39 7.35 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.61 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span x-show="!loadingGoogle">Daftar Cepat dengan Akun Google</span>
                    <span x-show="loadingGoogle" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menghubungkan Akun Google...</span>
                    </span>
                </button>

                <!-- Pembatas Visual -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Atau Lengkapi Data Pendaftaran</span>
                    </div>
                </div>

                <!-- Notifikasi Error Klien (Alpine.js) -->
                <div x-show="errorMessage" x-cloak class="mb-4 p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800 flex items-start justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 min-h-[32px] min-w-[32px] flex items-center justify-center rounded cursor-pointer" aria-label="Tutup notifikasi error">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Notifikasi Error Server (Laravel Fortify / Session) -->
                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800 shadow-2xs">
                        <div class="font-semibold mb-1 flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>Periksa kembali data pendaftaran Anda:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Pendaftaran -->
                <form method="POST" action="{{ route('register.store') }}" @submit="handleRegisterSubmit($event)" class="space-y-4">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Lengkap
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            x-model="name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Contoh: Sasa Cantika"
                            class="block w-full rounded-lg border @error('name') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-emerald-600 focus:ring-emerald-600/20 @enderror bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:ring-2 sm:text-sm transition min-h-[44px]"
                        />
                        @error('name')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            x-model="email"
                            required
                            autocomplete="email"
                            placeholder="nama@email.com"
                            class="block w-full rounded-lg border @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-emerald-600 focus:ring-emerald-600/20 @enderror bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:ring-2 sm:text-sm transition min-h-[44px]"
                        />
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Kata Sandi -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kata Sandi (Min. 8 Karakter)
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                required
                                autocomplete="new-password"
                                placeholder="Buat kata sandi aman (min. 8 karakter)"
                                class="block w-full rounded-lg border @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-emerald-600 focus:ring-emerald-600/20 @enderror bg-white px-3.5 py-2.5 pr-11 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:ring-2 sm:text-sm transition min-h-[44px]"
                            />
                            <button
                                type="button"
                                 @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center cursor-pointer"
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
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Konfirmasi Kata Sandi
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="passwordConfirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi kata sandi"
                                class="block w-full rounded-lg border @error('password_confirmation') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-emerald-600 focus:ring-emerald-600/20 @enderror bg-white px-3.5 py-2.5 pr-11 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:ring-2 sm:text-sm transition min-h-[44px]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center cursor-pointer"
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
                        @error('password_confirmation')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Tombol Daftar -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="loadingRegister || loadingGoogle"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition min-h-[44px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="!loadingRegister" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <svg x-show="loadingRegister" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-show="!loadingRegister">Daftar Akun Baru (Firebase)</span>
                            <span x-show="loadingRegister" x-cloak>Membuat Akun...</span>
                        </button>
                    </div>
                </form>

                <!-- Tautan Masuk -->
                <div class="mt-5 text-center">
                    <span class="text-xs text-slate-500">Sudah memiliki akun?</span>
                    <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline ml-1 inline-flex items-center min-h-[44px] py-2 px-1">
                        Masuk ke Portal
                    </a>
                </div>

                <!-- Footer Dalam Kartu -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('home') }}" class="font-medium text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1 min-h-[44px] py-2 px-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <span class="text-[11px] text-slate-400">OmniPharma Core v2.4</span>
                </div>
            </div>
        </div>

        <!-- Catatan Privasi -->
        <p class="mt-6 text-center text-xs text-slate-500">
            Data pribadi Anda dilindungi sesuai standar kerahasiaan medis farmasi.
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

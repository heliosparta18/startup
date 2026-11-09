<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun Pelanggan - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-slate-100/90 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 lg:p-8 font-sans">

    <!-- Container Utama Split Card Register (Sesuai Referensi Visual) -->
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row min-h-[640px]">

        <!-- KOLOM KIRI: Visual Hero / Ilustrasi & Brand Showcase -->
        <div class="lg:w-5/12 bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] p-8 sm:p-10 flex flex-col justify-between text-white relative overflow-hidden">
            <!-- Dekorasi Garis & Lingkaran Geometris Halus -->
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <!-- Header Brand Kolom Kiri -->
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-12 w-12 object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.45)] hover:scale-105 transition-transform duration-200">
                    <div>
                        <span class="block text-xs font-bold tracking-widest text-[#D4F94E] uppercase">OmniPharma Network</span>
                        <span class="block text-sm font-semibold text-emerald-100">Portal Pasien & Pelanggan</span>
                    </div>
                </div>

                <div class="mt-8">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Akses Layanan Medis Instan
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-snug">
                        Daftar Akun Pasien & Tebus Resep Tanpa Antre
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-200/80 mt-2 leading-relaxed">
                        Simpan riwayat resep obat Anda, nikmati notifikasi ketersediaan stok, dan lacak status persiapan obat secara waktu-nyata.
                    </p>
                </div>
            </div>

            <!-- Kartu Info Keunggulan Layanan -->
            <div class="my-6 py-4 flex items-center justify-center relative z-10">
                <div class="w-full max-w-[280px] rounded-2xl bg-white/5 border border-white/10 p-5 space-y-3 backdrop-blur-xs shadow-inner text-xs">
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0">✓</div>
                        <span>Penebusan Resep Dokter Terverifikasi SIP</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0">✓</div>
                        <span>Cek Stok Multi-Cabang Banyuwangi & Rogojampi</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0">✓</div>
                        <span>Live Tracking Status Pesanan (Reverb)</span>
                    </div>
                </div>
            </div>

            <!-- Footer Kolom Kiri -->
            <div class="relative z-10 text-[11px] text-emerald-300/70 flex items-center justify-between pt-4 border-t border-white/10">
                <span>Praktikum PSI Pak Devit</span>
                <span>v2.4 Production</span>
            </div>
        </div>

        <!-- KOLOM KANAN: Formulir Pendaftaran -->
        <div class="lg:w-7/12 p-6 sm:p-10 lg:p-12 flex flex-col justify-between bg-white" x-data="{
            name: '{{ old('name', '') }}',
            email: '{{ old('email', '') }}',
            password: '',
            passwordConfirmation: '',
            showPassword: false,
            loadingGoogle: false,
            loadingRegister: false,
            async handleGoogleRegister() {
                if (this.loadingGoogle || this.loadingRegister) return;
                this.loadingGoogle = true;
                try {
                    if (!window.OmniFirebaseAuth || typeof window.OmniFirebaseAuth.loginWithGoogle !== 'function') {
                        throw new Error('Modul autentikasi Firebase sedang dimuat. Silakan tunggu 1-2 detik.');
                    }
                    const result = await window.OmniFirebaseAuth.loginWithGoogle();
                    const userName = result.user.displayName || result.user.email;
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(`Pendaftaran berhasil! Selamat datang, ${userName}!`, 'success');
                    }
                    setTimeout(() => {
                        window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                    }, 600);
                } catch (err) {
                    console.error('Google Register Error:', err);
                    const errorMsg = window.OmniFirebaseAuth?.formatFirebaseError
                        ? window.OmniFirebaseAuth.formatFirebaseError(err)
                        : (err?.message || 'Gagal mendaftar dengan akun Google.');
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(errorMsg, 'error');
                    }
                } finally {
                    this.loadingGoogle = false;
                }
            },
            async handleRegisterSubmit(e) {
                e.preventDefault();
                if (!this.name || !this.email || !this.password) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan lengkapi seluruh kolom formulir.', 'error');
                    }
                    return;
                }
                if (this.password.length < 6) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Kata sandi minimal harus 6 karakter.', 'error');
                    }
                    return;
                }
                if (this.password !== this.passwordConfirmation) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Konfirmasi kata sandi tidak cocok.', 'error');
                    }
                    return;
                }

                this.loadingRegister = true;
                try {
                    if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.registerWithEmail === 'function') {
                        const result = await window.OmniFirebaseAuth.registerWithEmail(this.name, this.email, this.password);
                        if (window.OmniFirebaseAuth?.showToast) {
                            window.OmniFirebaseAuth.showToast(`Akun berhasil dibuat! Selamat datang, ${this.name}!`, 'success');
                        }
                        setTimeout(() => {
                            window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } else {
                        e.target.submit();
                    }
                } catch (err) {
                    console.error('Firebase Register Error:', err);
                    const errorMsg = window.OmniFirebaseAuth?.formatFirebaseError
                        ? window.OmniFirebaseAuth.formatFirebaseError(err)
                        : (err?.message || 'Gagal mendaftar akun baru.');
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(errorMsg, 'error');
                    }
                } finally {
                    this.loadingRegister = false;
                }
            }
        }">
            <div>
                <!-- Heading Form -->
                <!-- Mobile Brand Header -->
                <div class="flex items-center gap-2.5 mb-4 lg:hidden">
                    <img src="/logo.png" alt="OmniPharma" class="h-9 w-9 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.35)]">
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase">OmniPharma Network</span>
                </div>
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Buat Akun Baru</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar akun secara mudah dan mulai pesan obat dengan cepat.</p>
                </div>

                <!-- Tombol Daftar Cepat dengan Google -->
                <button
                    type="button"
                    @click="handleGoogleRegister()"
                    :disabled="loadingGoogle || loadingRegister"
                    aria-label="Daftar cepat dengan Akun Google"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.39 7.35 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.61 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span x-show="!loadingGoogle">Daftar Cepat dengan Akun Google</span>
                    <span x-show="loadingGoogle" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menghubungkan Akun Google...</span>
                    </span>
                </button>

                <!-- Pembatas Visual -->
                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Atau Isi Data Registrasi</span>
                    </div>
                </div>

                <!-- Notifikasi Error -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                        <div class="font-semibold mb-1">Periksa kembali data pendaftaran Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Pendaftaran -->
                <form method="POST" action="{{ route('register.store') }}" @submit="handleRegisterSubmit($event)" class="space-y-3.5">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nama Lengkap / Username
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
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
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
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                        />
                    </div>

                    <!-- Kata Sandi -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kata Sandi (Min. 6 Karakter)
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                required
                                autocomplete="new-password"
                                placeholder="Buat kata sandi aman"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center"
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

                    <!-- Konfirmasi Kata Sandi -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            x-model="passwordConfirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                        />
                    </div>

                    <!-- Tombol Submit -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="loadingRegister || loadingGoogle"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition min-h-[46px] cursor-pointer disabled:opacity-50"
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
                <div class="mt-4 text-center">
                    <span class="text-xs text-slate-500">Sudah memiliki akun?</span>
                    <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline ml-1 inline-flex items-center min-h-[44px]">
                        Masuk ke Portal
                    </a>
                </div>
            </div>

            <!-- Footer Bawah -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <a href="{{ route('home') }}" class="font-medium text-emerald-700 hover:underline inline-flex items-center gap-1">
                    <span>&larr; Kembali ke Beranda</span>
                </a>
                <span>OmniPharma Network Core</span>
            </div>
        </div>

    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>

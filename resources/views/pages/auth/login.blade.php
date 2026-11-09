<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Portal Apotek Multi-Klien - OmniPharma Network</title>
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

    <!-- Container Utama Split Card Login (Sesuai Referensi Visual) -->
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row min-h-[640px]">

        <!-- KOLOM KIRI: Visual Hero / Ilustrasi & Brand Showcase -->
        <div class="lg:w-5/12 bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] p-8 sm:p-10 flex flex-col justify-between text-white relative overflow-hidden">
            <!-- Dekorasi Garis & Lingkaran Geometris Halus -->
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <!-- Header Brand Kolom Kiri (Logo Transparan Berbayang) -->
            <div class="relative z-10">
                <div class="flex items-center gap-3.5">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-12 w-12 object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.45)] hover:scale-105 transition-transform duration-200">
                    <div>
                        <span class="block text-xs font-bold tracking-widest text-[#D4F94E] uppercase">OmniPharma Network</span>
                        <span class="block text-sm font-semibold text-emerald-100">Sistem Informasi Farmasi Terpadu</span>
                    </div>
                </div>

                <div class="mt-8">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-snug">
                        Pelayanan Farmasi Cepat, Akurat, dan Terpercaya
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-200/80 mt-2 leading-relaxed">
                        Manajemen multi-cabang dengan verifikasi OTP email, login Google sekali-klik, rotasi stok FEFO, serta pembayaran QRIS dinamis instan.
                    </p>
                </div>
            </div>

            <!-- Ilustrasi Vektor Farmasi Medis -->
            <div class="my-6 py-4 flex items-center justify-center relative z-10">
                <div class="w-full max-w-[280px] aspect-[4/3] rounded-2xl bg-white/5 border border-white/10 p-5 flex flex-col justify-between backdrop-blur-xs shadow-inner">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white">Verifikasi OTP Email</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/20 text-[#D4F94E] font-mono font-bold">2FA Aktif</span>
                    </div>

                    <div class="space-y-2 my-2">
                        <div class="h-2 w-3/4 bg-white/20 rounded-full"></div>
                        <div class="h-2 w-1/2 bg-white/15 rounded-full"></div>
                    </div>

                    <div class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-emerald-200">
                        <span>Multi-Cabang: BWI & Rogojampi</span>
                        <span class="font-bold text-white">Terhubung</span>
                    </div>
                </div>
            </div>

            <!-- Footer Kolom Kiri -->
            <div class="relative z-10 text-[11px] text-emerald-300/70 flex items-center justify-between pt-4 border-t border-white/10">
                <span>Praktikum PSI Pak Devit</span>
                <span>v2.4 Production</span>
            </div>
        </div>

        <!-- KOLOM KANAN: Formulir Masuk (Login Card) -->
        <div class="lg:w-7/12 p-6 sm:p-10 lg:p-12 flex flex-col justify-between bg-white" x-data="{
            authMode: 'otp', // 'otp' atau 'password'
            email: '{{ old('email', '') }}',
            password: '',
            otp: '',
            otpSent: false,
            otpCountdown: 300,
            timerStr: '05:00',
            timerInterval: null,
            demoOtpHint: '',
            showPassword: false,
            selectedRole: '',
            loadingOtpSend: false,
            loadingOtpVerify: false,
            loadingFirebase: false,
            loadingGoogle: false,

            selectDemo(roleKey, userEmail, userPass) {
                this.authMode = 'password';
                this.selectedRole = roleKey;
                this.email = userEmail;
                this.password = userPass;
            },

            async handleGoogleLogin() {
                if (this.loadingGoogle || this.loadingFirebase) return;
                this.loadingGoogle = true;
                try {
                    if (!window.OmniFirebaseAuth || typeof window.OmniFirebaseAuth.loginWithGoogle !== 'function') {
                        throw new Error('Modul autentikasi Firebase sedang dimuat. Silakan tunggu 1-2 detik.');
                    }
                    const result = await window.OmniFirebaseAuth.loginWithGoogle();
                    const userName = result.user.displayName || result.user.email;
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(`Selamat datang, ${userName}!`, 'success');
                    }
                    setTimeout(() => {
                        window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                    }, 600);
                } catch (err) {
                    console.error('Google Auth Error:', err);
                    const errorMsg = window.OmniFirebaseAuth?.formatFirebaseError
                        ? window.OmniFirebaseAuth.formatFirebaseError(err)
                        : (err?.message || 'Gagal masuk dengan akun Google.');
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(errorMsg, 'error');
                    }
                } finally {
                    this.loadingGoogle = false;
                }
            },

            async sendOtpCode() {
                if (!this.email) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan alamat email Anda.', 'error');
                    }
                    return;
                }
                this.loadingOtpSend = true;
                try {
                    const token = document.querySelector('meta[name=\'csrf-token\']')?.content;
                    const res = await fetch('/auth/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ email: this.email })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal mengirim kode OTP.');

                    this.otpSent = true;
                    this.demoOtpHint = data.demo_otp || '';
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(data.message || 'Kode OTP telah dikirimkan ke email Anda!', 'success');
                    }
                    this.startOtpTimer();
                } catch (err) {
                    console.error('Send OTP Error:', err);
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(err.message || 'Gagal mengirim kode OTP.', 'error');
                    }
                } finally {
                    this.loadingOtpSend = false;
                }
            },

            async verifyOtpCode() {
                if (!this.otp || this.otp.length !== 6) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan 6 digit kode OTP lengkap.', 'error');
                    }
                    return;
                }
                this.loadingOtpVerify = true;
                try {
                    const token = document.querySelector('meta[name=\'csrf-token\']')?.content;
                    const res = await fetch('/auth/verify-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ email: this.email, otp: this.otp })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Verifikasi OTP gagal.');

                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Verifikasi sukses! Mengalihkan ke dashboard...', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route('dashboard') }}';
                    }, 600);
                } catch (err) {
                    console.error('Verify OTP Error:', err);
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(err.message || 'Kode OTP tidak valid.', 'error');
                    }
                } finally {
                    this.loadingOtpVerify = false;
                }
            },

            startOtpTimer() {
                this.otpCountdown = 300;
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    this.otpCountdown--;
                    const m = Math.floor(this.otpCountdown / 60);
                    const s = this.otpCountdown % 60;
                    this.timerStr = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    if (this.otpCountdown <= 0) {
                        clearInterval(this.timerInterval);
                        this.timerStr = '00:00';
                    }
                }, 1000);
            },

            async handlePasswordLoginSubmit(e) {
                if (this.selectedRole) {
                    return true;
                }

                e.preventDefault();
                if (!this.email || !this.password) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan email/username dan kata sandi.', 'error');
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
                            window.location.href = result.session?.redirect || '{{ route('dashboard') }}';
                        }, 600);
                    } else {
                        e.target.submit();
                    }
                } catch (err) {
                    console.warn('Firebase login attempt fallback to Fortify:', err);
                    e.target.submit();
                } finally {
                    this.loadingFirebase = false;
                }
            }
        }">
            <div>
                <!-- Mobile Brand Header -->
                <div class="flex items-center gap-2.5 mb-4 lg:hidden">
                    <img src="/logo.png" alt="OmniPharma" class="h-9 w-9 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.35)]">
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase">OmniPharma Network</span>
                </div>

                <!-- Heading Form -->
                <div class="mb-5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih metode masuk cepat dengan Google atau verifikasi OTP email.</p>
                </div>

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

                <!-- Tab Mode: OTP Email vs Kata Sandi -->
                <div class="mt-5 p-1 rounded-xl bg-slate-100 flex items-center gap-1 text-xs font-bold">
                    <button
                        type="button"
                        @click="authMode = 'otp'"
                        :class="authMode === 'otp' ? 'bg-white text-emerald-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                        class="flex-1 py-2 rounded-lg transition min-h-[36px] flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span>Kode OTP Email (2FA)</span>
                    </button>
                    <button
                        type="button"
                        @click="authMode = 'password'"
                        :class="authMode === 'password' ? 'bg-white text-emerald-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                        class="flex-1 py-2 rounded-lg transition min-h-[36px] flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        <span>Kata Sandi & Demo</span>
                    </button>
                </div>

                <!-- ALUR 1: MASUK DENGAN KODE OTP EMAIL -->
                <div x-show="authMode === 'otp'" class="mt-4 space-y-4">
                    <!-- Banner Info Mode Demo OTP -->
                    <div x-show="demoOtpHint" x-cloak class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-xs text-emerald-900 font-medium flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <div>Kode OTP terkirim ke email Anda.</div>
                            <div class="mt-0.5 text-slate-700">Kode Verifikasi: <strong class="font-mono text-base text-emerald-700 select-all tracking-widest" x-text="demoOtpHint"></strong></div>
                        </div>
                    </div>

                    <!-- Input Email -->
                    <div>
                        <label for="otp-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email Pasien
                        </label>
                        <div class="relative">
                            <input
                                id="otp-email"
                                type="email"
                                x-model="email"
                                :disabled="otpSent"
                                placeholder="nama@email.com"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px] disabled:bg-slate-50 disabled:text-slate-500"
                            />
                            <button
                                x-show="otpSent"
                                type="button"
                                @click="otpSent = false; otp = ''; demoOtpHint = ''"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:underline"
                            >
                                Ganti
                            </button>
                        </div>
                    </div>

                    <!-- Langkah 1: Tombol Kirim OTP (Muncul jika belum dikirim) -->
                    <div x-show="!otpSent">
                        <button
                            type="button"
                            @click="sendOtpCode()"
                            :disabled="loadingOtpSend"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="!loadingOtpSend" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            <svg x-show="loadingOtpSend" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-show="!loadingOtpSend">Kirim Kode OTP ke Email</span>
                            <span x-show="loadingOtpSend" x-cloak>Mengirimkan Kode...</span>
                        </button>
                    </div>

                    <!-- Langkah 2: Input 6-Digit OTP & Tombol Verifikasi -->
                    <div x-show="otpSent" x-cloak class="space-y-4 pt-1">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="otp-code" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Masukkan 6-Digit Kode OTP
                                </label>
                                <span class="text-xs font-mono font-bold text-amber-600" x-text="'Sisa waktu: ' + timerStr"></span>
                            </div>
                            <input
                                id="otp-code"
                                type="text"
                                maxlength="6"
                                x-model="otp"
                                placeholder="Contoh: 123456"
                                class="block w-full text-center tracking-[0.5em] font-mono text-xl font-black rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 min-h-[48px]"
                            />
                        </div>

                        <button
                            type="button"
                            @click="verifyOtpCode()"
                            :disabled="loadingOtpVerify || otp.length !== 6"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="loadingOtpVerify" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-show="!loadingOtpVerify">Verifikasi & Masuk ke Portal</span>
                            <span x-show="loadingOtpVerify">Memverifikasi OTP...</span>
                        </button>

                        <div class="text-center text-xs">
                            <button
                                type="button"
                                @click="sendOtpCode()"
                                :disabled="loadingOtpSend || otpCountdown > 240"
                                class="text-emerald-700 font-bold hover:underline disabled:text-slate-400"
                            >
                                Kirim Ulang Kode OTP
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ALUR 2: MASUK DENGAN KATA SANDI & DEMO ROLE -->
                <div x-show="authMode === 'password'" x-cloak class="mt-4 space-y-4">
                    <form method="POST" action="{{ route('login.store') }}" @submit="handlePasswordLoginSubmit($event)" class="space-y-4">
                        @csrf

                        <!-- Kolom Email / Username -->
                        <div>
                            <label for="pwd-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Email atau Nama Pengguna
                            </label>
                            <input
                                id="pwd-email"
                                name="email"
                                type="text"
                                x-model="email"
                                required
                                autocomplete="email"
                                placeholder="nama@email.com atau username"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                            />
                        </div>

                        <!-- Kolom Sandi -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="pwd-pass" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
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
                                    id="pwd-pass"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    x-model="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan kata sandi"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 sm:text-sm transition min-h-[44px]"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none min-h-[44px] min-w-[44px] justify-center"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                >
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Masuk Sandi -->
                        <div>
                            <button
                                type="submit"
                                :disabled="loadingFirebase"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                            >
                                <svg x-show="!loadingFirebase" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                <svg x-show="loadingFirebase" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span x-show="!loadingFirebase">Masuk dengan Kata Sandi</span>
                                <span x-show="loadingFirebase" x-cloak>Memvalidasi Kredensial...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tautan Daftar -->
                <div class="mt-4 text-center">
                    <span class="text-xs text-slate-500">Belum memiliki akun pelanggan?</span>
                    <a href="{{ route('register') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline ml-1 inline-flex items-center min-h-[44px]">
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
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                    <button
                        type="button"
                        @click="selectDemo('super_admin', 'daffa@omnipharma.test', 'password')"
                        :class="selectedRole === 'super_admin' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer"
                    >
                        <span class="text-[11px] font-bold truncate">Super Admin</span>
                        <span class="text-[9px] text-slate-500 truncate">daffa@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('apt_bwi', 'bambang@omnipharma.test', 'password')"
                        :class="selectedRole === 'apt_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer"
                    >
                        <span class="text-[11px] font-bold truncate">Apoteker BWI</span>
                        <span class="text-[9px] text-slate-500 truncate">bambang@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('apt_rogo', 'dewi@omnipharma.test', 'password')"
                        :class="selectedRole === 'apt_rogo' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer"
                    >
                        <span class="text-[11px] font-bold truncate">Apoteker RGO</span>
                        <span class="text-[9px] text-slate-500 truncate">dewi@omnipharma.test</span>
                    </button>

                    <button
                        type="button"
                        @click="selectDemo('kasir_bwi', 'kasir.bwi@omnipharma.test', 'password')"
                        :class="selectedRole === 'kasir_bwi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700'"
                        class="p-2 rounded-lg border text-left transition min-h-[44px] flex flex-col justify-center cursor-pointer"
                    >
                        <span class="text-[11px] font-bold truncate">Kasir BWI</span>
                        <span class="text-[9px] text-slate-500 truncate">kasir.bwi@omnipharma.test</span>
                    </button>
                </div>
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

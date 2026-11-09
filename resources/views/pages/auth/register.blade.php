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
<body class="min-h-full bg-slate-100/90 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 lg:p-8 font-sans overflow-x-hidden">

    <!-- Container Utama Split Card Register (Sesuai Referensi Visual) -->
    <div class="w-full max-w-5xl bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row min-h-0 lg:min-h-[640px]">

        <!-- KOLOM KIRI: Visual Hero / Ilustrasi & Brand Showcase -->
        <div class="lg:w-5/12 bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] p-5 sm:p-8 lg:p-10 flex flex-col justify-between text-white relative overflow-hidden">
            <!-- Dekorasi Garis & Lingkaran Geometris Halus -->
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <!-- Header Brand Kolom Kiri (Logo Transparan Berbayang) -->
            <div class="relative z-10">
                <div class="flex items-center gap-3.5">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-12 w-12 object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.45)] hover:scale-105 transition-transform duration-200">
                    <div>
                        <span class="block text-xs font-bold tracking-widest text-[#D4F94E] uppercase">OmniPharma Network</span>
                        <span class="block text-sm font-semibold text-emerald-100">Portal Pasien & Pelanggan</span>
                    </div>
                </div>

                <div class="mt-6 sm:mt-8">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Verifikasi OTP Email Resmi
                    </span>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-white mt-3 leading-snug">
                        Daftar Akun Pasien & Validasi Email Terpercaya
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-200/80 mt-2 leading-relaxed">
                        Akun pasien terverifikasi 2FA dengan kode OTP email resmi untuk menjamin keamanan resep dokter dan riwayat obat Anda.
                    </p>
                </div>
            </div>

            <!-- Kartu Info Keunggulan Layanan -->
            <div class="my-4 sm:my-6 py-2 sm:py-4 flex items-center justify-center relative z-10">
                <div class="w-full max-w-[280px] rounded-2xl bg-white/5 border border-white/10 p-4 sm:p-5 space-y-2.5 sm:space-y-3 backdrop-blur-xs shadow-inner text-xs">
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0 font-bold">✓</div>
                        <span>Email Terverifikasi Otomatis via OTP</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0 font-bold">✓</div>
                        <span>Penebusan Resep Dokter Terintegrasi SIP</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-[#D4F94E] flex items-center justify-center shrink-0 font-bold">✓</div>
                        <span>Live Tracking Status Pesanan Real-Time</span>
                    </div>
                </div>
            </div>

            <!-- Footer Kolom Kiri -->
            <div class="relative z-10 text-[11px] text-emerald-300/70 flex items-center justify-between pt-4 border-t border-white/10">
                <span>Praktikum PSI Pak Devit</span>
                <span>v2.4 Production</span>
            </div>
        </div>

        <!-- KOLOM KANAN: Formulir Pendaftaran dengan Verifikasi OTP Email -->
        <div class="lg:w-7/12 p-5 sm:p-8 lg:p-12 flex flex-col justify-between bg-white" x-data="{
            username: '{{ old('name', '') }}',
            email: '{{ old('email', '') }}',
            password: '',
            passwordConfirmation: '',
            otp: '',
            otpStep: false,
            otpCountdown: 300,
            timerStr: '05:00',
            timerInterval: null,
            demoOtpHint: '',
            showPassword: false,
            loadingGoogle: false,
            loadingSendOtp: false,
            loadingRegister: false,

            async handleGoogleRegister() {
                if (this.loadingGoogle || this.loadingRegister || this.loadingSendOtp) return;
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

            async requestRegisterOtp() {
                if (!this.username.trim()) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan username atau nama lengkap Anda.', 'error');
                    }
                    return;
                }
                if (!this.email.trim() || !this.email.includes('@')) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan format alamat email yang valid.', 'error');
                    }
                    return;
                }
                if (!this.password || this.password.length < 6) {
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

                this.loadingSendOtp = true;
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
                    if (!res.ok) throw new Error(data.message || 'Gagal mengirim kode verifikasi OTP.');

                    this.otpStep = true;
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
                    this.loadingSendOtp = false;
                }
            },

            async completeRegisterWithOtp() {
                if (!this.otp || this.otp.length !== 6) {
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Silakan masukkan 6 digit kode OTP verifikasi.', 'error');
                    }
                    return;
                }

                this.loadingRegister = true;
                try {
                    const token = document.querySelector('meta[name=\'csrf-token\']')?.content;
                    const res = await fetch('/auth/register-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            username: this.username,
                            email: this.email,
                            password: this.password,
                            otp: this.otp
                        })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Pendaftaran gagal diverifikasi.');

                    // Jika ada Firebase SDK, sinkronkan juga akun ke Firebase Auth di background
                    if (window.OmniFirebaseAuth && typeof window.OmniFirebaseAuth.registerWithEmail === 'function') {
                        try {
                            await window.OmniFirebaseAuth.registerWithEmail(this.username, this.email, this.password);
                        } catch (fbErr) {
                            console.warn('Firebase registration sync note:', fbErr);
                        }
                    }

                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast('Pendaftaran akun berhasil & email terverifikasi!', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route('dashboard') }}';
                    }, 600);
                } catch (err) {
                    console.error('Register OTP Error:', err);
                    if (window.OmniFirebaseAuth?.showToast) {
                        window.OmniFirebaseAuth.showToast(err.message || 'Verifikasi OTP gagal.', 'error');
                    }
                } finally {
                    this.loadingRegister = false;
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
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">Buat Akun Pelanggan</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar instan dengan Google atau isi username & email dengan verifikasi OTP.</p>
                </div>

                <!-- Tombol Daftar Cepat dengan Google -->
                <button
                    type="button"
                    @click="handleGoogleRegister()"
                    :disabled="loadingGoogle || loadingRegister || loadingSendOtp"
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
                        <span class="bg-white px-2 sm:px-3 text-slate-400 font-semibold tracking-wider text-[11px] sm:text-xs">Atau Isi Data Registrasi Pasien</span>
                    </div>
                </div>

                <!-- Banner Informasi OTP Mode Demo (Jika sudah dikirim) -->
                <div x-show="otpStep && demoOtpHint" x-cloak class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-xs text-emerald-900 font-medium flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div>
                        <div>Kode OTP verifikasi telah dikirim ke <strong class="break-all" x-text="email"></strong>.</div>
                        <div class="mt-0.5 text-slate-700">Kode Verifikasi: <strong class="font-mono text-base text-emerald-700 select-all tracking-widest" x-text="demoOtpHint"></strong></div>
                    </div>
                </div>

                <!-- TAHAP 1: FORMULIR INPUT DATA AKUN -->
                <div x-show="!otpStep" class="space-y-3.5">
                    <!-- Input 1: Username -->
                    <div>
                        <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Username Pasien
                        </label>
                        <input
                            id="username"
                            type="text"
                            x-model="username"
                            required
                            autocomplete="username"
                            placeholder="Contoh: sasacantik12"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                        />
                    </div>

                    <!-- Input 2: Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email (Untuk Pengiriman OTP)
                        </label>
                        <input
                            id="email"
                            type="email"
                            inputmode="email"
                            autocomplete="email"
                            x-model="email"
                            required
                            placeholder="nama@email.com"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                        />
                    </div>

                    <!-- Input 3: Kata Sandi -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kata Sandi (Min. 6 Karakter)
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                x-model="password"
                                required
                                placeholder="Buat kata sandi aman"
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

                    <!-- Input 4: Konfirmasi Kata Sandi -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            id="password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            x-model="passwordConfirmation"
                            required
                            placeholder="Ulangi kata sandi"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                        />
                    </div>

                    <!-- Tombol Kirim OTP Verifikasi -->
                    <div class="pt-2">
                        <button
                            type="button"
                            @click="requestRegisterOtp()"
                            :disabled="loadingSendOtp || loadingGoogle"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition min-h-[46px] cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="!loadingSendOtp" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <svg x-show="loadingSendOtp" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-show="!loadingSendOtp">Kirim Kode Verifikasi OTP ke Email</span>
                            <span x-show="loadingSendOtp" x-cloak>Mengirimkan Kode OTP...</span>
                        </button>
                    </div>
                </div>

                <!-- TAHAP 2: VALIDASI KODE OTP EMAIL -->
                <div x-show="otpStep" x-cloak class="space-y-4 pt-1">
                    <div>
                        <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                            <label for="reg-otp" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Masukkan 6-Digit Kode OTP Email
                            </label>
                            <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200/70">
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span x-text="'Sisa: ' + timerStr"></span>
                            </span>
                        </div>
                        <div class="relative">
                            <input
                                id="reg-otp"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="one-time-code"
                                maxlength="6"
                                x-model="otp"
                                @input="otp = otp.replace(/[^0-9]/g, '').slice(0, 6)"
                                placeholder="000000"
                                class="block w-full text-center tracking-[0.35em] sm:tracking-[0.5em] pl-[0.35em] sm:pl-[0.5em] font-mono text-2xl font-black rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-slate-900 shadow-2xs placeholder:text-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 min-h-[52px]"
                            />
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1.5">
                            Kode OTP dikirimkan ke <strong class="text-slate-800 break-all" x-text="email"></strong>.
                        </p>
                    </div>

                    <!-- Tombol Verifikasi & Buat Akun -->
                    <button
                        type="button"
                        @click="completeRegisterWithOtp()"
                        :disabled="loadingRegister || otp.length !== 6"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition min-h-[48px] cursor-pointer disabled:opacity-50"
                    >
                        <svg x-show="loadingRegister" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-show="!loadingRegister">Validasi OTP & Buat Akun Sekarang</span>
                        <span x-show="loadingRegister">Membuat & Memvalidasi Akun...</span>
                    </button>

                    <!-- Opsi Kirim Ulang & Ubah Data -->
                    <div class="flex items-center justify-between text-xs pt-1 gap-2">
                        <button
                            type="button"
                            @click="otpStep = false; otp = ''"
                            class="inline-flex items-center gap-1.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-200 transition font-medium cursor-pointer"
                        >
                            <span>&larr; Ubah Email / Data</span>
                        </button>
                        <button
                            type="button"
                            @click="requestRegisterOtp()"
                            :disabled="loadingSendOtp || otpCountdown > 240"
                            class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-3.5 py-2 rounded-xl text-emerald-700 font-bold hover:bg-emerald-50 active:bg-emerald-100 transition cursor-pointer disabled:opacity-40 disabled:hover:bg-transparent disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span x-show="otpCountdown <= 240">Kirim Ulang OTP</span>
                            <span x-show="otpCountdown > 240" x-text="'Tunggu ' + (otpCountdown - 240) + 's'"></span>
                        </button>
                    </div>
                </div>

                <!-- Tautan Masuk -->
                <div class="mt-5 flex flex-wrap items-center justify-center gap-1 text-center">
                    <span class="text-xs text-slate-500">Sudah memiliki akun?</span>
                    <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center min-h-[44px]">
                        Masuk ke Portal
                    </a>
                </div>
            </div>

            <!-- Footer Bawah -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <a href="{{ route('home') }}" class="font-medium text-emerald-700 hover:underline inline-flex items-center gap-1 min-h-[44px] py-2">
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

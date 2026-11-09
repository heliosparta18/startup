<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil Akun - OmniPharma Network</title>
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
<body class="min-h-full bg-slate-100/90 text-slate-800 antialiased flex items-center justify-center p-3 sm:p-6 lg:p-8 font-sans overflow-x-hidden">

    <!-- Container Utama Split Card -->
    <div class="w-full max-w-5xl bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row min-h-0 lg:min-h-[640px]">

        <!-- KOLOM KIRI: Visual Showcase & Informasi Pasien -->
        <div class="lg:w-5/12 bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] p-5 sm:p-8 lg:p-10 flex flex-col justify-between text-white relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3.5">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-10 w-10 sm:h-12 sm:w-12 object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.45)] hover:scale-105 transition-transform duration-200 shrink-0">
                    <div class="min-w-0">
                        <span class="block text-xs font-bold tracking-widest text-[#D4F94E] uppercase truncate">OmniPharma Network</span>
                        <span class="block text-sm font-semibold text-emerald-100 truncate">Onboarding Pasien Baru</span>
                    </div>
                </div>

                <div class="mt-6 sm:mt-8">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Langkah Terakhir Pendaftaran
                    </span>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-white mt-3 leading-snug">
                        Satu Langkah Lagi Menuju Layanan Farmasi Penuh
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-200/80 mt-2 leading-relaxed">
                        Akun Google Anda berhasil diverifikasi. Lengkapi data kontak Anda untuk notifikasi status obat dan buat kata sandi agar Anda dapat masuk menggunakan email kapan saja.
                    </p>
                </div>
            </div>

            <!-- Kartu Preview Akun Terhubung -->
            <div class="my-4 sm:my-6 py-2 sm:py-4 relative z-10">
                <div class="rounded-2xl bg-white/5 border border-white/10 p-3.5 sm:p-4 space-y-3 backdrop-blur-xs text-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-emerald-400 shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-white text-sm truncate">{{ $user->name }}</div>
                            <div class="text-[11px] text-emerald-300 truncate">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-emerald-200">
                        <span>Penyedia Identitas:</span>
                        <span class="font-bold text-[#D4F94E]">Google Account</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-[11px] text-emerald-300/70 flex items-center justify-between pt-3 sm:pt-4 border-t border-white/10">
                <span>Data Medis Terenkripsi</span>
                <span>Standar SIA & SIP</span>
            </div>
        </div>

        <!-- KOLOM KANAN: Formulir Kelengkapan Profil -->
        <div class="lg:w-7/12 p-5 sm:p-8 lg:p-12 flex flex-col justify-between bg-white" x-data="{
            showPassword: false,
            submitting: false,
        }">
            <div>
                <!-- Mobile Header -->
                <div class="flex items-center gap-2.5 mb-4 lg:hidden">
                    <img src="/logo.png" alt="OmniPharma" class="h-8 w-8 sm:h-9 sm:w-9 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.35)] shrink-0">
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase">OmniPharma Network</span>
                </div>

                <div class="mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">Lengkapi Profil Pasien</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Isi nomor kontak aktif dan buat kata sandi untuk akun apotek Anda.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                        <div class="font-semibold mb-1">Periksa kembali formulir Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.complete-profile.save') }}" @submit="submitting = true" class="space-y-4">
                    @csrf

                    <!-- Nama Lengkap Pasien -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nama Lengkap Pasien
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                        />
                    </div>

                    <!-- Email (Read-only) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email (Akun Google)
                        </label>
                        <input
                            type="text"
                            value="{{ $user->email }}"
                            disabled
                            class="block w-full rounded-xl border border-slate-200 bg-slate-100/80 px-3.5 py-2.5 text-base sm:text-sm text-slate-600 cursor-not-allowed min-h-[44px]"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor WhatsApp / Telepon -->
                        <div>
                            <label for="telepon" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                No. WhatsApp / Telepon
                            </label>
                            <input
                                id="telepon"
                                name="telepon"
                                type="tel"
                                inputmode="tel"
                                autocomplete="tel"
                                value="{{ old('telepon') }}"
                                required
                                placeholder="Contoh: 081234567890"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                            />
                        </div>

                        <!-- Alamat Domisili -->
                        <div>
                            <label for="alamat" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Alamat Domisili / Kota
                            </label>
                            <input
                                id="alamat"
                                name="alamat"
                                type="text"
                                autocomplete="street-address"
                                value="{{ old('alamat') }}"
                                required
                                placeholder="Contoh: Banyuwangi Kota"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                            />
                        </div>
                    </div>

                    <!-- Buat Kata Sandi Akun -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Buat Kata Sandi Akun
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Min. 6 karakter"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 rounded-lg min-h-[44px] min-w-[44px] justify-center transition"
                                >
                                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Konfirmasi Kata Sandi
                            </label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi kata sandi"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-900 shadow-2xs placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition min-h-[44px]"
                            />
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-3">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition min-h-[48px] cursor-pointer disabled:opacity-50 text-center leading-snug"
                        >
                            <span x-show="!submitting">Simpan Profil & Lanjutkan ke Dashboard</span>
                            <span x-show="submitting" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Menyimpan Data Pasien...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Bawah -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4 text-xs text-slate-400">
                <span>OmniPharma Network Identity System</span>
                <span>Terverifikasi SIA</span>
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>

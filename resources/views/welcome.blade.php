<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniPharma Network - Apotek Resmi &amp; Pelayanan Resep Terpercaya</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 flex flex-col font-sans selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <!-- Header & Navigasi Utama -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[4rem] sm:min-h-[4.5rem] py-2 sm:py-0 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-xl p-1 min-h-[44px] shrink-0" aria-label="OmniPharma Beranda">
                <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 sm:h-11 sm:w-11 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.3)] group-hover:scale-105 transition-transform duration-200">
                <span class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">OmniPharma</span>
            </a>
            <!-- Navigasi Pintar Cabang & Kredensial -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="hidden lg:flex items-center gap-2.5 text-xs text-slate-700 bg-white border border-slate-200/90 px-3.5 py-1.5 rounded-xl shadow-2xs">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    <div class="flex items-center gap-1.5 leading-none">
                        <span class="font-bold text-slate-900">Apotek Terdaftar:</span>
                        <span class="font-medium text-emerald-800">Banyuwangi &amp; Rogojampi</span>
                    </div>
                </div>

                <!-- Tombol Akses Cepat Katalog -->
                <a
                    href="{{ route('katalog') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Katalog Obat</span>
                </a>

                @auth
                    @php
                        $userAuth = auth()->user();
                        $userRoleLabel = match($userAuth->role ?? '') {
                            'super_admin' => 'Super Admin',
                            'apoteker' => 'Apoteker',
                            'admin' => 'Kasir Staf',
                            default => 'Pasien'
                        };
                        $dashboardTarget = match($userAuth->role ?? '') {
                            'super_admin' => route('admin.dashboard'),
                            'apoteker' => route('tenant.dashboard', ['client' => ($userAuth->email === 'dewi@omnipharma.test' ? 'rogojampi' : 'banyuwangi-kota')]),
                            'admin' => route('admin.kasir.ledger'),
                            default => route('dashboard')
                        };
                    @endphp
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs text-slate-700">
                        <span class="font-bold">{{ $userAuth->name ?? 'Pengguna' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">{{ $userRoleLabel }}</span>
                    </div>
                    <a
                        href="{{ $dashboardTarget }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span>Masuk Dashboard</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs sm:text-sm font-semibold transition min-h-[44px] cursor-pointer"
                            aria-label="Keluar dari akun"
                        >
                            Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 space-y-12 sm:space-y-16 lg:space-y-24 pb-16 sm:pb-20">

        <!-- HERO SECTION: Sambutan Hangat & Ajakan Bertindak -->
        <section class="relative overflow-hidden pt-8 pb-12 sm:pt-12 sm:pb-16 lg:pt-20 lg:pb-24 border-b border-slate-200/80 bg-gradient-to-b from-white via-slate-50/50 to-[#F8FAFC]">
            <!-- Aksen Garis Geometris Halus (Non-Slop) -->
            <div class="absolute -top-40 right-0 w-96 h-96 rounded-full bg-emerald-50/60 blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- Teks Hero Kiri -->
                    <div class="lg:col-span-7 space-y-5 sm:space-y-6 text-center lg:text-left">
                        <div class="inline-block text-xs font-bold uppercase tracking-wider text-emerald-700 mb-1">
                            Apotek Resmi Berlisensi SIPA • Jaminan Obat Asli 100%
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                            Beli Obat Asli &amp; Tebus Resep <span class="text-emerald-600">dari Apotek Resmi</span>
                        </h1>

                        <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                            Layanan obat bebas, vitamin, dan resep dokter dengan jaminan keaslian obat dari apotek berizin di Banyuwangi dan Rogojampi. Pemeriksaan dan verifikasi langsung oleh apoteker.
                        </p>
                        <!-- Tombol CTA Utama -->
                        <div class="pt-2">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center lg:justify-start gap-3">
                                @auth
                                    @php
                                        $heroDashboardTarget = match(auth()->user()->role ?? '') {
                                            'super_admin' => route('admin.dashboard'),
                                            'apoteker' => route('tenant.dashboard', ['client' => (auth()->user()->email === 'dewi@omnipharma.test' ? 'rogojampi' : 'banyuwangi-kota')]),
                                            'admin' => route('admin.kasir.ledger'),
                                            default => route('dashboard')
                                        };
                                    @endphp
                                    <a
                                        href="{{ $heroDashboardTarget }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition min-h-[48px]"
                                    >
                                        <span>Buka Dashboard</span>
                                    </a>
                                    <a
                                        href="{{ route('katalog') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-2xs transition min-h-[48px]"
                                    >
                                        <span>Katalog Obat</span>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('katalog') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition min-h-[48px]"
                                    >
                                        <span>Mulai Belanja</span>
                                    </a>
                                    <a
                                        href="{{ route('login') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-2xs transition min-h-[48px]"
                                    >
                                        <span>Masuk Portal</span>
                                    </a>
                                    <a
                                        href="{{ route('register') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-transparent bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition min-h-[48px]"
                                    >
                                        <span>Daftar Akun</span>
                                    </a>
                                @endauth
                            </div>
                        </div>

                        <!-- Info Jaminan Pelayanan -->
                        <div class="pt-3 flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 text-xs font-semibold text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Jaminan Obat Asli 100%
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Pembayaran QRIS &amp; Kasir
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Konsultasi Apoteker Berlisensi
                            </span>
                        </div>

                    </div>

                    <!-- Kartu Ringkasan Apotek Cabang Resmi -->
                    <div class="lg:col-span-5 flex justify-center w-full">
                        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-sm space-y-4">
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <img src="/logo.png" alt="OmniPharma" class="h-8 w-8 object-contain">
                                    <div>
                                        <h3 class="font-extrabold text-slate-900 text-sm leading-tight">Apotek OmniPharma</h3>
                                        <p class="text-[11px] text-slate-500">Jaringan Apotek Berlisensi Resmi</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    6 Cabang Aktif
                                </span>
                            </div>


                            <!-- Daftar Cabang Resmi -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi Pelayanan Obat:</span>
                                </div>

                                <div class="space-y-2 pr-1.5 focus:outline-none" style="max-height: 168px; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #94a3b8 #f1f5f9;">
                                    <!-- Cabang 1: Banyuwangi Kota -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Banyuwangi Kota</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">24 Jam</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Ahmad Yani No. 45, Banyuwangi</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Bambang, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Cabang 2: Rogojampi -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Rogojampi</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 text-slate-700">07:00 - 22:00</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Rogojampi No. 12, Rogojampi</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Dewi Sartika, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'rogojampi']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Cabang 3: Genteng -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Genteng</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200/60">07:30 - 22:00</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Gajah Mada No. 88, Genteng</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Arya Dwi Mahendra, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'genteng']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Cabang 4: Muncar -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Muncar</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 text-slate-700">08:00 - 21:00</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Muncar No. 24, Muncar</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Siti Rahma, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'muncar']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Cabang 5: Giri & Kalipuro -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Giri &amp; Kalipuro</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 text-slate-700">07:00 - 21:00</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raden Wijaya No. 18, Giri</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Farhan Ramadhan, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'giri']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Cabang 6: Cluring -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-xs text-slate-900">Cabang Cluring</h4>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 text-slate-700">08:00 - 21:00</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Benculuk No. 5, Cluring</p>
                                            <p class="text-[10px] text-emerald-700 font-medium mt-1">Apoteker: apt. Nurul Hidayah, S.Farm</p>
                                        </div>
                                        @auth
                                            <a href="{{ route('customer.home', ['client' => 'cluring']) }}" class="shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline min-h-[36px] flex items-center">
                                                Buka &rarr;
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Mini Info -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Obat 100% Asli
                                </span>
                                <span>Distributor Resmi PBF</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Teks Berjalan Dukungan Resmi Pemerintah & Regulasi (Full Width) -->
                <div class="mt-8 sm:mt-10 pt-4 border-t border-slate-200/60 overflow-hidden relative rounded-2xl bg-white/70 border border-slate-200/70 p-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100/90 px-2.5 py-1 rounded-md shrink-0">
                            Lisensi &amp; Regulasi:
                        </span>
                        <div class="overflow-hidden relative w-full select-none" style="mask-image: linear-gradient(to right, transparent, black 4%, black 96%, transparent);">
                            <div class="animate-marquee flex items-center gap-8 whitespace-nowrap text-xs font-medium text-slate-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Kementerian Kesehatan RI (Kemenkes)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Badan Pengawas Obat &amp; Makanan (BPOM)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Ikatan Apoteker Indonesia (IAI)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Standar Cara Distribusi Obat yang Baik (CDOB)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Dinas Kesehatan Kabupaten Banyuwangi
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Standar Pembayaran QRIS (Bank Indonesia)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Pedagang Besar Farmasi (PBF) Resmi Terdaftar
                                </span>
                                <!-- Duplicate for infinite marquee loop -->
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Kementerian Kesehatan RI (Kemenkes)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Badan Pengawas Obat &amp; Makanan (BPOM)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Ikatan Apoteker Indonesia (IAI)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Standar Cara Distribusi Obat yang Baik (CDOB)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Dinas Kesehatan Kabupaten Banyuwangi
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Standar Pembayaran QRIS (Bank Indonesia)
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Pedagang Besar Farmasi (PBF) Terdaftar
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PANDUAN: Alur Pelayanan Pasien di Apotek -->
        <section id="cara-tebus" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Pelayanan Resep &amp; Obat</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Alur Pelayanan Pasien di Apotek
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Empat langkah mudah untuk mendapatkan obat dan menebus resep dokter dengan aman.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Langkah 1: Pilih Cabang -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs flex items-center justify-center mb-4 border border-emerald-200/60">
                            01
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-2">Pilih Cabang Apotek</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pilih lokasi apotek terdekat di Banyuwangi Kota atau Rogojampi untuk mengecek ketersediaan stok fisik obat secara akurat.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md inline-block">
                            Stok Fisik Tersedia
                        </span>
                    </div>
                </div>

                <!-- Langkah 2: Cari Obat / Unggah Resep -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs flex items-center justify-center mb-4 border border-emerald-200/60">
                            02
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-2">Pilih Obat / Unggah Resep</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pilih obat bebas langsung atau unggah foto resep dokter untuk ditelaah keabsahan dan dosisnya oleh apoteker.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md inline-block">
                            Pemeriksaan Apoteker
                        </span>
                    </div>
                </div>

                <!-- Langkah 3: Bayar dengan QRIS -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs flex items-center justify-center mb-4 border border-emerald-200/60">
                            03
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-2">Pembayaran Mudah</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Selesaikan transaksi praktis menggunakan pembayaran digital QRIS instan atau bayar tunai di kasir apotek.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md inline-block">
                            QRIS &amp; Tunai Kasir
                        </span>
                    </div>
                </div>

                <!-- Langkah 4: Obat Diserahkan -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs flex items-center justify-center mb-4 border border-emerald-200/60">
                            04
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-2">Obat Siap Diserahkan</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Obat disiapkan rapi lengkap dengan etiket aturan pakai resmi dan siap diambil di apotek atau dikirim ke lokasi Anda.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md inline-block">
                            Etiket Resmi SIPA
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- INFORMASI KEUNGGULAN LAYANAN APOTEK -->
        <section id="keunggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-50/80 border border-slate-200/90 p-6 sm:p-8 lg:p-10 shadow-xs relative overflow-hidden">
                <div class="max-w-2xl mb-8">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Komitmen Layanan Farmasi</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-1">
                        Standar Pelayanan Kefarmasian Resmi
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2">
                        Setiap resep dan sediaan obat ditangani langsung sesuai ketentuan izin operasional SIA dan pengawasan Apoteker SIPA.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 text-slate-900">
                    <!-- 1. Telaah Resep Dokter -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Telaah Resep Dokter</h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                Pemeriksaan kelengkapan resep, keabsahan SIP dokter, dosis, dan ada tidaknya interaksi obat oleh Apoteker resmi.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                            Apoteker Berlisensi SIPA
                        </div>
                    </div>

                    <!-- 2. Jaminan 100% Obat Asli -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Jaminan Obat Asli</h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                Pasokan obat bersumber langsung dari Pedagang Besar Farmasi (PBF) resmi berizin Kemenkes RI dan terdaftar di BPOM.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                            Distributor PBF Terdaftar
                        </div>
                    </div>

                    <!-- 3. Penyimpanan Standar Rantai Dingin -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Penyimpanan Terjaga</h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                Pengawasan suhu ruang dan rantai dingin berstandar CDOB untuk menjaga mutu dan stabilitas sediaan obat.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                            Standar CDOB &amp; Suhu Mutu
                        </div>
                    </div>

                    <!-- 4. Pelayanan & Pengambilan Fleksibel -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Ambil di Cabang / Antar</h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                Obat siap diambil di cabang apotek terdekat atau dikirim dengan pengemasan rapi dan terjaga kerahasiaannya.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                            Pengemasan Privasi Aman
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Resmi Apotek -->
    <footer class="border-t border-slate-200 bg-white py-12 px-4 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <!-- Kolom 1: Profil -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="/logo.png" alt="OmniPharma" class="h-7 w-7 object-contain">
                    <span class="font-extrabold text-base text-slate-900 tracking-tight">OmniPharma</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Jaringan apotek resmi berizin melayani kebutuhan obat bebas, suplemen, dan tebus resep dokter dengan pendampingan Apoteker berlisensi SIPA.
                </p>
                <div class="text-[11px] text-slate-400">
                    SIA: 503/SIA/BWI/2026 • Standar Kemenkes RI
                </div>
            </div>

            <!-- Kolom 2: Layanan -->
            <div class="space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Layanan Farmasi</h4>
                <ul class="space-y-1.5 text-xs text-slate-500">
                    <li>Tebus Resep Dokter</li>
                    <li>Sediaan Obat Bebas &amp; Rutin</li>
                    <li>Vitamin &amp; Nutrisi Kesehatan</li>
                    <li>Konsultasi Obat Apoteker</li>
                </ul>
            </div>

            <!-- Kolom 3: Lokasi Cabang -->
            <div class="space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Cabang Apotek</h4>
                <ul class="space-y-2 text-xs text-slate-500">
                    <li>
                        <strong class="text-slate-800 block">Cabang Banyuwangi Kota</strong>
                        <span>Jl. Ahmad Yani No. 45 • Layanan 24 Jam</span>
                    </li>
                    <li>
                        <strong class="text-slate-800 block">Cabang Rogojampi</strong>
                        <span>Jl. Raya Rogojampi No. 12 • 07:00 - 22:00 WIB</span>
                    </li>
                </ul>
            </div>

            <!-- Kolom 4: Kontak & Bantuan -->
            <div class="space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Bantuan &amp; Kontak</h4>
                <ul class="space-y-1.5 text-xs text-slate-500">
                    <li>Layanan Apotek: (0333) 412345</li>
                    <li>Email Layanan: apotek@omnipharma.co.id</li>
                    <li>Metode Bayar: QRIS &amp; Tunai Kasir</li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-400 text-[11px]">
            <p>&copy; 2026 Apotek OmniPharma. Seluruh hak cipta dilindungi undang-undang.</p>
            <p>Standar Pelayanan Kefarmasian • Permenkes RI No. 73/2016</p>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

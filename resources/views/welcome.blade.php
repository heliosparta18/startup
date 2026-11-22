<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light h-full bg-slate-50 antialiased" data-force-light="true" style="color-scheme: light;">
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
    <script>
        (function() {
            const root = document.documentElement;
            root.classList.remove('dark');
            root.classList.add('light');
            root.setAttribute('data-force-light', 'true');
            root.style.colorScheme = 'light';
        })();
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 flex flex-col font-sans selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <!-- Header & Navigasi Utama -->
    <header x-data="{ mobileNav: false }" @click.outside="mobileNav = false" @keydown.escape.window="mobileNav = false" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[3.75rem] sm:min-h-[4.25rem] py-2 sm:py-0 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-xl p-1 min-h-[44px] shrink-0" aria-label="OmniPharma Beranda">
                <img src="/logo.png" alt="OmniPharma Logo" class="h-8 w-8 sm:h-10 sm:w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.3)] group-hover:scale-105 transition-transform duration-200">
                <span class="text-[15px] sm:text-lg font-extrabold text-slate-900 tracking-tight">OmniPharma</span>
            </a>

            <!-- Navigasi Pintar Cabang & Kredensial -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
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

                @guest
                    <a
                        href="{{ route('login') }}"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Katalog Obat</span>
                    </a>
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold transition min-h-[44px]"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="hidden md:inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold transition min-h-[44px]"
                    >
                        Daftar
                    </a>
                @endguest

                @auth
                    @php
                        $userAuth = auth()->user();
                        $userRoleLabel = match($userAuth->role ?? '') {
                            'super_admin' => 'Super Admin',
                            'apoteker' => 'Apoteker',
                            'admin', 'kasir' => 'Kasir',
                            default => 'Pasien'
                        };
                        $dashboardTarget = method_exists($userAuth, 'homeRoute') ? $userAuth->homeRoute() : route('dashboard');
                    @endphp
                    <a
                        href="{{ route('katalog') }}"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs sm:text-sm font-bold transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Katalog Obat</span>
                    </a>
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs text-slate-700">
                        <span class="font-bold max-w-[140px] truncate">{{ $userAuth->name ?? 'Pengguna' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">{{ $userRoleLabel }}</span>
                    </div>
                    <a
                        href="{{ $dashboardTarget }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span class="hidden sm:inline">Masuk Dashboard</span>
                        <span class="sm:hidden">Dashboard</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:inline">
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

                <!-- Tombol Hamburger Mobile -->
                <button
                    type="button"
                    @click="mobileNav = !mobileNav"
                    class="md:hidden inline-flex items-center justify-center w-11 h-11 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                    :aria-expanded="mobileNav.toString()"
                    aria-label="Buka menu navigasi"
                >
                    <svg x-show="!mobileNav" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileNav" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Panel Navigasi Mobile -->
        <div x-show="mobileNav" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-3 shadow-lg max-h-[80dvh] overflow-y-auto">
            <div class="flex items-center justify-between text-xs bg-emerald-50/70 border border-emerald-200/70 rounded-xl px-3.5 py-2.5 text-emerald-900">
                <span class="font-bold">Apotek Terdaftar</span>
                <span class="font-semibold">Banyuwangi &amp; Rogojampi</span>
            </div>

            @auth
                <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $userAuth->name ?? 'Pengguna' }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $userAuth->email ?? '' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase shrink-0">{{ $userRoleLabel }}</span>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ $dashboardTarget }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 text-white text-sm font-bold min-h-[44px]">
                        Masuk Dashboard
                    </a>
                    <a href="{{ route('katalog') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold min-h-[44px]">
                        Katalog Obat Resmi
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-sm font-semibold min-h-[44px] cursor-pointer">
                            Keluar Akun
                        </button>
                    </form>
                </div>
            @else
                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-700 text-white text-sm font-bold min-h-[44px]">
                        Masuk Portal
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-sm font-bold min-h-[44px]">
                        Daftar Akun
                    </a>
                </div>
            @endauth
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 space-y-8 sm:space-y-14 lg:space-y-20 pb-10 sm:pb-16 lg:pb-20">

        <!-- HERO SECTION: Sambutan Hangat & Ajakan Bertindak -->
        <section class="relative overflow-hidden pt-5 pb-7 sm:pt-12 sm:pb-16 lg:pt-20 lg:pb-24 border-b border-slate-200/80 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-center">

                    <!-- Teks Hero Kiri -->
                    <div class="lg:col-span-7 space-y-3.5 sm:space-y-5 text-left">
                        <div class="inline-block text-[11px] sm:text-xs font-bold uppercase tracking-wider text-emerald-700">
                            Apotek Resmi Berlisensi SIPA • Jaminan Obat Asli 100%
                        </div>

                        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-[1.2] sm:leading-tight">
                            Beli Obat Asli &amp; Tebus Resep <span class="text-emerald-700">dari Apotek Resmi</span>
                        </h1>

                        <p class="text-[13px] sm:text-base text-slate-600 leading-relaxed max-w-2xl">
                            Layanan obat bebas, vitamin, dan resep dokter dengan jaminan keaslian obat dari apotek berizin di Banyuwangi dan Rogojampi. Pemeriksaan dan verifikasi langsung oleh apoteker.
                        </p>
                        <!-- Tombol CTA Utama -->
                        <div class="pt-1 sm:pt-2">
                            @auth
                                @php
                                    $heroDashboardTarget = method_exists(auth()->user(), 'homeRoute') ? auth()->user()->homeRoute() : route('dashboard');
                                @endphp
                                <div class="grid grid-cols-2 sm:flex sm:flex-row sm:items-center justify-start gap-2.5 sm:gap-3">
                                    <a
                                        href="{{ $heroDashboardTarget }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-[13px] sm:text-sm shadow-sm transition min-h-[44px]"
                                    >
                                        <span>Buka Dashboard</span>
                                    </a>
                                    <a
                                        href="{{ route('katalog') }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-[13px] sm:text-sm shadow-2xs transition min-h-[44px]"
                                    >
                                        <span>Katalog Obat</span>
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:flex sm:flex-row sm:items-center justify-start gap-2.5 sm:gap-3">
                                    <a
                                        href="{{ route('login') }}"
                                        class="col-span-2 sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-[13px] sm:text-sm shadow-sm transition min-h-[44px]"
                                    >
                                        <span>Mulai Belanja</span>
                                    </a>
                                    <a
                                        href="{{ route('login') }}"
                                        class="sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-6 py-2.5 sm:py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-[13px] sm:text-sm shadow-2xs transition min-h-[44px]"
                                    >
                                        <span>Masuk Portal</span>
                                    </a>
                                    <a
                                        href="{{ route('register') }}"
                                        class="sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-6 py-2.5 sm:py-3 rounded-xl border border-transparent bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[13px] sm:text-sm transition min-h-[44px]"
                                    >
                                        <span>Daftar Akun</span>
                                    </a>
                                </div>
                            @endauth
                        </div>

                        <!-- Info Jaminan Pelayanan -->
                        <div class="pt-1 sm:pt-2 flex flex-wrap items-center justify-start gap-x-3.5 gap-y-1.5 sm:gap-4 text-[11px] sm:text-xs font-semibold text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Jaminan Obat Asli 100%
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Pembayaran QRIS &amp; Kasir
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Konsultasi Apoteker Berlisensi
                            </span>
                        </div>

                    </div>

                    <!-- Kartu Ringkasan Apotek Cabang Resmi -->
                    <div class="lg:col-span-5 flex justify-start lg:justify-center w-full">
                        <div
                            x-data="{
                                branchSlide: 0,
                                totalBranches: 2,
                                touchStartX: 0,
                                touchEndX: 0,
                                handleTouchStart(e) {
                                    this.touchStartX = e.changedTouches[0].screenX;
                                },
                                handleTouchEnd(e) {
                                    this.touchEndX = e.changedTouches[0].screenX;
                                    if (this.touchStartX - this.touchEndX > 45) {
                                        this.branchSlide = (this.branchSlide + 1) % this.totalBranches;
                                    } else if (this.touchEndX - this.touchStartX > 45) {
                                        this.branchSlide = (this.branchSlide - 1 + this.totalBranches) % this.totalBranches;
                                    }
                                }
                            }"
                            class="w-full lg:max-w-md bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 p-3.5 sm:p-5 shadow-sm space-y-3"
                        >
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="/logo.png" alt="OmniPharma" class="h-7 w-7 sm:h-8 sm:w-8 object-contain shrink-0">
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-slate-900 text-[13px] sm:text-sm leading-tight truncate">Apotek OmniPharma</h3>
                                        <p class="text-[11px] text-slate-500 leading-tight truncate">Jaringan Apotek Berlisensi Resmi</p>
                                    </div>
                                </div>
                                <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                    2 Cabang Aktif
                                </span>
                            </div>

                            <!-- SLIDER CABANG: Tampilan Khusus Mobile & Tablet (Kompak & Geser Manual) -->
                            <div class="lg:hidden space-y-2.5">
                                <div class="flex items-center justify-between gap-1 pb-0.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pilih Lokasi Apotek:</span>
                                    <div class="inline-flex p-0.5 rounded-lg bg-slate-100 border border-slate-200/70">
                                        <button
                                            type="button"
                                            @click="branchSlide = 0"
                                            :class="branchSlide === 0 ? 'bg-white text-emerald-800 shadow-2xs font-bold border border-emerald-200/60' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                            class="px-2.5 py-1 rounded-md text-[10px] transition cursor-pointer min-h-[28px]"
                                        >
                                            Banyuwangi Kota
                                        </button>
                                        <button
                                            type="button"
                                            @click="branchSlide = 1"
                                            :class="branchSlide === 1 ? 'bg-white text-emerald-800 shadow-2xs font-bold border border-emerald-200/60' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                            class="px-2.5 py-1 rounded-md text-[10px] transition cursor-pointer min-h-[28px]"
                                        >
                                            Rogojampi
                                        </button>
                                    </div>
                                </div>

                                <!-- Viewport Slide Touch (Mobile) -->
                                <div
                                    class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white"
                                    @touchstart.passive="handleTouchStart($event)"
                                    @touchend.passive="handleTouchEnd($event)"
                                >
                                    <div
                                        class="flex transition-transform duration-300 ease-out"
                                        :style="'transform: translateX(-' + (branchSlide * 100) + '%)'"
                                    >
                                        <!-- Slide 0: Banyuwangi Kota (Default) -->
                                        <div class="w-full shrink-0 p-1">
                                            @auth
                                                <form method="POST" action="{{ route('branch.switch') }}">
                                                    @csrf
                                                    <input type="hidden" name="branch" value="banyuwangi-kota">
                                                    <input type="hidden" name="redirect_to" value="{{ route('dashboard') }}">
                                                    <button type="submit" class="w-full text-left p-3 rounded-xl bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40 transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                                <h4 class="font-bold text-[13px] text-slate-900">Cabang Banyuwangi Kota</h4>
                                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 shrink-0">24 Jam</span>
                                                            </div>
                                                            <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 shadow-2xs">
                                                                Masuk &rarr;
                                                            </span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-600 mt-1">Jl. Ahmad Yani No. 45, Banyuwangi</p>
                                                        <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Bambang, S.Farm</p>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('customer.catalog', ['client' => 'banyuwangi-kota']) }}" class="block p-3 rounded-xl bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                            <h4 class="font-bold text-[13px] text-slate-900">Cabang Banyuwangi Kota</h4>
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 shrink-0">24 Jam</span>
                                                        </div>
                                                        <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 shadow-2xs">
                                                            Masuk &rarr;
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-600 mt-1">Jl. Ahmad Yani No. 45, Banyuwangi</p>
                                                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Bambang, S.Farm</p>
                                                </a>
                                            @endauth
                                        </div>

                                        <!-- Slide 1: Rogojampi -->
                                        <div class="w-full shrink-0 p-1">
                                            @auth
                                                <form method="POST" action="{{ route('branch.switch') }}">
                                                    @csrf
                                                    <input type="hidden" name="branch" value="rogojampi">
                                                    <input type="hidden" name="redirect_to" value="{{ route('dashboard') }}">
                                                    <button type="submit" class="w-full text-left p-3 rounded-xl bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40 transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                                <h4 class="font-bold text-[13px] text-slate-900">Cabang Rogojampi</h4>
                                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 shrink-0">07:00 - 22:00</span>
                                                            </div>
                                                            <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 shadow-2xs">
                                                                Masuk &rarr;
                                                            </span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-600 mt-1">Jl. Raya Rogojampi No. 12, Rogojampi</p>
                                                        <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Dinda Ayu Shafira, S.Farm.</p>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('customer.catalog', ['client' => 'rogojampi']) }}" class="block p-3 rounded-xl bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                            <h4 class="font-bold text-[13px] text-slate-900">Cabang Rogojampi</h4>
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 shrink-0">07:00 - 22:00</span>
                                                        </div>
                                                        <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 shadow-2xs">
                                                            Masuk &rarr;
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-600 mt-1">Jl. Raya Rogojampi No. 12, Rogojampi</p>
                                                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Dinda Ayu Shafira, S.Farm.</p>
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>

                                <!-- Dots & Navigasi Mobile -->
                                <div class="flex items-center justify-between pt-0.5 px-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="branchSlide = 0"
                                            :class="branchSlide === 0 ? 'w-5 bg-emerald-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                                            class="h-1.5 rounded-full transition-all duration-200 cursor-pointer"
                                            aria-label="Pilih Cabang 1: Banyuwangi Kota"
                                        ></button>
                                        <button
                                            type="button"
                                            @click="branchSlide = 1"
                                            :class="branchSlide === 1 ? 'w-5 bg-emerald-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                                            class="h-1.5 rounded-full transition-all duration-200 cursor-pointer"
                                            aria-label="Pilih Cabang 2: Rogojampi"
                                        ></button>
                                        <span class="text-[10px] text-slate-500 font-semibold ml-1" x-text="(branchSlide + 1) + '/2 cabang'"></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            @click="branchSlide = (branchSlide - 1 + totalBranches) % totalBranches"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                                            aria-label="Cabang sebelumnya"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="branchSlide = (branchSlide + 1) % totalBranches"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                                            aria-label="Cabang berikutnya"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- DAFTAR CABANG: Tampilan Tetap untuk Desktop Layar Lebar (hidden lg:block) -->
                            <div class="hidden lg:block space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Lokasi Pelayanan Obat Resmi:</span>
                                </div>

                                <div class="space-y-2">
                                    <!-- Cabang 1: Banyuwangi Kota (Persis Asli) -->
                                    @auth
                                        <form method="POST" action="{{ route('branch.switch') }}">
                                            @csrf
                                            <input type="hidden" name="branch" value="banyuwangi-kota">
                                            <input type="hidden" name="redirect_to" value="{{ route('dashboard') }}">
                                            <button type="submit" class="w-full text-left p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/30 transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                        <h4 class="font-bold text-[13px] sm:text-xs text-slate-900">Cabang Banyuwangi Kota</h4>
                                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 shrink-0">24 Jam</span>
                                                    </div>
                                                    <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200">
                                                        Buka &rarr;
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-600 mt-1">Jl. Ahmad Yani No. 45, Banyuwangi</p>
                                                <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Bambang, S.Farm</p>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="block p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                    <h4 class="font-bold text-[13px] sm:text-xs text-slate-900">Cabang Banyuwangi Kota</h4>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 shrink-0">24 Jam</span>
                                                </div>
                                                <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200">
                                                    Masuk &rarr;
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-1">Jl. Ahmad Yani No. 45, Banyuwangi</p>
                                            <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Bambang, S.Farm</p>
                                        </a>
                                    @endauth

                                    <!-- Cabang 2: Rogojampi (Persis Asli) -->
                                    @auth
                                        <form method="POST" action="{{ route('branch.switch') }}">
                                            @csrf
                                            <input type="hidden" name="branch" value="rogojampi">
                                            <input type="hidden" name="redirect_to" value="{{ route('dashboard') }}">
                                            <button type="submit" class="w-full text-left p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/30 transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                        <h4 class="font-bold text-[13px] sm:text-xs text-slate-900">Cabang Rogojampi</h4>
                                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 shrink-0">07:00 - 22:00</span>
                                                    </div>
                                                    <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200">
                                                        Buka &rarr;
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-600 mt-1">Jl. Raya Rogojampi No. 12, Rogojampi</p>
                                                <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Dinda Ayu Shafira, S.Farm.</p>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="block p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                    <h4 class="font-bold text-[13px] sm:text-xs text-slate-900">Cabang Rogojampi</h4>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 shrink-0">07:00 - 22:00</span>
                                                </div>
                                                <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200">
                                                    Masuk &rarr;
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-1">Jl. Raya Rogojampi No. 12, Rogojampi</p>
                                            <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Apoteker: apt. Dinda Ayu Shafira, S.Farm.</p>
                                        </a>
                                    @endauth
                                </div>
                            </div>

                            <!-- Footer Mini Info -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-[11px] text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Obat 100% Asli
                                </span>
                                <span>Distributor Resmi PBF</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dukungan Resmi Pemerintah & Regulasi (Slide di Mobile/Tablet, Statis di Layar Lebar) -->
                <div class="mt-5 sm:mt-8 rounded-xl sm:rounded-2xl bg-white/80 border border-slate-200/80 p-2.5 sm:p-4 shadow-2xs overflow-hidden">
                    <!-- Versi Mobile/Tablet: Running Marquee Slide Rapi -->
                    <div class="lg:hidden flex items-center gap-2 overflow-hidden">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-800 bg-emerald-100/90 px-2 py-1 rounded-md shrink-0 whitespace-nowrap">
                            Lisensi:
                        </span>
                        <div class="relative overflow-hidden w-full flex-1">
                            <div class="absolute inset-y-0 left-0 w-3 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
                            <div class="absolute inset-y-0 right-0 w-3 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
                            <div class="animate-marquee flex items-center gap-3 text-[11px] font-semibold text-slate-700 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Kemenkes RI</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>BPOM</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Ikatan Apoteker Indonesia (IAI)</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Standar CDOB</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Dinkes Banyuwangi</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>QRIS Bank Indonesia</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Mitra PBF Resmi</span>
                                <!-- Duplikasi untuk perputaran continuous seamless -->
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Kemenkes RI</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>BPOM</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Ikatan Apoteker Indonesia (IAI)</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Standar CDOB</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Dinkes Banyuwangi</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>QRIS Bank Indonesia</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Mitra PBF Resmi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Versi Desktop Layar Lebar: 100% Persis Asli Tanpa Berubah -->
                    <div class="hidden lg:flex flex-wrap items-center justify-start gap-x-3.5 sm:gap-x-6 gap-y-1.5 sm:gap-y-2 text-[11px] sm:text-xs font-medium text-slate-600">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 sm:py-1 rounded-md shrink-0">
                            Lisensi &amp; Regulasi:
                        </span>
                        <span>Kemenkes RI</span>
                        <span>BPOM</span>
                        <span>Ikatan Apoteker Indonesia (IAI)</span>
                        <span>Standar CDOB</span>
                        <span>Dinkes Banyuwangi</span>
                        <span>QRIS Bank Indonesia</span>
                        <span>Mitra PBF Resmi</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- PANDUAN: Alur Pelayanan Pasien di Apotek -->
        <section id="cara-tebus" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-left lg:text-center max-w-2xl lg:mx-auto mb-5 sm:mb-10">
                <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-emerald-700">Pelayanan Resep &amp; Obat</span>
                <h2 class="text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Alur Pelayanan Pasien di Apotek
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 sm:mt-2">
                    Empat langkah mudah untuk mendapatkan obat dan menebus resep dokter dengan aman.
                </p>
            </div>

            <div
                x-data="{
                    activeStep: 0,
                    autoSlideTimer: null,
                    resumeTimer: null,
                    isPaused: false,
                    init() {
                        if (typeof window !== 'undefined' && window.innerWidth < 1024) {
                            this.startAutoSlide();
                        }
                    },
                    startAutoSlide() {
                        this.stopAutoSlide();
                        this.autoSlideTimer = setInterval(() => {
                            if (!this.isPaused && window.innerWidth < 1024) {
                                this.nextSlide(true);
                            }
                        }, 4500);
                    },
                    stopAutoSlide() {
                        if (this.autoSlideTimer) {
                            clearInterval(this.autoSlideTimer);
                            this.autoSlideTimer = null;
                        }
                    },
                    pause() {
                        this.isPaused = true;
                        if (this.resumeTimer) clearTimeout(this.resumeTimer);
                    },
                    resume() {
                        this.isPaused = false;
                    },
                    resumeDebounced() {
                        if (this.resumeTimer) clearTimeout(this.resumeTimer);
                        this.resumeTimer = setTimeout(() => {
                            this.isPaused = false;
                        }, 4500);
                    },
                    nextSlide(isAuto = false) {
                        if (!this.$refs.stepTrack) return;
                        if (this.activeStep >= 3) {
                            this.$refs.stepTrack.scrollTo({ left: 0, behavior: 'smooth' });
                        } else {
                            const cardWidth = this.$refs.stepTrack.firstElementChild ? this.$refs.stepTrack.firstElementChild.offsetWidth + 14 : 290;
                            this.$refs.stepTrack.scrollBy({ left: cardWidth, behavior: 'smooth' });
                        }
                    },
                    prevSlide() {
                        if (!this.$refs.stepTrack) return;
                        if (this.activeStep <= 0) {
                            const maxScroll = this.$refs.stepTrack.scrollWidth;
                            this.$refs.stepTrack.scrollTo({ left: maxScroll, behavior: 'smooth' });
                        } else {
                            const cardWidth = this.$refs.stepTrack.firstElementChild ? this.$refs.stepTrack.firstElementChild.offsetWidth + 14 : 290;
                            this.$refs.stepTrack.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                        }
                    },
                    scrollLeft() {
                        this.pause();
                        this.prevSlide();
                        this.resumeDebounced();
                    },
                    scrollRight() {
                        this.pause();
                        this.nextSlide(false);
                        this.resumeDebounced();
                    },
                    goTo(i) {
                        this.pause();
                        const cardWidth = this.$refs.stepTrack.firstElementChild ? this.$refs.stepTrack.firstElementChild.offsetWidth + 14 : 290;
                        this.$refs.stepTrack.scrollTo({ left: i * cardWidth, behavior: 'smooth' });
                        this.resumeDebounced();
                    },
                    updateActive(e) {
                        const el = e.target;
                        const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth + 14 : 280;
                        this.activeStep = Math.min(3, Math.max(0, Math.round(el.scrollLeft / cardWidth)));
                    }
                }"
                class="relative"
            >
                <!-- Track Slide: Flex Horizontal di Mobile/Tablet (< lg), Grid 4 Kolom di Layar Lebar (>= lg) -->
                <div
                    x-ref="stepTrack"
                    @scroll.debounce.50ms="updateActive($event)"
                    @mouseenter="pause()"
                    @mouseleave="resume()"
                    @touchstart.passive="pause()"
                    @touchend.passive="resumeDebounced()"
                    class="flex lg:grid overflow-x-auto lg:overflow-visible scroll-smooth snap-x snap-mandatory lg:grid-cols-4 gap-3.5 sm:gap-5 no-scrollbar pb-2 lg:pb-0 -mx-4 px-4 sm:mx-0 sm:px-0"
                >
                    <!-- Langkah 1: Pilih Cabang -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 sm:block mb-1.5 sm:mb-0">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-[11px] sm:text-xs flex items-center justify-center sm:mb-3.5 border border-emerald-200/60 shrink-0">
                                    01
                                </div>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm sm:mb-1.5">Pilih Cabang Apotek</h3>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Pilih lokasi apotek terdekat di Banyuwangi Kota atau Rogojampi untuk mengecek ketersediaan stok fisik obat secara akurat.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 sm:py-1 rounded-md inline-block">
                                Stok Fisik Tersedia
                            </span>
                        </div>
                    </div>

                    <!-- Langkah 2: Cari Obat / Unggah Resep -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 sm:block mb-1.5 sm:mb-0">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-[11px] sm:text-xs flex items-center justify-center sm:mb-3.5 border border-emerald-200/60 shrink-0">
                                    02
                                </div>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm sm:mb-1.5">Pilih Obat / Unggah Resep</h3>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Pilih obat bebas langsung atau unggah foto resep dokter untuk ditelaah keabsahan dan dosisnya oleh apoteker.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 sm:py-1 rounded-md inline-block">
                                Pemeriksaan Apoteker
                            </span>
                        </div>
                    </div>

                    <!-- Langkah 3: Bayar dengan QRIS -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 sm:block mb-1.5 sm:mb-0">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-[11px] sm:text-xs flex items-center justify-center sm:mb-3.5 border border-emerald-200/60 shrink-0">
                                    03
                                </div>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm sm:mb-1.5">Pembayaran Mudah</h3>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Selesaikan transaksi praktis menggunakan pembayaran digital QRIS instan atau bayar tunai di kasir apotek.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 sm:py-1 rounded-md inline-block">
                                QRIS &amp; Tunai Kasir
                            </span>
                        </div>
                    </div>

                    <!-- Langkah 4: Obat Diserahkan -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 sm:block mb-1.5 sm:mb-0">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-[11px] sm:text-xs flex items-center justify-center sm:mb-3.5 border border-emerald-200/60 shrink-0">
                                    04
                                </div>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm sm:mb-1.5">Obat Siap Diserahkan</h3>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Obat disiapkan rapi lengkap dengan etiket aturan pakai resmi dan siap diambil di apotek atau dikirim ke lokasi Anda.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 sm:py-1 rounded-md inline-block">
                                Etiket Resmi SIPA
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigasi & Dots khusus Layar Mobile / Tablet (< lg) -->
                <div class="flex lg:hidden items-center justify-between pt-2.5 px-1">
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in [0, 1, 2, 3]" :key="i">
                            <button
                                type="button"
                                @click="goTo(i)"
                                :class="activeStep === i ? 'w-5 bg-emerald-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                                class="h-1.5 rounded-full transition-all duration-200 cursor-pointer"
                                :aria-label="'Buka langkah ' + (i + 1)"
                            ></button>
                        </template>
                        <span class="text-[11px] text-slate-500 font-semibold ml-1.5" x-text="(activeStep + 1) + '/4 langkah'"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="scrollLeft()"
                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                            aria-label="Langkah sebelumnya"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button
                            type="button"
                            @click="scrollRight()"
                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                            aria-label="Langkah berikutnya"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- INFORMASI KEUNGGULAN LAYANAN APOTEK -->
        <section id="keunggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl sm:rounded-3xl bg-slate-50/80 border border-slate-200/90 p-4 sm:p-8 lg:p-10 shadow-xs relative overflow-hidden">
                <div class="max-w-2xl mb-5 sm:mb-8">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-emerald-700">Komitmen Layanan Farmasi</span>
                    <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-1">
                        Standar Pelayanan Kefarmasian Resmi
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 sm:mt-2">
                        Setiap resep dan sediaan obat ditangani langsung sesuai ketentuan izin operasional SIA dan pengawasan Apoteker SIPA.
                    </p>
                </div>

                <div
                    x-data="{
                        activeStandar: 0,
                        autoSlideTimer: null,
                        resumeTimer: null,
                        isPaused: false,
                        init() {
                            if (typeof window !== 'undefined' && window.innerWidth < 1024) {
                                this.startAutoSlide();
                            }
                        },
                        startAutoSlide() {
                            this.stopAutoSlide();
                            this.autoSlideTimer = setInterval(() => {
                                if (!this.isPaused && window.innerWidth < 1024) {
                                    this.nextSlide(true);
                                }
                            }, 4500);
                        },
                        stopAutoSlide() {
                            if (this.autoSlideTimer) {
                                clearInterval(this.autoSlideTimer);
                                this.autoSlideTimer = null;
                            }
                        },
                        pause() {
                            this.isPaused = true;
                            if (this.resumeTimer) clearTimeout(this.resumeTimer);
                        },
                        resume() {
                            this.isPaused = false;
                        },
                        resumeDebounced() {
                            if (this.resumeTimer) clearTimeout(this.resumeTimer);
                            this.resumeTimer = setTimeout(() => {
                                this.isPaused = false;
                            }, 4500);
                        },
                        nextSlide(isAuto = false) {
                            if (!this.$refs.standarTrack) return;
                            if (this.activeStandar >= 3) {
                                this.$refs.standarTrack.scrollTo({ left: 0, behavior: 'smooth' });
                            } else {
                                const cardWidth = this.$refs.standarTrack.firstElementChild ? this.$refs.standarTrack.firstElementChild.offsetWidth + 14 : 290;
                                this.$refs.standarTrack.scrollBy({ left: cardWidth, behavior: 'smooth' });
                            }
                        },
                        prevSlide() {
                            if (!this.$refs.standarTrack) return;
                            if (this.activeStandar <= 0) {
                                const maxScroll = this.$refs.standarTrack.scrollWidth;
                                this.$refs.standarTrack.scrollTo({ left: maxScroll, behavior: 'smooth' });
                            } else {
                                const cardWidth = this.$refs.standarTrack.firstElementChild ? this.$refs.standarTrack.firstElementChild.offsetWidth + 14 : 290;
                                this.$refs.standarTrack.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                            }
                        },
                        scrollLeft() {
                            this.pause();
                            this.prevSlide();
                            this.resumeDebounced();
                        },
                        scrollRight() {
                            this.pause();
                            this.nextSlide(false);
                            this.resumeDebounced();
                        },
                        goTo(i) {
                            this.pause();
                            const cardWidth = this.$refs.standarTrack.firstElementChild ? this.$refs.standarTrack.firstElementChild.offsetWidth + 14 : 290;
                            this.$refs.standarTrack.scrollTo({ left: i * cardWidth, behavior: 'smooth' });
                            this.resumeDebounced();
                        },
                        updateActive(e) {
                            const el = e.target;
                            const cardWidth = el.firstElementChild ? el.firstElementChild.offsetWidth + 14 : 280;
                            this.activeStandar = Math.min(3, Math.max(0, Math.round(el.scrollLeft / cardWidth)));
                        }
                    }"
                    class="relative"
                >
                    <!-- Track Slide: Flex Horizontal di Mobile/Tablet (< lg), Grid 4 Kolom di Layar Lebar (>= lg) -->
                    <div
                        x-ref="standarTrack"
                        @scroll.debounce.50ms="updateActive($event)"
                        @mouseenter="pause()"
                        @mouseleave="resume()"
                        @touchstart.passive="pause()"
                        @touchend.passive="resumeDebounced()"
                        class="flex lg:grid overflow-x-auto lg:overflow-visible scroll-smooth snap-x snap-mandatory lg:grid-cols-4 gap-3.5 sm:gap-5 text-slate-900 no-scrollbar pb-2 lg:pb-0 -mx-1 px-1 sm:mx-0 sm:px-0"
                    >
                    <!-- 1. Telaah Resep Dokter -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 sm:block mb-1.5 sm:mb-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-700 sm:mb-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm">Telaah Resep Dokter</h3>
                            </div>
                            <p class="text-xs text-slate-500 sm:mt-2 leading-relaxed">
                                Pemeriksaan kelengkapan resep, keabsahan SIP dokter, dosis, dan ada tidaknya interaksi obat oleh Apoteker resmi.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100 text-[11px] sm:text-xs font-semibold text-emerald-700">
                            Apoteker Berlisensi SIPA
                        </div>
                    </div>

                    <!-- 2. Jaminan 100% Obat Asli -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 sm:block mb-1.5 sm:mb-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-700 sm:mb-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm">Jaminan Obat Asli</h3>
                            </div>
                            <p class="text-xs text-slate-500 sm:mt-2 leading-relaxed">
                                Pasokan obat bersumber langsung dari Pedagang Besar Farmasi (PBF) resmi berizin Kemenkes RI dan terdaftar di BPOM.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100 text-[11px] sm:text-xs font-semibold text-emerald-700">
                            Distributor PBF Terdaftar
                        </div>
                    </div>

                    <!-- 3. Penyimpanan Standar Rantai Dingin -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 sm:block mb-1.5 sm:mb-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-700 sm:mb-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm">Penyimpanan Terjaga</h3>
                            </div>
                            <p class="text-xs text-slate-500 sm:mt-2 leading-relaxed">
                                Pengawasan suhu ruang dan rantai dingin berstandar CDOB untuk menjaga mutu dan stabilitas sediaan obat.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100 text-[11px] sm:text-xs font-semibold text-emerald-700">
                            Standar CDOB &amp; Suhu Mutu
                        </div>
                    </div>

                    <!-- 4. Pelayanan & Pengambilan Fleksibel -->
                    <div class="w-[80vw] max-w-[285px] sm:w-[310px] lg:w-auto lg:max-w-none shrink-0 snap-start p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 sm:block mb-1.5 sm:mb-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-700 sm:mb-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h3 class="font-bold text-slate-900 text-[13px] sm:text-sm">Ambil di Cabang / Antar</h3>
                            </div>
                            <p class="text-xs text-slate-500 sm:mt-2 leading-relaxed">
                                Obat siap diambil di cabang apotek terdekat atau dikirim dengan pengemasan rapi dan terjaga kerahasiaannya.
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100 text-[11px] sm:text-xs font-semibold text-emerald-700">
                            Pengemasan Privasi Aman
                        </div>
                    </div>
                </div>

                <!-- Navigasi & Dots khusus Layar Mobile / Tablet (< lg) -->
                <div class="flex lg:hidden items-center justify-between pt-2.5 px-1">
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in [0, 1, 2, 3]" :key="i">
                            <button
                                type="button"
                                @click="goTo(i)"
                                :class="activeStandar === i ? 'w-5 bg-emerald-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                                class="h-1.5 rounded-full transition-all duration-200 cursor-pointer"
                                :aria-label="'Buka standar ' + (i + 1)"
                            ></button>
                        </template>
                        <span class="text-[11px] text-slate-500 font-semibold ml-1.5" x-text="(activeStandar + 1) + '/4 standar'"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="scrollLeft()"
                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                            aria-label="Standar sebelumnya"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button
                            type="button"
                            @click="scrollRight()"
                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:scale-90 active:bg-emerald-50 flex items-center justify-center transition-all duration-150 cursor-pointer shadow-2xs"
                            aria-label="Standar berikutnya"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Resmi Apotek -->
    <footer class="border-t border-slate-200 bg-white py-8 sm:py-12 px-4 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-5 sm:gap-8 mb-6 sm:mb-8">
            <!-- Kolom 1: Profil -->
            <div class="col-span-2 md:col-span-1 space-y-2.5 sm:space-y-3">
                <div class="flex items-center gap-2">
                    <img src="/logo.png" alt="OmniPharma" class="h-7 w-7 object-contain">
                    <span class="font-extrabold text-base text-slate-900 tracking-tight">OmniPharma</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Jaringan apotek resmi berizin melayani kebutuhan obat bebas, suplemen, dan tebus resep dokter dengan pendampingan Apoteker berlisensi SIPA.
                </p>
                <div class="text-[11px] text-slate-500">
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
            <div class="col-span-2 md:col-span-1 space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Bantuan &amp; Kontak</h4>
                <ul class="space-y-1.5 text-xs text-slate-500">
                    <li>Layanan Apotek: (0333) 412345</li>
                    <li>Email Layanan: apotek@omnipharma.co.id</li>
                    <li>Metode Bayar: QRIS &amp; Tunai Kasir</li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-slate-500 text-[11px]">
            <p>&copy; 2026 Apotek OmniPharma. Seluruh hak cipta dilindungi undang-undang.</p>
            <p>Standar Pelayanan Kefarmasian • Permenkes RI No. 73/2016</p>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

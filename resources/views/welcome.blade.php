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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

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
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 hover:text-emerald-700 hover:bg-slate-100 transition min-h-[44px]"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <span>Daftar<span class="hidden sm:inline"> Pasien</span></span>
                    </a>
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
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-900 text-xs font-semibold border border-emerald-200/90 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Apotek Resmi Berlisensi SIPA • Jaminan Obat Asli 100%</span>
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
                                        href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-2xs transition min-h-[48px]"
                                    >
                                        <span>Katalog Obat</span>
                                    </a>
                                @else
                                    <a
                                        href="#demo-login"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition min-h-[48px]"
                                    >
                                        <span>Mulai Belanja</span>
                                    </a>
                                    <a
                                        href="{{ route('login') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-2xs transition min-h-[48px]"
                                    >
                                        <span>Masuk</span>
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
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    SIPA Aktif
                                </span>
                            </div>

                            <!-- Daftar Cabang Resmi -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi Pelayanan Obat:</span>
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 flex items-center gap-1">
                                        6 Cabang &bull; Geser &darr;
                                    </span>
                                </div>

                                <div class="max-h-[215px] overflow-y-auto space-y-2.5 pr-1.5 focus:outline-none" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
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
            </div>
        </section>

        <!-- PANDUAN: Cara Cari & Pesan Obat Sesuai Proyek OmniPharma -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Alur Pelayanan Pasien</span>
                <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Cara Tebus &amp; Pesan Obat di OmniPharma
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Proses pemesanan obat dirancang sistematis sesuai standar pelayanan kefarmasian di apotek.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Langkah 1: Pilih Cabang -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center mb-4">
                            01
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Pilih Cabang Apotek</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pilih cabang apotek terdekat (Banyuwangi Kota atau Rogojampi) untuk mengecek ketersediaan stok fisik obat secara akurat.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                        Stok Fisik Tersedia
                    </div>
                </div>

                <!-- Langkah 2: Cari Obat / Unggah Resep -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 font-black text-sm flex items-center justify-center mb-4">
                            02
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Cari Obat atau Unggah Resep</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Obat bebas dapat langsung dibeli. Untuk obat keras (bertanda lingkaran K merah), unggah foto resep dokter untuk diverifikasi apoteker.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700">
                        Pemeriksaan Apoteker
                    </div>
                </div>

                <!-- Langkah 3: Bayar dengan QRIS -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 font-black text-sm flex items-center justify-center mb-4">
                            03
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Checkout &amp; Bayar QRIS</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Stok obat dikunci secara pesimistik saat checkout. Lakukan pembayaran instan menggunakan QRIS dinamis standar EMVCo.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-amber-700">
                        QRIS &amp; Kasir Apotek
                    </div>
                </div>

                <!-- Langkah 4: Live Tracking Reverb -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 font-black text-sm flex items-center justify-center mb-4">
                            04
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Lacak Pesanan Real-Time</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pantau tahapan persiapan obat di apotek melalui live stepper WebSocket Reverb hingga pesanan siap diambil di kasir.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-purple-700">
                        Etiket &amp; Aturan Pakai
                    </div>
                </div>
            </div>
        </section>
        <!-- KARTU AKSES 4 PORTAL DEMO PRAKTIKUM (PAK DEVIT) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-900 text-white p-5 sm:p-8 lg:p-12 shadow-xl relative overflow-hidden">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Pusat Layanan Farmasi</span>
                    <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white mt-1">
                        Akses Portal OmniPharma Network
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-2">
                        Pilih portal layanan sesuai kebutuhan Anda: pembelian obat pasien, telaah resep apoteker cabang, atau manajemen pusat.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-slate-900">
                    <!-- 1. Katalog / Dashboard Pasien -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-emerald-700 uppercase bg-emerald-50 px-2 py-0.5 rounded-md">Layar 1 • Pasien</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-emerald-700">Dashboard Pasien</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Akses keranjang obat, unggah resep dokter, dan status pesanan.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-600 flex items-center justify-between min-h-[44px]">
                                <span>Buka Dashboard</span>
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </div>
                        </a>
                    @else
                        <div class="min-h-[44px] p-5 rounded-2xl bg-white flex flex-col justify-between">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-emerald-700 uppercase bg-emerald-50 px-2 py-0.5 rounded-md">Layar 1 • Pasien</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2">Dashboard Pasien</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Wajib login terlebih dahulu untuk mengakses keranjang, beli obat, &amp; tebus resep.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <form method="POST" action="{{ route('login.store') }}">
                                    @csrf
                                    <input type="hidden" name="email" value="gunturafandy5@gmail.com">
                                    <input type="hidden" name="password" value="password">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]">
                                        <span>Masuk Pasien (1-Klik)</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    <!-- 2. Portal Apoteker (APJ) -->
                    @auth
                        <a href="{{ route('tenant.dashboard', ['client' => (auth()->user()->email === 'dewi@omnipharma.test' ? 'rogojampi' : 'banyuwangi-kota')]) }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-amber-700 uppercase bg-amber-50 px-2 py-0.5 rounded-md">Layar 2 • Apoteker</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-amber-700">Antrean Resep &amp; FEFO</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Telaah resep dokter, validasi SIP, dan pantau batch mendekati ED.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center justify-between min-h-[44px]">
                                <span>Buka Portal Cabang</span>
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </div>
                        </a>
                    @else
                        <div class="min-h-[44px] p-5 rounded-2xl bg-white flex flex-col justify-between">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-amber-700 uppercase bg-amber-50 px-2 py-0.5 rounded-md">Layar 2 • Apoteker</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2">Antrean Resep &amp; FEFO</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Telaah resep dokter, validasi SIP, dan pantau batch mendekati ED.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <form method="POST" action="{{ route('login.store') }}">
                                    @csrf
                                    <input type="hidden" name="email" value="bambang@omnipharma.test">
                                    <input type="hidden" name="password" value="password">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]">
                                        <span>Masuk Apoteker (1-Klik)</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    <!-- 3. Super Admin Eksekutif -->
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-purple-700 uppercase bg-purple-50 px-2 py-0.5 rounded-md">Layar 3-4 • Admin</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-purple-700">Executive Dashboard</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Agregasi omzet cabang, tutup periode akuntansi, dan ekspor CSV.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-purple-600 flex items-center justify-between min-h-[44px]">
                                <span>Buka Super Admin</span>
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </div>
                        </a>
                    @else
                        <div class="min-h-[44px] p-5 rounded-2xl bg-white flex flex-col justify-between">
                            <div>
                                <span class="inline-block text-[10px] font-bold text-purple-700 uppercase bg-purple-50 px-2 py-0.5 rounded-md">Layar 3-4 • Admin</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2">Executive Dashboard</h3>
                                <p class="text-[11px] text-slate-500 mt-1">Agregasi omzet cabang, tutup periode akuntansi, dan ekspor CSV.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <form method="POST" action="{{ route('login.store') }}">
                                    @csrf
                                    <input type="hidden" name="email" value="daffa@omnipharma.test">
                                    <input type="hidden" name="password" value="password">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]">
                                        <span>Masuk Admin (1-Klik)</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    <!-- 4. Portal Otentikasi -->
                    <a href="{{ route('login') }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div>
                            <span class="inline-block text-[10px] font-bold text-blue-700 uppercase bg-blue-50 px-2 py-0.5 rounded-md">Otentikasi • Akun</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-blue-700">Masuk &amp; Registrasi</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Login 1-klik Google, verifikasi OTP email, atau akun demo praktikum.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-blue-600 flex items-center justify-between min-h-[44px]">
                            <span>Masuk Portal</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- PANEL KHUSUS DEMO PRAKTIKUM (1-KLIK MASUK MULTI-PERAN) -->
        <section id="demo-login" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-8 lg:p-10 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 mb-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>Akses Cepat Pengujian: Akun Demo 4 Peran</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Pilih Akun Demo untuk Masuk Cepat
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Gunakan akun di bawah ini untuk mencoba hak akses dan fitur masing-masing peran secara instan tanpa mengetik manual.
                        </p>
                    </div>
                    @auth
                        <div class="shrink-0 flex items-center gap-2 text-xs">
                            <span class="text-slate-500">Akun Aktif:</span>
                            <span class="font-bold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-emerald-100 text-emerald-800">
                                {{ auth()->user()->role ?? 'pelanggan' }}
                            </span>
                        </div>
                    @endauth
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Peran 1: Super Admin Eksekutif -->
                    <div class="p-5 rounded-2xl border border-purple-200/80 bg-purple-50/40 hover:bg-purple-50/70 transition flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                                    Super Admin
                                </span>
                                <span class="text-lg">👑</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Muhammad Daffa Ariby</h3>
                                <p class="text-xs font-mono text-purple-900/80 mt-0.5">daffa@omnipharma.test</p>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Akses penuh omzet agregat lintas cabang, tutup periode akuntansi, dan ekspor laporan CSV.
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-purple-200/60">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="email" value="daffa@omnipharma.test">
                                <input type="hidden" name="password" value="password">
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]"
                                >
                                    <span>Masuk Super Admin</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Peran 2: Apoteker APJ Banyuwangi Kota -->
                    <div class="p-5 rounded-2xl border border-amber-200/80 bg-amber-50/40 hover:bg-amber-50/70 transition flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                    Apoteker (BWI)
                                </span>
                                <span class="text-lg">🩺</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">apt. Bambang, S.Farm</h3>
                                <p class="text-xs font-mono text-amber-900/80 mt-0.5">bambang@omnipharma.test</p>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Telaah resep dokter, validasi SIP, dan rotasi stok FEFO cabang Banyuwangi Kota.
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-amber-200/60">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="email" value="bambang@omnipharma.test">
                                <input type="hidden" name="password" value="password">
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]"
                                >
                                    <span>Masuk Apoteker (Kota)</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Peran 3: Apoteker APJ Rogojampi -->
                    <div class="p-5 rounded-2xl border border-emerald-200/80 bg-emerald-50/40 hover:bg-emerald-50/70 transition flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Apoteker (Rogojampi)
                                </span>
                                <span class="text-lg">💊</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">apt. Dewi Sartika, S.Farm</h3>
                                <p class="text-xs font-mono text-emerald-900/80 mt-0.5">dewi@omnipharma.test</p>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Manajemen resep dan pemantauan masa kedaluwarsa rantai dingin cabang Rogojampi.
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-emerald-200/60">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="email" value="dewi@omnipharma.test">
                                <input type="hidden" name="password" value="password">
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]"
                                >
                                    <span>Masuk Apoteker (Rogo)</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Peran 4: Pasien / Pelanggan Biasa -->
                    <div class="p-5 rounded-2xl border border-blue-200/80 bg-blue-50/40 hover:bg-blue-50/70 transition flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                    Pasien / Pelanggan
                                </span>
                                <span class="text-lg">🛒</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Guntur Afandy</h3>
                                <p class="text-xs font-mono text-blue-900/80 mt-0.5">gunturafandy5@gmail.com</p>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Belanja obat bebas, unggah foto resep, checkout dengan QRIS EMVCo &amp; pelacakan Reverb.
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-blue-200/60">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="email" value="gunturafandy5@gmail.com">
                                <input type="hidden" name="password" value="password">
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-h-[44px]"
                                >
                                    <span>Masuk Pasien</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-8 px-4 text-center text-xs text-slate-500 space-y-2">
        <div class="flex items-center justify-center gap-2">
            <img src="/logo.png" alt="OmniPharma" class="h-6 w-6 object-contain">
            <span class="font-bold text-slate-800">OmniPharma</span>
        </div>
        <p>Proyek Sistem Informasi Apotek Jaringan OmniPharma • Dosen Pengampu: Pak Devit Suwardiyanto</p>
        <p class="text-slate-400 text-[11px]">&copy; 2026 OmniPharma. Hak Cipta Dilindungi.</p>
    </footer>

    @fluxScripts
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100/90">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Utama - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 antialiased flex flex-col font-sans overflow-x-hidden">
    @php
        $photoUrl = session('firebase_photo') ?? null;
        $provider = session('firebase_provider', 'password');
        $user = auth()->user();
        $userName = $user->name ?? 'Pengguna';
        $userEmail = $user->email ?? '-';
        $userRole = $user->role ?? ($user->peran ?? 'pelanggan');
        $isApoteker = $user && ($user->hasRole('apoteker') || $user->role === 'apoteker');
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || $user->role === 'super_admin');
        $userClientSlug = $user && method_exists($user, 'getClientSlug') ? $user->getClientSlug() : 'banyuwangi-kota';
        $userClientName = $user && method_exists($user, 'getClientName') ? $user->getClientName() : 'Cabang Banyuwangi Kota';
    @endphp

    <!-- Navbar Atas -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
            <!-- Brand -->
            <div class="flex items-center min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 min-h-[44px] py-1 shrink-0" aria-label="OmniPharma Dashboard">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 sm:h-10 sm:w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.25)] shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="block text-sm sm:text-base font-extrabold text-slate-900 tracking-tight leading-none">OmniPharma</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                @if($isSuperAdmin) PUSAT @elseif($isApoteker) APJ @else PASIEN @endif
                            </span>
                        </div>
                        <span class="block text-xs font-semibold text-slate-500 leading-tight truncate mt-1">
                            @if($isSuperAdmin)
                                Dashboard Pusat (Super Admin)
                            @elseif($isApoteker)
                                Portal Apoteker ({{ $userClientName }})
                            @else
                                Dashboard Pelanggan • Portal Layanan Farmasi
                            @endif
                        </span>
                    </div>
                </a>
            </div>

            <!-- Profile Dropdown (Clean, Clickable) -->
            <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex items-center gap-2.5 p-1 sm:px-2.5 sm:py-1.5 rounded-full border border-slate-200/90 bg-slate-50/80 hover:bg-slate-100/90 text-xs transition cursor-pointer min-h-[44px] focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    id="user-menu-button"
                    :aria-expanded="open"
                    aria-haspopup="true"
                    aria-label="Menu Pengguna"
                >
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shrink-0 border border-emerald-500/40">
                    @else
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="hidden sm:flex flex-col text-left leading-none">
                        <span class="font-bold text-slate-800 text-xs truncate max-w-[130px]">{{ $userName }}</span>
                        <span class="text-[10px] text-emerald-700 font-semibold mt-0.5 capitalize">{{ str_replace('_', ' ', $userRole) }}</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-xl border border-slate-200/90 z-50 text-xs"
                    role="menu"
                    aria-orientation="vertical"
                    aria-labelledby="user-menu-button"
                >
                    <!-- Header Dropdown Info -->
                    <div class="px-3 py-2.5 bg-slate-50 rounded-xl mb-1.5 border border-slate-100">
                        <p class="font-extrabold text-slate-900 text-sm truncate">{{ $userName }}</p>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $userEmail }}</p>
                        <span class="inline-block mt-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                            {{ str_replace('_', ' ', $userRole) }} &bull; {{ $userClientName }}
                        </span>
                    </div>

                    <!-- Menu Navigasi -->
                    <div class="space-y-0.5">
                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium transition min-h-[40px]"
                            role="menuitem"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>Pengaturan Akun &amp; Profil</span>
                        </a>

                        @if(!$isSuperAdmin && !$isApoteker)
                            <a
                                href="{{ route('customer.catalog', ['client' => 'banyuwangi-kota']) }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium transition min-h-[40px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                <span>Katalog Obat Cabang</span>
                            </a>
                        @endif

                        <a
                            href="{{ route('home') }}?welcome=1"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium transition min-h-[40px]"
                            role="menuitem"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span>Halaman Sambutan Utama</span>
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="my-1.5 border-t border-slate-100"></div>

                    <!-- Tombol Keluar (Logout) -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-bold transition min-h-[40px] text-left cursor-pointer"
                            role="menuitem"
                        >
                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama Dashboard -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

        @if(!$isSuperAdmin && !$isApoteker)
            <!-- Sambutan Alami & Humanis untuk Pasien (Tanpa Card AI Template) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs font-semibold text-emerald-800">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            Apotek Resmi Terverifikasi
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-600">{{ $userClientName }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        Halo, {{ $userName }}!
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                        Selamat datang di Portal Farmasi OmniPharma. Akses obat resmi dengan jaminan standar farmasi BPOM.
                    </p>
                </div>

                <!-- Tombol Aksi Cepat Pasien -->
                <div class="flex items-center gap-3 shrink-0">
                    <a
                        href="{{ route('customer.catalog', ['client' => session('active_branch', 'banyuwangi-kota')]) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-xs transition min-h-[44px] cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Mulai Belanja Obat</span>
                    </a>
                    <a
                        href="{{ route('customer.cart', ['client' => session('active_branch', 'banyuwangi-kota')]) }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm shadow-2xs transition min-h-[44px] cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Lihat Keranjang</span>
                    </a>
                </div>
                </div>
            </div>
        @else
            <!-- Hero Banner Internal (Super Admin / Apoteker) -->
            <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 min-w-0">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Avatar" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-emerald-400 shadow-md shrink-0">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 text-[#D4F94E] flex items-center justify-center font-black text-2xl sm:text-3xl border border-emerald-400/30 shadow-md shrink-0">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0 w-full sm:w-auto">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                                Standar Permenkes RI No. 73/2016
                            </span>

                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-2 leading-tight">
                                Halo, {{ $userName }}!
                            </h1>

                            <p class="text-xs sm:text-sm text-emerald-200/90 mt-1">
                                @if($isSuperAdmin)
                                    Selamat datang di Executive Control Panel OmniPharma Headquarter (Monitoring Lintas Cabang).
                                @elseif($isApoteker)
                                    Selamat bertugas di {{ $userClientName }}. Verifikasi resep dokter, kontrol FEFO, dan kelola antrean pasien.
                                @endif
                            </p>

                            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs">
                                <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/15 text-white font-bold uppercase tracking-wider">
                                    Peran: {{ str_replace('_', ' ', $userRole) }}
                                </span>
                                @if($isApoteker)
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-[#D4F94E] font-bold">
                                        {{ $userClientName }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/15 text-emerald-200 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                                    Sesi Terautentikasi ({{ $provider === 'google' ? 'Google Auth' : 'Firebase Password' }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                        @if($isSuperAdmin)
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-md transition min-h-[46px] cursor-pointer"
                            >
                                <span>Overview Omzet &amp; KPI</span>
                            </a>
                            <a
                                href="{{ route('admin.progres.index') }}"
                                class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-white/20 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm transition min-h-[46px] cursor-pointer"
                            >
                                <span>Live Monitoring Obat</span>
                            </a>
                        @elseif($isApoteker)
                            <a
                                href="{{ route('tenant.dashboard', ['client' => $userClientSlug]) }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-md transition min-h-[46px] cursor-pointer"
                            >
                                <span>Telaah Resep &amp; FEFO</span>
                            </a>
                            <a
                                href="{{ route('tenant.antrean.index', ['client' => $userClientSlug]) }}"
                                class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-white/20 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm transition min-h-[46px] cursor-pointer"
                            >
                                <span>Kelola Antrean Cabang</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Bagian Portal Akses Terpadu (Sesuai Hak Akses Peran) -->
        <div>
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Portal Modul &amp; Navigasi Terpadu</h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        @if($isSuperAdmin)
                            Akses modul manajerial pusat, pelaporan konsolidasi, dan pengawasan operasional seluruh cabang.
                        @elseif($isApoteker)
                            Akses modul operasional apoteker untuk {{ $userClientName }} (Verifikasi Resep, Antrean &amp; Stok FEFO).
                        @else
                            Pilih katalog cabang untuk memeriksa ketersediaan obat dan melakukan pemesanan obat.
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Hak Akses: {{ strtoupper(str_replace('_', ' ', $userRole)) }}
                </span>
            </div>

            <!-- Grid Kartu Modul -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                {{-- ========================================================= --}}
                {{-- KHUSUS SUPER ADMIN SAJA: Executive KPI & Live Monitoring --}}
                {{-- ========================================================= --}}
                @if($isSuperAdmin)
                    <!-- 1. Executive Dashboard KPI -->
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex items-center justify-center mb-4 border border-emerald-500/20 shadow-xs group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">PUSAT EXCLUSIVE</span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Super Admin Eksekutif &amp; KPI</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Omzet agregat lintas cabang, penutupan periode akuntansi ADR-05, dan ekspor konsolidasi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Overview Eksekutif</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 2. Live Monitoring Penyiapan Obat Pusat (Read-Only) -->
                    <a
                        href="{{ route('admin.progres.index') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <span class="text-xl">⏱️</span>
                            </div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">READ-ONLY MONITORING</span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Live Monitoring Obat Lintas Cabang</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pantau obat yang sedang diracik dan disiapkan di Banyuwangi &amp; Rogojampi secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Live Monitoring</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 3. Buku Kasir & Ledger Finansial -->
                    <a
                        href="{{ route('admin.kasir.ledger') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Buku Kasir &amp; Ledger Lintas Cabang</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pencatatan kas masuk, rekonsiliasi QRIS, dan arus kas penutupan harian kasir.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Akses Ledger Kasir</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 4. Inspeksi Cabang Banyuwangi Kota -->
                    <a
                        href="{{ route('tenant.dashboard', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Inspeksi Cabang Banyuwangi Kota</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Supervisi stok FEFO, antrean masuk, dan verifikasi SIP Apoteker Penanggung Jawab BWI.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Masuk ke Cabang BWI</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 5. Inspeksi Cabang Rogojampi -->
                    <a
                        href="{{ route('tenant.dashboard', ['client' => 'rogojampi']) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Inspeksi Cabang Rogojampi</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Supervisi operasional dan perputaran batch obat Cabang Rogojampi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Masuk ke Cabang Rogojampi</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 6. Supervisi Staf & Cabang Pengaturan -->
                    <a
                        href="{{ route('admin.cabang.staf', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Supervisi Staf &amp; SIPA Apoteker</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Kelola data tenaga kefarmasian, validitas legalitas SIPA, dan penugasan cabang.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Kelola Staf Farmasi</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                {{-- ========================================================= --}}
                {{-- KHUSUS APOTEKER CABANG (BANYUWANGI MAUPUN ROGOJAMPI)     --}}
                {{-- HILANGKAN TOTAL MENU SUPER ADMIN DAN LIVE MONITORING    --}}
                {{-- ========================================================= --}}
                @elseif($isApoteker)
                    <!-- 1. Telaah Resep Dokter & FEFO (APJ) -->
                    <a
                        href="{{ route('tenant.dashboard', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex items-center justify-center mb-4 border border-emerald-500/20 shadow-xs group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    {{ $userClientName }}
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Telaah Resep Dokter &amp; FEFO</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Verifikasi administratif &amp; klinis resep dokter, cek SIP dokter, dan alokasi obat batch FEFO terdekat ED.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Verifikasi Resep</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 2. Antrean Virtual & Tombol Progresi Status Cabang -->
                    <a
                        href="{{ route('tenant.antrean.index', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    LOKET FARMASI
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Antrean &amp; Progres Status</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Panggil antrean berjalan (+1), terbitkan tiket walk-in, dan majukan tahapan penyiapan obat secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Kelola Antrean {{ $userClientName }}</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 3. Manajemen Batch FEFO & Expired Date -->
                    <a
                        href="{{ route('tenant.stok.fefo', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Manajemen Stok Batch FEFO</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Rotasi nomor batch, pantau tanggal kedaluwarsa (ED), dan karantina obat rusak/expired.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Periksa Batch Obat</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 4. Antrean Resep Masuk -->
                    <a
                        href="{{ route('tenant.resep.antrean', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Daftar Antrean Resep Masuk</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Semua berkas foto resep dokter yang diunggah oleh pasien untuk ditelaah apoteker.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Antrean Resep</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 5. Rekam Data Pasien Farmasi -->
                    <a
                        href="{{ route('tenant.pasien.index', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Rekam Data Pasien &amp; Riwayat Obat</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Catatan riwayat alergi obat, riwayat penebusan resep, dan identitas pasien cabang.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Lihat Data Pasien</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <!-- 6. Katalog Obat Cabang Apoteker -->
                    <a
                        href="{{ route('customer.catalog', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Katalog Publik {{ $userClientName }}</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pratinjau etalase obat bebas, bebas terbatas, dan harga yang dilihat oleh pelanggan.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Lihat Etalase Cabang</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                {{-- ========================================================= --}}
                {{-- KHUSUS PELANGGAN (CUSTOMER)                              --}}
                {{-- ========================================================= --}}
                @else
                    <!-- 1. Katalog Pasien Cabang Banyuwangi Kota -->
                    <a
                        href="{{ route('customer.catalog', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                    Cabang Utama
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Katalog Cabang Banyuwangi Kota</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Cek stok obat bebas &amp; keras, unggah foto resep dokter, dan pesan obat cepat.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Belanja di Banyuwangi Kota</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>

                    <!-- 2. Katalog Pasien Cabang Rogojampi -->
                    <a
                        href="{{ route('customer.catalog', ['client' => 'rogojampi']) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider">
                                    Cabang 2
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Katalog Cabang Rogojampi</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Cek ketersediaan obat resmi pada Apotek OmniPharma Cabang Rogojampi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Belanja di Rogojampi</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>

                    <!-- 3. Keranjang & Pilihan Slot Ambil (Click & Collect) -->
                    <a
                        href="{{ route('customer.cart', ['client' => session('active_branch', 'banyuwangi-kota')]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 uppercase tracking-wider">
                                    Loket Cepat
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Keranjang &amp; Slot Pengambilan</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pilih jam ambil obat di apotek tanpa perlu antre lama di loket.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Keranjang Saya</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        <!-- Rincian Informasi Sesi & Kredensial Medis (Nuansa Bersih Sesuai Desain Login) -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Informasi Sesi Terautentikasi &amp; Kredensial Medis</h3>
                </div>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    Terverifikasi SIA &amp; SIP
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Status Autentikasi</span>
                    <span class="font-bold text-emerald-700 text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Terverifikasi Sesi</span>
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold mt-1 block">Standar Permenkes RI No. 73/2016</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Penyedia Identitas (IdP)</span>
                    <span class="font-bold text-slate-800 text-sm break-words">
                        {{ $provider === 'google' ? 'Google OAuth 2.0 (Firebase)' : 'Email & Password (Firebase)' }}
                    </span>
                    <span class="text-[10px] text-slate-500 mt-1 block font-mono">
                        {{ $userEmail }}
                    </span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Hak Akses &amp; Penugasan</span>
                    <span class="font-bold text-slate-800 text-sm uppercase">
                        {{ str_replace('_', ' ', $userRole) }}
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold mt-1 block">
                        @if($isApoteker)
                            Penugasan: {{ $userClientName }}
                        @elseif($isSuperAdmin)
                            Super Administrator Pusat (HQ)
                        @else
                            Akun Pelanggan Terdaftar
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Standar Sesuai Halaman Login -->
    <footer class="mt-auto border-t border-slate-200/80 bg-white py-4 px-4 text-center text-xs text-slate-500">
        &copy; 2026 OmniPharma Network • Sistem Informasi Farmasi Terpadu (Praktikum PSI Pak Devit Suwardiyanto)
    </footer>

    @fluxScripts
</body>
</html>
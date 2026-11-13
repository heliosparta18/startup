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
    <x-welcome-splash />
    @php
        $photoUrl = session('firebase_photo') ?? null;
        $provider = session('firebase_provider', 'password');
        $user = auth()->user();
        $userName = $user->name ?? 'Guntur Afandy';
        $userEmail = $user->email ?? '-';
        $userRole = $user->role ?? ($user->peran ?? 'pelanggan');
        $isApoteker = $user && ($user->hasRole('apoteker') || $user->role === 'apoteker');
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || $user->role === 'super_admin');
        $userClientSlug = $user && method_exists($user, 'getClientSlug') ? $user->getClientSlug() : 'banyuwangi-kota';
        $userClientName = $user && method_exists($user, 'getClientName') ? $user->getClientName() : 'Cabang Banyuwangi Kota';
        $activeBranchSlug = session('active_branch') ?? (session('active_client') ?? $userClientSlug);
        $activeBranchName = $activeBranchSlug === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang Banyuwangi Kota';
    @endphp

    <!-- Top Header: GoApotik Consumer Portal Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3 sm:gap-6">
            <!-- Brand Logo -->
            <div class="flex items-center shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 min-h-[44px] py-1" aria-label="OmniPharma Dashboard">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 sm:h-10 sm:w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.25)] shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="block text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">OmniPharma</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                @if($isSuperAdmin) PUSAT @elseif($isApoteker) APJ @else PASIEN @endif
                            </span>
                        </div>
                        <span class="block text-[11px] font-semibold text-slate-400 leading-tight truncate mt-0.5">
                            @if($isSuperAdmin)
                                Dashboard Pusat (Super Admin)
                            @elseif($isApoteker)
                                Portal Apoteker ({{ $userClientName }})
                            @else
                                Portal Farmasi Resmi Terverifikasi
                            @endif
                        </span>
                    </div>
                </a>
            </div>

            <!-- Central Search Bar (GoApotik Style) -->
            <form action="{{ route('katalog') }}" method="GET" class="hidden md:flex flex-1 max-w-xl mx-2">
                <div class="relative w-full">
                    <input
                        type="search"
                        name="search"
                        placeholder="Cari produk obat, suplemen, atau kebutuhan kesehatan..."
                        class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 bg-slate-50/70 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:bg-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-2xs"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </form>

            <!-- Actions: Cart, Alerts & Profile Dropdown -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Keranjang Button --}}
                <a
                    href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                    class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 border border-slate-200 bg-white transition min-h-[44px] min-w-[44px] shadow-2xs"
                    aria-label="Keranjang Belanja"
                    title="Keranjang Belanja"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="hidden lg:inline text-xs font-bold ml-1.5">Keranjang</span>
                </a>

                {{-- Notifikasi Icon --}}
                <button
                    type="button"
                    class="hidden sm:inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition min-h-[44px] min-w-[44px]"
                    title="Notifikasi"
                    aria-label="Notifikasi"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>

                <!-- Profile Dropdown (Clean, Clickable) -->
                <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-full border border-slate-200/90 bg-slate-50 hover:bg-slate-100 text-xs transition cursor-pointer min-h-[44px] focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        id="user-menu-button"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Menu Pengguna"
                    >
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shrink-0 border border-emerald-500/40">
                        @else
                            <div class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden sm:flex flex-col text-left leading-none">
                            <span class="font-bold text-slate-800 text-xs truncate max-w-[120px]">{{ $userName }}</span>
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
                        class="absolute right-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 text-xs"
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
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>Pengaturan Profil</span>
                            </a>

                            <a
                                href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                <span>Katalog Farmasi Publik</span>
                            </a>

                            <a
                                href="{{ route('home') }}?welcome=1"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
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
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-bold transition min-h-[44px] text-left cursor-pointer"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub-bar: Points, Voucher & Location Selector (Matching GoApotik Image #1) -->
        <div class="bg-slate-50/90 border-t border-slate-100 px-4 sm:px-6 lg:px-8 py-2 text-xs">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-slate-600">
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-4 h-4 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center text-[10px] font-black">P</span>
                        <span>0 Poin</span>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-sky-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Voucher Tersedia</span>
                    </span>
                </div>

                {{-- Location / Cabang Selector --}}
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-[11px]">Tentukan Lokasimu:</span>
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1 font-bold text-slate-800 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        <span class="text-xs">{{ $activeBranchName }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama Dashboard -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 space-y-6 sm:space-y-8">

        {{-- ========================================================= --}}
        {{-- KHUSUS PELANGGAN: GoApotik Consumer Portal Layout (Image #1) --}}
        {{-- ========================================================= --}}
        @if(!$isSuperAdmin && !$isApoteker)
            {{-- 1. Hero Promo Banner Carousel (GoApotik Image #1) --}}
            <div class="relative rounded-3xl bg-gradient-to-r from-sky-100 via-emerald-50 to-teal-100 border border-sky-200/80 p-6 sm:p-10 shadow-xs overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center relative z-10">
                    <div class="lg:col-span-7 space-y-3.5">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-white text-emerald-800 border border-emerald-300 shadow-2xs">
                                GoApotik Resmi
                            </span>
                            <span class="text-xs font-semibold text-slate-600">
                                Halo, <strong>{{ $userName }}</strong>!
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                            LangkahSehat Jadi Lebih Mudah
                        </h1>
                        <p class="text-xs sm:text-base text-slate-600 max-w-xl font-medium leading-relaxed">
                            Diskon Produk Kesehatan &amp; Penebusan Resep Dokter <strong class="text-rose-600">up to 10K / 50%</strong>. Terhubung langsung dengan apotek resmi berizin BPOM.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <span class="px-3 py-1.5 rounded-lg bg-amber-400/90 text-amber-950 font-black text-xs uppercase tracking-wider shadow-2xs">
                                KODE: GOASIAGA
                            </span>
                            <a
                                href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-xs transition min-h-[44px] cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Mulai Belanja Obat</span>
                            </a>
                            <a
                                href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm shadow-2xs transition min-h-[44px]"
                            >
                                <span>Lihat Keranjang</span>
                            </a>
                        </div>
                    </div>

                    {{-- Hero Illustration Graphics --}}
                    <div class="lg:col-span-5 flex items-center justify-center relative">
                        <div class="relative w-full max-w-sm aspect-4/3 rounded-2xl bg-white/70 p-4 border border-white/80 shadow-md backdrop-blur-xs flex items-center justify-around">
                            {{-- Omron Monitor Graphic --}}
                            <div class="flex flex-col items-center p-3 rounded-xl bg-sky-50 border border-sky-100 text-sky-800">
                                <span class="text-2xl">🩺</span>
                                <span class="text-[10px] font-bold mt-1">Tensimeter</span>
                            </div>
                            {{-- First Aid Kit --}}
                            <div class="flex flex-col items-center p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 scale-110 shadow-xs">
                                <span class="text-3xl">🩹</span>
                                <span class="text-[10px] font-black mt-1 uppercase">P3K Lengkap</span>
                            </div>
                            {{-- Vitamins Bottle --}}
                            <div class="flex flex-col items-center p-3 rounded-xl bg-amber-50 border border-amber-100 text-amber-800">
                                <span class="text-2xl">💊</span>
                                <span class="text-[10px] font-bold mt-1">Suplemen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Services Card & Quick Categories Grid (GoApotik Image #1) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                {{-- Left Card: Konsultasi Dokter --}}
                <div class="lg:col-span-3 rounded-2xl bg-white border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 border border-sky-200 shadow-2xs">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white uppercase tracking-wider mb-1">
                            GRATIS
                        </span>
                        <h2 class="text-base font-extrabold text-slate-900 leading-snug">Konsultasi Dokter &amp; Resep</h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Tanya apoteker kami atau unggah resep dokter langsung ke cabang apotek.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 mt-4">
                        <a
                            href="{{ route('customer.home', ['client' => $activeBranchSlug]) }}#unggah-resep"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition shadow-xs min-h-[44px]"
                        >
                            <span>Unggah Foto Resep</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Right 2-Row Category Grid (GoApotik Image #1 Exact Icons) --}}
                <div class="lg:col-span-9 rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs">
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 sm:gap-4 text-center">
                        {{-- Row 1 --}}
                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Obat']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">💊</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Obat</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Suplemen']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🧴</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Suplemen</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Nutrisi']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 border border-orange-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🧃</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Nutrisi</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Herbal']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🌿</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Herbal</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Ibu & Anak']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 border border-rose-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🍼</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Produk Bayi</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Alat Kesehatan']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 border border-red-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🩺</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Alkes</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'kategori' => 'Perawatan Diri']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">💄</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Kecantikan</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">👁️</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Mata</span>
                        </a>

                        {{-- Row 2 (Specific GoApotik categories from Image #1) --}}
                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'search' => 'Dewasa']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-pink-50 text-pink-600 border border-pink-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">👫</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Dewasa 21+</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'subKategori' => 'Pereda Demam & Nyeri']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🎯</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Anti Nyeri</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'subKategori' => 'Antibiotik']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🛡️</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Antibiotik</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'search' => 'Antiseptik']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-cyan-50 text-cyan-700 border border-cyan-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🧴</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Antiseptik</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'search' => 'Asma']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🫁</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Asma</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'subKategori' => 'Batuk & Flu']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🤧</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Batuk &amp; Flu</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug, 'subKategori' => 'Kebersihan Tubuh']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-violet-50 text-violet-700 border border-violet-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">🪥</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Consumer</span>
                        </a>

                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px]">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs">
                                <span class="text-xl">📑</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Semua</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Flash Sale & Special Promo Showcase (GoApotik Image #1) --}}
            <section class="space-y-4" aria-labelledby="harga-spesial-heading">
                {{-- Tabs Bar --}}
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                    <button type="button" class="px-4 py-2 rounded-xl bg-[#0070CD] text-white font-extrabold text-xs shadow-xs min-h-[40px] cursor-pointer">
                        Harga Spesial
                    </button>
                    <button type="button" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs min-h-[40px] cursor-pointer">
                        Official Store
                    </button>
                    <button type="button" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs min-h-[40px] cursor-pointer">
                        Rekomendasi
                    </button>
                    <button type="button" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs min-h-[40px] cursor-pointer">
                        Apotek Terdekat
                    </button>
                </div>

                {{-- Product Cards Grid with Left Promo Banner --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    {{-- Left Promotional Poster --}}
                    <div class="md:col-span-3 rounded-2xl bg-gradient-to-b from-[#1C4E80] to-[#0A231E] text-white p-5 flex flex-col justify-between shadow-xs overflow-hidden relative">
                        <div class="relative z-10 space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-300">SPECIAL PROMO</span>
                            <h3 class="text-xl font-black leading-tight">Stay Healthy Save More</h3>
                            <div class="inline-block mt-2 px-2.5 py-1 rounded-lg bg-amber-400 text-amber-950 font-black text-xs">
                                DISCOUNT UP TO 50%
                            </div>
                        </div>
                        <div class="my-4 py-2 flex items-center justify-center">
                            <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/sanmol_drops_15_ml_0001.jpg" alt="Promo Medicine" class="h-28 w-28 object-contain rounded-xl bg-white p-2 shadow-md">
                        </div>
                        <a href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}" class="relative z-10 w-full py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs text-center transition">
                            Lihat Semua Promo &rarr;
                        </a>
                    </div>

                    {{-- Right Product Cards (4 Cards matching GoApotik Image #1) --}}
                    <div class="md:col-span-9 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        {{-- Card 1: Citicoline --}}
                        <div class="bg-white rounded-xl border border-slate-200 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group">
                            <span class="absolute top-2 left-2 z-10 px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">
                                -11%
                            </span>
                            <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_resep_with_watermark/low/amoxicillin-trihydrate-first_medipharma_500_mg_box_100_kaplet_0001-61-925251-1.jpg" alt="Amoxicillin" class="w-full h-full object-contain group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-400 bg-white/80 px-1 rounded">OmniPharma</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem]">
                                    AMOXICILLIN TRIHYDRATE 500 MG BOX 100 KAPLET
                                </h4>
                                <div class="mt-1">
                                    <span class="text-xs sm:text-sm font-black text-emerald-800">Rp 14.027</span>
                                    <span class="text-[10px] text-slate-400 line-through ml-1">Rp 15.800</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    <span class="truncate">{{ $activeBranchName }}</span>
                                </div>
                                <div class="text-[10px] font-semibold text-amber-600 mt-0.5">
                                    ★ 5.0 • 42 terjual
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Promag Suspensi --}}
                        <div class="bg-white rounded-xl border border-slate-200 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group">
                            <span class="absolute top-2 left-2 z-10 px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">
                                -10%
                            </span>
                            <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/promag_suspensi_60_ml_0001.jpg" alt="Promag Suspensi" class="w-full h-full object-contain group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-400 bg-white/80 px-1 rounded">OmniPharma</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem]">
                                    PROMAG SUSPENSI ISI 60 ML BOTOL
                                </h4>
                                <div class="mt-1">
                                    <span class="text-xs sm:text-sm font-black text-emerald-800">Rp 15.700</span>
                                    <span class="text-[10px] text-slate-400 line-through ml-1">Rp 17.500</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    <span class="truncate">{{ $activeBranchName }}</span>
                                </div>
                                <div class="text-[10px] font-semibold text-amber-600 mt-0.5">
                                    ★ 4.9 • 88 terjual
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Insto Drops --}}
                        <div class="bg-white rounded-xl border border-slate-200 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group">
                            <span class="absolute top-2 left-2 z-10 px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">
                                -15%
                            </span>
                            <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/insto_regular_drops_7_5_ml.jpg" alt="Insto Regular Drops" class="w-full h-full object-contain group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-400 bg-white/80 px-1 rounded">OmniPharma</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem]">
                                    INSTO REGULAR DROPS 7.5 ML
                                </h4>
                                <div class="mt-1">
                                    <span class="text-xs sm:text-sm font-black text-emerald-800">Rp 14.564</span>
                                    <span class="text-[10px] text-slate-400 line-through ml-1">Rp 17.200</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    <span class="truncate">{{ $activeBranchName }}</span>
                                </div>
                                <div class="text-[10px] font-semibold text-amber-600 mt-0.5">
                                    ★ 5.0 • 120 terjual
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Sanmol Drops --}}
                        <div class="bg-white rounded-xl border border-slate-200 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group">
                            <span class="absolute top-2 left-2 z-10 px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">
                                -10%
                            </span>
                            <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/sanmol_drops_15_ml_0001.jpg" alt="Sanmol Drops" class="w-full h-full object-contain group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-400 bg-white/80 px-1 rounded">OmniPharma</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem]">
                                    SANMOL DROPS ISI 15 ML BOTOL
                                </h4>
                                <div class="mt-1">
                                    <span class="text-xs sm:text-sm font-black text-emerald-800">Rp 21.000</span>
                                    <span class="text-[10px] text-slate-400 line-through ml-1">Rp 23.500</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    <span class="truncate">{{ $activeBranchName }}</span>
                                </div>
                                <div class="text-[10px] font-semibold text-amber-600 mt-0.5">
                                    ★ 4.9 • 65 terjual
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- PORTAL MODUL & NAVIGASI TERPADU (MEMENUHI SEMUA PERSYARATAN TEST SUITE)  --}}
        {{-- ========================================================================= --}}
        <section class="space-y-4" aria-labelledby="portal-modul-heading">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 id="portal-modul-heading" class="text-lg font-black text-slate-900 tracking-tight">Portal Modul &amp; Navigasi Terpadu</h2>
                    <p class="text-xs text-slate-500">
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
                {{-- KHUSUS SUPER ADMIN --}}
                @if($isSuperAdmin)
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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">PUSAT EXCLUSIVE</span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Super Admin Eksekutif &amp; KPI</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Omzet agregat lintas cabang, penutupan periode akuntansi ADR-05, dan ekspor konsolidasi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Overview Eksekutif</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.progres.index') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <span class="text-xl">⏱️</span>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">READ-ONLY MONITORING</span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Live Monitoring Obat Lintas Cabang</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pantau obat yang sedang diracik dan disiapkan di Banyuwangi &amp; Rogojampi secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Live Monitoring</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

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

                {{-- KHUSUS APOTEKER CABANG --}}
                @elseif($isApoteker)
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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                {{ $userClientName }}
                            </span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Telaah Resep Dokter &amp; FEFO</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Verifikasi administratif &amp; klinis resep dokter, cek SIP dokter, dan alokasi obat batch FEFO terdekat ED.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Verifikasi Resep</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                LOKET FARMASI
                            </span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Antrean &amp; Progres Status</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Panggil antrean berjalan (+1), terbitkan tiket walk-in, dan majukan tahapan penyiapan obat secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Kelola Antrean {{ $userClientName }}</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

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

                {{-- KHUSUS PELANGGAN (CUSTOMER) --}}
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

                    <!-- 3. Keranjang & Slot Pengambilan -->
                    <a
                        href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
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
        </section>

        <!-- Rincian Informasi Sesi & Kredensial Medis -->
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

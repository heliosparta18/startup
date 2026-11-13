@props(['title' => 'OmniPharma', 'client' => null, 'fullWidth' => false])
@php
    $clientSlug = $client ?? (auth()->check() && method_exists(auth()->user(), 'getClientSlug') ? auth()->user()->getClientSlug() : 'banyuwangi-kota');
    $clientDisplay = ucwords(str_replace('-', ' ', $clientSlug));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100/90 text-slate-800 font-sans flex flex-col overflow-x-hidden">
    <x-welcome-splash />
    <!-- Top Navigation Header: Sesuai Tema Login Hijau Deep Spruce & Putih Bersih -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 shadow-2xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.home', ['client' => $clientSlug]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] p-1.5 shadow-xs border border-emerald-500/20 hover:scale-105 transition-transform duration-200 shrink-0">
                    <img src="/logo.png" alt="OmniPharma" class="h-full w-full object-contain">
                </a>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="block text-xs font-black tracking-widest text-emerald-800 uppercase">OmniPharma Network</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#0A231E] text-[#D4F94E]">PASIEN</span>
                    </div>
                    <span class="text-sm font-extrabold text-slate-900 tracking-tight">Katalog Farmasi Publik</span>
                </div>
            </div>

            <!-- Cabang / Klien Active Indicator -->
            <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-xs text-slate-700">
                <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Cabang: <strong class="text-slate-900 font-bold">{{ $clientDisplay }}</strong></span>
                <span class="text-[#D4F94E] font-extrabold text-[10px] bg-[#0A231E] px-2 py-0.5 rounded-full border border-emerald-500/30">Terhubung</span>
            </div>

            <!-- Actions: Keranjang & Profile (Touch targets >= 44px) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-xl border border-emerald-600/30 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-900 hover:bg-emerald-100 transition shadow-2xs" aria-label="Kembali ke Dashboard Utama">
                    <svg class="h-4 w-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="hidden sm:inline">Dashboard Utama</span>
                </a>
                <a href="{{ route('customer.cart', ['client' => $clientSlug]) }}" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs" aria-label="Keranjang Belanja">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="hidden sm:inline">Keranjang</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0A231E] text-[#D4F94E] font-black text-xs border border-emerald-500/30 shadow-xs hover:scale-105 transition" title="Dashboard Pengguna ({{ auth()->user()->name }})">
                        {{ strtoupper(substr(auth()->user()->name ?? 'Pengguna', 0, 2)) }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs" title="Masuk ke Akun">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>

        <!-- Mobile Location Sub-bar (visible only on mobile screens < md) -->
        <div class="md:hidden flex items-center justify-between border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-600">
            <div class="flex items-center gap-1.5 font-medium truncate">
                <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">Cabang: <strong class="text-slate-900 font-bold">{{ $clientDisplay }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="shrink-0 text-[10px] font-extrabold text-[#D4F94E] bg-[#0A231E] px-2 py-0.5 rounded-full border border-emerald-500/30">Terhubung</span>
            </div>
        </div>
    </header>

    <!-- Main Body -->
    @if ($fullWidth ?? false)
        <main class="flex-1 w-full flex flex-col">
            {{ $slot }}
        </main>
    @else
        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8">
            {{ $slot }}
        </main>
    @endif
    <!-- Footer -->
    <footer class="border-t border-slate-200/80 bg-white p-6 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="font-bold text-slate-800">OmniPharma Network (Sistem Informasi Farmasi Terpadu)</p>
            <p class="text-[11px] text-slate-400">Standar Permenkes RI No. 73/2016 • SIA &amp; SIP Terverifikasi</p>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
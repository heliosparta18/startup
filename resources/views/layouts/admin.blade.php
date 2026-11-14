@props(['title' => 'Executive Dashboard'])
@php
    $loggedUser = auth()->user();
    $adminName = $loggedUser->name ?? 'Muhammad Daffa Ariby';
    $adminInitials = $loggedUser && method_exists($loggedUser, 'initials') ? $loggedUser->initials() : 'DA';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-100/90 text-slate-800 font-sans flex flex-col overflow-x-hidden">
    <div x-data="{ mobileNav: false }" class="flex min-h-screen flex-col">
        <!-- Main Top Bar: Nuansa Hijau Deep Spruce & Putih Bersih -->
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <button
                        type="button"
                        @click="mobileNav = !mobileNav"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-slate-200 lg:hidden text-slate-700 hover:bg-slate-50 transition shrink-0 cursor-pointer"
                        :aria-expanded="mobileNav"
                        aria-label="Menu Navigasi Mobile">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] p-1.5 shadow-xs border border-emerald-500/20 hover:scale-105 transition-transform duration-200 shrink-0">
                        <img src="/logo.png" alt="OmniPharma Logo" class="h-full w-full object-contain">
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="block text-[11px] sm:text-xs font-black tracking-wide sm:tracking-widest text-emerald-800 uppercase truncate">OmniPharma Network</span>
                            <span class="text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md bg-[#0A231E] text-[#D4F94E] shrink-0">PUSAT</span>
                        </div>
                        <h1 class="text-xs sm:text-sm font-extrabold uppercase tracking-tight text-slate-900 truncate">
                            Executive Dashboard <span class="hidden md:inline">: Agregasi Cabang</span>
                        </h1>
                        <div class="hidden md:block text-[11px] font-semibold text-emerald-700 truncate">Periode 01-16 Sep 2026 &bull; Standar Permenkes RI No. 73/2016</div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-3 text-xs shrink-0">
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl border border-emerald-600/30 bg-emerald-50 text-emerald-900 font-bold hover:bg-emerald-100 transition shadow-2xs shrink-0" title="Kembali ke Dashboard Utama" aria-label="Dashboard Utama">
                        <svg class="h-4 w-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="hidden sm:inline">Dashboard Utama</span>
                    </a>
                    <div class="text-right hidden md:block">
                        <div class="font-extrabold text-slate-900 truncate max-w-[160px]">{{ $adminName }}</div>
                        <div class="text-[11px] text-emerald-700 font-semibold">Super Administrator (Headquarter)</div>
                    </div>
                    <div class="hidden sm:flex h-10 w-10 items-center justify-center rounded-xl bg-[#0A231E] text-[#D4F94E] font-extrabold text-sm border border-emerald-500/30 shadow-xs cursor-default shrink-0" title="Super Administrator">
                        {{ strtoupper(substr($adminInitials ?? 'DA', 0, 2)) }}
                    </div>

                    <button
                        type="button"
                        onclick="if(window.OmniFirebaseAuth) { window.OmniFirebaseAuth.logoutBoth(); } else { document.getElementById('admin-logout-form').submit(); }"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 p-2 font-bold transition cursor-pointer shadow-2xs shrink-0"
                        title="Keluar dari Akun"
                        aria-label="Keluar dari Akun">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                    <form id="admin-logout-form" method="POST" action="{{ route('auth.firebase.logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>

            <!-- Mobile Drawer Menu (< lg) -->
            <div
                x-show="mobileNav"
                x-cloak
                @click.outside="mobileNav = false"
                class="lg:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1.5 text-xs font-medium">
                <div class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-200/80 mb-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Super Administrator (Pusat)</div>
                    <div class="font-extrabold text-slate-900 truncate">{{ $adminName }}</div>
                    <div class="text-[11px] text-slate-600 truncate">Periode 01-16 Sep 2026 &bull; Permenkes RI No. 73/2016</div>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-bold' }}">
                    <span>Overview Omzet</span>
                </a>
                <a href="{{ route('admin.progres.index') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl {{ request()->routeIs('admin.progres.index*') ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-bold' }}">
                    <span>Monitoring Progres Obat</span>
                </a>
                <a href="{{ route('admin.cabang.staf', ['client' => 'banyuwangi-kota']) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl {{ request()->routeIs('admin.cabang.staf*') ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-bold' }}">
                    <span>Staf &amp; Supervisi APJ</span>
                </a>
                <a href="{{ route('admin.kasir.ledger') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl {{ request()->routeIs('admin.kasir.ledger*') ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-bold' }}">
                    <span>Buku Kasir &amp; Ledger</span>
                </a>
                <a href="{{ route('admin.cabang.pengaturan', ['client' => 'banyuwangi-kota']) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl {{ request()->routeIs('admin.cabang.pengaturan*') ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-bold' }}">
                    <span>Pengaturan Cabang</span>
                </a>
                <div class="pt-2 border-t border-slate-100 space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold">
                        <span>&larr; Dashboard Utama</span>
                    </a>
                    <button
                        type="button"
                        onclick="if(window.OmniFirebaseAuth) { window.OmniFirebaseAuth.logoutBoth(); } else { document.getElementById('admin-logout-form').submit(); }"
                        class="w-full flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-rose-700 hover:bg-rose-50 font-bold text-left cursor-pointer">
                        <span>Keluar dari Akun</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Admin Navigation Ribbon: Desktop (lg+) -->
        <nav class="hidden lg:block bg-white border-b border-slate-200/80 overflow-x-auto no-scrollbar shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-1.5 sm:gap-2 text-xs font-bold py-2 whitespace-nowrap">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>Overview Omzet</span>
                </a>
                <a href="{{ route('admin.progres.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('admin.progres.index*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>Monitoring Progres Obat</span>
                </a>
                <a href="{{ route('admin.cabang.staf', ['client' => 'banyuwangi-kota']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('admin.cabang.staf*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>Staf &amp; Supervisi APJ</span>
                </a>
                <a href="{{ route('admin.kasir.ledger') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('admin.kasir.ledger*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>Buku Kasir &amp; Ledger</span>
                </a>
                <a href="{{ route('admin.cabang.pengaturan', ['client' => 'banyuwangi-kota']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('admin.cabang.pengaturan*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>Pengaturan Cabang</span>
                </a>
            </div>
        </nav>

        <!-- Main Content Body -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            <div class="max-w-7xl mx-auto">
                OmniPharma Network Pusat &bull; Sistem Pelaporan Konsolidasi Multi-Klien &amp; Kunci Periode Akuntansi ADR-05
            </div>
        </footer>
    </div>
    @fluxScripts
</body>
</html>

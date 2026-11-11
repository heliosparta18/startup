@props(['title' => 'Portal Apoteker', 'client' => null])
@php
    $loggedUser = auth()->user();
    $aptName = $loggedUser->name ?? 'apt. Bambang, S.Farm';
    $aptInitials = $loggedUser && method_exists($loggedUser, 'initials') ? $loggedUser->initials() : 'AP';
    $clientSlug = $client ?? ($loggedUser && method_exists($loggedUser, 'getClientSlug') ? $loggedUser->getClientSlug() : 'banyuwangi-kota');
    $clientDisplay = ucwords(str_replace('-', ' ', $clientSlug));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100/90 text-slate-800 font-sans flex flex-col overflow-x-hidden">
    <div x-data="{ mobileNav: false }" class="flex min-h-screen flex-col">
        <!-- Main Top Bar: Nuansa Hijau Deep Spruce & Putih Bersih Sesuai Login -->
        <header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="mobileNav = !mobileNav"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-slate-200 lg:hidden text-slate-700 hover:bg-slate-50 transition"
                        aria-label="Menu Navigasi Mobile">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] p-1.5 shadow-xs border border-emerald-500/20 hover:scale-105 transition-transform duration-200">
                        <img src="/logo.png" alt="OmniPharma" class="h-full w-full object-contain">
                    </a>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-black tracking-widest text-emerald-800 uppercase">OmniPharma Network</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#0A231E] text-[#D4F94E]">APJ</span>
                        </div>
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Portal Apoteker Cabang</div>
                        <div class="text-sm font-extrabold text-slate-900 tracking-tight">{{ $clientDisplay }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 text-xs">
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-600/30 bg-emerald-50 text-emerald-900 font-bold hover:bg-emerald-100 transition shadow-2xs" title="Kembali ke Dashboard Utama">
                        <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="hidden sm:inline">Dashboard Utama</span>
                        <span class="sm:hidden">Home</span>
                    </a>
                    <div class="text-right hidden sm:block">
                        <div class="font-extrabold text-slate-900">{{ $aptName }}</div>
                        <div class="text-[11px] text-emerald-700 font-semibold">Apoteker Penanggung Jawab (SIPA)</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0A231E] text-[#D4F94E] font-extrabold text-sm border border-emerald-500/30 shadow-xs cursor-default" title="Petugas Farmasi">
                        {{ strtoupper(substr($aptInitials, 0, 2)) }}
                    </div>

                    <button
                        type="button"
                        onclick="if(window.OmniFirebaseAuth) { window.OmniFirebaseAuth.logoutBoth(); } else { document.getElementById('tenant-logout-form').submit(); }"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 p-2 font-bold transition cursor-pointer shadow-2xs"
                        title="Keluar dari Akun">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                    <form id="tenant-logout-form" method="POST" action="{{ route('auth.firebase.logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>

            <!-- Mobile Drawer Menu (reflow when toggled on phone) -->
            <div
                x-show="mobileNav"
                x-cloak
                @click.outside="mobileNav = false"
                class="lg:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-xs font-medium">
                <div class="px-3 py-2 text-slate-500 border-b border-slate-100">
                    Apoteker: <strong class="text-slate-900">{{ $aptName }}</strong> ({{ $clientDisplay }})
                </div>
                <a href="{{ route('tenant.dashboard', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl bg-emerald-50 text-emerald-900 font-bold border border-emerald-200">
                    <span>📋 Verifikasi Resep &amp; Stok FEFO</span>
                </a>
                <a href="{{ route('tenant.antrean.index', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>⏱️ Antrean &amp; Progres Status</span>
                </a>
                <a href="{{ route('tenant.resep.antrean', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>📥 Antrean Resep Masuk</span>
                </a>
                <a href="{{ route('tenant.stok.fefo', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>📦 Manajemen Batch FEFO</span>
                </a>
                <a href="{{ route('tenant.pasien.index', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>👤 Rekam Data Pasien</span>
                </a>
                <a href="{{ route('tenant.bantuan.index', ['client' => $clientSlug]) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>📖 Panduan SOP Farmasi</span>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>&larr; Kembali ke Dashboard Utama</span>
                </a>
            </div>
        </header>

        <!-- Tenant Pharmacist Navigation Ribbon: Nuansa Deep Spruce Hijau Sesuai Tema Login -->
        <nav class="bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 overflow-x-auto no-scrollbar shadow-2xs">
            <div class="max-w-7xl mx-auto flex items-center gap-1.5 sm:gap-2 text-xs font-bold py-2 whitespace-nowrap">
                <a href="{{ route('tenant.dashboard', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.dashboard') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>📋 Telaah Resep &amp; FEFO</span>
                </a>
                <a href="{{ route('tenant.antrean.index', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.antrean.index*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>⏱️ Antrean &amp; Progres Status</span>
                </a>
                <a href="{{ route('tenant.resep.antrean', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.resep.antrean*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>📥 Antrean Masuk</span>
                </a>
                <a href="{{ route('tenant.stok.fefo', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.stok.fefo*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>📦 Stok Batch FEFO</span>
                </a>
                <a href="{{ route('tenant.pasien.index', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.pasien.index*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>👤 Rekam Pasien</span>
                </a>
                <a href="{{ route('tenant.bantuan.index', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ request()->routeIs('tenant.bantuan.index*') ? 'bg-gradient-to-r from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50' }} min-h-[44px] transition">
                    <span>📖 SOP Farmasi</span>
                </a>
                <a href="{{ route('customer.home', ['client' => $clientSlug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-emerald-50/50 min-h-[44px] transition">
                    <span>🛒 Buka Katalog</span>
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-4 px-6 text-center text-xs text-slate-500">
            Apotek Multi-Klien OmniPharma Network • Modul Verifikasi Resep &amp; Manajemen FEFO Batch • Standar Permenkes RI No. 73/2016
        </footer>
    </div>
    @fluxScripts
</body>
</html>

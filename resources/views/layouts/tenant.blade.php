@props(['title' => 'Portal Apoteker'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100/90 text-slate-800 font-sans flex flex-col">
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
                    <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] p-1.5 shadow-xs border border-emerald-500/20">
                        <img src="/logo.png" alt="OmniPharma" class="h-full w-full object-contain">
                    </a>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-black tracking-widest text-emerald-700 uppercase">OmniPharma Network</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#0A231E] text-[#D4F94E]">APJ</span>
                        </div>
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Portal Apoteker Cabang</div>
                        <div class="text-sm font-extrabold text-slate-900 uppercase tracking-tight">Cabang {{ $client ?? 'Banyuwangi Kota' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-600/30 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-100 transition min-h-[40px]">
                        &larr; Dashboard Terpadu
                    </a>
                    <div class="text-right hidden sm:block">
                        <div class="font-bold text-slate-900">apt. Candia Purwa, S.Farm</div>
                        <div class="text-[11px] text-emerald-700 font-semibold">Apoteker Penanggung Jawab (SIPA)</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0A231E] text-[#D4F94E] font-extrabold text-sm border border-emerald-500/30 shadow-xs cursor-default" title="Petugas Farmasi">
                        CP
                    </div>
                </div>
            </div>

            <!-- Mobile Drawer Menu (reflow when toggled on phone) -->
            <div
                x-show="mobileNav"
                x-cloak
                @click.outside="mobileNav = false"
                class="lg:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-xs font-medium">
                <div class="px-3 py-2 text-slate-500 border-b border-slate-100">
                    Apoteker: <strong class="text-slate-900">apt. Candia Purwa, S.Farm</strong>
                </div>
                <a href="{{ route('tenant.dashboard', ['client' => $client ?? 'banyuwangi-kota']) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl bg-emerald-50 text-emerald-800 font-bold border border-emerald-200">
                    <span>📋 Verifikasi Resep &amp; Stok FEFO</span>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold">
                    <span>&larr; Kembali ke Dashboard Terpadu</span>
                </a>
            </div>
        </header>

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

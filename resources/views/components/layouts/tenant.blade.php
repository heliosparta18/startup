@props(['title' => 'Portal Apoteker'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 dark:bg-zinc-950 dark:text-zinc-100 font-sans flex flex-col">
    <div x-data="{ mobileNav: false }" class="flex min-h-screen flex-col">
        <!-- Main Top Bar -->
        <header class="sticky top-0 z-30 bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="mobileNav = !mobileNav"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border border-slate-200 lg:hidden text-slate-700 dark:border-zinc-700 dark:text-zinc-200 hover:bg-slate-50 transition"
                        aria-label="Menu Navigasi Mobile">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Portal Apoteker Cabang</div>
                        <div class="text-sm font-extrabold text-slate-900 dark:text-zinc-100 uppercase tracking-tight">Cabang {{ $client ?? 'Banyuwangi Kota' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <div class="text-right hidden sm:block">
                        <div class="font-bold text-slate-800 dark:text-zinc-200">apt. Candia Purwa, S.Farm</div>
                        <div class="text-[11px] text-slate-500 dark:text-zinc-400">Apoteker Penanggung Jawab (SIPA)</div>
                    </div>
                    <div class="flex h-[44px] w-[44px] items-center justify-center rounded-full bg-emerald-700 text-white font-extrabold text-sm shadow-xs cursor-default" title="Petugas Farmasi">
                        CP
                    </div>
                </div>
            </div>

            <!-- Mobile Drawer Menu (reflow when toggled on phone) -->
            <div
                x-show="mobileNav"
                x-cloak
                @click.outside="mobileNav = false"
                class="lg:hidden border-t border-slate-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 px-4 py-3 space-y-2 text-xs font-medium">
                <div class="px-3 py-2 text-slate-500 dark:text-zinc-400 border-b border-slate-100 dark:border-zinc-800">
                    Apoteker: <strong class="text-slate-800 dark:text-zinc-200">apt. Candia Purwa, S.Farm</strong>
                </div>
                <a href="{{ route('tenant.dashboard', ['client' => $client ?? 'banyuwangi-kota']) }}" class="flex items-center gap-2 min-h-[44px] px-3 py-2 rounded-lg bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">
                    <span>📋 Verifikasi Resep &amp; Stok FEFO</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-4 px-6 text-center text-xs text-slate-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
            Apotek Multi-Klien OmniPharma Network • Modul Verifikasi Resep &amp; Manajemen FEFO Batch
        </footer>
    </div>
    @fluxScripts
</body>
</html>

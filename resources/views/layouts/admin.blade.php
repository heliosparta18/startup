@props(['title' => 'Executive Dashboard'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 font-sans overflow-x-hidden">
    <div class="flex min-h-screen flex-col">
        <!-- Main Top Bar -->
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-zinc-200 bg-white px-6 lg:px-10 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="flex items-center gap-3">
                <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 object-contain drop-shadow-[0_2px_4px_rgba(15,23,42,0.25)]">
                <div>
                    <h1 class="text-sm font-extrabold uppercase tracking-tight text-zinc-900 dark:text-zinc-100">Apotek Connect Pusat : Executive Dashboard</h1>
                    <div class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Periode 01-16 Sep 2026</div>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 font-semibold dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 shadow-2xs transition" title="Kembali ke Dashboard Utama">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="hidden sm:inline">Dashboard Utama</span>
                    <span class="sm:hidden">Home</span>
                </a>
                <div class="text-right hidden sm:block">
                    <div class="font-extrabold text-zinc-900 dark:text-zinc-100">M. Daffa Ariby</div>
                    <div class="text-[11px] text-zinc-500">Super Administrator (Headquarter)</div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-zinc-900 text-white font-extrabold text-sm dark:bg-zinc-100 dark:text-zinc-900 shadow-xs" title="Super Administrator">
                    DA
                </div>
            </div>
        </header>

        <!-- Main Content Body -->
        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-white py-3 px-8 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
            Apotek Connect Pusat Network • Sistem Pelaporan Konsolidasi Multi-Klien &amp; Kunci Periode Akuntansi
        </footer>
    </div>
    @fluxScripts
</body>
</html>

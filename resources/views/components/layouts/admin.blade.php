@props(['title' => 'Executive Dashboard'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 font-sans">
    <div class="flex min-h-screen flex-col">
        <!-- Main Top Bar -->
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-zinc-200 bg-white px-6 lg:px-10 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-900 text-white font-black text-sm dark:bg-zinc-100 dark:text-zinc-900">
                    AC
                </div>
                <div>
                    <h1 class="text-sm font-extrabold uppercase tracking-tight text-zinc-900 dark:text-zinc-100">Apotek Connect Pusat : Executive Dashboard</h1>
                    <div class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Periode 01-16 Sep 2026</div>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
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

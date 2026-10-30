@props(['title' => 'Portal Apoteker'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 font-sans">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen flex-col">
        <!-- Main Top Bar -->
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-zinc-200 bg-white px-4 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-zinc-200 lg:hidden text-zinc-700 dark:border-zinc-700 dark:text-zinc-200" aria-label="Buka Menu Navigasi">
                    ☰
                </button>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Portal Apoteker Cabang</div>
                    <div class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100 uppercase">Cabang Banyuwangi Kota</div>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <div class="text-right hidden sm:block">
                    <div class="font-bold text-zinc-800 dark:text-zinc-200">apt. Candia Purwa, S.Farm</div>
                    <div class="text-[11px] text-zinc-500">Apoteker Penanggung Jawab (SIPA Terdaftar)</div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-700 text-white font-extrabold text-sm shadow-xs" title="Petugas Farmasi">
                    CP
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-white py-3 px-6 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
            Apotek Multi-Klien OmniPharma Network • Modul Verifikasi Resep &amp; Manajemen FEFO Batch
        </footer>
    </div>
    @fluxScripts
</body>
</html>

@props(['title' => 'OmniPharma'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 font-sans">
    <div class="mx-auto flex min-h-screen w-full max-w-2xl flex-col bg-white shadow-sm dark:bg-zinc-900 border-x border-zinc-200 dark:border-zinc-800">
        <!-- Top App Bar with >= 44px interactive elements -->
        <header class="sticky top-0 z-20 flex min-h-16 items-center justify-between border-b border-zinc-200 bg-white/95 px-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-lg tracking-tight focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-md p-1 min-h-11 min-w-11">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-base">OP</span>
                    <span>OmniPharma</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex min-h-11 items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200" aria-label="Keranjang Belanja">
                    <svg class="h-4 w-4 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Keranjang</span>
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px] font-bold">2</span>
                </button>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm border border-emerald-300 dark:bg-emerald-900/60 dark:text-emerald-200" title="Akun Pelanggan (AD)">
                    AD
                </div>
            </div>
        </header>

        <!-- Subheader Lokasi Cabang -->
        <div class="flex min-h-11 items-center justify-between border-b border-zinc-100 bg-emerald-50/60 px-4 py-2 text-xs text-zinc-700 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-300">
            <div class="flex items-center gap-1.5 font-medium">
                <svg class="h-4 w-4 text-emerald-700 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Cabang: <strong class="text-zinc-900 dark:text-zinc-100">Banyuwangi Kota</strong></span>
            </div>
            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/70 dark:text-emerald-300">Radius &lt; 5 km</span>
        </div>

        <!-- Main Body -->
        <main class="flex-1 p-4 pb-12">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-zinc-50 p-4 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
            <p class="font-medium text-zinc-700 dark:text-zinc-300">OmniPharma Network (Apotek Multi-Klien)</p>
            <p class="mt-1 text-[11px]">Sistem Terverifikasi SIA &amp; SIP Farmasi • Layanan Siaga 24 Jam</p>
        </footer>
    </div>
    @fluxScripts
</body>
</html>

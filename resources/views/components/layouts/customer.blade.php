@props(['title' => 'OmniPharma'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 dark:bg-zinc-950 dark:text-zinc-100 font-sans flex flex-col">
    <!-- Top Navigation Header: Responsive, >= 44px tap targets -->
    <header class="sticky top-0 z-30 bg-white/95 dark:bg-zinc-900/95 backdrop-blur border-b border-slate-200 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-lg tracking-tight focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-lg p-1 min-h-[44px] min-w-[44px]">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-base shadow-xs">OP</span>
                    <span class="font-bold text-slate-900 dark:text-white">OmniPharma</span>
                </a>
            </div>

            <!-- Cabang / Klien Active Indicator (collapses on phone) -->
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-zinc-800 text-xs text-slate-600 dark:text-zinc-300">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Cabang: <strong class="text-slate-900 dark:text-zinc-100 font-semibold">{{ $client ?? 'Banyuwangi Kota' }}</strong></span>
                <span class="text-emerald-700 dark:text-emerald-400 font-medium text-[11px] bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">&lt; 5 km</span>
            </div>

            <!-- Actions: Keranjang & Profile (Touch targets >= 44px) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" class="inline-flex min-h-[44px] items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 transition active:scale-[0.98]" aria-label="Keranjang Belanja">
                    <svg class="h-4 w-4 text-slate-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[11px] font-bold">2</span>
                </button>
                <div class="flex h-[44px] w-[44px] items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm border border-emerald-300 dark:bg-emerald-900/60 dark:text-emerald-200 cursor-default" title="Akun Pelanggan (AD)">
                    AD
                </div>
            </div>
        </div>

        <!-- Mobile Location Sub-bar (visible only on mobile screens < md) -->
        <div class="md:hidden flex items-center justify-between border-t border-slate-100 bg-slate-50/80 px-4 py-2 text-xs text-slate-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-400">
            <div class="flex items-center gap-1.5 font-medium truncate">
                <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <span class="truncate">Cabang: <strong class="text-slate-900 dark:text-zinc-100 font-semibold">{{ $client ?? 'Banyuwangi Kota' }}</strong></span>
            </div>
            <span class="shrink-0 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full dark:bg-emerald-950 dark:text-emerald-300">Radius &lt; 5 km</span>
        </div>
    </header>

    <!-- Main Body: full width, clean max-w-6xl container, responsive padding -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6">
        {{ $slot }}
    </main>

    <!-- Footer: Clean, responsive -->
    <footer class="border-t border-slate-200 bg-white p-6 text-center text-xs text-slate-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="font-medium text-slate-700 dark:text-zinc-300">OmniPharma Network (Apotek Multi-Klien)</p>
            <p class="text-[11px] text-slate-400">Sistem Farmasi Multi-Klien Terverifikasi SIA &amp; SIP • Layanan 24 Jam</p>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

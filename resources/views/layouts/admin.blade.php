@props(['title' => 'Executive Dashboard'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100/90 text-slate-800 font-sans flex flex-col overflow-x-hidden">
    <div class="flex min-h-screen flex-col">
        <!-- Main Top Bar: Nuansa Hijau Deep Spruce & Putih Bersih Sesuai Login -->
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-slate-200/80 bg-white px-6 lg:px-10 shadow-2xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] p-1.5 shadow-xs border border-emerald-500/20">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-full w-full object-contain">
                </a>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="block text-xs font-black tracking-widest text-emerald-700 uppercase">OmniPharma Network</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#0A231E] text-[#D4F94E]">PUSAT</span>
                    </div>
                    <h1 class="text-sm font-extrabold uppercase tracking-tight text-slate-900">Executive Dashboard : Agregasi Cabang</h1>
                    <div class="text-[11px] font-semibold text-emerald-700">Periode 01-16 Sep 2026 • Standar Permenkes RI No. 73/2016</div>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-600/30 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-100 transition shadow-2xs" title="Kembali ke Dashboard Utama">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="hidden sm:inline">Dashboard Utama</span>
                    <span class="sm:hidden">Home</span>
                </a>
                </a>
                <div class="text-right hidden sm:block">
                    <div class="font-extrabold text-slate-900">M. Daffa Ariby</div>
                    <div class="text-[11px] text-emerald-700 font-semibold">Super Administrator (Headquarter)</div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0A231E] text-[#D4F94E] font-extrabold text-sm border border-emerald-500/30 shadow-xs" title="Super Administrator">
                    DA
                </div>
            </div>
        </header>

        <!-- Main Content Body -->
        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-4 px-8 text-center text-xs text-slate-500">
            OmniPharma Network Pusat • Sistem Pelaporan Konsolidasi Multi-Klien &amp; Kunci Periode Akuntansi ADR-05
        </footer>
    </div>
    @fluxScripts
</body>
</html>

{{-- Left Sidebar Subcategory Filter (GoApotik Authentic Layout) --}}
<aside class="w-full md:w-64 lg:w-72 shrink-0 space-y-3" aria-label="Navigasi Subkategori">
    {{-- Mobile Collapse Toggle Header --}}
    <div class="md:hidden">
        <button
            type="button"
            x-data
            @click="$dispatch('toggle-mobile-sidebar')"
            class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-800 shadow-2xs cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
            aria-label="Buka filter subkategori"
        >
            <span class="flex items-center gap-2 text-slate-800">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter Subkategori</span>
                @if ($subKategoriAktif)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                        {{ $subKategoriAktif }}
                    </span>
                @endif
            </span>
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    {{-- Subcategory Card Container (GoApotik style) --}}
    <div
        x-data="{ mobileOpen: false }"
        @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
        :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }"
        class="hidden md:block bg-white rounded-2xl border border-slate-200 p-3 shadow-2xs space-y-2"
    >
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 px-2 pt-1">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#0A231E]">
                    Subkategori
                </h3>
                <p class="text-[11px] text-slate-500">
                    Kategori: <strong class="text-emerald-700">{{ $kategoriAktif ?: 'Obat' }}</strong>
                </p>
            </div>
            @if ($subKategoriAktif)
                <button
                    type="button"
                    wire:click="selectSubCategory('')"
                    class="text-[11px] text-emerald-700 hover:text-emerald-900 font-bold underline cursor-pointer"
                >
                    Reset
                </button>
            @endif
        </div>

        <nav class="space-y-1 max-h-[620px] overflow-y-auto pr-1 no-scrollbar" aria-label="Daftar Subkategori">
            {{-- Tombol Semua Produk (GoApotik Blue/Emerald Pill) --}}
            <button
                type="button"
                wire:click="selectSubCategory('')"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ empty($subKategoriAktif) ? 'bg-[#0A231E] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-900' }}"
                aria-pressed="{{ empty($subKategoriAktif) ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-2.5 truncate">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ empty($subKategoriAktif) ? 'bg-white text-[#0A231E]' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ empty($subKategoriAktif) ? 'bg-[#0A231E]' : 'bg-slate-400' }}"></span>
                    </span>
                    <span class="truncate">Semua {{ $kategoriAktif ?: 'Produk' }}</span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ empty($subKategoriAktif) ? 'bg-white/20 text-white font-bold' : 'bg-slate-100 text-slate-600' }}">
                    {{ $obats->total() }}
                </span>
            </button>

            {{-- Subcategories with Anatomical / Thematic Icons --}}
            @forelse ($subkategoriList as $subkat)
                @php
                    $isSubActive = ($subKategoriAktif === $subkat->nama_subkategori);
                    $subNameLower = strtolower($subkat->nama_subkategori);

                    // Anatomical icon mapping matching GoApotik
                    $iconSvg = match(true) {
                        str_contains($subNameLower, 'demam') || str_contains($subNameLower, 'nyeri') || str_contains($subNameLower, 'kepala') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        str_contains($subNameLower, 'darah') || str_contains($subNameLower, 'stamina') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
                        str_contains($subNameLower, 'lambung') || str_contains($subNameLower, 'pencernaan') || str_contains($subNameLower, 'maag') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
                        str_contains($subNameLower, 'batuk') || str_contains($subNameLower, 'flu') || str_contains($subNameLower, 'telinga') || str_contains($subNameLower, 'hidung') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        str_contains($subNameLower, 'mata') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
                        str_contains($subNameLower, 'antibiotik') || str_contains($subNameLower, 'infeksi') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        str_contains($subNameLower, 'kulit') || str_contains($subNameLower, 'ruam') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>',
                        str_contains($subNameLower, 'tulang') || str_contains($subNameLower, 'otot') || str_contains($subNameLower, 'sendi') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                        str_contains($subNameLower, 'vitamin') || str_contains($subNameLower, 'mineral') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
                        str_contains($subNameLower, 'bayi') || str_contains($subNameLower, 'anak') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        str_contains($subNameLower, 'herbal') || str_contains($subNameLower, 'madu') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
                        default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>',
                    };
                @endphp
                <button
                    type="button"
                    wire:click="selectSubCategory('{{ $subkat->nama_subkategori }}')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isSubActive ? 'bg-[#0A231E] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-900' }}"
                    aria-pressed="{{ $isSubActive ? 'true' : 'false' }}"
                >
                    <span class="flex items-center gap-2.5 truncate">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $isSubActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvg !!}
                            </svg>
                        </span>
                        <span class="truncate">{{ $subkat->nama_subkategori }}</span>
                    </span>
                    @if (isset($subkat->obat_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $isSubActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $subkat->obat_count }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="px-3 py-4 text-center text-xs text-slate-400">
                    Tidak ada subkategori khusus
                </div>
            @endforelse
        </nav>
    </div>
</aside>

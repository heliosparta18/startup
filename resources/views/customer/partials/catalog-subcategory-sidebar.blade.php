{{-- Left Sidebar Subcategory Filter (Green-and-White Theme) --}}
<aside class="w-full md:w-64 lg:w-72 shrink-0 space-y-3">
    {{-- Mobile Collapse Toggle Header --}}
    <div class="md:hidden">
        <button
            type="button"
            x-data
            @click="$dispatch('toggle-mobile-sidebar')"
            class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 shadow-2xs cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
            aria-label="Buka filter subkategori"
        >
            <span class="flex items-center gap-2 text-slate-800">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter Subkategori</span>
                @if ($subKategoriAktif)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        {{ $subKategoriAktif }}
                    </span>
                @endif
            </span>
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    {{-- Subcategory Card Container --}}
    <div
        x-data="{ mobileOpen: false }"
        @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
        :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }"
        class="hidden md:block bg-white rounded-2xl border border-slate-200 p-3.5 shadow-2xs space-y-2"
    >
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 px-1">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#0A231E]">
                    Subkategori
                </h3>
                <p class="text-[11px] text-slate-500">
                    Kategori: <strong class="text-emerald-700">{{ $kategoriAktif ?: 'Semua' }}</strong>
                </p>
            </div>
            @if ($subKategoriAktif)
                <button
                    type="button"
                    wire:click="selectSubCategory('')"
                    class="text-[11px] text-emerald-700 hover:text-emerald-900 font-semibold underline cursor-pointer"
                >
                    Reset
                </button>
            @endif
        </div>

        <nav class="space-y-1 max-h-[600px] overflow-y-auto pr-1" aria-label="Daftar Subkategori">
            {{-- Tombol Semua --}}
            <button
                type="button"
                wire:click="selectSubCategory('')"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-colors cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ empty($subKategoriAktif) ? 'bg-[#0A231E] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-900' }}"
                aria-pressed="{{ empty($subKategoriAktif) ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-2.5 truncate">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center {{ empty($subKategoriAktif) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </span>
                    <span class="truncate">Semua Produk</span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ empty($subKategoriAktif) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $obats->total() }}
                </span>
            </button>

            {{-- Daftar Subkategori Dinamis --}}
            @forelse ($subkategoriList as $subkat)
                @php
                    $isSubActive = ($subKategoriAktif === $subkat->nama_subkategori);
                @endphp
                <button
                    type="button"
                    wire:click="selectSubCategory('{{ $subkat->nama_subkategori }}')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-colors cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isSubActive ? 'bg-[#0A231E] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-900' }}"
                    aria-pressed="{{ $isSubActive ? 'true' : 'false' }}"
                >
                    <span class="flex items-center gap-2.5 truncate">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $isSubActive ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
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

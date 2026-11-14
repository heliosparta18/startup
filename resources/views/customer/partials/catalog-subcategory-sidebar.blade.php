{{-- Left Sidebar Subcategory Filter (GoApotik Icon List & Pill Active Style) --}}
<aside class="w-full md:w-64 lg:w-72 shrink-0 space-y-3" aria-label="Navigasi Subkategori">
    {{-- Mobile Collapse Toggle Header --}}
    <div class="md:hidden">
        <button
            type="button"
            x-data
            @click="$dispatch('toggle-mobile-sidebar')"
            class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-900 shadow-2xs cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
            aria-label="Buka filter subkategori"
        >
            <span class="flex items-center gap-2 text-slate-900">
                {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('filter', 'w-4 h-4 text-emerald-800 shrink-0') !!}
                <span>Filter Subkategori</span>
                @if ($subKategoriAktif)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $subKategoriAktif }}
                    </span>
                @endif
            </span>
            <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    {{-- Subcategory Card Container --}}
    <div
        x-data="{ mobileOpen: false }"
        @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
        :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }"
        class="hidden md:block bg-white rounded-2xl border border-slate-200/90 p-3 shadow-2xs space-y-2.5"
    >
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 px-2 pt-1">
            <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wide text-slate-900">
                    Subkategori
                </h3>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Kategori: <strong class="text-emerald-800">{{ $kategoriAktif ?: 'Obat' }}</strong>
                </p>
            </div>
            @if ($subKategoriAktif)
                <button
                    type="button"
                    wire:click="selectSubCategory('')"
                    class="text-xs text-emerald-800 hover:underline font-bold cursor-pointer min-h-[44px] px-2 focus-visible:outline-2 focus-visible:outline-emerald-700 rounded"
                >
                    Reset
                </button>
            @endif
        </div>

        <nav class="space-y-1 max-h-[660px] overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden" aria-label="Daftar Subkategori">
            {{-- Tombol "Semua" Pill ala GoApotik --}}
            <button
                type="button"
                wire:click="selectSubCategory('')"
                class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 {{ empty($subKategoriAktif) ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-800 font-semibold hover:bg-slate-50' }}"
                aria-pressed="{{ empty($subKategoriAktif) ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ empty($subKategoriAktif) ? 'bg-white text-emerald-700' : 'bg-[#F0F7FF] text-emerald-800' }}" aria-hidden="true">
                        <span class="w-2.5 h-2.5 rounded-full {{ empty($subKategoriAktif) ? 'bg-emerald-700' : 'bg-emerald-700/60' }}"></span>
                    </span>
                    <span class="truncate">Semua {{ $kategoriAktif ?: 'Produk' }}</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold shrink-0 {{ empty($subKategoriAktif) ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                    {{ $obats->total() }}
                </span>
            </button>

            {{-- Daftar Subkategori dengan Ikon SVG Resmi GoApotik --}}
            @forelse ($subkategoriList as $subkat)
                @php
                    $isSubActive = ($subKategoriAktif === $subkat->nama_subkategori);
                    $subIcon = \App\Modules\Catalog\Support\GoApotikCatalogAssets::subcategoryIcon($subkat->nama_subkategori, $kategoriAktif);
                @endphp
                <button
                    type="button"
                    wire:click='selectSubCategory(@json($subkat->nama_subkategori))'
                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 {{ $isSubActive ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-800 font-medium hover:bg-slate-50 hover:text-emerald-900' }}"
                    aria-pressed="{{ $isSubActive ? 'true' : 'false' }}"
                >
                    <span class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 overflow-hidden p-1 {{ $isSubActive ? 'bg-white' : 'bg-[#F0F7FF]' }}" aria-hidden="true">
                            <img
                                src="{{ $subIcon['svg_url'] }}"
                                alt=""
                                class="w-full h-full object-contain"
                                loading="lazy"
                                onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                            />
                            <span class="hidden">{!! $subIcon['svg'] !!}</span>
                        </span>
                        <span class="truncate text-left">{{ $subkat->nama_subkategori }}</span>
                    </span>
                    @if (isset($subkat->obat_count))
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-bold shrink-0 {{ $isSubActive ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $subkat->obat_count }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="px-3 py-4 text-center text-xs text-slate-600">
                    Tidak ada subkategori khusus
                </div>
            @endforelse
        </nav>
    </div>
</aside>

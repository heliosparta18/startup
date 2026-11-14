{{-- Top Category Navigation Tabs (GoApotik SVG/Icon Bar + OmniPharma Emerald Theme) --}}
@php
    $categories = [
        ['name' => 'Obat', 'slug' => 'obat'],
        ['name' => 'Suplemen', 'slug' => 'suplemen'],
        ['name' => 'Nutrisi', 'slug' => 'nutrisi'],
        ['name' => 'Herbal', 'slug' => 'herbal'],
        ['name' => 'Ibu & Anak', 'slug' => 'ibu-anak'],
        ['name' => 'Alat Kesehatan', 'slug' => 'alat-kesehatan'],
        ['name' => 'Perawatan Diri', 'slug' => 'perawatan-diri'],
        ['name' => 'Mata', 'slug' => 'mata'],
    ];

    $kategoriMap = isset($kategoriList) ? collect($kategoriList)->keyBy('nama_kategori') : collect();
@endphp

<div class="{{ ($inDashboard ?? false) ? 'rounded-2xl bg-white border border-slate-200 px-2.5 sm:px-6 pt-3 pb-0 shadow-2xs' : 'w-full bg-white border-b border-slate-200 pt-3 pb-0' }}">
    <div class="{{ ($inDashboard ?? false) ? '' : 'max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8' }}">
        <div class="flex items-end justify-start lg:justify-center gap-1.5 sm:gap-4 lg:gap-6 overflow-x-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden overscroll-x-contain touch-pan-x snap-x snap-mandatory scroll-smooth">
            @foreach ($categories as $cat)
                @php
                    $isActive = (strtolower($kategoriAktif ?: 'obat') === strtolower($cat['name']));
                    $catCount = (int) ($kategoriMap->get($cat['name'])?->obat_count ?? 0);
                    $catIcon = \App\Modules\Catalog\Support\GoApotikCatalogAssets::categoryIcon($cat['name']);
                @endphp
                <button
                    type="button"
                    wire:click="selectCategory('{{ $cat['name'] }}')"
                    class="snap-start relative flex flex-col items-center justify-end gap-1.5 sm:gap-2 min-w-[78px] sm:min-w-[104px] pt-1 pb-2.5 sm:pb-3 px-1.5 sm:px-2 rounded-t-xl transition-colors cursor-pointer min-h-[44px] shrink-0 group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="Kategori {{ $cat['name'] }}{{ $catCount > 0 ? ' (' . $catCount . ' produk)' : '' }}"
                >
                    {{-- Circular GoApotik Category Icon Badge --}}
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center transition-all duration-150 {{ $isActive ? 'bg-emerald-50 ring-2 ring-emerald-700/30 scale-105' : 'bg-[#F0F7FF] group-hover:bg-emerald-50 group-hover:scale-105' }}">
                        <img
                            src="{{ $catIcon['svg_url'] }}"
                            alt=""
                            class="w-8 h-8 sm:w-11 sm:h-11 object-contain select-none"
                            loading="eager"
                            onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                            aria-hidden="true"
                        />
                        <span class="hidden" aria-hidden="true">{!! $catIcon['svg'] !!}</span>
                    </div>

                    {{-- Category Title --}}
                    <div class="flex items-center gap-1">
                        <span class="text-xs whitespace-nowrap tracking-tight transition-colors {{ $isActive ? 'font-extrabold text-emerald-700' : 'font-semibold text-slate-700 group-hover:text-emerald-800' }}">
                            {{ $cat['name'] }}
                        </span>
                        @if ($catCount > 0)
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                                {{ $catCount }}
                            </span>
                        @endif
                    </div>

                    {{-- Bottom Active Underline Bar --}}
                    <span
                        class="absolute bottom-0 left-2 right-2 h-[3px] rounded-t-full transition-opacity duration-150 {{ $isActive ? 'bg-emerald-700 opacity-100' : 'bg-transparent opacity-0 group-hover:bg-emerald-200 group-hover:opacity-100' }}"
                        aria-hidden="true"
                    ></span>
                </button>
            @endforeach
        </div>
    </div>
</div>

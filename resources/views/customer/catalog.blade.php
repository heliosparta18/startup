{{-- Unified Catalog Component (GoApotik SVG Visual Style, Standalone & Embedded in Dashboard) --}}
<div class="{{ ($inDashboard ?? false) ? 'space-y-4' : 'min-h-screen bg-[#F8FAFC] pb-12' }}">
    {{-- Top Category Navigation Strip --}}
    @include('customer.partials.catalog-category-tabs')

    <div class="{{ ($inDashboard ?? false) ? 'pt-1' : 'max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6' }}">
        <div class="grid grid-cols-1 md:grid-cols-[16rem_minmax(0,1fr)] lg:grid-cols-12 gap-4 sm:gap-5 items-start">
            {{-- Left Subcategory Filter Sidebar --}}
            <div class="w-full lg:col-span-3 min-w-0 [&>aside]:w-full!">
                @include('customer.partials.catalog-subcategory-sidebar')
            </div>

            {{-- Main Product Catalog Area --}}
            <section class="lg:col-span-9 min-w-0 w-full space-y-3.5 sm:space-y-4" id="main-catalog-content" aria-label="Daftar Sediaan Katalog">
                {{-- GoApotik-Style Search Bar (Clean Input + Right Magnifying Glass SVG) --}}
                <div class="relative w-full">
                    <label for="catalog-search-input" class="sr-only">Cari produk obat dan suplemen</label>
                    <input
                        id="catalog-search-input"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari di Kategori {{ $kategoriAktif ?: 'Semua' }} (contoh: Paracetamol, Postinor, Promag)..."
                        class="w-full min-h-[46px] pl-4 {{ $search ? 'pr-20' : 'pr-12' }} rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm shadow-2xs focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 transition-colors"
                    />
                    @if ($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-10 px-2 flex items-center justify-center min-h-[44px] min-w-[40px] text-slate-500 hover:text-slate-900 cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700"
                            aria-label="Bersihkan pencarian"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                        {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('ic_search_outline', 'w-5 h-5') !!}
                    </div>
                </div>

                {{-- Toolbar: Results Meta & Sediaan Quick Filter Pills --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-1">
                    <div class="text-xs text-slate-700 flex flex-wrap items-center gap-1.5">
                        <span>Menampilkan <strong class="text-emerald-800 font-bold">{{ $obats->total() }}</strong> produk</span>
                        @if ($kategoriAktif && ! ($search && empty($subKategoriAktif)))
                            <span>pada kategori <span class="text-slate-900 font-semibold">{{ $kategoriAktif }}</span></span>
                        @elseif ($search && empty($subKategoriAktif))
                            <span>di <span class="text-slate-900 font-semibold">Semua Kategori</span></span>
                        @endif
                        @if ($subKategoriAktif)
                            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 min-h-[36px]">
                                <span>{{ $subKategoriAktif }}</span>
                                <button type="button" wire:click="selectSubCategory('')" class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-emerald-100 cursor-pointer font-bold" aria-label="Hapus filter subkategori">&times;</button>
                            </span>
                        @endif
                        @if ($sediaan)
                            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize min-h-[36px]">
                                <span>Sediaan: {{ $sediaan }}</span>
                                <button type="button" wire:click="filterSediaan('')" class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-emerald-100 cursor-pointer font-bold" aria-label="Hapus filter sediaan">&times;</button>
                            </span>
                        @endif
                        @if ($search)
                            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-900 border border-slate-200 min-h-[36px]">
                                <span>"{{ $search }}"</span>
                                <button type="button" wire:click="$set('search', '')" class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-slate-200 cursor-pointer font-bold" aria-label="Hapus pencarian">&times;</button>
                            </span>
                        @endif
                    </div>

                    {{-- Dropdown Filter Bentuk Sediaan --}}
                    @if (! empty($sediaanList))
                        <div
                            x-data="{ sediaanOpen: false }"
                            @click.outside="sediaanOpen = false"
                            @keydown.escape.window="sediaanOpen = false"
                            class="relative w-full sm:w-auto shrink-0"
                        >
                            <button
                                type="button"
                                @click="sediaanOpen = !sediaanOpen"
                                :aria-expanded="sediaanOpen.toString()"
                                aria-haspopup="listbox"
                                aria-label="Filter bentuk sediaan"
                                class="w-full sm:w-auto min-w-[190px] inline-flex items-center justify-between gap-2.5 px-3.5 py-2 rounded-xl border text-xs font-semibold transition-colors cursor-pointer min-h-[44px] shadow-2xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 {{ $sediaan ? 'border-emerald-700 bg-[#0A231E] text-[#D4F94E]' : 'border-slate-300 bg-white text-slate-800 hover:border-emerald-600' }}"
                            >
                                <span class="flex items-center gap-2 min-w-0 truncate">
                                    {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('filter', 'w-3.5 h-3.5 shrink-0 ' . ($sediaan ? 'text-[#D4F94E]' : 'text-emerald-700')) !!}
                                    <span class="truncate">
                                        <span class="{{ $sediaan ? 'text-emerald-200 font-medium' : 'text-slate-500 font-medium' }}">Sediaan:</span>
                                        <span class="capitalize font-bold">{{ $sediaan ?: 'Semua Sediaan' }}</span>
                                    </span>
                                </span>
                                <svg
                                    class="w-4 h-4 shrink-0 transition-transform duration-150"
                                    :class="sediaanOpen ? 'rotate-180' : ''"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                x-show="sediaanOpen"
                                x-cloak
                                role="listbox"
                                aria-label="Pilihan bentuk sediaan"
                                class="absolute right-0 mt-1.5 w-full sm:w-60 max-h-64 overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden rounded-xl bg-white border border-slate-200 shadow-lg p-1.5 z-30 space-y-0.5"
                            >
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected="{{ empty($sediaan) ? 'true' : 'false' }}"
                                    wire:click="filterSediaan('')"
                                    @click="sediaanOpen = false"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs text-left transition-colors cursor-pointer min-h-[40px] {{ empty($sediaan) ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                                >
                                    <span>Semua Sediaan</span>
                                    @if (empty($sediaan))
                                        <span class="font-bold" aria-hidden="true">&#10003;</span>
                                    @endif
                                </button>

                                @foreach ($sediaanList as $jenis)
                                    <button
                                        type="button"
                                        role="option"
                                        aria-selected="{{ $sediaan === $jenis ? 'true' : 'false' }}"
                                        wire:click='filterSediaan(@json($jenis))'
                                        @click="sediaanOpen = false"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs text-left capitalize transition-colors cursor-pointer min-h-[40px] {{ $sediaan === $jenis ? 'bg-[#0A231E] text-[#D4F94E] font-bold' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                                    >
                                        <span class="truncate">{{ $jenis }}</span>
                                        @if ($sediaan === $jenis)
                                            <span class="font-bold shrink-0" aria-hidden="true">&#10003;</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Loading Skeleton --}}
                <div wire:loading.delay.class.remove="hidden" class="hidden w-full" role="status" aria-label="Memuat produk katalog">
                    @include('customer.partials.catalog-skeleton')
                </div>

                {{-- 5-Column Responsive Product Card Grid --}}
                <div wire:loading.delay.class="hidden" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2.5 sm:gap-4">
                    @forelse ($obats as $obat)
                        @include('customer.partials.catalog-product-card', [
                            'obat' => $obat,
                            'client' => $client,
                            'inDashboard' => $inDashboard ?? false,
                        ])
                    @empty
                        @include('customer.partials.catalog-empty-state')
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if ($obats->hasPages())
                    <div class="pt-6 flex justify-center">
                        {{ $obats->links('customer.partials.catalog-pagination') }}
                    </div>
                @elseif ($obats->isNotEmpty())
                    <div class="pt-6 pb-2 text-center">
                        <p class="text-xs sm:text-sm font-semibold text-slate-600">
                            Semua Produk Telah Ditampilkan
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>

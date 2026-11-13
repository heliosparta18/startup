<div class="min-h-screen bg-slate-50">
    {{-- Top Category Horizontal Strip --}}
    @include('customer.partials.catalog-category-tabs')

    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col md:flex-row gap-4 sm:gap-6 items-start">
            {{-- Left Subcategory Filter Sidebar --}}
            @include('customer.partials.catalog-subcategory-sidebar')

            {{-- Main Product Catalog Area --}}
            <main class="flex-1 min-w-0 w-full space-y-4" id="main-catalog-content">
                {{-- Search Bar with Dynamic Category Placeholder --}}
                <div class="relative w-full">
                    <label for="catalog-search-input" class="sr-only">Cari produk obat dan suplemen</label>
                    <input
                        id="catalog-search-input"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari di Kategori {{ $kategoriAktif ?: 'Semua' }} (contoh: Paracetamol, Promag, Enervon)..."
                        class="w-full h-12 pl-4 pr-12 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 text-xs sm:text-sm shadow-2xs focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Toolbar: Results Meta & Sediaan Quick Filter Pills --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-1">
                    <div class="text-xs text-slate-600">
                        Menampilkan <strong class="text-emerald-800 font-bold">{{ $obats->total() }}</strong> produk
                        @if ($kategoriAktif)
                            pada kategori <span class="text-slate-800 font-semibold">{{ $kategoriAktif }}</span>
                        @endif
                        @if ($subKategoriAktif)
                            <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                {{ $subKategoriAktif }}
                                <button type="button" wire:click="selectSubCategory('')" class="hover:text-emerald-950 cursor-pointer" aria-label="Hapus filter subkategori">&times;</button>
                            </span>
                        @endif
                        @if ($search)
                            <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-800">
                                "{{ $search }}"
                                <button type="button" wire:click="$set('search', '')" class="hover:text-black cursor-pointer" aria-label="Hapus pencarian">&times;</button>
                            </span>
                        @endif
                    </div>

                    {{-- Sediaan Pills --}}
                    @if (! empty($sediaanList))
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <button
                                type="button"
                                wire:click="filterSediaan('')"
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition cursor-pointer min-h-[32px] {{ empty($sediaan) ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50' }}"
                            >
                                Semua Sediaan
                            </button>
                            @foreach (array_slice($sediaanList, 0, 5) as $jenis)
                                <button
                                    type="button"
                                    wire:click="filterSediaan('{{ $jenis }}')"
                                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize transition cursor-pointer min-h-[32px] {{ $sediaan === $jenis ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50' }}"
                                >
                                    {{ $jenis }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Loading Skeleton (Antislop R-27) --}}
                <div wire:loading.delay.class.remove="hidden" class="hidden w-full">
                    @include('customer.partials.catalog-skeleton')
                </div>

                {{-- 5-Column Product Card Grid --}}
                <div wire:loading.delay.class="hidden" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                    @forelse ($obats as $obat)
                        @include('customer.partials.catalog-product-card', ['obat' => $obat, 'client' => $client])
                    @empty
                        @include('customer.partials.catalog-empty-state')
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if ($obats->hasPages())
                    <div class="pt-4 flex justify-center">
                        {{ $obats->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

{{-- Left Sidebar Subcategory Filter (GoApotik Icon List & Pill Active Style with Smooth Sliding Indicator) --}}
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

    {{-- Subcategory Card Container with Alpine Glide Indicator --}}
    <div
        x-data="{
            mobileOpen: false,
            activeSub: @js($subKategoriAktif ?: ''),
            indicatorTop: 0,
            indicatorHeight: 0,
            indicatorVisible: false,
            init() {
                this.$nextTick(() => this.repositionIndicator());
                this.$watch('activeSub', (newVal) => {
                    this.$nextTick(() => this.repositionIndicator());
                });
                this.$watch('$wire.subKategori', (newVal) => {
                    if (newVal !== undefined && newVal !== this.activeSub) {
                        this.activeSub = newVal || '';
                        this.$nextTick(() => this.repositionIndicator());
                    }
                });
                this.$watch('$wire.kategori', () => {
                    this.activeSub = '';
                    this.$nextTick(() => this.repositionIndicator());
                });
                this.$watch('mobileOpen', (val) => {
                    if (val) this.$nextTick(() => this.repositionIndicator());
                });
            },
            setActiveSub(name, event) {
                const isDeselecting = (this.activeSub === name) || !name;
                this.activeSub = (this.activeSub === name || !name) ? '' : name;
                this.$nextTick(() => {
                    this.repositionIndicator(isDeselecting ? this.resolveTargetEl() : (event?.currentTarget || this.resolveTargetEl()));
                });
            },
            resolveTargetEl() {
                const nav = this.$refs.subcatNav;
                if (!nav) return null;
                const safeVal = typeof CSS !== 'undefined' && CSS.escape ? CSS.escape(this.activeSub || '') : (this.activeSub || '');
                return nav.querySelector(`button[data-subcat='${safeVal}']`) || nav.querySelector(`[data-subcat='${safeVal}']`);
            },
            repositionIndicator(targetEl = null) {
                const nav = this.$refs.subcatNav;
                if (!nav) return;
                const target = targetEl || this.resolveTargetEl();
                if (target) {
                    this.indicatorTop = target.offsetTop;
                    this.indicatorHeight = target.offsetHeight;
                    this.indicatorVisible = true;
                } else {
                    this.indicatorVisible = false;
                }
            }
        }"
        @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
        @resize.window.debounce.100ms="repositionIndicator()"
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
                    @click="setActiveSub('', $event)"
                    class="text-xs text-emerald-800 hover:underline font-bold cursor-pointer min-h-[44px] px-2 focus-visible:outline-2 focus-visible:outline-emerald-700 rounded"
                >
                    Reset
                </button>
            @endif
        </div>

        <nav
            x-ref="subcatNav"
            class="relative space-y-1 max-h-[660px] overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
            aria-label="Daftar Subkategori"
        >
            {{-- Sliding Active Indicator Pill --}}
            <div
                wire:ignore
                x-ref="activeSubcatIndicator"
                class="absolute left-0 right-0 rounded-xl bg-emerald-700 shadow-xs pointer-events-none transition-all duration-300 ease-[cubic-bezier(0.2,0.8,0.2,1)]"
                :style="{
                    top: indicatorTop + 'px',
                    height: indicatorHeight + 'px',
                    opacity: indicatorVisible ? 1 : 0
                }"
                aria-hidden="true"
            ></div>

            {{-- Tombol 'Semua' Pill ala GoApotik --}}
            <button
                type="button"
                data-subcat=""
                wire:click="selectSubCategory('')"
                @click="setActiveSub('', $event)"
                class="relative z-10 w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition-colors duration-200 cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                :class="activeSub === '' ? 'text-white font-bold' : 'text-slate-800 font-semibold hover:bg-slate-50/80 dark:hover:bg-[#262626]'"
                :aria-pressed="activeSub === '' ? 'true' : 'false'"
            >
                <span class="flex items-center gap-2.5 min-w-0">
                    <span
                        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                        :class="activeSub === '' ? 'bg-white text-emerald-700 shadow-2xs' : 'bg-[#F0F7FF] dark:bg-[#1c1c1f] text-emerald-800 dark:text-emerald-400'"
                        aria-hidden="true"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </span>
                    <span class="truncate">Semua {{ $kategoriAktif ?: 'Produk' }}</span>
                </span>
                <span
                    class="text-[11px] px-2 py-0.5 rounded-full font-bold shrink-0 transition-colors duration-200"
                    :class="activeSub === '' ? 'bg-emerald-950/60 text-white' : 'bg-slate-100 text-slate-700'"
                >
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
                    data-subcat="{{ $subkat->nama_subkategori }}"
                    wire:click='selectSubCategory(@json($subkat->nama_subkategori))'
                    @click="setActiveSub(@js($subkat->nama_subkategori), $event)"
                    class="relative z-10 w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition-colors duration-200 cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    :class="activeSub === @js($subkat->nama_subkategori) ? 'text-white font-bold' : 'text-slate-800 font-medium hover:bg-slate-50/80 hover:text-emerald-900 dark:hover:bg-[#262626] dark:hover:text-emerald-300'"
                    :aria-pressed="activeSub === @js($subkat->nama_subkategori) ? 'true' : 'false'"
                >
                    <span class="flex items-center gap-2.5 min-w-0">
                        <span
                            class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 overflow-hidden p-1 transition-all duration-200"
                            :class="activeSub === @js($subkat->nama_subkategori) ? 'bg-white shadow-2xs' : 'bg-[#F0F7FF] dark:bg-[#1c1c1f]'"
                            aria-hidden="true"
                        >
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
                        <span
                            class="text-[11px] px-2 py-0.5 rounded-full font-bold shrink-0 transition-colors duration-200"
                            :class="activeSub === @js($subkat->nama_subkategori) ? 'bg-emerald-950/60 text-white' : 'bg-slate-100 text-slate-600'"
                        >
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

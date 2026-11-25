{{-- Top Category Navigation Tabs with Smooth Sliding Indicators (R1) --}}
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
    $currentCategory = $kategoriAktif ?: 'Obat';
@endphp

<div
    wire:ignore.self
    class="{{ ($inDashboard ?? false) ? 'rounded-2xl bg-white border border-slate-200 px-2.5 sm:px-6 pt-3 pb-0 shadow-2xs' : 'w-full bg-white border-b border-slate-200 pt-3 pb-0' }}"
    x-data="{
        categories: {{ \Illuminate\Support\Js::from(array_column($categories, 'name')) }},
        activeCat: {{ \Illuminate\Support\Js::from($currentCategory) }},
        pendingCategory: null,
        initialized: false,
        circleStyle: { transform: 'translate3d(0,0,0)', width: '0px', height: '0px', opacity: 0 },
        barStyle: { transform: 'translate3d(0,0,0)', width: '0px', opacity: 0 },

        init() {
            this.$nextTick(() => {
                this.updatePositions(this.activeCat, false);
                this.$nextTick(() => { this.initialized = true; });
            });
            this.$watch('$wire.kategori', (newVal) => {
                if (newVal && newVal !== this.activeCat) {
                    if (this.pendingCategory && newVal !== this.pendingCategory) {
                        return; // Ignore stale response from prior in-flight click
                    }
                    if (newVal === this.pendingCategory) {
                        this.pendingCategory = null;
                    }
                    this.goTo(newVal);
                }
            });
        },

        goTo(name) {
            const idx = this.categories.indexOf(name);
            if (idx === -1) return;
            this.activeCat = name;
            this.updatePositions(name, true);
            const btn = this.$refs['btn_' + idx];
            if (btn) {
                btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        },

        select(name) {
            this.pendingCategory = name;
            this.goTo(name);
            if (this.$wire && typeof this.$wire.selectCategory === 'function') {
                this.$wire.selectCategory(name);
            }
        },

        nextCheckpoint() {
            const idx = this.categories.indexOf(this.activeCat);
            // Terminal Checkpoint Enforcement: Mata is index 7 (cannot advance beyond Mata)
            if (idx >= 0 && idx < this.categories.length - 1) {
                const target = this.categories[idx + 1];
                this.pendingCategory = target;
                this.select(target);
            }
        },

        prevCheckpoint() {
            const idx = this.categories.indexOf(this.activeCat);
            if (idx > 0) {
                const target = this.categories[idx - 1];
                this.pendingCategory = target;
                this.select(target);
            }
        },

        updatePositions(name, animate = true) {
            const idx = this.categories.indexOf(name);
            if (idx === -1) return;
            const container = this.$refs.tabContainer;
            const btn = this.$refs['btn_' + idx];
            const icon = this.$refs['icon_' + idx];
            if (!container || !btn || !icon) return;

            const cRect = container.getBoundingClientRect();
            const bRect = btn.getBoundingClientRect();
            const iRect = icon.getBoundingClientRect();

            const cLeft = (iRect.left - cRect.left) + container.scrollLeft;
            const cTop = (iRect.top - cRect.top) + container.scrollTop;
            const bLeft = (bRect.left - cRect.left) + container.scrollLeft + 8;
            const bWidth = Math.max(0, bRect.width - 16);

            this.circleStyle = {
                transform: `translate3d(${cLeft}px, ${cTop}px, 0)`,
                width: `${iRect.width}px`,
                height: `${iRect.height}px`,
                opacity: 1
            };

            this.barStyle = {
                transform: `translate3d(${bLeft}px, 0, 0)`,
                width: `${bWidth}px`,
                opacity: 1
            };
        }
    }"
    @resize.window.debounce.100ms="updatePositions(activeCat, false)"
>
    <div class="{{ ($inDashboard ?? false) ? '' : 'max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8' }}">
        <div
            x-ref="tabContainer"
            role="tablist"
            aria-label="Kategori Produk Farmasi"
            class="relative flex items-end justify-start lg:justify-center gap-1.5 sm:gap-4 lg:gap-6 overflow-x-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden overscroll-x-contain touch-pan-x snap-x snap-mandatory scroll-smooth"
            @keydown.arrow-right.prevent="nextCheckpoint()"
            @keydown.arrow-left.prevent="prevCheckpoint()"
        >
            {{-- 1. Sliding Green Circular Indicator (Layer z-5: glides over slot backgrounds, under SVG icons) --}}
            <div
                x-ref="activeCircleIndicator"
                class="absolute top-0 left-0 pointer-events-none rounded-full ring-2 ring-emerald-600/40 bg-emerald-50 dark:bg-[#07291f] dark:ring-emerald-500/50 z-5"
                :class="initialized ? 'transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]' : 'transition-none'"
                :style="circleStyle"
                aria-hidden="true"
            ></div>

            {{-- 2. Sliding Green Bottom Indicator Line (Layer z-10) --}}
            <span
                x-ref="activeBottomBar"
                class="absolute bottom-0 left-0 h-[3.5px] rounded-t-full bg-emerald-700 dark:bg-emerald-500 pointer-events-none z-10"
                :class="initialized ? 'transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]' : 'transition-none'"
                :style="barStyle"
                aria-hidden="true"
            ></span>

            {{-- Category Checkpoint Buttons --}}
            @foreach ($categories as $cat)
                @php
                    $isTerminal = ($cat['name'] === 'Mata');
                    $catCount = (int) ($kategoriMap->get($cat['name'])?->obat_count ?? 0);
                    $catIcon = \App\Modules\Catalog\Support\GoApotikCatalogAssets::categoryIcon($cat['name']);
                @endphp
                <button
                    type="button"
                    role="tab"
                    x-ref="btn_{{ $loop->index }}"
                    data-category="{{ $cat['name'] }}"
                    data-index="{{ $loop->index }}"
                    data-terminal="{{ $isTerminal ? 'true' : 'false' }}"
                    @click="select('{{ $cat['name'] }}')"
                    class="snap-start relative flex flex-col items-center justify-end gap-1.5 sm:gap-2 min-w-[78px] sm:min-w-[104px] pt-1 pb-2.5 sm:pb-3 px-1.5 sm:px-2 rounded-t-xl transition-colors cursor-pointer min-h-[44px] shrink-0 group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 z-10"
                    :aria-selected="activeCat === '{{ $cat['name'] }}' ? 'true' : 'false'"
                    aria-label="Kategori {{ $cat['name'] }}{{ $catCount > 0 ? ' (' . $catCount . ' produk)' : '' }}{{ $isTerminal ? ' - Pos Pemeriksaan Akhir' : '' }}"
                >
                    {{-- Circular Icon Slot (Layer z-0 background, z-10 SVG) --}}
                    <div
                        x-ref="icon_{{ $loop->index }}"
                        class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center transition-colors duration-200 relative"
                        :class="activeCat === '{{ $cat['name'] }}' ? 'bg-transparent' : 'bg-[#F0F7FF] dark:bg-[#1c1c1f] group-hover:bg-emerald-50/50 dark:group-hover:bg-[#07291f]'"
                    >
                        <img
                            src="{{ $catIcon['svg_url'] }}"
                            alt=""
                            class="relative z-10 w-8 h-8 sm:w-11 sm:h-11 object-contain select-none transition-transform duration-200 group-hover:scale-105"
                            loading="eager"
                            onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                            aria-hidden="true"
                        />
                        <span class="hidden relative z-10" aria-hidden="true">{!! $catIcon['svg'] !!}</span>
                    </div>

                    {{-- Category Title & Badge --}}
                    <div class="flex items-center gap-1 relative z-10">
                        <span
                            class="text-xs whitespace-nowrap tracking-tight transition-colors"
                            :class="activeCat === '{{ $cat['name'] }}' ? 'font-extrabold text-emerald-700 dark:text-emerald-400' : 'font-semibold text-slate-700 dark:text-zinc-300 group-hover:text-emerald-800 dark:group-hover:text-emerald-300'"
                        >
                            {{ $cat['name'] }}
                        </span>
                        @if ($catCount > 0)
                            <span
                                class="text-[10px] font-bold px-1.5 py-0.2 rounded-full transition-colors"
                                :class="activeCat === '{{ $cat['name'] }}' ? 'bg-emerald-700 text-white dark:bg-emerald-700' : 'bg-slate-100 text-slate-600 dark:bg-[#262626] dark:text-zinc-200'"
                            >
                                {{ $catCount }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>

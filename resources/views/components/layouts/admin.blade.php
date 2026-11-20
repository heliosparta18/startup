@props([
    'title' => 'Super Admin Pusat - OmniPharma',
    'showSplash' => true,
])
@php
    $navItems = [
        [
            'label' => 'Ringkasan Pusat',
            'href' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
            'keyword' => 'dashboard',
        ],
        [
            'label' => 'Cek Apoteker',
            'href' => route('admin.apoteker.index'),
            'active' => request()->routeIs('admin.apoteker.*'),
            'keyword' => 'apoteker',
        ],
        [
            'label' => 'Monitoring Obat',
            'href' => route('admin.progres.index'),
            'active' => request()->routeIs('admin.progres.index*'),
            'keyword' => 'progres',
        ],
        [
            'label' => 'Katalog & Obat',
            'href' => route('admin.obat.index'),
            'active' => request()->routeIs('admin.obat.*'),
            'keyword' => 'obat',
        ],
        [
            'label' => 'Staf & APJ Cabang',
            'href' => route('admin.cabang.staf', ['client' => request()->route('client') ?? 'banyuwangi-kota']),
            'active' => request()->routeIs('admin.cabang.staf*'),
            'keyword' => 'staf',
        ],
        [
            'label' => 'Kasir & Ledger',
            'href' => route('admin.kasir.ledger'),
            'active' => request()->routeIs('admin.kasir.ledger*'),
            'keyword' => 'kasir',
        ],
        [
            'label' => 'Pengaturan Cabang',
            'href' => route('admin.cabang.pengaturan', ['client' => request()->route('client') ?? 'banyuwangi-kota']),
            'active' => request()->routeIs('admin.cabang.pengaturan*') || request()->routeIs('admin.cabang.store') || request()->routeIs('admin.cabang.destroy'),
            'keyword' => 'pengaturan',
        ],
    ];

    $initialIndex = 0;
    foreach ($navItems as $idx => $item) {
        if (!empty($item['active'])) {
            $initialIndex = $idx;
            break;
        }
    }
@endphp
<x-layouts.internal-portal
    :title="$title"
    portal-subtitle="Pusat Multi-Cabang"
    :nav-items="$navItems"
    :portal-home-route="route('admin.dashboard')"
    portal-home-label="Executive Dashboard"
    :show-splash="$showSplash"
>
    <x-slot:customNav>
        @persist('super-admin-desktop-navbar')
        <nav
            x-data="{
                activeIndex: window.__superAdminActiveIndex ?? {{ $initialIndex }},
                isReady: !!window.__superAdminReady,
                reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                pillStyle: window.__superAdminPillStyle || '',
                get tabs() {
                    return Array.from(this.$el.querySelectorAll('[data-nav-item]'));
                },
                updatePill(index, instant = false) {
                    const tabs = this.tabs;
                    if (!tabs || !tabs[index]) return;
                    const target = tabs[index];
                    this.activeIndex = index;
                    window.__superAdminActiveIndex = index;
                    const style = `transform: translate3d(${target.offsetLeft}px, ${target.offsetTop}px, 0); width: ${target.offsetWidth}px; height: ${target.offsetHeight}px; opacity: 1;`;
                    this.pillStyle = style;
                    window.__superAdminPillStyle = style;
                },
                onTabClick(index) {
                    if (index === this.activeIndex) return;
                    this.updatePill(index, false);
                },
                syncFromUrl() {
                    const path = window.location.pathname;
                    const tabs = this.tabs;
                    let matchedIndex = -1;
                    let bestLen = 0;
                    tabs.forEach((tab, i) => {
                        const tabUrl = new URL(tab.href, window.location.origin);
                        if (path === tabUrl.pathname) {
                            matchedIndex = i;
                            bestLen = 9999;
                        } else if (tabUrl.pathname !== '/admin/dashboard' && path.startsWith(tabUrl.pathname) && tabUrl.pathname.length > bestLen) {
                            matchedIndex = i;
                            bestLen = tabUrl.pathname.length;
                        } else if (tab.dataset.keyword && path.includes(tab.dataset.keyword) && bestLen < 9999) {
                            matchedIndex = i;
                        }
                    });
                    if (matchedIndex !== -1) {
                        this.updatePill(matchedIndex, false);
                    }
                },
                init() {
                    this.$nextTick(() => {
                        const shouldAnimate = this.isReady;
                        this.updatePill(this.activeIndex, !shouldAnimate);
                        window.__superAdminReady = true;
                        this.isReady = true;
                    });

                    window.addEventListener('resize', () => {
                        this.updatePill(this.activeIndex, true);
                    }, { passive: true });

                    document.addEventListener('livewire:navigated', () => {
                        this.syncFromUrl();
                    });

                    if (document.fonts) {
                        document.fonts.ready.then(() => {
                            this.updatePill(this.activeIndex, true);
                        });
                    }
                }
            }"
            aria-label="Navigasi Portal"
            class="relative hidden lg:flex border-t border-slate-100 py-1.5 items-center gap-1 overflow-x-auto text-xs"
        >
            <!-- Sliding Checkpoint Indicator Pill -->
            <div
                class="absolute top-0 left-0 bg-emerald-700 rounded-xl shadow-xs pointer-events-none will-change-[transform,width] z-0"
                :class="isReady && !reducedMotion ? 'transition-[transform,width] duration-380 duration-[380ms] ease-[cubic-bezier(0.22,1,0.36,1)]' : 'transition-none'"
                :style="pillStyle"
                aria-hidden="true"
            ></div>

            @foreach($navItems as $idx => $item)
                <a
                    href="{{ $item['href'] }}"
                    wire:navigate
                    wire:navigate.hover
                    data-nav-item
                    data-keyword="{{ $item['keyword'] ?? '' }}"
                    @click="onTabClick({{ $idx }})"
                    class="relative z-10 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl min-h-[44px] whitespace-nowrap transition-colors duration-200"
                    :class="activeIndex === {{ $idx }} ? 'text-white font-bold' : 'text-slate-600 hover:text-emerald-800 hover:bg-emerald-50/60 font-semibold'"
                >
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        @endpersist
    </x-slot:customNav>

    {{ $slot }}
</x-layouts.internal-portal>

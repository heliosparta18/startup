@php
    $drawerUser = auth()->user();
    $drawerIsInternal = $isInternalStaff ?? false;
    $drawerInDashboard = $inDashboard ?? false;
    $drawerCatalogUrl = $drawerInDashboard ? '#katalog-utama' : route('katalog');
    $drawerStaffActions = [];
    if ($drawerUser && $drawerIsInternal && method_exists($drawerUser, 'profileActions')) {
        $drawerStaffActions = collect($drawerUser->profileActions($activeBranchSlug ?? 'banyuwangi-kota'))
            ->where('section', '!=', 'pelanggan')
            ->all();
    }
@endphp

<div
    x-show="mobileNav"
    x-cloak
    @keydown.escape.window="mobileNav = false"
    class="fixed inset-0 z-50 md:hidden flex"
    role="dialog"
    aria-modal="true"
    aria-label="Menu Navigasi Mobile"
>
    {{-- Translucent Backdrop Scrim --}}
    <div
        class="fixed inset-0 bg-black/55 backdrop-blur-[2px]"
        @click="mobileNav = false"
        aria-hidden="true"
    ></div>

    {{-- Left Slide-Over Navigation Panel --}}
    <div
        @click.stop
        class="relative z-10 w-[86vw] max-w-[320px] h-full bg-white shadow-2xl flex flex-col border-r border-slate-200 overflow-hidden"
    >
        {{-- Sticky Drawer Header --}}
        <div class="flex items-center justify-between px-4 h-14 border-b border-slate-200 shrink-0 bg-white">
            <a href="{{ route('dashboard') }}" @click="mobileNav = false" class="flex items-center gap-2 min-w-0">
                <img src="/logo.png" alt="OmniPharma Logo" class="w-7 h-7 object-contain shrink-0">
                <span class="text-sm font-extrabold text-slate-900 tracking-tight whitespace-nowrap">OmniPharma</span>
            </a>
            <button
                type="button"
                @click="mobileNav = false"
                class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] -mr-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                aria-label="Tutup menu navigasi"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable Body --}}
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5 text-xs">
            {{-- 1. Akses Tamu (Pengguna login mengakses Profil, Tema & Keluar dari menu profil kanan atas) --}}
            @guest
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <p class="text-[11px] font-semibold text-slate-600 leading-snug">
                        Masuk untuk unggah resep dokter, pantau antrean apotek, dan kelola pesanan.
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold transition shadow-2xs"
                        >
                            Masuk
                        </a>
                        @if (\Illuminate\Support\Facades\Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 font-bold transition"
                            >
                                Daftar
                            </a>
                        @endif
                    </div>
                </div>
            @endguest

            {{-- 2. Segmented Branch Switcher --}}
            <div class="space-y-1.5">
                <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Cabang Apotek Aktif</p>
                <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-slate-100 border border-slate-200/80">
                    <form method="POST" action="{{ route('branch.switch') }}">
                        @csrf
                        <input type="hidden" name="branch" value="banyuwangi-kota">
                        <button
                            type="submit"
                            aria-label="Cabang Banyuwangi Kota"
                            class="w-full min-h-[44px] px-2 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $activeBranchSlug === 'banyuwangi-kota' ? 'bg-white text-emerald-800 shadow-2xs border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            @if ($activeBranchSlug === 'banyuwangi-kota')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-700 shrink-0" aria-hidden="true"></span>
                            @endif
                            <span class="truncate">Banyuwangi</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('branch.switch') }}">
                        @csrf
                        <input type="hidden" name="branch" value="rogojampi">
                        <button
                            type="submit"
                            aria-label="Cabang Rogojampi"
                            class="w-full min-h-[44px] px-2 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $activeBranchSlug === 'rogojampi' ? 'bg-white text-emerald-800 shadow-2xs border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            @if ($activeBranchSlug === 'rogojampi')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-700 shrink-0" aria-hidden="true"></span>
                            @endif
                            <span class="truncate">Rogojampi</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- 3. Layanan & Belanja Utama --}}
            <div class="space-y-1.5">
                <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Menu Utama</p>
                <nav class="rounded-2xl border border-slate-200/80 bg-white divide-y divide-slate-100 overflow-hidden" aria-label="Navigasi Layanan">
                    @if (! $drawerIsInternal)
                        <a
                            href="{{ $drawerCatalogUrl }}"
                            @click="mobileNav = false"
                            class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span class="truncate">Katalog Obat</span>
                            </span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>

                        <a
                            href="{{ $resepFormUrl }}"
                            @if ($drawerInDashboard) @click.prevent="openResepModal(''); mobileNav = false" @else @click="mobileNav = false" @endif
                            class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="truncate">Unggah Resep Dokter</span>
                            </span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    @endif

                    <a
                        href="{{ $keranjangUrl }}"
                        @click="mobileNav = false"
                        class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="truncate">Keranjang Belanja</span>
                        </span>
                        <span class="flex items-center gap-2 shrink-0">
                            <span
                                x-show="cartCount > 0"
                                x-cloak
                                x-text="cartCount > 99 ? '99+' : cartCount"
                                class="px-2 py-0.5 rounded-full bg-emerald-700 text-white text-[10px] font-extrabold leading-none shrink-0"
                            ></span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ $riwayatPesananUrl }}"
                        @click="mobileNav = false"
                        class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="truncate">Riwayat Pesanan</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <button
                        type="button"
                        @click="$dispatch('open-tanya-apoteker'); mobileNav = false"
                        class="w-full flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold text-left transition cursor-pointer"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="truncate">Tanya Apoteker</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </nav>
            </div>

            {{-- 4. Portal Staff / Cabang (Hanya untuk Staf / Admin atau Akses Cepat Portal Dashboard) --}}
            @if ($drawerInDashboard || ! empty($drawerStaffActions))
                <div class="space-y-1.5">
                    <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Portal Sistem</p>
                    <nav class="rounded-2xl border border-slate-200/80 bg-white divide-y divide-slate-100 overflow-hidden" aria-label="Portal Peran">
                        @if ($drawerInDashboard)
                            <a
                                href="#portal-modul-heading"
                                @click="mobileNav = false"
                                class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                            >
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                    <span class="truncate">Portal Cabang &amp; Modul</span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endif

                        @foreach ($drawerStaffActions as $action)
                            <a
                                href="{{ $action['href'] }}"
                                @click="mobileNav = false"
                                class="flex items-center justify-between gap-3 px-3.5 min-h-[46px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                            >
                                <span class="flex items-center gap-3 min-w-0">
                                    @include('partials.profile-role-icon', ['icon' => $action['icon'], 'class' => 'w-4 h-4 text-emerald-700 shrink-0'])
                                    <span class="truncate">{{ $action['label'] }}</span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endif
        </div>

        @guest
            {{-- Sticky Footer (Khusus Tamu karena tidak memiliki dropdown profil di header kanan atas) --}}
            <div class="border-t border-slate-200 bg-slate-50/80 px-4 py-3 shrink-0">
                <div
                    class="flex items-center justify-between gap-2"
                    x-data="{ themeMode: window.omniGetTheme ? window.omniGetTheme() : 'light' }"
                    @omni-theme-changed.window="themeMode = $event.detail.theme"
                >
                    <span class="text-xs font-bold text-slate-700">Tampilan Tema</span>
                    <div class="grid grid-cols-2 gap-1 p-0.5 rounded-xl bg-white border border-slate-200">
                        <button
                            type="button"
                            @click="themeMode = window.omniSetTheme('light')"
                            :class="themeMode === 'light' ? 'bg-emerald-700 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="min-h-[44px] px-3 rounded-lg text-[11px] font-bold transition cursor-pointer"
                        >
                            Putih
                        </button>
                        <button
                            type="button"
                            @click="themeMode = window.omniSetTheme('dark')"
                            :class="themeMode === 'dark' ? 'bg-emerald-700 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="min-h-[44px] px-3 rounded-lg text-[11px] font-bold transition cursor-pointer"
                        >
                            Dark
                        </button>
                    </div>
                </div>
            </div>
        @endguest
    </div>
</div>

@props(['title' => 'OmniPharma', 'client' => null, 'fullWidth' => false])
@php
    $loggedUser = auth()->user();
    $userName = $loggedUser->name ?? 'Pengguna OmniPharma';
    $userEmail = $loggedUser->email ?? '-';
    $userRole = $loggedUser->role ?? ($loggedUser->peran ?? 'pelanggan');
    $isApoteker = $loggedUser && ($loggedUser->hasRole('apoteker') || $loggedUser->role === 'apoteker');
    $isSuperAdmin = $loggedUser && ($loggedUser->hasRole('super_admin') || $loggedUser->role === 'super_admin');
    $routeClient = request()->route('client');
    $userClientSlug = $loggedUser && method_exists($loggedUser, 'getClientSlug') ? $loggedUser->getClientSlug() : 'banyuwangi-kota';
    $userClientName = $loggedUser && method_exists($loggedUser, 'getClientName') ? $loggedUser->getClientName() : 'Cabang Banyuwangi Kota';
    $activeBranchSlug = $client ?: (is_string($routeClient) && $routeClient !== '' ? $routeClient : (session('active_branch') ?? (session('active_client') ?? $userClientSlug)));
    $activeBranchName = $activeBranchSlug === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang '.ucwords(str_replace('-', ' ', $activeBranchSlug));
    $photoUrl = session('firebase_photo') ?? null;

    $keranjangUrl = \Illuminate\Support\Facades\Route::has('keranjang')
        ? route('keranjang')
        : route('customer.cart', ['client' => $activeBranchSlug]);
    $riwayatPesananUrl = \Illuminate\Support\Facades\Route::has('pesanan.riwayat')
        ? route('pesanan.riwayat')
        : route('customer.order.history', ['client' => $activeBranchSlug]);
    $roleLabelText = $loggedUser && method_exists($loggedUser, 'roleLabel') ? $loggedUser->roleLabel() : ucfirst(str_replace('_', ' ', $userRole));
    $resepFormUrl = auth()->check()
        ? (\Illuminate\Support\Facades\Route::has('resep.form') ? route('resep.form') : route('dashboard').'#unggah-resep')
        : route('customer.home', ['client' => $activeBranchSlug]).'#unggah-resep';
    $catalogSearchTarget = auth()->check() ? route('dashboard') : route('katalog');

    $cartCookie = request()->cookie('omnipharma_customer_session') ?? request()->cookie('omni_session');
    $cartSessionId = (is_string($cartCookie) && $cartCookie !== '')
        ? $cartCookie
        : (session()->getId() !== '' ? session()->getId() : 'guest-'.md5((string) request()->ip()));
    $activeClientUuid = \App\Models\Client::where('slug', $activeBranchSlug)
        ->orWhere('id_client', $activeBranchSlug)
        ->value('id_client') ?? $activeBranchSlug;
    $initialCartCount = app(\App\Modules\Ordering\Services\CartService::class)->getCount($cartSessionId, (string) $activeClientUuid);
    $keranjangAddUrl = request()->is('klien/*')
        ? route('customer.cart.add', ['client' => $activeBranchSlug])
        : (\Illuminate\Support\Facades\Route::has('keranjang.add')
            ? route('keranjang.add')
            : route('customer.cart.add', ['client' => $activeBranchSlug]));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body
    class="min-h-screen bg-[#F8FAFC] text-slate-900 font-sans flex flex-col overflow-x-hidden"
    @add-to-cart.window="addToCart($event.detail)"
    @cart-updated.window="handleCartUpdated($event.detail)"
    x-data="{
        mobileNav: false,
        cartCount: {{ (int) $initialCartCount }},
        cartBounce: false,
        cartToast: '',
        cartToastTimer: null,
        chatComingSoon: false,
        chatComingSoonTimer: null,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        searchError: '',
        searchDropdownOpen: false,
        activeBranch: {{ \Illuminate\Support\Js::from($activeBranchSlug) }},
        branchMenuOpen: false,
        addToCart(detail) {
            if (!detail || !detail.id) return;
            const qty = Math.max(1, Number(detail.qty || 1));
            const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
            const endpoint = {{ \Illuminate\Support\Js::from($keranjangAddUrl) }};

            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    id_obat: detail.id,
                    jumlah: qty
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Gagal menambahkan produk ke keranjang.');
                return data;
            })
            .then(data => {
                this.handleCartUpdated({
                    count: typeof data.cart_count === 'number' ? data.cart_count : null,
                    added: qty,
                    nama: detail.nama || 'Produk'
                });
            })
            .catch(err => {
                this.cartToast = err.message || 'Gagal menambahkan produk ke keranjang.';
                clearTimeout(this.cartToastTimer);
                this.cartToastTimer = setTimeout(() => { this.cartToast = ''; }, 3500);
            });
        },
        handleCartUpdated(detail) {
            if (typeof detail?.count === 'number') {
                this.cartCount = detail.count;
            } else {
                this.cartCount = Number(this.cartCount || 0) + Number(detail?.added || 1);
            }
            this.cartBounce = true;
            setTimeout(() => { this.cartBounce = false; }, 450);

            if (detail?.silent) {
                return;
            }

            const nama = detail?.nama ? detail.nama : 'Produk';
            this.cartToast = nama + ' ditambahkan ke keranjang (' + this.cartCount + ' item).';
            clearTimeout(this.cartToastTimer);
            this.cartToastTimer = setTimeout(() => { this.cartToast = ''; }, 3500);
        },
        triggerChatComingSoon() {
            this.chatComingSoon = true;
            clearTimeout(this.chatComingSoonTimer);
            this.chatComingSoonTimer = setTimeout(() => { this.chatComingSoon = false; }, 3500);
        },
        liveSearch() {
            this.searchError = '';
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                this.searchLoading = false;
                this.searchDropdownOpen = false;
                return;
            }
            this.searchLoading = true;
            this.searchDropdownOpen = true;
            fetch('/api/search-obat?q=' + encodeURIComponent(this.searchQuery.trim()) + '&client=' + encodeURIComponent(this.activeBranch))
                .then(res => {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    this.searchResults = Array.isArray(data) ? data : [];
                    this.searchLoading = false;
                })
                .catch(() => {
                    this.searchResults = [];
                    this.searchLoading = false;
                    this.searchError = 'Gagal memuat saran pencarian cepat.';
                });
        },
        goToCatalogSearch() {
            const q = this.searchQuery.trim();
            const base = {{ \Illuminate\Support\Js::from($catalogSearchTarget) }};
            window.location.href = q ? (base + '?search=' + encodeURIComponent(q)) : base;
        },
        clearSearch() {
            this.searchQuery = '';
            this.searchResults = [];
            this.searchError = '';
            this.searchDropdownOpen = false;
        }
    }"
>
    <!-- Floating Notification Toasts (Cart Updated & Chat Coming Soon) -->
    <div
        class="fixed top-20 right-4 z-50 flex flex-col gap-2.5 max-w-sm w-[calc(100vw-2rem)] sm:w-auto pointer-events-none"
        aria-live="polite"
    >
        <div
            x-show="cartToast !== ''"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            class="pointer-events-auto flex items-center gap-3 rounded-2xl bg-emerald-700 text-white px-4 py-3 shadow-lg border border-emerald-500/40 text-xs"
            role="status"
        >
            <span class="w-7 h-7 rounded-xl bg-white text-emerald-700 flex items-center justify-center shrink-0 font-black">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-white leading-snug" x-text="cartToast"></p>
                <a href="{{ $keranjangUrl }}" class="inline-block mt-1 text-[11px] font-bold text-emerald-100 hover:underline">
                    Lihat Keranjang &rarr;
                </a>
            </div>
            <button type="button" @click="cartToast = ''" class="text-slate-300 hover:text-white p-1 shrink-0 cursor-pointer" aria-label="Tutup notifikasi keranjang">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div
            x-show="chatComingSoon"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            class="pointer-events-auto flex items-center gap-3 rounded-2xl bg-slate-900 text-white px-4 py-3 shadow-lg border border-emerald-400/40 text-xs"
            role="status"
        >
            <span class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-white leading-snug">Konsultasi Apoteker (Coming Soon)</p>
                <p class="text-[11px] text-slate-300 mt-0.5">Fitur Chat Konsultasi Apoteker segera hadir!</p>
            </div>
            <button type="button" @click="chatComingSoon = false" class="text-slate-300 hover:text-white p-1 shrink-0 cursor-pointer" aria-label="Tutup informasi fitur konsultasi">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Mobile Hamburger & Brand Logo -->
            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                <button
                    type="button"
                    @click="mobileNav = !mobileNav"
                    class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-slate-200 md:hidden text-slate-700 hover:bg-slate-50 transition shrink-0 cursor-pointer"
                    :aria-expanded="mobileNav"
                    aria-label="Menu Navigasi Mobile"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-2 sm:gap-2.5 min-h-[44px] py-1 min-w-0 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    aria-label="OmniPharma Dashboard Utama"
                >
                    <span class="sr-only">Dashboard Utama</span>
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-8 w-8 sm:h-9 sm:w-9 object-contain shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                            <span class="block text-sm sm:text-lg font-extrabold text-slate-900 tracking-tight leading-none truncate">OmniPharma</span>
                        </div>
                        <span class="block text-[10px] sm:text-[11px] font-medium text-slate-600 leading-tight truncate mt-0.5 max-w-[140px] sm:max-w-[230px]">
                            Portal Farmasi Resmi Terverifikasi
                        </span>
                    </div>
                </a>
            </div>

            <!-- Desktop Quick Search Bar with /api/search-obat Autocomplete -->
            <div
                class="hidden md:flex flex-1 max-w-xl mx-2 relative"
                @click.outside="searchDropdownOpen = false"
                @keydown.escape.stop="searchDropdownOpen = false"
            >
                <div class="relative w-full">
                    <label for="desktop-customer-layout-search" class="sr-only">Cari obat, suplemen, atau kebutuhan kesehatan</label>
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        id="desktop-customer-layout-search"
                        type="search"
                        x-model="searchQuery"
                        @input.debounce.250ms="liveSearch()"
                        @focus="if(searchQuery.trim().length >= 2) searchDropdownOpen = true"
                        @keydown.enter.prevent="goToCatalogSearch()"
                        placeholder="Cari nama obat, zat aktif, atau suplemen..."
                        class="w-full min-h-[44px] pl-10 pr-12 rounded-xl border border-slate-300 bg-slate-50/70 focus:bg-white text-slate-900 placeholder:text-slate-400 text-sm shadow-2xs focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 transition-colors"
                    />
                    <button
                        type="button"
                        x-show="searchQuery"
                        x-cloak
                        @click="clearSearch()"
                        class="absolute inset-y-0 right-0 px-3 flex items-center justify-center min-w-[44px] min-h-[44px] text-slate-500 hover:text-slate-900 cursor-pointer font-bold text-lg focus-visible:outline-2 focus-visible:outline-emerald-700 rounded-r-xl"
                        aria-label="Hapus pencarian"
                    >
                        &times;
                    </button>
                </div>

                <!-- Desktop Autocomplete Dropdown -->
                <div
                    x-show="searchDropdownOpen"
                    x-cloak
                    class="absolute top-12 left-0 right-0 bg-white rounded-xl shadow-md border border-slate-200 p-2 z-50 max-h-96 overflow-y-auto"
                    role="region"
                    aria-label="Hasil pencarian cepat"
                >
                    <div x-show="searchLoading" class="p-4 text-center text-xs font-medium text-slate-600" role="status">
                        Memuat data sediaan farmasi...
                    </div>
                    <div x-show="!searchLoading && searchError" class="p-4 text-center text-xs font-medium text-rose-800 bg-rose-50 rounded-lg border border-rose-200" role="alert" x-text="searchError"></div>
                    <template x-for="item in searchResults" :key="item.id">
                        <a
                            :href="item.url"
                            class="flex items-center gap-3 p-2.5 min-h-[44px] rounded-lg hover:bg-emerald-50 transition-colors group border-b border-slate-100 last:border-0 focus-visible:outline-2 focus-visible:outline-emerald-700"
                        >
                            <img :src="item.gambar" :alt="item.nama" class="w-11 h-11 object-contain rounded-lg bg-slate-50 p-1 border border-slate-200 shrink-0" onerror="this.onerror=null;this.src='/images/placeholder-product.svg';">
                            <div class="flex-1 min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-emerald-800 truncate" x-text="item.nama"></h5>
                                <p class="text-[11px] text-slate-600 mt-0.5 truncate" x-text="(item.kategori || 'Kesehatan') + ' / ' + (item.sediaan || 'Kemasan Resmi')"></p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-bold text-slate-900" x-text="item.harga_formatted"></span>
                                <span x-show="item.tersedia" class="block text-[11px] font-semibold text-emerald-800">Tersedia</span>
                                <span x-show="!item.tersedia" class="block text-[11px] font-semibold text-slate-600">Cek Cabang Lain</span>
                            </div>
                        </a>
                    </template>
                    <div x-show="!searchLoading && !searchError && searchResults.length === 0" class="p-4 text-center space-y-2">
                        <p class="text-xs font-medium text-slate-700">Tidak ada hasil cepat untuk kata kunci tersebut.</p>
                        <button
                            type="button"
                            @click="goToCatalogSearch()"
                            class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 text-white text-xs font-bold cursor-pointer hover:bg-emerald-700"
                        >
                            Cari di Katalog Utama
                        </button>
                    </div>
                    <div x-show="!searchLoading && searchResults.length > 0" class="pt-1 mt-1 border-t border-slate-200">
                        <button
                            type="button"
                            @click="goToCatalogSearch()"
                            class="w-full flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 text-white text-xs font-bold cursor-pointer hover:bg-emerald-700"
                        >
                            Tampilkan Semua Hasil di Katalog
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Actions: Chat Konsultasi (Coming Soon), Keranjang & Account Menu -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                <button
                    type="button"
                    @click="triggerChatComingSoon()"
                    class="relative inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-900 transition shadow-2xs min-h-[44px] min-w-[44px] px-2.5 py-2 shrink-0 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    title="Konsultasi Apoteker (Coming Soon)"
                    aria-label="Konsultasi Apoteker (Coming Soon)"
                >
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="absolute -top-1 -right-1 px-1 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[8px] font-extrabold leading-none tracking-tight">Soon</span>
                </button>

                <a
                    href="{{ $keranjangUrl }}"
                    class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-800 hover:text-emerald-900 hover:bg-emerald-50 border border-slate-200 bg-white shadow-2xs transition-colors min-h-[44px] min-w-[44px] shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    title="Keranjang Belanja"
                    aria-label="Keranjang Belanja"
                >
                    <svg class="w-5 h-5 text-emerald-700 shrink-0 transition-transform duration-200" :class="cartBounce ? 'scale-125' : 'scale-100'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="sr-only">Keranjang</span>
                    <span
                        x-show="cartCount > 0"
                        x-cloak
                        x-text="cartCount > 99 ? '99+' : cartCount"
                        :class="cartBounce ? 'scale-125' : 'scale-100'"
                        class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 rounded-full bg-emerald-600 text-white text-[10px] font-black leading-none flex items-center justify-center border-2 border-white shadow-xs transition-transform duration-200"
                    >{{ $initialCartCount > 99 ? '99+' : $initialCartCount }}</span>
                </a>

                @auth
                    <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-slate-100 shadow-2xs text-xs transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                            id="customer-layout-user-menu-button"
                            :aria-expanded="open"
                            aria-haspopup="true"
                            aria-label="Menu Pengguna"
                        >
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Foto Profil {{ $userName }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-300">
                            @else
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($userName, 0, 2)) }}
                                </div>
                            @endif
                            <div class="hidden sm:flex flex-col text-left leading-none">
                                <span class="font-bold text-slate-900 text-xs truncate max-w-[120px]">{{ $userName }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-600 transition-transform duration-150 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            class="absolute right-0 mt-2 w-64 rounded-xl bg-white p-2 shadow-md border border-slate-200 z-50 text-xs"
                            role="menu"
                            aria-orientation="vertical"
                            aria-labelledby="customer-layout-user-menu-button"
                        >
                            <div class="px-3 py-2.5 bg-slate-50 rounded-lg mb-1.5 border border-slate-200">
                                <p class="font-bold text-slate-900 text-sm truncate">{{ $userName }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $userEmail }}</p>
                            </div>

                            <div class="space-y-1">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px]"
                                    role="menuitem"
                                >
                                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <span>Profile</span>
                                </a>

                                <a
                                    href="{{ $riwayatPesananUrl }}"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px]"
                                    role="menuitem"
                                >
                                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <span>Riwayat Pesanan</span>
                                </a>

                                <div
                                    class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200/80"
                                    x-data="{ themeMode: window.omniGetTheme ? window.omniGetTheme() : 'light' }"
                                    @omni-theme-changed.window="themeMode = $event.detail.theme"
                                >
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[11px] font-bold text-slate-700">Tema Tampilan</span>
                                        <span class="text-[10px] font-extrabold uppercase text-emerald-700" x-text="themeMode === 'dark' ? 'Dark' : 'Putih'"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button
                                            type="button"
                                            @click="themeMode = window.omniSetTheme('light')"
                                            :class="themeMode === 'light' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                            class="min-h-[34px] px-2.5 py-1.5 rounded-lg border text-[11px] font-bold flex items-center justify-center gap-1.5 transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span>Putih</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="themeMode = window.omniSetTheme('dark')"
                                            :class="themeMode === 'dark' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                            class="min-h-[34px] px-2.5 py-1.5 rounded-lg border text-[11px] font-bold flex items-center justify-center gap-1.5 transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                            <span>Dark</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="my-1.5 border-t border-slate-200"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-800 hover:bg-rose-50 font-bold transition-colors min-h-[44px] text-left cursor-pointer"
                                    role="menuitem"
                                    data-test="logout-button"
                                >
                                    <svg class="w-4 h-4 text-rose-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex min-h-[44px] items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 sm:px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs shrink-0"
                        title="Masuk ke Akun"
                    >
                        <span>Masuk</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Mobile Search Row (< md) -->
        <div
            class="md:hidden border-t border-slate-200 bg-white px-4 py-2.5 relative"
            @click.outside="searchDropdownOpen = false"
            @keydown.escape.stop="searchDropdownOpen = false"
        >
            <div class="relative w-full">
                <label for="mobile-customer-layout-search" class="sr-only">Cari obat atau suplemen</label>
                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    id="mobile-customer-layout-search"
                    type="search"
                    x-model="searchQuery"
                    @input.debounce.250ms="liveSearch()"
                    @focus="if(searchQuery.trim().length >= 2) searchDropdownOpen = true"
                    @keydown.enter.prevent="goToCatalogSearch()"
                    placeholder="Cari nama obat atau suplemen..."
                    class="w-full min-h-[44px] pl-10 pr-12 rounded-xl border border-slate-300 bg-slate-50/70 focus:bg-white text-slate-900 placeholder:text-slate-400 text-sm shadow-2xs focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                />
                <button
                    type="button"
                    x-show="searchQuery"
                    x-cloak
                    @click="clearSearch()"
                    class="absolute inset-y-0 right-0 px-3 flex items-center justify-center min-w-[44px] min-h-[44px] text-slate-500 hover:text-slate-900 cursor-pointer font-bold text-lg"
                    aria-label="Hapus pencarian mobile"
                >
                    &times;
                </button>
            </div>

            <div
                x-show="searchDropdownOpen"
                x-cloak
                class="mt-2 bg-white rounded-xl shadow-md border border-slate-200 p-2 max-h-80 overflow-y-auto"
                role="region"
                aria-label="Hasil pencarian mobile"
            >
                <div x-show="searchLoading" class="p-3 text-center text-xs font-medium text-slate-600" role="status">
                    Memuat data sediaan farmasi...
                </div>
                <div x-show="!searchLoading && searchError" class="p-3 text-center text-xs font-medium text-rose-800 bg-rose-50 rounded-lg border border-rose-200" role="alert" x-text="searchError"></div>
                <template x-for="item in searchResults" :key="'mob-cust-layout-' + item.id">
                    <a
                        :href="item.url"
                        class="flex items-center gap-3 p-2 min-h-[44px] rounded-lg hover:bg-emerald-50 border-b border-slate-100 last:border-0"
                    >
                        <img :src="item.gambar" :alt="item.nama" class="w-10 h-10 object-contain rounded-lg bg-slate-50 p-1 border border-slate-200 shrink-0" onerror="this.onerror=null;this.src='/images/placeholder-product.svg';">
                        <div class="flex-1 min-w-0">
                            <span class="block text-xs font-bold text-slate-900 truncate" x-text="item.nama"></span>
                            <span class="block text-[11px] text-slate-600 truncate" x-text="item.harga_formatted"></span>
                        </div>
                    </a>
                </template>
                <div x-show="!searchLoading && !searchError && searchResults.length === 0" class="p-3 text-center space-y-2">
                    <p class="text-xs text-slate-700">Tidak ada hasil cepat untuk kata kunci ini.</p>
                    <button type="button" @click="goToCatalogSearch()" class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 text-white text-xs font-bold cursor-pointer hover:bg-emerald-700">
                        Cari di Katalog Utama
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu (< md) -->
        <div
            x-show="mobileNav"
            x-cloak
            @click.outside="mobileNav = false"
            class="md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-xs font-medium"
        >
            @auth
                <div class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-900 truncate">{{ $userName }}</p>
                    <p class="text-[11px] text-slate-600 truncate">{{ $userEmail }}</p>
                </div>
            @endauth

            <button
                type="button"
                @click="triggerChatComingSoon(); mobileNav = false"
                class="w-full flex items-center justify-between gap-2.5 min-h-[44px] px-3 py-2 rounded-xl bg-emerald-50 text-emerald-900 font-bold border border-emerald-200 cursor-pointer text-left"
            >
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    <span>Chat Konsultasi Apoteker</span>
                </span>
                <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-extrabold leading-none">Coming Soon</span>
            </button>

            <a href="{{ $keranjangUrl }}" class="flex items-center justify-between gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span>Keranjang Belanja</span>
                </span>
                <span x-show="cartCount > 0" x-cloak x-text="cartCount > 99 ? '99+' : cartCount" class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-extrabold leading-none"></span>
            </a>

            <a href="{{ $resepFormUrl }}" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span>Unggah Resep Dokter</span>
            </a>

            <div class="pt-2 border-t border-slate-100 space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-wide text-slate-500">Pilih Cabang Aktif</p>
                <form method="POST" action="{{ route('branch.switch') }}">
                    @csrf
                    <input type="hidden" name="branch" value="banyuwangi-kota">
                    <button type="submit" class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-xl text-left cursor-pointer {{ $activeBranchSlug === 'banyuwangi-kota' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-700 hover:bg-slate-50 font-semibold' }}">
                        <span>Cabang Banyuwangi Kota</span>
                        @if($activeBranchSlug === 'banyuwangi-kota')
                            <span aria-hidden="true">&#10003;</span>
                        @endif
                    </button>
                </form>
                <form method="POST" action="{{ route('branch.switch') }}">
                    @csrf
                    <input type="hidden" name="branch" value="rogojampi">
                    <button type="submit" class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-xl text-left cursor-pointer {{ $activeBranchSlug === 'rogojampi' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-700 hover:bg-slate-50 font-semibold' }}">
                        <span>Cabang Rogojampi</span>
                        @if($activeBranchSlug === 'rogojampi')
                            <span aria-hidden="true">&#10003;</span>
                        @endif
                    </button>
                </form>
            </div>

            <div class="pt-2 border-t border-slate-100 space-y-1">
                @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold">
                        <span>Profile</span>
                    </a>
                    <a href="{{ $riwayatPesananUrl }}" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold">
                        <span>Riwayat Pesanan</span>
                    </a>
                    <div
                        class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2"
                        x-data="{ themeMode: window.omniGetTheme ? window.omniGetTheme() : 'light' }"
                        @omni-theme-changed.window="themeMode = $event.detail.theme"
                    >
                        <span class="text-xs font-bold text-slate-700">Tema</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="themeMode = window.omniSetTheme('light')" :class="themeMode === 'light' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-2.5 py-1 rounded-lg text-[11px] font-bold cursor-pointer">Putih</button>
                            <button type="button" @click="themeMode = window.omniSetTheme('dark')" :class="themeMode === 'dark' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-2.5 py-1 rounded-lg text-[11px] font-bold cursor-pointer">Dark</button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-rose-700 hover:bg-rose-50 font-bold text-left cursor-pointer">
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                @else
                    <a href="{{ $riwayatPesananUrl }}" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold">
                        <span>Riwayat Pesanan</span>
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                        <span>Masuk ke Akun</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Operational Sub-bar: Regulatory Scope & Interactive Branch Switcher -->
        <div class="bg-slate-50 border-t border-slate-200 px-4 sm:px-6 lg:px-8 py-2 text-xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-3">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-700 min-w-0">
                    <span class="hidden sm:inline font-semibold text-slate-800 truncate">
                        Standar Permenkes RI No. 73/2016
                    </span>
                    <span class="sm:hidden font-semibold text-slate-700 truncate">
                        Apotek Resmi Terverifikasi
                    </span>
                    <span class="text-slate-400 hidden md:inline" aria-hidden="true">|</span>
                    <a href="{{ route('dashboard') }}" class="hidden md:inline font-semibold text-emerald-800 hover:underline py-1">
                        Katalog Utama
                    </a>
                    <a href="{{ $resepFormUrl }}" class="hidden md:inline font-semibold text-emerald-800 hover:underline py-1">
                        Unggah Resep
                    </a>
                </div>

                <!-- Interactive Location / Branch Selector -->
                <div class="relative shrink-0" @click.outside="branchMenuOpen = false" @keydown.escape.stop="branchMenuOpen = false">
                    <button
                        type="button"
                        @click="branchMenuOpen = !branchMenuOpen"
                        class="flex items-center gap-1.5 sm:gap-2 bg-white border border-slate-300 shadow-2xs rounded-xl px-2.5 sm:px-3 py-1.5 font-bold text-slate-900 hover:border-emerald-700 transition-colors cursor-pointer min-h-[44px] max-w-[210px] sm:max-w-none"
                        :aria-expanded="branchMenuOpen"
                        aria-haspopup="true"
                    >
                        <svg class="w-4 h-4 text-emerald-800 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-xs truncate">{{ $activeBranchName }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div
                        x-show="branchMenuOpen"
                        x-cloak
                        class="absolute right-0 mt-1.5 w-60 sm:w-64 bg-white rounded-xl shadow-md border border-slate-200 p-2 z-50 text-xs space-y-1"
                    >
                        <p class="px-2 py-1 text-[11px] font-bold text-slate-600 uppercase tracking-wide">Pilih Cabang Apotek</p>
                        <form method="POST" action="{{ route('branch.switch') }}">
                            @csrf
                            <input type="hidden" name="branch" value="banyuwangi-kota">
                            <button
                                type="submit"
                                class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-lg text-left transition-colors cursor-pointer {{ $activeBranchSlug === 'banyuwangi-kota' ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-800 font-medium' }}"
                            >
                                <span>Cabang Banyuwangi Kota</span>
                                @if($activeBranchSlug === 'banyuwangi-kota')
                                    <span class="font-bold" aria-hidden="true">&#10003;</span>
                                @endif
                            </button>
                        </form>
                        <form method="POST" action="{{ route('branch.switch') }}">
                            @csrf
                            <input type="hidden" name="branch" value="rogojampi">
                            <button
                                type="submit"
                                class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-lg text-left transition-colors cursor-pointer {{ $activeBranchSlug === 'rogojampi' ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-800 font-medium' }}"
                            >
                                <span>Cabang Rogojampi</span>
                                @if($activeBranchSlug === 'rogojampi')
                                    <span class="font-bold" aria-hidden="true">&#10003;</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body -->
    @if ($fullWidth ?? false)
        <main class="flex-1 w-full flex flex-col">
            {{ $slot }}
        </main>
    @else
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
            {{ $slot }}
        </main>
    @endif

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-5 px-4 sm:px-6 lg:px-8 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <span class="font-bold text-slate-900">OmniPharma Network (Sistem Informasi Farmasi Terpadu)</span>
            <span class="text-[11px] text-slate-500">Standar Permenkes RI No. 73/2016 &bull; SIA &amp; SIP Terverifikasi</span>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

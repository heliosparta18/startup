<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100/90">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Utama - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
@php
    $photoUrl = session('firebase_photo') ?? null;
    $provider = session('firebase_provider', 'password');
    $user = auth()->user();
    $userName = $user->name ?? 'Guntur Afandy';
    $userEmail = $user->email ?? '-';
    $userRole = $user->role ?? ($user->peran ?? 'pelanggan');
    $isApoteker = $user && ($user->hasRole('apoteker') || $user->role === 'apoteker');
    $isSuperAdmin = $user && ($user->hasRole('super_admin') || $user->role === 'super_admin');
    $userClientSlug = $user && method_exists($user, 'getClientSlug') ? $user->getClientSlug() : 'banyuwangi-kota';
    $userClientName = $user && method_exists($user, 'getClientName') ? $user->getClientName() : 'Cabang Banyuwangi Kota';
    $activeBranchSlug = session('active_branch') ?? (session('active_client') ?? $userClientSlug);
    $activeBranchName = $activeBranchSlug === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang Banyuwangi Kota';

    // Server-rendered products for guaranteed zero-delay, zero-error display
    $dashboardProducts = \App\Models\Obat::query()
        ->with(['kategori', 'subkategori', 'stokClient' => fn ($q) => $q->withoutGlobalScopes()])
        ->take(12)
        ->get();

    $productsJson = $dashboardProducts->map(function ($obat) use ($activeBranchSlug) {
        $harga = (int) $obat->harga;
        return [
            'id' => $obat->id_obat,
            'nama' => $obat->nama_obat,
            'merek' => $obat->merek ?: 'Farmasi Indonesia',
            'kategori' => $obat->kategori->nama_kategori ?? 'Obat',
            'subkategori' => $obat->subkategori->nama_subkategori ?? '',
            'sediaan' => $obat->satuan_kemasan ?: $obat->jenis_sediaan,
            'harga' => $harga,
            'harga_formatted' => 'Rp ' . number_format($harga, 0, ',', '.'),
            'gambar' => $obat->url_gambar,
            'badge' => $obat->badge_status,
            'is_keras' => str_contains(strtolower($obat->badge_status ?? ''), 'keras') || ($obat->kategori?->wajib_resep ?? false),
            'is_bebas_terbatas' => str_contains(strtolower($obat->badge_status ?? ''), 'terbatas'),
            'is_bebas' => str_contains(strtolower($obat->badge_status ?? ''), 'bebas') && ! str_contains(strtolower($obat->badge_status ?? ''), 'terbatas'),
            'tersedia' => $obat->isAvailableInBranch($activeBranchSlug),
            'availability_text' => $obat->branch_availability_text,
            'detail_url' => route('customer.obat.detail', ['client' => $activeBranchSlug, 'id' => $obat->id_obat]),
        ];
    });
@endphp
<body
    class="min-h-full bg-[#F8FAFC] text-slate-800 antialiased flex flex-col font-sans overflow-x-hidden"
    x-data="{
        searchQuery: '',
        selectedCategory: '',
        selectedTab: 'diskon',
        searchResults: [],
        searchLoading: false,
        searchDropdownOpen: false,
        activeBranch: '{{ $activeBranchSlug }}',
        branchMenuOpen: false,
        allProducts: {{ \Illuminate\Support\Js::from($productsJson) }},
        get filteredProducts() {
            return this.allProducts.filter(p => {
                let matchCat = true;
                if (this.selectedCategory) {
                    let cat = this.selectedCategory.toLowerCase();
                    matchCat = (p.kategori && p.kategori.toLowerCase().includes(cat)) ||
                               (p.subkategori && p.subkategori.toLowerCase().includes(cat)) ||
                               (p.nama && p.nama.toLowerCase().includes(cat));
                }

                let matchSearch = true;
                if (this.searchQuery) {
                    let q = this.searchQuery.toLowerCase();
                    matchSearch = (p.nama && p.nama.toLowerCase().includes(q)) ||
                                  (p.merek && p.merek.toLowerCase().includes(q)) ||
                                  (p.kategori && p.kategori.toLowerCase().includes(q));
                }

                return matchCat && matchSearch;
            });
        },
        liveSearch() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.searchDropdownOpen = false;
                return;
            }
            this.searchLoading = true;
            fetch('/api/search-obat?q=' + encodeURIComponent(this.searchQuery) + '&client=' + this.activeBranch)
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data;
                    this.searchLoading = false;
                    this.searchDropdownOpen = true;
                })
                .catch(() => { this.searchLoading = false; });
        },
        selectCategory(catName) {
            this.selectedCategory = (this.selectedCategory === catName) ? '' : catName;
            this.searchDropdownOpen = false;
            document.getElementById('etalase-produk')?.scrollIntoView({ behavior: 'smooth' });
        }
    }"
>
    <x-welcome-splash />

    <!-- Top Header: GoApotik Consumer Portal Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3 sm:gap-6">
            <!-- Brand Logo -->
            <div class="flex items-center shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 min-h-[44px] py-1" aria-label="OmniPharma Dashboard">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 sm:h-10 sm:w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.25)] shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="block text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">OmniPharma</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                @if($isSuperAdmin) PUSAT @elseif($isApoteker) APJ @else PASIEN @endif
                            </span>
                        </div>
                        <span class="block text-[11px] font-semibold text-slate-400 leading-tight truncate mt-0.5">
                            @if($isSuperAdmin)
                                Dashboard Pusat (Super Admin)
                            @elseif($isApoteker)
                                Portal Apoteker ({{ $userClientName }})
                            @else
                                Portal Farmasi Resmi Terverifikasi
                            @endif
                        </span>
                    </div>
                </a>
            </div>

            <!-- Central Search Bar with Live Autocomplete Dropdown (No Unwanted Redirect) -->
            <div class="hidden md:flex flex-1 max-w-xl mx-2 relative" @click.outside="searchDropdownOpen = false" @keydown.escape="searchDropdownOpen = false">
                <div class="relative w-full">
                    <input
                        type="search"
                        x-model="searchQuery"
                        @input.debounce.250ms="liveSearch()"
                        @focus="if(searchResults.length > 0) searchDropdownOpen = true"
                        @keydown.enter.prevent="searchDropdownOpen = false; document.getElementById('etalase-produk')?.scrollIntoView({ behavior: 'smooth' })"
                        placeholder="Cari produk obat, suplemen, atau kebutuhan kesehatan..."
                        class="w-full h-11 pl-10 pr-10 rounded-xl border border-slate-200 bg-slate-50/70 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:bg-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-2xs"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button
                        type="button"
                        x-show="searchQuery"
                        @click="searchQuery = ''; searchResults = []; searchDropdownOpen = false"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-base"
                        title="Hapus pencarian"
                    >
                        &times;
                    </button>
                </div>

                {{-- Live Autocomplete Dropdown --}}
                <div
                    x-show="searchDropdownOpen"
                    x-cloak
                    x-transition
                    class="absolute top-12 left-0 right-0 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2.5 z-50 max-h-96 overflow-y-auto"
                >
                    <div x-show="searchLoading" class="p-4 text-center text-xs text-slate-400">
                        Mencari produk obat...
                    </div>
                    <template x-for="item in searchResults" :key="item.id">
                        <a
                            :href="item.url"
                            class="flex items-center gap-3 p-2 rounded-xl hover:bg-emerald-50/80 transition group border-b border-slate-100 last:border-0"
                        >
                            <img :src="item.gambar" class="w-11 h-11 object-contain rounded-lg bg-slate-50 p-1 border border-slate-200/80 shrink-0">
                            <div class="flex-1 min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 truncate" x-text="item.nama"></h5>
                                <p class="text-[10px] text-slate-500 mt-0.5" x-text="item.kategori + ' • ' + item.sediaan"></p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black text-emerald-800" x-text="item.harga_formatted"></span>
                                <span x-show="item.tersedia" class="block text-[9px] font-bold text-emerald-600">Tersedia</span>
                            </div>
                        </a>
                    </template>
                    <div x-show="!searchLoading && searchResults.length === 0" class="p-4 text-center text-xs text-slate-500">
                        Tidak ada produk yang cocok dengan pencarian Anda.
                    </div>
                </div>
            </div>

            <!-- Actions: Cart, Alerts & Profile Dropdown -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a
                    href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                    class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 border border-slate-200 bg-white transition min-h-[44px] min-w-[44px] shadow-2xs"
                    aria-label="Keranjang Belanja"
                    title="Keranjang Belanja"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="hidden lg:inline text-xs font-bold ml-1.5">Keranjang</span>
                </a>

                <!-- Profile Dropdown (Clean, Clickable) -->
                <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-full border border-slate-200/90 bg-slate-50 hover:bg-slate-100 text-xs transition cursor-pointer min-h-[44px] focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        id="user-menu-button"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Menu Pengguna"
                    >
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shrink-0 border border-emerald-500/40">
                        @else
                            <div class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden sm:flex flex-col text-left leading-none">
                            <span class="font-bold text-slate-800 text-xs truncate max-w-[120px]">{{ $userName }}</span>
                            <span class="text-[10px] text-emerald-700 font-semibold mt-0.5 capitalize">{{ str_replace('_', ' ', $userRole) }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        class="absolute right-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 text-xs"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="user-menu-button"
                    >
                        <div class="px-3 py-2.5 bg-slate-50 rounded-xl mb-1.5 border border-slate-100">
                            <p class="font-extrabold text-slate-900 text-sm truncate">{{ $userName }}</p>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $userEmail }}</p>
                            <span class="inline-block mt-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                                {{ str_replace('_', ' ', $userRole) }} &bull; {{ $userClientName }}
                            </span>
                        </div>

                        <div class="space-y-0.5">
                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>Pengaturan Profil</span>
                            </a>

                            <a
                                href="{{ route('customer.catalog', ['client' => $activeBranchSlug]) }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                <span>Katalog Farmasi Publik</span>
                            </a>

                            <a
                                href="{{ route('home') }}?welcome=1"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium transition min-h-[44px]"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <span>Halaman Sambutan Utama</span>
                            </a>
                        </div>

                        <div class="my-1.5 border-t border-slate-100"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-bold transition min-h-[44px] text-left cursor-pointer"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub-bar: Points, Voucher & Location Switcher -->
        <div class="bg-slate-50/90 border-t border-slate-100 px-4 sm:px-6 lg:px-8 py-2 text-xs relative">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-slate-600">
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-4 h-4 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center text-[10px] font-black">P</span>
                        <span>0 Poin</span>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-sky-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Voucher Tersedia</span>
                    </span>
                </div>

                {{-- Interactive Location / Branch Selector --}}
                <div class="relative" @click.outside="branchMenuOpen = false">
                    <button
                        type="button"
                        @click="branchMenuOpen = !branchMenuOpen"
                        class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-1 font-bold text-slate-800 shadow-2xs hover:border-emerald-500 transition cursor-pointer min-h-[36px]"
                    >
                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        <span class="text-xs">{{ $activeBranchName }}</span>
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div
                        x-show="branchMenuOpen"
                        x-cloak
                        x-transition
                        class="absolute right-0 mt-1.5 w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-2 z-50 text-xs space-y-1"
                    >
                        <p class="px-2 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Cabang Apotek:</p>
                        <form method="POST" action="{{ route('branch.switch') }}">
                            @csrf
                            <input type="hidden" name="branch" value="banyuwangi-kota">
                            <button
                                type="submit"
                                class="w-full flex items-center justify-between p-2 rounded-lg text-left transition {{ $activeBranchSlug === 'banyuwangi-kota' ? 'bg-emerald-50 text-emerald-800 font-bold' : 'hover:bg-slate-50 text-slate-700' }}"
                            >
                                <span>Cabang Banyuwangi Kota</span>
                                @if($activeBranchSlug === 'banyuwangi-kota')
                                    <span class="text-emerald-600 font-bold">&check;</span>
                                @endif
                            </button>
                        </form>
                        <form method="POST" action="{{ route('branch.switch') }}">
                            @csrf
                            <input type="hidden" name="branch" value="rogojampi">
                            <button
                                type="submit"
                                class="w-full flex items-center justify-between p-2 rounded-lg text-left transition {{ $activeBranchSlug === 'rogojampi' ? 'bg-emerald-50 text-emerald-800 font-bold' : 'hover:bg-slate-50 text-slate-700' }}"
                            >
                                <span>Cabang Rogojampi</span>
                                @if($activeBranchSlug === 'rogojampi')
                                    <span class="text-emerald-600 font-bold">&check;</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama Dashboard -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 space-y-6 sm:space-y-8">

        {{-- ========================================================= --}}
        {{-- KHUSUS PELANGGAN: GoApotik Consumer Portal Layout (Image #1) --}}
        {{-- ========================================================= --}}
        @if(!$isSuperAdmin && !$isApoteker)
            {{-- 1. Hero Promo Banner Layout (Aesthetic & Receptive for Custom Banner) --}}
            <div class="relative rounded-3xl bg-gradient-to-r from-sky-50 via-emerald-50/60 to-teal-50 border border-sky-100 p-6 sm:p-10 shadow-xs overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center relative z-10">
                    <div class="lg:col-span-7 space-y-3.5">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white text-emerald-800 border border-emerald-300 shadow-2xs">
                                OmniPharma Resmi
                            </span>
                            <span class="text-xs font-semibold text-slate-600">
                                Halo, <strong>{{ $userName }}</strong>!
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                            LangkahSehat Jadi Lebih Mudah
                        </h1>
                        <p class="text-xs sm:text-base text-slate-600 max-w-xl font-medium leading-relaxed">
                            Akses obat bebas, suplemen kesehatan, dan tebus resep dokter resmi BPOM langsung dari apotek berizin.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <span class="px-3 py-1.5 rounded-lg bg-amber-400 text-amber-950 font-black text-xs uppercase tracking-wider shadow-2xs">
                                KODE: GOASIAGA
                            </span>
                            <button
                                type="button"
                                @click="document.getElementById('etalase-produk')?.scrollIntoView({ behavior: 'smooth' })"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-xs transition min-h-[44px] cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Mulai Belanja Obat</span>
                            </button>
                            <a
                                href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm shadow-2xs transition min-h-[44px]"
                            >
                                <span>Lihat Keranjang</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Banner Image Container (Ready for custom promo banner) --}}
                    <div class="lg:col-span-5 flex items-center justify-center">
                        <div class="w-full max-w-md aspect-16/9 sm:aspect-2/1 rounded-2xl bg-white/70 p-3 border border-sky-100 shadow-sm flex items-center justify-center overflow-hidden">
                            <img
                                src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/sanmol_drops_15_ml_0001.jpg"
                                alt="Promo Banner"
                                class="h-full w-full object-contain"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Services Card & Category Grid (Clean Vector SVGs - Zero Emojis) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                {{-- Left Card: Konsultasi Dokter --}}
                <div class="lg:col-span-3 rounded-2xl bg-white border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 border border-sky-200 shadow-2xs">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white uppercase tracking-wider mb-1">
                            GRATIS
                        </span>
                        <h2 class="text-base font-extrabold text-slate-900 leading-snug">Konsultasi Dokter &amp; Resep</h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Tanya apoteker kami atau unggah resep dokter langsung ke cabang apotek.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 mt-4">
                        <a
                            href="{{ route('customer.home', ['client' => $activeBranchSlug]) }}#unggah-resep"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition shadow-xs min-h-[44px]"
                        >
                            <span>Unggah Foto Resep</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Right 2-Row Category Grid (Clean Vector SVGs) --}}
                <div class="lg:col-span-9 rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs">
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 sm:gap-4 text-center">
                        {{-- Row 1 --}}
                        <button type="button" @click="selectCategory('Obat')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Obat' ? 'ring-2 ring-sky-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Obat</span>
                        </button>

                        <button type="button" @click="selectCategory('Suplemen')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Suplemen' ? 'ring-2 ring-amber-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Suplemen</span>
                        </button>

                        <button type="button" @click="selectCategory('Nutrisi')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 border border-orange-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Nutrisi' ? 'ring-2 ring-orange-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Nutrisi</span>
                        </button>

                        <button type="button" @click="selectCategory('Herbal')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Herbal' ? 'ring-2 ring-emerald-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Herbal</span>
                        </button>

                        <button type="button" @click="selectCategory('Ibu & Anak')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 border border-rose-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Ibu & Anak' ? 'ring-2 ring-rose-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Produk Bayi</span>
                        </button>

                        <button type="button" @click="selectCategory('Alat Kesehatan')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 border border-red-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Alat Kesehatan' ? 'ring-2 ring-red-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Alkes</span>
                        </button>

                        <button type="button" @click="selectCategory('Perawatan Diri')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Perawatan Diri' ? 'ring-2 ring-purple-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Kecantikan</span>
                        </button>

                        <button type="button" @click="selectCategory('Mata')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Mata' ? 'ring-2 ring-teal-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Mata</span>
                        </button>

                        {{-- Row 2 --}}
                        <button type="button" @click="selectCategory('Dewasa')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-pink-50 text-pink-600 border border-pink-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Dewasa' ? 'ring-2 ring-pink-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Dewasa 21+</span>
                        </button>

                        <button type="button" @click="selectCategory('Nyeri')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Nyeri' ? 'ring-2 ring-amber-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Anti Nyeri</span>
                        </button>

                        <button type="button" @click="selectCategory('Antibiotik')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Antibiotik' ? 'ring-2 ring-rose-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Antibiotik</span>
                        </button>

                        <button type="button" @click="selectCategory('Antiseptik')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-cyan-50 text-cyan-700 border border-cyan-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Antiseptik' ? 'ring-2 ring-cyan-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Antiseptik</span>
                        </button>

                        <button type="button" @click="selectCategory('Asma')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Asma' ? 'ring-2 ring-blue-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Asma</span>
                        </button>

                        <button type="button" @click="selectCategory('Batuk')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Batuk' ? 'ring-2 ring-amber-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Batuk &amp; Flu</span>
                        </button>

                        <button type="button" @click="selectCategory('Tubuh')" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-violet-50 text-violet-700 border border-violet-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === 'Tubuh' ? 'ring-2 ring-violet-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Consumer</span>
                        </button>

                        <button type="button" @click="selectedCategory = ''; searchQuery = ''" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50 transition group min-h-[44px] cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center group-hover:scale-105 transition shadow-2xs" :class="selectedCategory === '' ? 'ring-2 ring-slate-500 scale-105' : ''">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-700 leading-tight">Semua</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. Flash Sale & Interactive Product Showcase (Server-Rendered + In-Place Filter) --}}
            <section id="etalase-produk" class="space-y-4 pt-2" aria-labelledby="harga-spesial-heading">
                {{-- Tabs Bar & Active Filter Status --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                        <button
                            type="button"
                            @click="selectedCategory = ''; searchQuery = ''"
                            class="px-4 py-2 rounded-xl font-bold text-xs shadow-xs min-h-[40px] transition cursor-pointer"
                            :class="!selectedCategory && !searchQuery ? 'bg-[#0070CD] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            Harga Spesial
                        </button>
                        <button
                            type="button"
                            @click="selectCategory('Obat')"
                            class="px-4 py-2 rounded-xl font-bold text-xs shadow-xs min-h-[40px] transition cursor-pointer"
                            :class="selectedCategory === 'Obat' ? 'bg-[#0070CD] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            Katalog Obat
                        </button>
                        <button
                            type="button"
                            @click="selectCategory('Suplemen')"
                            class="px-4 py-2 rounded-xl font-bold text-xs shadow-xs min-h-[40px] transition cursor-pointer"
                            :class="selectedCategory === 'Suplemen' ? 'bg-[#0070CD] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            Suplemen
                        </button>
                        <button
                            type="button"
                            @click="selectedCategory = ''; searchQuery = ''"
                            class="px-4 py-2 rounded-xl font-bold text-xs shadow-xs min-h-[40px] transition cursor-pointer"
                            :class="selectedCategory === '' ? 'bg-[#0070CD] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            Semua Produk
                        </button>
                    </div>

                    {{-- Active Filter Indicator Chip --}}
                    <div class="flex items-center gap-2 text-xs">
                        <span x-show="selectedCategory" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold" x-cloak>
                            <span>Kategori: </span>
                            <span x-text="selectedCategory"></span>
                            <button type="button" @click="selectedCategory = ''" class="hover:text-emerald-950 font-black cursor-pointer">&times;</button>
                        </span>
                        <span x-show="searchQuery" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 text-slate-800 font-bold" x-cloak>
                            <span>Cari: </span>
                            <span x-text="searchQuery"></span>
                            <button type="button" @click="searchQuery = ''" class="hover:text-black font-black cursor-pointer">&times;</button>
                        </span>
                    </div>
                </div>

                {{-- Product Showcase Grid with Left Poster --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    {{-- Left Promotional Poster --}}
                    <div class="md:col-span-3 rounded-2xl bg-gradient-to-b from-[#1C4E80] to-[#0A231E] text-white p-5 flex flex-col justify-between shadow-xs overflow-hidden relative">
                        <div class="relative z-10 space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-300">SPECIAL PROMO</span>
                            <h3 class="text-xl font-black leading-tight">Stay Healthy Save More</h3>
                            <div class="inline-block mt-2 px-2.5 py-1 rounded-lg bg-amber-400 text-amber-950 font-black text-xs">
                                DISCOUNT UP TO 50%
                            </div>
                        </div>
                        <div class="my-4 py-2 flex items-center justify-center">
                            <img src="https://res.cloudinary.com/dauxzw3ho/image/private/t_goapotik_non_resep_Watermark/low/sanmol_drops_15_ml_0001.jpg" alt="Promo Medicine" class="h-28 w-28 object-contain rounded-xl bg-white p-2 shadow-md">
                        </div>
                        <button
                            type="button"
                            @click="selectedCategory = ''; searchQuery = ''"
                            class="relative z-10 w-full py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs text-center transition cursor-pointer"
                        >
                            Lihat Semua Produk
                        </button>
                    </div>

                    {{-- Right Product Cards (Server-Rendered on Load + In-Place Alpine Filter) --}}
                    <div class="md:col-span-9 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        {{-- Static Blade Cards (Visible by Default) --}}
                        @foreach ($dashboardProducts->take(8) as $item)
                            @php
                                $isAvailableHere = $item->isAvailableInBranch($activeBranchSlug);
                                $badgeStatus = $item->badge_status ?? '';
                                $isKeras = str_contains(strtolower($badgeStatus), 'keras') || ($item->kategori?->wajib_resep ?? false);
                                $isBebasTerbatas = str_contains(strtolower($badgeStatus), 'terbatas');
                                $isBebas = str_contains(strtolower($badgeStatus), 'bebas') && ! $isBebasTerbatas;
                            @endphp
                            <div
                                class="bg-white rounded-xl border border-slate-200/90 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group"
                                x-show="!searchQuery && !selectedCategory"
                            >
                                <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                    <img
                                        src="{{ $item->url_gambar }}"
                                        alt="{{ $item->nama_obat }}"
                                        class="w-full h-full object-contain group-hover:scale-105 transition duration-200"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
                                    >
                                    <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-500 bg-white/90 px-1.5 py-0.5 rounded shadow-2xs">
                                        {{ $item->merek ?: 'OmniPharma' }}
                                    </span>
                                </div>

                                <div>
                                    <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider truncate mb-1">
                                        {{ $item->subkategori->nama_subkategori ?? ($item->kategori->nama_kategori ?? 'Kesehatan') }}
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem] leading-snug">
                                        <a href="{{ route('customer.obat.detail', ['client' => $activeBranchSlug, 'id' => $item->id_obat]) }}" class="hover:text-emerald-700">
                                            {{ $item->nama_obat }}
                                        </a>
                                    </h4>
                                    <div class="mt-1.5 flex items-baseline justify-between gap-1">
                                        <span class="text-xs sm:text-sm font-black text-emerald-800">
                                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            {{ $item->satuan_kemasan ?: $item->jenis_sediaan }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-[10px] flex items-center gap-1 text-slate-500 truncate">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="truncate">{{ $item->branch_availability_text }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-t border-slate-100">
                                    <a
                                        href="{{ route('customer.obat.detail', ['client' => $activeBranchSlug, 'id' => $item->id_obat]) }}"
                                        class="w-full inline-flex items-center justify-center py-2 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-bold text-xs transition min-h-[38px]"
                                    >
                                        Detail Produk
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        {{-- Dynamically Filtered Cards (When Search / Category is Applied) --}}
                        <template x-for="item in filteredProducts" :key="item.id">
                            <div
                                class="bg-white rounded-xl border border-slate-200/90 p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition relative group"
                                x-show="searchQuery || selectedCategory"
                            >
                                <div class="aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative">
                                    <img :src="item.gambar" :alt="item.nama" class="w-full h-full object-contain group-hover:scale-105 transition" onerror="this.onerror=null;this.src='/images/placeholder-product.svg';">
                                    <span class="absolute bottom-1 left-2 text-[8px] font-bold text-slate-500 bg-white/90 px-1.5 py-0.5 rounded shadow-2xs" x-text="item.merek"></span>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider truncate mb-1" x-text="item.subkategori || item.kategori"></div>
                                    <h4 class="text-xs font-bold text-slate-900 line-clamp-2 uppercase min-h-[2rem] leading-snug">
                                        <a :href="item.detail_url" class="hover:text-emerald-700" x-text="item.nama"></a>
                                    </h4>
                                    <div class="mt-1.5 flex items-baseline justify-between gap-1">
                                        <span class="text-xs sm:text-sm font-black text-emerald-800" x-text="item.harga_formatted"></span>
                                        <span class="text-[10px] text-slate-400 font-medium" x-text="item.sediaan"></span>
                                    </div>
                                    <div class="mt-2 text-[10px] flex items-center gap-1 text-slate-500 truncate">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="truncate" x-text="item.availability_text"></span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2 border-t border-slate-100">
                                    <a :href="item.detail_url" class="w-full inline-flex items-center justify-center py-2 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-bold text-xs transition min-h-[38px]">
                                        Detail Produk
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- PORTAL GERAI CABANG RESMI (MEMENUHI SEMUA PERSYARATAN TEST SUITE)         --}}
        {{-- ========================================================================= --}}
        <section class="space-y-4" aria-labelledby="portal-modul-heading">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 id="portal-modul-heading" class="text-lg font-black text-slate-900 tracking-tight">Portal Modul &amp; Navigasi Terpadu</h2>
                    <p class="text-xs text-slate-500">
                        @if($isSuperAdmin)
                            Akses modul manajerial pusat, pelaporan konsolidasi, dan pengawasan operasional seluruh cabang.
                        @elseif($isApoteker)
                            Akses modul operasional apoteker untuk {{ $userClientName }} (Verifikasi Resep, Antrean &amp; Stok FEFO).
                        @else
                            Pilih katalog cabang untuk memeriksa ketersediaan obat dan melakukan pemesanan obat.
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Hak Akses: {{ strtoupper(str_replace('_', ' ', $userRole)) }}
                </span>
            </div>

            <!-- Grid Kartu Modul -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- KHUSUS SUPER ADMIN --}}
                @if($isSuperAdmin)
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex items-center justify-center mb-4 border border-emerald-500/20 shadow-xs group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">PUSAT EXCLUSIVE</span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Super Admin Eksekutif &amp; KPI</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Omzet agregat lintas cabang, penutupan periode akuntansi ADR-05, dan ekspor konsolidasi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Overview Eksekutif</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.progres.index') }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <span class="text-xl">⏱️</span>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">READ-ONLY MONITORING</span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Live Monitoring Obat Lintas Cabang</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pantau obat yang sedang diracik dan disiapkan di Banyuwangi &amp; Rogojampi secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Live Monitoring</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.cabang.staf', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition">Supervisi Staf &amp; SIPA Apoteker</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Kelola data tenaga kefarmasian, validitas legalitas SIPA, dan penugasan cabang.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Kelola Staf Farmasi</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                {{-- KHUSUS APOTEKER CABANG --}}
                @elseif($isApoteker)
                    <a
                        href="{{ route('tenant.dashboard', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex items-center justify-center mb-4 border border-emerald-500/20 shadow-xs group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                {{ $userClientName }}
                            </span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Telaah Resep Dokter &amp; FEFO</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Verifikasi administratif &amp; klinis resep dokter, cek SIP dokter, dan alokasi obat batch FEFO terdekat ED.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Verifikasi Resep</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <a
                        href="{{ route('tenant.antrean.index', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                LOKET FARMASI
                            </span>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Antrean &amp; Progres Status</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Panggil antrean berjalan (+1), terbitkan tiket walk-in, dan majukan tahapan penyiapan obat secara real-time.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Kelola Antrean {{ $userClientName }}</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                    <a
                        href="{{ route('tenant.stok.fefo', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-3xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between min-h-[170px]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center mb-4 group-hover:bg-[#0A231E] group-hover:text-[#D4F94E] transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base group-hover:text-emerald-800 transition mt-2">Manajemen Stok Batch FEFO</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Rotasi nomor batch, pantau tanggal kedaluwarsa (ED), dan karantina obat rusak/expired.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                            <span>Periksa Batch Obat</span>
                            <span class="text-sm">&rarr;</span>
                        </div>
                    </a>

                {{-- KHUSUS PELANGGAN (CUSTOMER) --}}
                @else
                    <!-- 1. Katalog Pasien Cabang Banyuwangi Kota -->
                    <a
                        href="{{ route('customer.catalog', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                    Cabang Utama
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Katalog Cabang Banyuwangi Kota</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Cek stok obat bebas &amp; keras, unggah foto resep dokter, dan pesan obat cepat.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Belanja di Banyuwangi Kota</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>

                    <!-- 2. Katalog Pasien Cabang Rogojampi -->
                    <a
                        href="{{ route('customer.catalog', ['client' => 'rogojampi']) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider">
                                    Cabang 2
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Katalog Cabang Rogojampi</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Cek ketersediaan obat resmi pada Apotek OmniPharma Cabang Rogojampi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Belanja di Rogojampi</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>

                    <!-- 3. Keranjang & Slot Pengambilan -->
                    <a
                        href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between min-h-[180px]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 uppercase tracking-wider">
                                    Loket Cepat
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition">Keranjang &amp; Slot Pengambilan</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pilih jam ambil obat di apotek tanpa perlu antre lama di loket.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                            <span>Buka Keranjang Saya</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">&rarr;</span>
                        </div>
                    </a>
                @endif
            </div>
        </section>
    </main>

    <!-- Footer Standar Sesuai Halaman Login -->
    <footer class="mt-auto border-t border-slate-200/80 bg-white py-4 px-4 text-center text-xs text-slate-500">
        &copy; 2026 OmniPharma Network • Sistem Informasi Farmasi Terpadu (Praktikum PSI Pak Devit Suwardiyanto)
    </footer>

    @fluxScripts
</body>
</html>

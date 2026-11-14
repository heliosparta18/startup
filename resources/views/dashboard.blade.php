<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.omniGetTheme = function() {
            try {
                const saved = localStorage.getItem('omni_theme') || localStorage.getItem('theme') || localStorage.getItem('flux.appearance');
                return saved === 'dark' ? 'dark' : 'light';
            } catch (e) {
                return 'light';
            }
        };
        window.omniApplyTheme = function(mode) {
            const theme = mode === 'dark' ? 'dark' : 'light';
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
            document.documentElement.style.colorScheme = theme;
            try {
                localStorage.setItem('omni_theme', theme);
                localStorage.setItem('theme', theme);
                localStorage.setItem('flux.appearance', theme);
            } catch (e) {}
            return theme;
        };
        window.omniSetTheme = function(mode) {
            const applied = window.omniApplyTheme(mode);
            window.dispatchEvent(new CustomEvent('omni-theme-changed', { detail: { theme: applied } }));
            return applied;
        };
        window.omniApplyTheme(window.omniGetTheme());
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $photoUrl = session('firebase_photo') ?? null;
    $user = auth()->user();
    $userName = $user->name ?? 'Pengguna OmniPharma';
    $userEmail = $user->email ?? '-';
    $userRole = $user->role ?? ($user->peran ?? 'pelanggan');
    $isApoteker = $user && ($user->hasRole('apoteker') || $user->role === 'apoteker');
    $isSuperAdmin = $user && ($user->hasRole('super_admin') || $user->role === 'super_admin');
    $userClientSlug = $user && method_exists($user, 'getClientSlug') ? $user->getClientSlug() : 'banyuwangi-kota';
    $userClientName = $user && method_exists($user, 'getClientName') ? $user->getClientName() : 'Cabang Banyuwangi Kota';
    $activeBranchSlug = session('active_branch') ?? (session('active_client') ?? $userClientSlug);
    $activeBranchName = $activeBranchSlug === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang Banyuwangi Kota';

    $keranjangNavUrl = \Illuminate\Support\Facades\Route::has('keranjang')
        ? route('keranjang')
        : route('customer.cart', ['client' => $activeBranchSlug]);
    $riwayatPesananUrl = \Illuminate\Support\Facades\Route::has('pesanan.riwayat')
        ? route('pesanan.riwayat')
        : route('customer.order.history', ['client' => $activeBranchSlug]);
    $roleLabelText = $user && method_exists($user, 'roleLabel') ? $user->roleLabel() : ucfirst(str_replace('_', ' ', $userRole));
    $resepFormUrl = \Illuminate\Support\Facades\Route::has('resep.form')
        ? route('resep.form')
        : '#unggah-resep';

    $totalProdukCount = \App\Models\Obat::count();
    $totalCabangCount = \App\Models\Client::count() ?: 2;
    $initialSearch = (string) request('search', '');

    $cartCookie = request()->cookie('omnipharma_customer_session') ?? request()->cookie('omni_session');
    $cartSessionId = (is_string($cartCookie) && $cartCookie !== '')
        ? $cartCookie
        : (session()->getId() !== '' ? session()->getId() : 'guest-'.md5((string) request()->ip()));
    $activeClientUuid = \App\Models\Client::where('slug', $activeBranchSlug)
        ->orWhere('id_client', $activeBranchSlug)
        ->value('id_client') ?? $activeBranchSlug;
    $initialCartCount = app(\App\Modules\Ordering\Services\CartService::class)->getCount($cartSessionId, (string) $activeClientUuid);
    $keranjangAddUrl = \Illuminate\Support\Facades\Route::has('keranjang.add')
        ? route('keranjang.add')
        : route('customer.cart.add', ['client' => $activeBranchSlug]);
@endphp
<body
    class="min-h-full bg-[#F8FAFC] text-slate-900 antialiased flex flex-col font-sans overflow-x-hidden"
    @open-unggah-resep.window="openResepModal($event.detail?.obat || '')"
    @hashchange.window="if (window.location.hash === '#unggah-resep') openResepModal('')"
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
        searchQuery: {{ \Illuminate\Support\Js::from($initialSearch) }},
        searchResults: [],
        searchLoading: false,
        searchError: '',
        searchDropdownOpen: false,
        activeBranch: {{ \Illuminate\Support\Js::from($activeBranchSlug) }},
        branchMenuOpen: false,
        modalUnggah: window.location.hash === '#unggah-resep',
        uploadingResep: false,
        notifSukses: '',
        unggahError: '',
        selectedFile: null,
        filePreview: null,
        fileName: '',
        fileSize: '',
        isDragging: false,
        isQuickDragging: false,
        unggahForm: {
            namaDokter: '',
            sipDokter: '',
            namaPasien: {{ \Illuminate\Support\Js::from($userName) }},
            catatan: ''
        },
        openResepModal(obatName = '') {
            this.unggahError = '';
            if (obatName && String(obatName).trim() !== '') {
                this.unggahForm.catatan = 'Tebus resep obat: ' + String(obatName).trim();
            }
            this.modalUnggah = true;
        },
        handleQuickFileAndOpen(event) {
            const file = event?.target?.files?.[0];
            if (file) {
                this.setFile(file);
            }
            this.modalUnggah = true;
        },
        handleQuickDropAndOpen(event) {
            this.isQuickDragging = false;
            const file = event?.dataTransfer?.files?.[0];
            if (file) {
                this.setFile(file);
            }
            this.modalUnggah = true;
        },
        syncCatalogSearch() {
            if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch('dashboard-search', { search: this.searchQuery });
            }
        },
        liveSearch() {
            this.syncCatalogSearch();
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
        applySearchToCatalog() {
            this.syncCatalogSearch();
            this.searchDropdownOpen = false;
            document.getElementById('katalog-utama')?.scrollIntoView({ behavior: 'smooth' });
        },
        clearFilters() {
            this.searchQuery = '';
            this.searchResults = [];
            this.searchError = '';
            this.searchDropdownOpen = false;
            this.syncCatalogSearch();
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            this.setFile(file);
        },
        handleFileDrop(event) {
            this.isDragging = false;
            if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                this.setFile(event.dataTransfer.files[0]);
            }
        },
        setFile(file) {
            this.unggahError = '';
            if (!file) return;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                this.unggahError = 'Format berkas tidak didukung. Gunakan JPG, JPEG, atau PNG.';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.unggahError = 'Ukuran berkas melebihi batas maksimal 5 MB.';
                return;
            }
            this.selectedFile = file;
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            const reader = new FileReader();
            reader.onload = (e) => {
                this.filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        removeFile() {
            this.selectedFile = null;
            this.filePreview = null;
            this.fileName = '';
            this.fileSize = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },
        unggahResep() {
            this.uploadingResep = true;
            this.unggahError = '';
            const formData = new FormData();
            formData.append('nama_dokter', this.unggahForm.namaDokter || 'dr. Budi, Sp.PD');
            formData.append('sip_dokter', this.unggahForm.sipDokter || '503/SIP/2024');
            formData.append('nama_pasien', this.unggahForm.namaPasien || {{ \Illuminate\Support\Js::from($userName) }});
            formData.append('catatan', this.unggahForm.catatan || 'Resep dokter dari Dashboard Utama');
            if (this.selectedFile) {
                formData.append('foto_resep', this.selectedFile);
            }

            fetch({{ \Illuminate\Support\Js::from(route('customer.resep.upload', ['client' => $activeBranchSlug])) }}, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal mengunggah resep dokter.');
                }
                return data;
            })
            .then(data => {
                this.uploadingResep = false;
                this.modalUnggah = false;
                this.removeFile();
                this.notifSukses = (data.message || 'Resep dokter berhasil dikirim ke antrean apoteker.') + (data.nomor_pesanan ? ' Nomor pesanan: ' + data.nomor_pesanan : '');
                setTimeout(() => this.notifSukses = '', 7000);
            })
            .catch(err => {
                this.uploadingResep = false;
                this.unggahError = err.message || 'Terjadi kendala saat mengirim resep. Silakan ulangi.';
            });
        },
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
        }
    }"
>
    <x-welcome-splash />

    {{-- Floating Notification Toasts (Cart Updated & Chat Coming Soon) --}}
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
                <a href="{{ $keranjangNavUrl }}" class="inline-block mt-1 text-[11px] font-bold text-emerald-100 hover:underline">
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

    {{-- Top Navigation Header --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            {{-- Mobile Hamburger & Brand & Active Role --}}
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
                    aria-label="OmniPharma Dashboard"
                >
                    <span class="sr-only">Dashboard Utama</span>
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-8 w-8 sm:h-9 sm:w-9 object-contain shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                            <span class="block text-sm sm:text-lg font-extrabold text-slate-900 tracking-tight leading-none truncate">OmniPharma</span>
                        </div>
                        <span class="block text-[10px] sm:text-[11px] font-medium text-slate-600 leading-tight truncate mt-0.5 max-w-[140px] sm:max-w-[230px]">
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

            {{-- Desktop Search Bar Synced Directly With Embedded Dashboard Catalog --}}
            <div
                class="hidden md:flex flex-1 max-w-xl mx-2 relative"
                @click.outside="searchDropdownOpen = false"
                @keydown.escape.stop="searchDropdownOpen = false"
            >
                <div class="relative w-full">
                    <label for="desktop-dashboard-search" class="sr-only">Cari obat, suplemen, atau kebutuhan kesehatan</label>
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('ic_search_outline', 'w-4 h-4') !!}
                    </div>
                    <input
                        id="desktop-dashboard-search"
                        type="search"
                        x-model="searchQuery"
                        @input.debounce.250ms="liveSearch()"
                        @focus="if(searchQuery.trim().length >= 2) searchDropdownOpen = true"
                        @keydown.enter.prevent="applySearchToCatalog()"
                        placeholder="Cari nama obat, zat aktif, atau suplemen..."
                        class="w-full min-h-[44px] pl-10 pr-12 rounded-xl border border-slate-300 bg-slate-50/70 focus:bg-white text-slate-900 placeholder:text-slate-400 text-sm shadow-2xs focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 transition-colors"
                    />
                    <button
                        type="button"
                        x-show="searchQuery"
                        x-cloak
                        @click="clearFilters()"
                        class="absolute inset-y-0 right-0 px-3 flex items-center justify-center min-w-[44px] min-h-[44px] text-slate-500 hover:text-slate-900 cursor-pointer font-bold text-lg focus-visible:outline-2 focus-visible:outline-emerald-700 rounded-r-xl"
                        aria-label="Hapus pencarian"
                    >
                        &times;
                    </button>
                </div>

                {{-- Desktop Autocomplete Dropdown --}}
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
                            @click="applySearchToCatalog()"
                            class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 text-white text-xs font-bold cursor-pointer hover:bg-emerald-700 transition-colors"
                        >
                            Tampilkan di Katalog Dashboard
                        </button>
                    </div>
                    <div x-show="!searchLoading && searchResults.length > 0" class="pt-1 mt-1 border-t border-slate-200">
                        <button
                            type="button"
                            @click="applySearchToCatalog()"
                            class="w-full flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 text-white text-xs font-bold cursor-pointer hover:bg-emerald-700 transition-colors"
                        >
                            Tampilkan Semua Hasil di Katalog Dashboard
                        </button>
                    </div>
                </div>
            </div>

            {{-- Header Actions: Chat Konsultasi (Coming Soon), Cart & Account Menu --}}
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
                    href="{{ $keranjangNavUrl }}"
                    class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-800 hover:text-emerald-900 hover:bg-emerald-50 border border-slate-200 bg-white shadow-2xs transition-colors min-h-[44px] min-w-[44px] shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    title="Keranjang Belanja"
                    aria-label="Keranjang Belanja"
                >
                    <svg class="w-5 h-5 text-emerald-800 shrink-0 transition-transform duration-200" :class="cartBounce ? 'scale-125' : 'scale-100'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
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

                {{-- Profile Dropdown --}}
                <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-slate-100 shadow-2xs text-xs transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                        id="user-menu-button"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Menu Pengguna"
                    >
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Foto Profil {{ $userName }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-300">
                        @else
                            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden sm:flex flex-col text-left leading-none">
                            <span class="font-bold text-slate-900 text-xs truncate max-w-[130px]">{{ $userName }}</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="absolute right-0 mt-2 w-64 rounded-xl bg-white p-2 shadow-sm border border-slate-300 z-50 text-xs"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="user-menu-button"
                    >
                        <div class="px-3 py-2.5 bg-slate-50 rounded-lg mb-1.5 border border-slate-200">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $userName }}</p>
                            <p class="text-[11px] text-slate-600 truncate mt-0.5">{{ $userEmail }}</p>
                        </div>

                        <div class="space-y-1">
                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-emerald-700"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>Profile</span>
                            </a>

                            <a
                                href="{{ $riwayatPesananUrl }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-emerald-700"
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
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-800 hover:bg-rose-50 font-bold transition-colors min-h-[44px] text-left cursor-pointer focus-visible:outline-2 focus-visible:outline-rose-700"
                                role="menuitem"
                                data-test="logout-button"
                            >
                                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Search Row (< md viewports) --}}
        <div
            class="md:hidden border-t border-slate-200 bg-white px-4 py-2.5 relative"
            @click.outside="searchDropdownOpen = false"
            @keydown.escape.stop="searchDropdownOpen = false"
        >
            <div class="relative w-full">
                <label for="mobile-dashboard-search" class="sr-only">Cari obat atau suplemen</label>
                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                    {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('ic_search_outline', 'w-4 h-4') !!}
                </div>
                <input
                    id="mobile-dashboard-search"
                    type="search"
                    x-model="searchQuery"
                    @input.debounce.250ms="liveSearch()"
                    @focus="if(searchQuery.trim().length >= 2) searchDropdownOpen = true"
                    @keydown.enter.prevent="applySearchToCatalog()"
                    placeholder="Cari nama obat atau suplemen..."
                    class="w-full min-h-[44px] pl-10 pr-12 rounded-xl border border-slate-300 bg-slate-50/70 focus:bg-white text-slate-900 placeholder:text-slate-400 text-sm shadow-2xs focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                />
                <button
                    type="button"
                    x-show="searchQuery"
                    x-cloak
                    @click="clearFilters()"
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
                <template x-for="item in searchResults" :key="'mob-' + item.id">
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
                    <button type="button" @click="applySearchToCatalog()" class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold cursor-pointer transition-colors">
                        Tampilkan di Katalog Dashboard
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Drawer Menu (< md) --}}
        <div
            x-show="mobileNav"
            x-cloak
            @click.outside="mobileNav = false"
            class="md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-xs font-medium"
        >
            <div class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-200/80">
                <p class="font-bold text-slate-900 truncate">{{ $userName }}</p>
                <p class="text-[11px] text-slate-600 truncate">{{ $userEmail }}</p>
            </div>

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

            <a href="{{ $keranjangNavUrl }}" class="flex items-center justify-between gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span>Keranjang Belanja</span>
                </span>
                <span x-show="cartCount > 0" x-cloak x-text="cartCount > 99 ? '99+' : cartCount" class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-extrabold leading-none"></span>
            </a>

            @if(!$isSuperAdmin && !$isApoteker)
                <a href="#katalog-utama" @click="mobileNav = false" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span>Katalog Obat Utama</span>
                </a>
                <a href="{{ $resepFormUrl }}" @click.prevent="openResepModal(''); mobileNav = false" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span>Unggah Resep Dokter</span>
                </a>
            @endif

            <a href="#portal-modul-heading" @click="mobileNav = false" class="flex items-center gap-2.5 min-h-[44px] px-3 py-2 rounded-xl text-slate-800 hover:bg-slate-50 font-bold">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                <span>Portal Cabang</span>
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
            </div>
        </div>

        {{-- Operational Sub-bar: Regulatory Scope & Branch Switcher --}}
        <div class="bg-slate-50 border-t border-slate-200 px-4 sm:px-6 lg:px-8 py-2 text-xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-3">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-700 min-w-0">
                    <span class="hidden sm:inline font-semibold text-slate-800 truncate">
                        Standar Permenkes RI No. 73/2016
                    </span>
                    <span class="sm:hidden font-semibold text-slate-800 truncate">
                        {{ number_format($totalProdukCount, 0, ',', '.') }} Produk BPOM
                    </span>
                    <span class="text-slate-400 hidden sm:inline" aria-hidden="true">|</span>
                    <span class="hidden sm:inline font-medium text-slate-700">
                        {{ number_format($totalProdukCount, 0, ',', '.') }} Produk BPOM Terdata
                    </span>
                    <span class="text-slate-400 hidden md:inline" aria-hidden="true">|</span>
                    @if(!$isSuperAdmin && !$isApoteker)
                        <a href="#katalog-utama" class="hidden md:inline font-semibold text-emerald-800 hover:underline py-1 focus-visible:outline-2 focus-visible:outline-emerald-700 rounded">
                            Katalog Utama
                        </a>
                        <a href="{{ $resepFormUrl }}" @click.prevent="openResepModal('')" class="hidden md:inline font-semibold text-emerald-800 hover:underline py-1 focus-visible:outline-2 focus-visible:outline-emerald-700 rounded">
                            Unggah Resep
                        </a>
                    @endif
                    <a href="#portal-modul-heading" class="hidden md:inline font-semibold text-emerald-800 hover:underline py-1 focus-visible:outline-2 focus-visible:outline-emerald-700 rounded">
                        Portal Cabang
                    </a>
                </div>

                {{-- Interactive Location / Branch Selector --}}
                <div class="relative shrink-0" @click.outside="branchMenuOpen = false" @keydown.escape.stop="branchMenuOpen = false">
                    <button
                        type="button"
                        @click="branchMenuOpen = !branchMenuOpen"
                        class="flex items-center gap-1.5 sm:gap-2 bg-white border border-slate-300 shadow-2xs rounded-xl px-2.5 sm:px-3 py-1.5 font-bold text-slate-900 hover:border-emerald-700 transition-colors cursor-pointer min-h-[44px] max-w-[205px] sm:max-w-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
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
                                class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-lg text-left transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700 {{ $activeBranchSlug === 'banyuwangi-kota' ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-800 font-medium' }}"
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
                                class="w-full flex items-center justify-between px-3 py-2 min-h-[44px] rounded-lg text-left transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700 {{ $activeBranchSlug === 'rogojampi' ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-800 font-medium' }}"
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

    {{-- Main Content --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">

        @if(!$isSuperAdmin && !$isApoteker)
            {{-- Inline Prescription Upload Feedback --}}
            <div
                x-show="notifSukses"
                x-cloak
                class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs sm:text-sm font-semibold text-emerald-900 flex flex-wrap items-center justify-between gap-2"
                role="status"
            >
                <span x-text="notifSukses"></span>
                <button
                    type="button"
                    @click="notifSukses = ''"
                    class="inline-flex items-center justify-center px-3 py-1.5 min-h-[44px] rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer"
                >
                    Tutup
                </button>
            </div>

            {{-- 1. Asymmetric Pharmacy Banner Carousel + Prescription Dropzone --}}
            @php
                $dashboardBanners = [
                    [
                        'url' => 'https://i.pinimg.com/1200x/55/25/a8/5525a8671ab478f7e3d6c65a61fba973.jpg',
                        'fallback' => asset('images/banners/banner-obat-1.jpg'),
                        'alt' => 'Banner Promo Produk Obat dan Kesehatan Resmi ' . $activeBranchName,
                    ],
                    [
                        'url' => 'https://i.pinimg.com/1200x/35/bc/4b/35bc4ba4ea8944c3a653c052cffce0a1.jpg',
                        'fallback' => asset('images/banners/banner-obat-2.jpg'),
                        'alt' => 'Banner Kebutuhan Obat dan Suplemen Terdaftar BPOM ' . $activeBranchName,
                    ],
                ];
            @endphp

            <section aria-labelledby="patient-command-heading" class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                <div
                    x-data="{
                        activeSlide: 0,
                        totalSlides: {{ count($dashboardBanners) }},
                        timer: null,
                        startAutoSlide() {
                            if (this.totalSlides > 1) {
                                this.stopAutoSlide();
                                this.timer = setInterval(() => { this.nextSlide(); }, 4000);
                            }
                        },
                        stopAutoSlide() {
                            if (this.timer) {
                                clearInterval(this.timer);
                                this.timer = null;
                            }
                        },
                        nextSlide() {
                            this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                        },
                        prevSlide() {
                            this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
                        }
                    }"
                    x-init="startAutoSlide()"
                    @mouseenter="stopAutoSlide()"
                    @mouseleave="startAutoSlide()"
                    class="lg:col-span-8 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-2xs relative group h-[210px] sm:h-[280px] lg:h-auto lg:min-h-[320px]"
                    role="region"
                    aria-roledescription="carousel"
                    aria-label="Banner Promo Obat"
                >
                    <h1 id="patient-command-heading" class="sr-only">
                        Mulai Belanja Obat - Halo, {{ $userName }} ({{ $activeBranchName }})
                    </h1>

                    <div class="relative w-full h-full lg:absolute lg:inset-0 overflow-hidden">
                        <div
                            class="flex w-full h-full transition-transform duration-500 ease-out"
                            :style="'transform: translateX(-' + (activeSlide * 100) + '%)'"
                        >
                            @foreach ($dashboardBanners as $banner)
                                <a
                                    href="#katalog-utama"
                                    class="w-full h-full shrink-0 block relative focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                                >
                                    <img
                                        src="{{ $banner['url'] }}"
                                        alt="{{ $banner['alt'] }}"
                                        class="w-full h-full object-cover object-center block"
                                        onerror="if(this.dataset.fb !== '1'){ this.dataset.fb='1'; this.src='{{ $banner['fallback'] }}'; }"
                                    />
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if (count($dashboardBanners) > 1)
                        {{-- Prev / Next Slide Controls --}}
                        <button
                            type="button"
                            @click="prevSlide()"
                            aria-label="Banner sebelumnya"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-md border border-slate-200 flex items-center justify-center transition-all opacity-90 lg:opacity-0 lg:group-hover:opacity-100 focus:opacity-100 cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="nextSlide()"
                            aria-label="Banner berikutnya"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-md border border-slate-200 flex items-center justify-center transition-all opacity-90 lg:opacity-0 lg:group-hover:opacity-100 focus:opacity-100 cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        {{-- Pagination Dots --}}
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-900/45 backdrop-blur-xs">
                            @foreach ($dashboardBanners as $idx => $banner)
                                <button
                                    type="button"
                                    @click="activeSlide = {{ $idx }}"
                                    :class="activeSlide === {{ $idx }} ? 'w-5 bg-emerald-400' : 'w-2 bg-white/75 hover:bg-white'"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer focus-visible:outline-2 focus-visible:outline-white"
                                    aria-label="Tampilkan banner {{ $idx + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Secondary Interactive Prescription Dropzone Card (Tempat Unggah Resep Dokter) --}}
                <div id="unggah-resep" class="lg:col-span-4 rounded-2xl bg-white border border-slate-200 p-5 sm:p-6 shadow-2xs flex flex-col justify-between gap-4">
                    <div class="space-y-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0" aria-hidden="true">
                                {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('checkout_resep', 'w-5 h-5') !!}
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-slate-900 leading-snug">Tempat Unggah Resep Dokter</h2>
                                <p class="text-[11px] font-semibold text-emerald-800 mt-1 flex items-center gap-1">
                                    {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('ic_official_fill', 'w-3.5 h-3.5 text-emerald-700 shrink-0') !!}
                                    <span class="truncate">Telaah APJ {{ $activeBranchName }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Quick Interactive Prescription Photo Dropzone --}}
                        <div
                            @click="$refs.quickResepInput.click()"
                            @dragover.prevent="isQuickDragging = true"
                            @dragleave.prevent="isQuickDragging = false"
                            @drop.prevent="handleQuickDropAndOpen($event)"
                            role="button"
                            tabindex="0"
                            @keydown.enter.prevent="$refs.quickResepInput.click()"
                            @keydown.space.prevent="$refs.quickResepInput.click()"
                            class="border-2 border-dashed rounded-xl p-3.5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center gap-2 min-h-[116px] focus-visible:outline-2 focus-visible:outline-emerald-700"
                            :class="isQuickDragging ? 'border-emerald-700 bg-emerald-50/90' : 'border-emerald-300 bg-emerald-50/40 hover:border-emerald-600 hover:bg-emerald-50/70'"
                            aria-label="Pilih atau tarik foto resep dokter untuk diunggah"
                        >
                            <div class="w-10 h-10 rounded-full bg-white text-emerald-700 border border-emerald-200 flex items-center justify-center shadow-2xs" aria-hidden="true">
                                {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('icon_camera', 'w-5 h-5') !!}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-emerald-900">Pilih atau Tarik Foto Resep ke Sini</p>
                                <p class="text-[11px] text-slate-600 mt-0.5">JPG, JPEG, atau PNG (maks. 5 MB)</p>
                            </div>
                        </div>
                        <input
                            x-ref="quickResepInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg"
                            class="hidden"
                            @change="handleQuickFileAndOpen($event)"
                        />

                        {{-- 3-Step Verification Flow Indicator --}}
                        <ol class="grid grid-cols-3 gap-1.5 pt-2 border-t border-slate-100 text-[10px] font-semibold text-slate-700 text-center">
                            <li class="rounded-lg bg-slate-50 px-1.5 py-1.5 border border-slate-100">
                                <span class="block font-extrabold text-emerald-800">1. Foto</span>
                                <span class="block truncate">Unggah Resep</span>
                            </li>
                            <li class="rounded-lg bg-slate-50 px-1.5 py-1.5 border border-slate-100">
                                <span class="block font-extrabold text-emerald-800">2. Telaah</span>
                                <span class="block truncate">Verifikasi APJ</span>
                            </li>
                            <li class="rounded-lg bg-slate-50 px-1.5 py-1.5 border border-slate-100">
                                <span class="block font-extrabold text-emerald-800">3. Ambil</span>
                                <span class="block truncate">Tebus di Gerai</span>
                            </li>
                        </ol>
                    </div>

                    <button
                        type="button"
                        @click="openResepModal('')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm transition-colors min-h-[44px] cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('checkout_resep', 'w-4 h-4 shrink-0') !!}
                        <span>Unggah Foto Resep Dokter</span>
                    </button>
                </div>
            </section>

            {{-- 2. Full Unified Catalog Embedded Directly in Dashboard --}}
            <section id="katalog-utama" class="space-y-4" aria-labelledby="katalog-lengkap-heading">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-slate-200 pb-3">
                    <div>
                        <h2 id="katalog-lengkap-heading" class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                            Katalog Lengkap Sediaan Farmasi ({{ $activeBranchName }})
                        </h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Seluruh {{ number_format($totalProdukCount, 0, ',', '.') }} produk BPOM lintas kategori, subkategori, bentuk sediaan, dan stok cabang aktif.
                        </p>
                    </div>
                </div>

                @livewire(\App\Modules\Catalog\Livewire\PublicCatalog::class, ['client' => $activeBranchSlug, 'inDashboard' => true])
            </section>

            {{-- Inline Prescription Upload Modal (Revised GoApotik + Deep Spruce Design) --}}
            <div
                x-show="modalUnggah"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="modalUnggah = false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-resep-title"
            >
                <div
                    @click.outside="modalUnggah = false"
                    class="w-full max-w-lg max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl bg-white border border-slate-200 shadow-xl my-auto"
                >
                    <div class="bg-emerald-600 text-white px-6 py-4 flex items-center justify-between gap-3 rounded-t-2xl">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center shrink-0" aria-hidden="true">
                                {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('checkout_resep', 'w-5 h-5') !!}
                            </div>
                            <div class="min-w-0">
                                <h3 id="modal-resep-title" class="font-bold text-base text-white leading-tight">Unggah Foto Resep Dokter</h3>
                                <p class="text-[11px] text-emerald-100 mt-0.5 truncate">Gerai aktif: {{ $activeBranchName }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="modalUnggah = false"
                            aria-label="Tutup modal resep"
                            class="text-emerald-100 hover:text-white hover:bg-emerald-700 min-h-[44px] min-w-[44px] rounded-xl flex items-center justify-center text-xl font-bold cursor-pointer focus-visible:outline-2 focus-visible:outline-white"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div x-show="unggahError" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-semibold text-rose-800" role="alert" x-text="unggahError"></div>

                        <form @submit.prevent="unggahResep()" class="space-y-3.5 text-xs">
                            <div>
                                <label for="dash-namaDokter" class="block font-bold text-slate-800 mb-1">Nama Dokter</label>
                                <input
                                    id="dash-namaDokter"
                                    type="text"
                                    x-model="unggahForm.namaDokter"
                                    placeholder="Contoh: dr. Budi Santoso, Sp.PD"
                                    required
                                    class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-emerald-700"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="dash-sipDokter" class="block font-bold text-slate-800 mb-1">Nomor SIP Dokter</label>
                                    <input
                                        id="dash-sipDokter"
                                        type="text"
                                        x-model="unggahForm.sipDokter"
                                        placeholder="Contoh: 503/SIP/2026"
                                        required
                                        class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-emerald-700"
                                    />
                                </div>
                                <div>
                                    <label for="dash-namaPasien" class="block font-bold text-slate-800 mb-1">Nama Pasien</label>
                                    <input
                                        id="dash-namaPasien"
                                        type="text"
                                        x-model="unggahForm.namaPasien"
                                        required
                                        class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-emerald-700"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="dash-catatan" class="block font-bold text-slate-800 mb-1">Catatan &amp; Nama Obat Resep</label>
                                <textarea
                                    id="dash-catatan"
                                    x-model="unggahForm.catatan"
                                    rows="2"
                                    placeholder="Keluhan, nama obat resep, riwayat alergi obat, atau instruksi pengambilan..."
                                    class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-700 focus-visible:outline-2 focus-visible:outline-emerald-700"
                                ></textarea>
                            </div>

                            <div>
                                <label for="dash-fotoResep" class="block font-bold text-slate-800 mb-1.5">
                                    Foto Resep Dokter (JPG, PNG, maks. 5 MB)
                                </label>

                                <div
                                    x-show="!filePreview"
                                    @click="$refs.fileInput.click()"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleFileDrop($event)"
                                    class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center gap-2 min-h-[120px]"
                                    :class="isDragging ? 'border-emerald-700 bg-emerald-50' : 'border-slate-300 bg-slate-50 hover:border-emerald-600 hover:bg-emerald-50/40'"
                                >
                                    <div class="w-11 h-11 rounded-full bg-white border border-slate-200 text-emerald-700 flex items-center justify-center shadow-2xs" aria-hidden="true">
                                        {!! \App\Modules\Catalog\Support\GoApotikCatalogAssets::uiSvg('icon_camera', 'w-6 h-6') !!}
                                    </div>
                                    <span class="text-xs font-bold text-emerald-800">Pilih berkas foto resep atau seret ke area ini</span>
                                    <span class="text-[11px] text-slate-600">Format JPG, JPEG, atau PNG (maksimal 5 MB)</span>
                                </div>

                                <div x-show="filePreview" x-cloak class="border border-slate-200 rounded-xl p-3 bg-slate-50 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="filePreview" alt="Pratinjau Resep" class="w-14 h-14 object-cover rounded-lg border border-slate-200 shrink-0" />
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></p>
                                            <p class="text-[11px] text-slate-600" x-text="fileSize"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="$refs.fileInput.click()" class="min-h-[44px] px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white text-slate-900 hover:bg-slate-100 cursor-pointer">
                                            Ganti
                                        </button>
                                        <button type="button" @click="removeFile()" class="min-h-[44px] px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-50 border border-rose-200 text-rose-800 hover:bg-rose-100 cursor-pointer">
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <input
                                    x-ref="fileInput"
                                    id="dash-fotoResep"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg"
                                    class="hidden"
                                    @change="handleFileChange($event)"
                                />
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    @click="modalUnggah = false"
                                    class="w-full sm:w-auto min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-900 hover:bg-slate-50 font-semibold cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="uploadingResep"
                                    class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-60 text-white font-bold cursor-pointer inline-flex items-center justify-center gap-2"
                                >
                                    <span x-show="!uploadingResep">Kirim Resep ke Apoteker</span>
                                    <span x-show="uploadingResep" x-cloak>Mengirim Resep...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Role-Based Branch & Operational Portal Directory --}}
        <section class="space-y-4" aria-labelledby="portal-modul-heading">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 id="portal-modul-heading" class="text-lg font-bold text-slate-900 tracking-tight">Portal Modul &amp; Navigasi Terpadu</h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        @if($isSuperAdmin)
                            Akses modul manajerial pusat, pelaporan konsolidasi, dan pengawasan operasional seluruh cabang.
                        @elseif($isApoteker)
                            Akses modul operasional apoteker untuk {{ $userClientName }} (Verifikasi Resep, Antrean &amp; Stok FEFO).
                        @else
                            Pilih cabang aktif apotek untuk memfilter stok langsung di Dashboard atau buka keranjang pengambilan loket.
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 {{ $isSuperAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-5">
                @if($isSuperAdmin)
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 border-l-4 border-l-emerald-600 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-emerald-800">Pusat Eksekutif</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Super Admin Eksekutif &amp; KPI</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Omzet agregat lintas cabang, penutupan periode akuntansi ADR-05, dan ekspor konsolidasi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Buka Overview Eksekutif
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.progres.index') }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Monitoring Lintas Cabang</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Live Monitoring Obat Lintas Cabang</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Pantau obat yang sedang diracik dan disiapkan di Banyuwangi Kota dan Rogojampi secara langsung.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Buka Live Monitoring
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.cabang.staf', ['client' => 'banyuwangi-kota']) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">SDM &amp; Legalitas</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Supervisi Staf &amp; SIPA Apoteker</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Kelola data tenaga kefarmasian, validitas legalitas SIPA, dan penugasan cabang.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Kelola Staf Farmasi
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.kasir.ledger') }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Kasir &amp; Akuntansi</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Rekonsiliasi Kasir &amp; Ledger Pembayaran</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Verifikasi setoran QRIS, tunai kasir, dan pencocokan ledger pembayaran harian.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Buka Ledger Kasir
                        </div>
                    </a>

                @elseif($isApoteker)
                    <a
                        href="{{ route('tenant.dashboard', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 border-l-4 border-l-emerald-600 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-emerald-800">{{ $userClientName }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Telaah Resep Dokter &amp; FEFO</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Verifikasi administratif dan klinis resep dokter, cek SIP dokter, dan alokasi obat batch FEFO terdekat ED.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Buka Verifikasi Resep
                        </div>
                    </a>

                    <a
                        href="{{ route('tenant.antrean.index', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Loket Farmasi</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Antrean &amp; Progres Status</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Panggil antrean berjalan, terbitkan tiket walk-in, dan majukan tahapan penyiapan obat.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Kelola Antrean {{ $userClientName }}
                        </div>
                    </a>

                    <a
                        href="{{ route('tenant.stok.fefo', ['client' => $userClientSlug]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Inventaris FEFO</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Manajemen Stok Batch FEFO</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Rotasi nomor batch, pantau tanggal kedaluwarsa (ED), dan karantina sediaan kedaluwarsa.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Periksa Batch Obat
                        </div>
                    </a>

                @else
                    {{-- Customer Card 1: Switch active branch to Banyuwangi Kota directly on /dashboard --}}
                    <form method="POST" action="{{ route('branch.switch') }}" class="flex">
                        @csrf
                        <input type="hidden" name="branch" value="banyuwangi-kota">
                        <button
                            type="submit"
                            class="w-full text-left group p-6 rounded-2xl bg-white border border-slate-200 border-l-4 border-l-emerald-600 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                        >
                            <div class="w-full">
                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-emerald-800">
                                        {{ $activeBranchSlug === 'banyuwangi-kota' ? 'Cabang Aktif' : 'Cabang Utama' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Katalog Cabang Banyuwangi Kota</h3>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Aktifkan stok Cabang Banyuwangi Kota langsung pada katalog dashboard tanpa pindah halaman.</p>
                            </div>
                            <div class="w-full mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                                {{ $activeBranchSlug === 'banyuwangi-kota' ? 'Sedang Aktif di Dashboard' : 'Pilih Cabang Banyuwangi Kota' }}
                            </div>
                        </button>
                    </form>

                    {{-- Customer Card 2: Switch active branch to Rogojampi directly on /dashboard --}}
                    <form method="POST" action="{{ route('branch.switch') }}" class="flex">
                        @csrf
                        <input type="hidden" name="branch" value="rogojampi">
                        <button
                            type="submit"
                            class="w-full text-left group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                        >
                            <div class="w-full">
                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600">
                                        {{ $activeBranchSlug === 'rogojampi' ? 'Cabang Aktif' : 'Cabang 2' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Katalog Cabang Rogojampi</h3>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Aktifkan stok Cabang Rogojampi langsung pada katalog dashboard tanpa pindah halaman.</p>
                            </div>
                            <div class="w-full mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                                {{ $activeBranchSlug === 'rogojampi' ? 'Sedang Aktif di Dashboard' : 'Pilih Cabang Rogojampi' }}
                            </div>
                        </button>
                    </form>

                    {{-- Customer Card 3: Cart & Pickup Slot --}}
                    <a
                        href="{{ route('customer.cart', ['client' => $activeBranchSlug]) }}"
                        class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-700 transition-colors flex flex-col justify-between min-h-[180px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Loket Ambil Cepat</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-800 transition-colors">Keranjang &amp; Slot Pengambilan</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Pilih jadwal ambil obat di apotek sesuai ketersediaan slot loket cabang aktif.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-800">
                            Buka Keranjang Saya
                        </div>
                    </a>
                @endif
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="mt-auto border-t border-slate-200 bg-white py-5 px-4 sm:px-6 lg:px-8 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <span class="font-bold text-slate-900">OmniPharma Network</span>
            <span>&copy; 2026 Sistem Informasi Farmasi Terpadu (Praktikum PSI Pak Devit Suwardiyanto)</span>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

@php
    $isRogojampi = str_contains(strtolower($client), 'rogojampi');
    $originLat = $isRogojampi ? -8.3069 : -8.2192;
    $originLng = $isRogojampi ? 114.2896 : 114.3691;

    $branchDisplayName = $clientModel->nama_client ?? 'Cabang ' . ucwords(str_replace('-', ' ', $client));
    $slots = [
        '15 Menit Lagi (Segera)',
        'Hari ini, 10:00 - 12:00 WIB',
        'Hari ini, 13:00 - 15:00 WIB',
        'Hari ini, 16:00 - 18:00 WIB',
        'Hari ini, 19:00 - 21:00 WIB',
    ];
    $checkoutJsConfig = [
        'defaultSlot' => $slots[0],
        'subtotalObat' => (int) round($total),
        'branchName' => $branchDisplayName,
        'originLat' => $originLat,
        'originLng' => $originLng,
    ];
@endphp

<x-layouts.customer :title="'Checkout Pesanan - OmniPharma'" :fullWidth="true">
    <script>
    window.omniCheckoutPageData = function(config) {
        return {
            metodeLayanan: 'click_collect',
            selectedSlot: config.defaultSlot,
            subtotalObat: config.subtotalObat,
            branchName: config.branchName,
            originLat: config.originLat,
            originLng: config.originLng,
            destLat: null,
            destLng: null,
            hasExtractedCoords: false,
            jarakKm: 2.0,
            mapsInput: '',
            alamatPengantaran: '',
            mapsStatusMessage: '',
            mapsStatusType: 'info',
            submitting: false,
            get ongkir() {
                if (this.metodeLayanan !== 'delivery') return 0;
                const d = parseFloat(this.jarakKm) || 2.0;
                return d <= 2 ? 5000 : 5000 + Math.ceil(d - 2) * 2500;
            },
            get totalBayar() {
                return this.subtotalObat + this.ongkir;
            },
            get koordinatTujuan() {
                if (!this.hasExtractedCoords || this.destLat === null || this.destLng === null) {
                    return '';
                }
                return Number(this.destLat).toFixed(5) + ',' + Number(this.destLng).toFixed(5);
            },
            get mapsRouteUrl() {
                const raw = (this.mapsInput || '').trim();
                if (this.hasExtractedCoords && this.koordinatTujuan !== '') {
                    return 'https://www.google.com/maps/dir/?api=1&origin=' + this.originLat + ',' + this.originLng + '&destination=' + this.koordinatTujuan + '&travelmode=driving';
                }
                if (/^https?:\/\//i.test(raw)) {
                    return raw;
                }
                const alamat = (this.alamatPengantaran || '').trim();
                if (alamat !== '') {
                    return 'https://www.google.com/maps/dir/?api=1&origin=' + this.originLat + ',' + this.originLng + '&destination=' + encodeURIComponent(alamat) + '&travelmode=driving';
                }
                return 'https://www.google.com/maps/@' + this.originLat + ',' + this.originLng + ',15z';
            },
            hitungJarakHaversine(lat1, lon1, lat2, lon2) {
                const R = 6371;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
                const straight = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                const roadAdjusted = Math.max(0.5, Math.min(25.0, straight * 1.25));
                return Math.round(roadAdjusted * 10) / 10;
            },
            async tempelLinkMaps() {
                if (!navigator.clipboard || typeof navigator.clipboard.readText !== 'function') {
                    this.mapsStatusType = 'warn';
                    this.mapsStatusMessage = 'Silakan tempel manual (Ctrl+V atau tekan tahan lalu Tempel) link Google Maps ke kolom di samping.';
                    return;
                }
                try {
                    const text = (await navigator.clipboard.readText()).trim();
                    if (!text) {
                        this.mapsStatusType = 'warn';
                        this.mapsStatusMessage = 'Clipboard kosong. Salin dulu link lokasi Anda dari aplikasi atau web Google Maps.';
                        return;
                    }
                    this.mapsInput = text;
                    this.parseMapsInput();
                } catch (e) {
                    this.mapsStatusType = 'warn';
                    this.mapsStatusMessage = 'Browser membatasi akses clipboard otomatis. Silakan klik kolom input lalu tempel link (Ctrl+V / Tempel).';
                }
            },
            parseMapsInput() {
                const raw = (this.mapsInput || '').trim();
                if (!raw) {
                    this.hasExtractedCoords = false;
                    this.destLat = null;
                    this.destLng = null;
                    this.mapsStatusMessage = '';
                    return;
                }

                const patterns = [
                    /@(-?\d+\.\d+),(-?\d+\.\d+)/,
                    /[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/,
                    /!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,
                    /(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)/
                ];
                for (const regex of patterns) {
                    const m = raw.match(regex);
                    if (m) {
                        const lat = parseFloat(m[1]);
                        const lng = parseFloat(m[2]);
                        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                            this.destLat = lat;
                            this.destLng = lng;
                            this.hasExtractedCoords = true;
                            this.jarakKm = this.hitungJarakHaversine(this.originLat, this.originLng, lat, lng);
                            this.mapsStatusType = 'success';
                            this.mapsStatusMessage = 'Koordinat lokasi terbaca dari link Maps! Estimasi jarak terhitung otomatis: ' + this.jarakKm + ' km dari ' + this.branchName + '.';
                            return;
                        }
                    }
                }

                this.hasExtractedCoords = false;
                this.destLat = null;
                this.destLng = null;

                if (/^https?:\/\/(maps\.app\.goo\.gl|goo\.gl|maps\.google\.com|www\.google\.com\/maps)/i.test(raw) || /^https?:\/\//i.test(raw)) {
                    this.mapsStatusType = 'info';
                    this.mapsStatusMessage = 'Link Google Maps berhasil ditempel dan akan langsung dikirim ke kurir. Sesuaikan perkiraan jarak (km) di bawah ini jika perlu.';
                } else {
                    this.mapsStatusType = 'warn';
                    this.mapsStatusMessage = 'Tempel link lengkap Google Maps (contoh: https://maps.app.goo.gl/... atau https://www.google.com/maps/@-8.2115,114.3740,17z).';
                }
            },
            formatRp(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val));
            }
        };
    };
    </script>

    <div
        class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8"
        x-data="window.omniCheckoutPageData(@js($checkoutJsConfig))"
    >
        <!-- Breadcrumb & Cabang Aktif -->
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <nav class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition flex items-center gap-1.5 font-semibold text-slate-800 py-1">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Katalog Apotek</span>
                </a>
                <span class="text-slate-400">/</span>
                <a href="{{ \Illuminate\Support\Facades\Route::has('keranjang') ? route('keranjang') : route('customer.cart', ['client' => $client]) }}" class="hover:text-emerald-700 transition font-medium py-1">
                    Keranjang
                </a>
                <span class="text-slate-400">/</span>
                <span class="font-bold text-emerald-800">Checkout</span>
            </nav>

            <span class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-bold px-3 py-1.5 bg-emerald-50 text-emerald-900 rounded-full border border-emerald-200 shrink-0">
                <span>{{ $branchDisplayName }}</span>
            </span>
        </div>

        <!-- Header & Stepper 3 Langkah -->
        <div class="mb-6 sm:mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Konfirmasi Pesanan &amp; Pengiriman</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih ambil di apotek atau pengantaran kurir dengan mengisi alamat dan menempel link lokasi Google Maps Anda.</p>
            </div>

            <div class="inline-flex flex-wrap sm:flex-nowrap items-center gap-x-2.5 gap-y-1.5 sm:gap-3 bg-white border border-slate-200 rounded-2xl px-3.5 py-2.5 shadow-2xs shrink-0 self-start lg:self-auto">
                <a href="{{ \Illuminate\Support\Facades\Route::has('keranjang') ? route('keranjang') : route('customer.cart', ['client' => $client]) }}" class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0 whitespace-nowrap hover:underline">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black shrink-0">&#10003;</span>
                    <span>Keranjang</span>
                </a>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-600 text-white inline-flex items-center justify-center text-[11px] font-black shrink-0">2</span>
                    <span>Checkout</span>
                </div>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-slate-100 text-slate-500 inline-flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                    <span>Bayar QRIS</span>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm flex items-start sm:items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ \Illuminate\Support\Facades\Route::has('checkout.store') ? route('checkout.store') : route('customer.checkout.process', ['client' => $client]) }}" method="POST" @submit="submitting = true">
            @csrf
            <input type="hidden" name="jarak_km" :value="metodeLayanan === 'delivery' ? jarakKm : 0">
            <input type="hidden" name="koordinat_tujuan" :value="metodeLayanan === 'delivery' ? koordinatTujuan : ''">
            <input type="hidden" name="ongkos_kirim" :value="metodeLayanan === 'delivery' ? ongkir : 0">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
                <!-- Kolom Kiri: Form Pengiriman & Data Pemesan -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- Cabang Apotek Pelaksana -->
                    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Gerai Pelaksana</span>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 mt-0.5">{{ $branchDisplayName }}</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    {{ $clientModel->alamat ?? 'Apotek resmi berizin operasional lengkap & apoteker siaga.' }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold shrink-0">
                                Stok Terkunci Aman
                            </span>
                        </div>
                    </div>

                    <!-- Metode Layanan & Isian Alamat + Paste Link Google Maps -->
                    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-xs">
                        <h3 class="text-base font-extrabold text-slate-900 mb-3">
                            Pilih Metode Penerimaan Obat
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <label
                                class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[104px]"
                                :class="metodeLayanan === 'click_collect' ? 'border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="metode_layanan" value="click_collect" x-model="metodeLayanan" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                        <span class="font-extrabold text-sm text-slate-900">Ambil di Apotek</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">GRATIS</span>
                                </div>
                                <span class="text-xs text-slate-500 mt-2 leading-relaxed">Tanpa ongkir. Obat disiapkan cepat dalam 15 menit sesuai nomor antrean.</span>
                            </label>

                            <label
                                class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[104px]"
                                :class="metodeLayanan === 'delivery' ? 'border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="metode_layanan" value="delivery" x-model="metodeLayanan" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                        <span class="font-extrabold text-sm text-slate-900">Kurir Pengantaran</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-800" x-text="metodeLayanan === 'delivery' ? formatRp(ongkir) : 'Mulai Rp 5 rb'">Mulai Rp 5 rb</span>
                                </div>
                                <span class="text-xs text-slate-500 mt-2 leading-relaxed">Isi alamat lengkap dan paste link Google Maps lokasi Anda agar kurir langsung mengantar ke titik tujuan.</span>
                            </label>
                        </div>

                        <!-- Opsi Click & Collect: Pilihan Slot Jam -->
                        <div x-show="metodeLayanan === 'click_collect'" class="mt-5 pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-emerald-800">
                                    Pilih Slot Jam Pengambilan
                                </label>
                                <span class="text-[11px] font-medium text-slate-500">Nomor antrean terbit otomatis setelah bayar</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($slots as $slot)
                                    <label
                                        class="cursor-pointer border rounded-xl p-3 flex items-center gap-3 transition min-h-[44px]"
                                        :class="selectedSlot === @js($slot) ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 shadow-2xs' : 'border-slate-200 bg-white hover:border-emerald-400 text-slate-800'"
                                    >
                                        <input type="radio" name="slot_waktu" value="{{ $slot }}" x-model="selectedSlot" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 shrink-0">
                                        <span class="text-xs font-bold">{{ $slot }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Opsi Delivery: Form Isian Alamat Lengkap + Paste Link Google Maps Saat Ini -->
                        <div x-show="metodeLayanan === 'delivery'" x-cloak class="mt-5 pt-5 border-t border-slate-100 space-y-4">
                            <div class="rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-sky-50 border border-emerald-200 p-4 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-900">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Isi Alamat &amp; Link Lokasi Pengiriman</span>
                                        </span>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Tulis alamat lengkap Anda lalu paste link share lokasi dari Google Maps saat ini.</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-white border border-emerald-200 text-[11px] font-extrabold text-emerald-800 shrink-0">
                                        Tarif: &le;2 km Rp 5.000, lalu +Rp 2.500/km
                                    </span>
                                </div>

                                <!-- 1. Isian Alamat Lengkap -->
                                <div>
                                    <label for="alamat_pengantaran" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Isi Alamat Lengkap Pengiriman <span class="text-rose-600">*</span>
                                    </label>
                                    <textarea
                                        id="alamat_pengantaran"
                                        name="alamat_pengantaran"
                                        x-model="alamatPengantaran"
                                        :required="metodeLayanan === 'delivery'"
                                        rows="3"
                                        placeholder="Contoh: Jl. Ahmad Yani No. 12, RT 02 RW 04, Kel. Tukangkayu, Kec. Banyuwangi (pagar hijau sebelah minimarket)"
                                        class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"
                                    ></textarea>
                                </div>

                                <!-- 2. Paste Link Lokasi Google Maps Saat Ini -->
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                        <label for="link_maps" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
                                            Paste Link Lokasi Google Maps Saat Ini <span class="text-rose-600">*</span>
                                        </label>
                                        <a
                                            href="https://www.google.com/maps"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-50 text-[11px] font-bold transition shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span>Buka Google Maps</span>
                                        </a>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input
                                            id="link_maps"
                                            type="text"
                                            name="link_maps"
                                            x-model="mapsInput"
                                            @input="parseMapsInput()"
                                            :required="metodeLayanan === 'delivery'"
                                            placeholder="Tempel link Google Maps Anda, misal: https://maps.app.goo.gl/... atau https://www.google.com/maps/@-8.2115,114.3740,17z"
                                            class="flex-1 min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"
                                        >
                                        <button
                                            type="button"
                                            @click="tempelLinkMaps()"
                                            class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition cursor-pointer shrink-0 inline-flex items-center justify-center gap-1.5"
                                        >
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Tempel Link</span>
                                        </button>
                                    </div>

                                    <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">
                                        Cara mudah: buka <strong>Google Maps</strong> &rarr; ketuk titik lokasi Anda sekarang &rarr; pilih <strong>Bagikan / Share</strong> &rarr; <strong>Salin Link</strong> &rarr; tempel di kotak atas.
                                    </p>

                                    <div
                                        x-show="mapsStatusMessage"
                                        x-cloak
                                        class="mt-2 p-2.5 rounded-xl border text-[11px] font-semibold leading-relaxed"
                                        :class="mapsStatusType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : (mapsStatusType === 'warn' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-sky-50 border-sky-200 text-sky-900')"
                                        x-text="mapsStatusMessage"
                                    ></div>
                                </div>

                                <!-- 3. Estimasi Jarak & Ongkos Kirim + Tautan Rute Kurir -->
                                <div class="bg-white rounded-2xl border border-slate-200 p-3.5 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <span class="font-bold text-slate-700">Perkiraan Jarak (Apotek &rarr; Lokasi Anda):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-900 font-black" x-text="jarakKm + ' km'"></span>
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-black" x-text="'Ongkir: ' + formatRp(ongkir)"></span>
                                        </div>
                                    </div>

                                    <input
                                        type="range"
                                        min="0.5"
                                        max="15"
                                        step="0.5"
                                        x-model.number="jarakKm"
                                        class="w-full accent-emerald-600 cursor-pointer"
                                    >

                                    <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 font-semibold">
                                        <span>0.5 - 2 km (Rp 5.000)</span>
                                        <span>5 km (Rp 12.500)</span>
                                        <span>10 km (Rp 25.000)</span>
                                        <span>15 km (Rp 37.500)</span>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-[11px] text-slate-600">Kurir &amp; apoteker otomatis menerima alamat dan link Maps Anda.</span>
                                        <a
                                            :href="mapsRouteUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#059669] hover:bg-[#047857] text-white text-[11px] font-bold transition shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                            <span>Lihat Rute Pengiriman di Google Maps</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Pemesan -->
                    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-xs space-y-4">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Informasi Penerima / Pemesan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                                <input type="text" name="nama_pemesan" required value="{{ auth()->user()->name ?? 'Budi Santoso' }}" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                                <input type="tel" name="telepon" required value="{{ auth()->user()->phone ?? old('telepon', '081234567890') }}" placeholder="Contoh: 081234567890" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan untuk Apoteker / Kurir (Opsional)</label>
                            <textarea name="catatan" rows="2" placeholder="Contoh: Tolong pisahkan etiket minum atau titip ke satpam" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Ringkasan Tagihan + Cover Obat + Ongkir Dinamis -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs lg:sticky lg:top-24 min-w-0">
                    <div class="flex items-center justify-between gap-2 pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Ringkasan Tagihan</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">
                            {{ count($items) }} Item
                        </span>
                    </div>

                    <div class="space-y-3 mb-5 max-h-72 overflow-y-auto pr-1">
                        @foreach($items as $item)
                            <div class="flex items-center gap-3 pb-3 border-b border-slate-100 last:border-b-0">
                                <div class="w-12 h-12 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if(!empty($item['url_gambar']))
                                        <img src="{{ $item['url_gambar'] }}" alt="{{ $item['nama_obat'] }}" class="w-full h-full object-contain p-1">
                                    @else
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs text-slate-900 line-clamp-2">{{ $item['nama_obat'] }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $item['jumlah'] }}x &times; Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-extrabold text-xs text-right shrink-0 text-slate-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2.5 pt-3 border-t border-slate-100 text-xs sm:text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Obat</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span>Ongkos Kirim</span>
                                <span x-show="metodeLayanan === 'delivery'" x-cloak class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-bold" x-text="jarakKm + ' km'"></span>
                            </span>
                            <span x-show="metodeLayanan !== 'delivery'" class="font-bold text-emerald-700">GRATIS</span>
                            <span x-show="metodeLayanan === 'delivery'" x-cloak class="font-bold text-slate-900" x-text="formatRp(ongkir)"></span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-base sm:text-lg font-black text-slate-900 border-t border-slate-200 mt-4 pt-4 mb-5">
                        <span>Total Bayar</span>
                        <span class="text-emerald-700" x-text="formatRp(totalBayar)">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full min-h-[48px] py-3.5 px-4 bg-[#059669] hover:bg-[#047857] active:bg-emerald-800 text-white font-bold rounded-xl text-center text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 cursor-pointer"
                    >
                        <span x-show="!submitting" class="flex items-center gap-2">
                            <span>Lanjut Bayar via QRIS</span>
                            <span>&rarr;</span>
                        </span>
                        <span x-show="submitting" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Transaksi...</span>
                        </span>
                    </button>

                    <div class="mt-3 p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/70 text-[11px] text-emerald-900 font-medium text-center">
                        Stok di gerai {{ $branchDisplayName }} akan dikunci otomatis saat pembayaran QRIS diterbitkan.
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.customer>

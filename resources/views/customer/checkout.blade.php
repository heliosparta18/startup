@php
    $isRogojampi = str_contains(strtolower($client), 'rogojampi');
    $originLat = $isRogojampi ? -8.3069 : -8.2192;
    $originLng = $isRogojampi ? 114.2896 : 114.3691;
    $defaultDestLat = $isRogojampi ? -8.2950 : -8.2065;
    $defaultDestLng = $isRogojampi ? 114.2980 : 114.3745;

    $areaPresets = $isRogojampi
        ? [
            ['label' => 'Rogojampi Kota (~1.2 km)', 'lat' => -8.3010, 'lng' => 114.2940],
            ['label' => 'Blimbingsari / Mangir (~4.8 km)', 'lat' => -8.3210, 'lng' => 114.3245],
            ['label' => 'Gladag / Bubuk (~4.5 km)', 'lat' => -8.3380, 'lng' => 114.2810],
            ['label' => 'Singojuruh (~7.6 km)', 'lat' => -8.2990, 'lng' => 114.2340],
        ]
        : [
            ['label' => 'Kepatihan / Pusat Kota (~1.3 km)', 'lat' => -8.2115, 'lng' => 114.3740],
            ['label' => 'Karangente / Sobo (~2.8 km)', 'lat' => -8.2385, 'lng' => 114.3630],
            ['label' => 'Giri / Kalipuro (~5.3 km)', 'lat' => -8.1815, 'lng' => 114.3755],
            ['label' => 'Kabat / Pakistaji (~9.0 km)', 'lat' => -8.2730, 'lng' => 114.3315],
        ];

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
        'destLat' => $defaultDestLat,
        'destLng' => $defaultDestLng,
        'defaultAreaLabel' => $areaPresets[0]['label'],
    ];
@endphp

<x-layouts.customer :title="'Checkout Pesanan - OmniPharma'" :fullWidth="true">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
    window.omniCheckoutPageData = function(config) {
        return {
            metodeLayanan: 'click_collect',
            selectedSlot: config.defaultSlot,
            subtotalObat: config.subtotalObat,
            branchName: config.branchName,
            originLat: config.originLat,
            originLng: config.originLng,
            destLat: config.destLat,
            destLng: config.destLng,
            jarakKm: 2.0,
            selectedAreaLabel: config.defaultAreaLabel,
            mapsInput: '',
            alamatPengantaran: '',
            addressSearchQuery: '',
            addressSearchResults: [],
            addressSearchLoading: false,
            gpsLoading: false,
            gpsStatus: 'idle',
            gpsLocked: false,
            gpsAccuracy: null,
            gpsMessage: '',
            mapViewMode: 'interactive',
            tileLayerMode: 'google_street',
            mapInstance: null,
            destMarker: null,
            originMarker: null,
            routePolyline: null,
            tileLayers: {},
            activeTileLayer: null,
            submitting: false,
            gestureFallbackBound: false,
            init() {
                this.syncFromSavedGps();

                window.addEventListener('gps-location-updated', (e) => {
                    if (e.detail && typeof e.detail.lat === 'number' && typeof e.detail.lng === 'number') {
                        this.applyCustomerPoint(
                            e.detail.lat,
                            e.detail.lng,
                            e.detail.address ? 'GPS Perangkat Anda (' + e.detail.address.split(',').slice(0, 2).join(',') + ')' : 'Lokasi GPS Anda Saat Ini',
                            true,
                            e.detail.accuracy || null
                        );
                        if (e.detail.address && !this.alamatPengantaran) {
                            this.alamatPengantaran = e.detail.address;
                        }
                    }
                });

                this.$watch('metodeLayanan', (val) => {
                    if (val === 'delivery') {
                        this.$nextTick(() => {
                            this.initInteractiveMap();
                            if (!this.gpsLocked && this.gpsStatus !== 'denied') {
                                this.gunakanGpsSaatIni(false);
                            }
                        });
                    }
                });

                if (!this.gpsLocked && this.gpsStatus !== 'denied') {
                    this.gunakanGpsSaatIni(true);
                }
            },
            bindGestureFallback() {
                if (this.gestureFallbackBound) return;
                this.gestureFallbackBound = true;
                const retryOnce = () => {
                    window.removeEventListener('pointerdown', retryOnce);
                    if (!this.gpsLocked && this.gpsStatus !== 'denied') {
                        this.gunakanGpsSaatIni(true);
                    }
                };
                window.addEventListener('pointerdown', retryOnce, { once: true, passive: true });
            },
            syncFromSavedGps() {
                try {
                    const cachedRaw = localStorage.getItem('omnipharma_user_gps');
                    if (cachedRaw) {
                        const cached = JSON.parse(cachedRaw);
                        const fresh = cached && cached.ts && (Date.now() - cached.ts < 1000 * 60 * 60 * 12);
                        if (fresh && typeof cached.lat === 'number' && typeof cached.lng === 'number') {
                            this.destLat = cached.lat;
                            this.destLng = cached.lng;
                            this.gpsAccuracy = cached.accuracy || null;
                            this.gpsLocked = true;
                            this.gpsStatus = 'granted';
                            this.jarakKm = this.hitungJarakHaversine(this.originLat, this.originLng, this.destLat, this.destLng);
                            this.mapsInput = this.destLat.toFixed(5) + ', ' + this.destLng.toFixed(5);
                            if (cached.address) {
                                this.alamatPengantaran = cached.address;
                            }
                            this.selectedAreaLabel = 'Lokasi GPS Terdeteksi';
                            this.gpsMessage = 'Koordinat GPS aktif dari perangkat (' + this.jarakKm + ' km dari gerai).';
                        }
                    }
                } catch (e) {}
            },
            get ongkir() {
                if (this.metodeLayanan !== 'delivery') return 0;
                const d = parseFloat(this.jarakKm) || 2.0;
                return d <= 2 ? 5000 : 5000 + Math.ceil(d - 2) * 2500;
            },
            get totalBayar() {
                return this.subtotalObat + this.ongkir;
            },
            get koordinatTujuan() {
                return Number(this.destLat).toFixed(5) + ',' + Number(this.destLng).toFixed(5);
            },
            get mapsRouteUrl() {
                return 'https://www.google.com/maps/dir/?api=1&origin=' + this.originLat + ',' + this.originLng + '&destination=' + this.koordinatTujuan + '&travelmode=driving';
            },
            get mapsEmbedUrl() {
                return 'https://maps.google.com/maps?q=' + this.koordinatTujuan + '&z=15&output=embed';
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
            initInteractiveMap() {
                if (typeof L === 'undefined') return;
                const el = document.getElementById('checkout-interactive-map');
                if (!el) return;

                if (this.mapInstance) {
                    setTimeout(() => {
                        this.mapInstance.invalidateSize();
                        this.updateMapVisuals(false);
                    }, 150);
                    return;
                }

                this.mapInstance = L.map(el, { zoomControl: true }).setView([this.destLat, this.destLng], 14);

                this.tileLayers = {
                    google_street: L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    }),
                    google_hybrid: L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps Satellite'
                    }),
                    osm: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    })
                };

                this.activeTileLayer = this.tileLayers[this.tileLayerMode] || this.tileLayers.google_street;
                this.activeTileLayer.addTo(this.mapInstance);

                this.originMarker = L.circleMarker([this.originLat, this.originLng], {
                    radius: 9,
                    color: '#0A231E',
                    weight: 3,
                    fillColor: '#D4F94E',
                    fillOpacity: 1
                }).addTo(this.mapInstance).bindPopup('<b>' + this.branchName + '</b><br>Titik Keberangkatan Kurir');

                this.destMarker = L.marker([this.destLat, this.destLng], {
                    draggable: true
                }).addTo(this.mapInstance).bindPopup('<b>Lokasi Tujuan Anda</b><br>Geser pin atau klik peta untuk ubah titik.');

                this.routePolyline = L.polyline(
                    [[this.originLat, this.originLng], [this.destLat, this.destLng]],
                    { color: '#059669', weight: 4, dashArray: '8, 6', opacity: 0.9 }
                ).addTo(this.mapInstance);

                this.destMarker.on('dragend', () => {
                    const pos = this.destMarker.getLatLng();
                    this.applyCustomerPoint(pos.lat, pos.lng, 'Pin Peta Digeser', false);
                    this.reverseGeocode(pos.lat, pos.lng);
                });

                this.mapInstance.on('click', (e) => {
                    this.applyCustomerPoint(e.latlng.lat, e.latlng.lng, 'Titik Dipilih di Peta', false);
                    this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                });

                setTimeout(() => {
                    this.mapInstance.invalidateSize();
                    this.updateMapVisuals(true);
                }, 200);
            },
            switchTileLayer(mode) {
                this.tileLayerMode = mode;
                if (!this.mapInstance || !this.tileLayers[mode]) return;
                if (this.activeTileLayer) {
                    this.mapInstance.removeLayer(this.activeTileLayer);
                }
                this.activeTileLayer = this.tileLayers[mode];
                this.activeTileLayer.addTo(this.mapInstance);
            },
            updateMapVisuals(fitBounds = true) {
                if (!this.mapInstance || !this.destMarker || !this.routePolyline) return;
                this.destMarker.setLatLng([this.destLat, this.destLng]);
                this.routePolyline.setLatLngs([[this.originLat, this.originLng], [this.destLat, this.destLng]]);
                if (fitBounds) {
                    const bounds = L.latLngBounds([[this.originLat, this.originLng], [this.destLat, this.destLng]]);
                    this.mapInstance.fitBounds(bounds, { padding: [36, 36], maxZoom: 15 });
                } else {
                    this.mapInstance.panTo([this.destLat, this.destLng]);
                }
            },
            applyCustomerPoint(lat, lng, label, isFromGps = false, accuracy = null) {
                this.destLat = Number(lat);
                this.destLng = Number(lng);
                this.jarakKm = this.hitungJarakHaversine(this.originLat, this.originLng, this.destLat, this.destLng);
                this.selectedAreaLabel = label || 'Titik Lokasi Pilihan';
                this.mapsInput = this.destLat.toFixed(5) + ', ' + this.destLng.toFixed(5);
                if (isFromGps) {
                    this.gpsLocked = true;
                    this.gpsStatus = 'granted';
                    if (accuracy) this.gpsAccuracy = Math.round(Number(accuracy));
                }
                this.gpsMessage = this.selectedAreaLabel + ' (' + this.jarakKm + ' km dari ' + this.branchName + ').';
                this.updateMapVisuals(true);
            },
            reverseGeocode(lat, lng) {
                fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&accept-language=id')
                    .then(res => res.ok ? res.json() : null)
                    .then(geo => {
                        if (geo && geo.display_name) {
                            this.alamatPengantaran = geo.display_name;
                        }
                    })
                    .catch(() => {});
            },
            cariAlamatDiPeta() {
                const q = (this.addressSearchQuery || '').trim();
                if (q.length < 3) {
                    this.addressSearchResults = [];
                    return;
                }
                this.addressSearchLoading = true;
                fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&countrycodes=id&limit=5&accept-language=id&q=' + encodeURIComponent(q))
                    .then(res => res.ok ? res.json() : [])
                    .then(data => {
                        this.addressSearchLoading = false;
                        this.addressSearchResults = Array.isArray(data) ? data : [];
                    })
                    .catch(() => {
                        this.addressSearchLoading = false;
                        this.addressSearchResults = [];
                    });
            },
            pilihHasilPencarianPeta(place) {
                const lat = parseFloat(place.lat);
                const lng = parseFloat(place.lon);
                if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                    this.alamatPengantaran = place.display_name || '';
                    this.addressSearchQuery = (place.display_name || '').split(',').slice(0, 2).join(',');
                    this.addressSearchResults = [];
                    this.applyCustomerPoint(lat, lng, 'Hasil Pencarian Peta', false);
                }
            },
            pilihAreaPreset(label, lat, lng) {
                this.applyCustomerPoint(lat, lng, label, false);
                if (!this.alamatPengantaran || this.alamatPengantaran.startsWith('Area ')) {
                    this.alamatPengantaran = 'Area ' + label;
                }
            },
            gunakanGpsSaatIni(isAutoPrompt = false) {
                if (!navigator.geolocation) {
                    this.gpsStatus = 'unsupported';
                    this.gpsMessage = 'Browser Anda tidak mendukung deteksi GPS otomatis. Silakan klik peta atau tempel link Google Maps.';
                    return;
                }
                this.gpsLoading = true;
                this.gpsMessage = 'Meminta izin browser untuk menyalakan akses GPS Anda...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.gpsLoading = false;
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        const acc = pos.coords.accuracy;
                        this.applyCustomerPoint(lat, lng, 'Lokasi GPS Anda Sekarang', true, acc);
                        this.reverseGeocode(lat, lng);

                        const payload = {
                            lat,
                            lng,
                            accuracy: Math.round(acc),
                            address: this.alamatPengantaran,
                            ts: Date.now()
                        };
                        try {
                            localStorage.setItem('omnipharma_user_gps', JSON.stringify(payload));
                        } catch (e) {}
                        window.dispatchEvent(new CustomEvent('gps-location-updated', { detail: payload }));
                    },
                    (err) => {
                        this.gpsLoading = false;
                        if (err && err.code === 1) {
                            this.gpsStatus = 'denied';
                            this.gpsMessage = isAutoPrompt
                                ? 'Browser menunda atau menolak akses GPS. Klik tombol Nyalakan & Gunakan GPS Saya atau buka izin Lokasi di ikon gembok address bar.'
                                : 'Izin GPS ditolak browser. Buka ikon gembok di address bar lalu ubah Lokasi ke Izinkan, atau geser pin langsung di peta.';
                        } else {
                            this.gpsStatus = 'unavailable';
                            this.gpsMessage = 'Sinyal GPS belum terdeteksi. Pastikan Location perangkat nyala, atau klik langsung titik rumah di peta.';
                            if (isAutoPrompt) {
                                this.bindGestureFallback();
                            }
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                );
            },
            parseMapsInput() {
                const raw = (this.mapsInput || '').trim();
                if (!raw) return;
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
                            this.applyCustomerPoint(lat, lng, 'Pin Link / Koordinat Google Maps', false);
                            return;
                        }
                    }
                }
                this.gpsMessage = 'Format link belum berisi koordinat lintang/bujur. Contoh: -8.2115, 114.3740 atau URL Google Maps dengan @lat,lng.';
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
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>{{ $branchDisplayName }}</span>
            </span>
        </div>

        <!-- Header & Stepper 3 Langkah -->
        <div class="mb-6 sm:mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Konfirmasi Pesanan &amp; Pengiriman</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih ambil di apotek atau pengantaran kurir dengan akses GPS &amp; peta interaktif Google Maps.</p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-2xl px-3.5 py-2.5 shadow-2xs overflow-x-auto">
                <a href="{{ \Illuminate\Support\Facades\Route::has('keranjang') ? route('keranjang') : route('customer.cart', ['client' => $client]) }}" class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0 hover:underline">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black">&#10003;</span>
                    <span>Keranjang</span>
                </a>
                <span class="text-slate-300 text-xs">/</span>
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 shrink-0">
                    <span class="h-5 w-5 rounded-full bg-[#0A231E] text-[#D4F94E] inline-flex items-center justify-center text-[11px] font-black">2</span>
                    <span>Checkout</span>
                </div>
                <span class="text-slate-300 text-xs">/</span>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 shrink-0">
                    <span class="h-5 w-5 rounded-full bg-slate-100 text-slate-500 inline-flex items-center justify-center text-[11px] font-bold">3</span>
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

                    <!-- Metode Layanan & Integrasi GPS + Google Maps / Ongkir -->
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
                                <span class="text-xs text-slate-500 mt-2 leading-relaxed">Nyalakan GPS atau geser pin di peta. Ongkir otomatis per km &amp; kurir mendapat rute Google Maps.</span>
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

                        <!-- Opsi Delivery: Akses GPS + Peta Interaktif Leaflet/Google Maps + Hitung Ongkir -->
                        <div x-show="metodeLayanan === 'delivery'" x-cloak class="mt-5 pt-5 border-t border-slate-100 space-y-5">
                            <div class="rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-sky-50 border border-emerald-200 p-4 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-900">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Share Lokasi Pengantaran (Terintegrasi Google Maps)</span>
                                        </span>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Website meminta akses GPS browser atau Anda dapat menggeser pin langsung pada peta Google Maps di bawah.</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-white border border-emerald-200 text-[11px] font-extrabold text-emerald-800 shrink-0">
                                        Tarif: &le;2 km Rp 5.000, lalu +Rp 2.500/km
                                    </span>
                                </div>

                                <!-- Kartu Permintaan Akses GPS Perangkat -->
                                <div class="p-3.5 rounded-2xl bg-white border border-emerald-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-2.5">
                                        <span
                                            class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                            :class="gpsLocked ? 'bg-emerald-100 text-emerald-700' : (gpsStatus === 'denied' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800')"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        <div class="text-xs">
                                            <div class="font-extrabold text-slate-900">
                                                <span x-show="gpsLocked">GPS Aktif &amp; Titik Lokasi Terkunci <span x-show="gpsAccuracy" class="text-emerald-700" x-text="'(&plusmn;' + gpsAccuracy + ' m)'"></span></span>
                                                <span x-show="!gpsLocked && gpsStatus === 'denied'" class="text-rose-700">Izin GPS Diblokir Browser</span>
                                                <span x-show="!gpsLocked && gpsStatus !== 'denied'">Aktifkan Akses GPS Perangkat Anda</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-0.5">
                                                Izinkan browser membaca posisi GPS agar pin peta, alamat jalan, dan ongkir terisi otomatis.
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="gunakanGpsSaatIni(false)"
                                        :disabled="gpsLoading"
                                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#0A231E] hover:bg-[#133830] text-[#D4F94E] text-xs font-bold transition shadow-xs cursor-pointer disabled:opacity-60 shrink-0"
                                    >
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span x-text="gpsLoading ? 'Mendeteksi GPS...' : 'Gunakan Lokasi GPS Saya Saat Ini'"></span>
                                    </button>
                                </div>

                                <!-- Pencarian Alamat / Jalan Langsung di Peta -->
                                <div class="relative">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Cari Jalan / Kelurahan / Lokasi di Peta
                                    </label>
                                    <div class="flex gap-2">
                                        <input
                                            type="text"
                                            x-model="addressSearchQuery"
                                            @keydown.enter.prevent="cariAlamatDiPeta()"
                                            placeholder="Ketik nama jalan, perumahan, atau desa (misal: Jl. Ahmad Yani Banyuwangi)"
                                            class="flex-1 min-h-[42px] px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"
                                        >
                                        <button
                                            type="button"
                                            @click="cariAlamatDiPeta()"
                                            :disabled="addressSearchLoading"
                                            class="px-4 py-2 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition cursor-pointer shrink-0 disabled:opacity-60"
                                        >
                                            <span x-text="addressSearchLoading ? 'Mencari...' : 'Cari di Peta'"></span>
                                        </button>
                                    </div>
                                    <div x-show="addressSearchResults.length > 0" x-cloak class="mt-1.5 bg-white border border-slate-200 rounded-xl shadow-md divide-y divide-slate-100 max-h-44 overflow-y-auto z-20 relative">
                                        <template x-for="(place, idx) in addressSearchResults" :key="idx">
                                            <button
                                                type="button"
                                                @click="pilihHasilPencarianPeta(place)"
                                                class="w-full text-left px-3 py-2.5 text-xs hover:bg-emerald-50 transition cursor-pointer"
                                            >
                                                <div class="font-bold text-slate-900 line-clamp-2" x-text="place.display_name"></div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Pilihan Cepat Area Google Maps -->
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Atau Pilih Titik Area Tujuan Cepat:
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($areaPresets as $preset)
                                            <button
                                                type="button"
                                                @click="pilihAreaPreset(@js($preset['label']), {{ $preset['lat'] }}, {{ $preset['lng'] }})"
                                                class="text-left px-3 py-2 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-between gap-2"
                                                :class="selectedAreaLabel === @js($preset['label']) ? 'border-emerald-600 bg-emerald-100/70 text-emerald-950 font-bold' : 'border-slate-200 bg-white hover:border-emerald-400 text-slate-700'"
                                            >
                                                <span class="truncate">{{ $preset['label'] }}</span>
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Input Tempel Link Share Lokasi / Koordinat Google Maps -->
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Tempel Link Share Lokasi / Koordinat Google Maps (Opsional)
                                    </label>
                                    <input
                                        type="text"
                                        x-model="mapsInput"
                                        @input="parseMapsInput()"
                                        placeholder="Contoh: https://www.google.com/maps/@-8.2115,114.3740,17z atau -8.2115, 114.3740"
                                        class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"
                                    >
                                    <p x-show="gpsMessage" x-text="gpsMessage" class="mt-1.5 text-[11px] font-semibold text-emerald-800"></p>
                                </div>

                                <!-- Peta Interaktif Pin + Garis Rute & Mode Switcher Google Maps -->
                                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-2xs">
                                    <div class="px-3.5 py-2.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            <button
                                                type="button"
                                                @click="mapViewMode = 'interactive'; $nextTick(() => initInteractiveMap())"
                                                class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
                                                :class="mapViewMode === 'interactive' ? 'bg-[#0A231E] text-[#D4F94E]' : 'bg-white border border-slate-200 text-slate-700'"
                                            >
                                                Peta Pin Interaktif
                                            </button>
                                            <button
                                                type="button"
                                                @click="mapViewMode = 'embed'"
                                                class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
                                                :class="mapViewMode === 'embed' ? 'bg-[#0A231E] text-[#D4F94E]' : 'bg-white border border-slate-200 text-slate-700'"
                                            >
                                                Google Maps Embed
                                            </button>
                                            <template x-if="mapViewMode === 'interactive'">
                                                <div class="flex items-center gap-1 ml-1">
                                                    <button type="button" @click="switchTileLayer('google_street')" class="px-2 py-1 rounded text-[10px] font-bold cursor-pointer" :class="tileLayerMode === 'google_street' ? 'bg-emerald-100 text-emerald-900' : 'text-slate-500 hover:bg-slate-100'">Peta Jalan</button>
                                                    <button type="button" @click="switchTileLayer('google_hybrid')" class="px-2 py-1 rounded text-[10px] font-bold cursor-pointer" :class="tileLayerMode === 'google_hybrid' ? 'bg-emerald-100 text-emerald-900' : 'text-slate-500 hover:bg-slate-100'">Satelit</button>
                                                    <button type="button" @click="switchTileLayer('osm')" class="px-2 py-1 rounded text-[10px] font-bold cursor-pointer" :class="tileLayerMode === 'osm' ? 'bg-emerald-100 text-emerald-900' : 'text-slate-500 hover:bg-slate-100'">OSM</button>
                                                </div>
                                            </template>
                                        </div>
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

                                    <div x-show="mapViewMode === 'interactive'">
                                        <div id="checkout-interactive-map" class="w-full h-64 z-0 bg-slate-100"></div>
                                    </div>
                                    <div x-show="mapViewMode === 'embed'" x-cloak>
                                        <iframe
                                            :src="mapsEmbedUrl"
                                            class="w-full h-64 border-0"
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                            title="Preview Peta Lokasi Tujuan Pengiriman"
                                        ></iframe>
                                    </div>

                                    <div class="px-3.5 py-2 bg-emerald-50/60 flex flex-wrap items-center justify-between gap-2 text-[11px] text-emerald-900 font-medium">
                                        <span>Klik peta atau geser pin biru untuk menentukan titik pengantaran tepat di rumah Anda.</span>
                                        <span class="font-mono font-bold text-emerald-800" x-text="koordinatTujuan"></span>
                                    </div>
                                </div>

                                <!-- Slider Penyesuaian Jarak & Kalkulasi Ongkir Real-Time -->
                                <div class="bg-white rounded-2xl border border-slate-200 p-3.5 space-y-2.5">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <span class="font-bold text-slate-700">Jarak Terukur (Apotek &rarr; Tujuan):</span>
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
                                    <div class="flex justify-between text-[10px] text-slate-400 font-semibold">
                                        <span>0.5 km (Rp 5.000)</span>
                                        <span>5 km (Rp 12.500)</span>
                                        <span>10 km (Rp 25.000)</span>
                                        <span>15 km (Rp 37.500)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Alamat & Patokan -->
                            <div>
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Detail Alamat &amp; Patokan Rumah (Terisi Otomatis dari GPS / Peta atau Edit Manual)
                                </label>
                                <textarea
                                    name="alamat_pengantaran"
                                    x-model="alamatPengantaran"
                                    rows="2"
                                    placeholder="Contoh: Jl. Ahmad Yani No. 12, pagar hijau sebelah minimarket"
                                    class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"
                                ></textarea>
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

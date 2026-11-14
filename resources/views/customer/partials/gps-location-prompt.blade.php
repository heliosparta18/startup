@php
    $gpsActiveBranch = $activeBranchSlug ?? 'banyuwangi-kota';
@endphp

<script>
window.omniGpsAssistantData = function(activeBranchSlug) {
    return {
        activeBranchSlug: activeBranchSlug || 'banyuwangi-kota',
        status: 'idle',
        showBanner: false,
        modalOpen: false,
        loading: false,
        lat: null,
        lng: null,
        accuracy: null,
        address: '',
        distBwi: null,
        distRgj: null,
        nearestBranch: null,
        errorMessage: '',
        branches: {
            'banyuwangi-kota': { slug: 'banyuwangi-kota', name: 'Cabang Banyuwangi Kota', lat: -8.2192, lng: 114.3691 },
            'rogojampi': { slug: 'rogojampi', name: 'Cabang Rogojampi', lat: -8.3069, lng: 114.2896 }
        },
        init() {
            window.addEventListener('gps-location-updated', (e) => {
                if (e.detail && typeof e.detail.lat === 'number' && typeof e.detail.lng === 'number') {
                    this.applyCoordinates(e.detail.lat, e.detail.lng, e.detail.accuracy || null, e.detail.address || '');
                }
            });

            try {
                const cachedRaw = localStorage.getItem('omnipharma_user_gps');
                if (cachedRaw) {
                    const cached = JSON.parse(cachedRaw);
                    const fresh = cached && cached.ts && (Date.now() - cached.ts < 1000 * 60 * 60 * 12);
                    if (fresh && typeof cached.lat === 'number' && typeof cached.lng === 'number') {
                        this.applyCoordinates(cached.lat, cached.lng, cached.accuracy || null, cached.address || '');
                        this.showBanner = false;
                        return;
                    }
                }
            } catch (e) {}

            try {
                if (sessionStorage.getItem('omnipharma_gps_dismissed') === '1') {
                    this.showBanner = false;
                    return;
                }
            } catch (e) {}

            if (!('geolocation' in navigator)) {
                this.status = 'unsupported';
                return;
            }

            if (navigator.permissions && typeof navigator.permissions.query === 'function') {
                navigator.permissions.query({ name: 'geolocation' })
                    .then((perm) => {
                        if (perm.state === 'granted') {
                            this.requestGpsAccess(true);
                        } else if (perm.state === 'denied') {
                            this.status = 'denied';
                            this.errorMessage = 'Izin lokasi browser diblokir. Aktifkan ikon gembok / izin Lokasi pada browser dan nyalakan GPS perangkat.';
                            this.showBanner = true;
                        } else {
                            this.status = 'prompt';
                            this.showBanner = true;
                            this.requestGpsAccess(true);
                            this.bindFirstGestureGps();
                        }
                    })
                    .catch(() => {
                        this.status = 'prompt';
                        this.showBanner = true;
                        this.requestGpsAccess(true);
                        this.bindFirstGestureGps();
                    });
            } else {
                this.status = 'prompt';
                this.showBanner = true;
                this.requestGpsAccess(true);
                this.bindFirstGestureGps();
            }
        },
        bindFirstGestureGps() {
            const onGesture = () => {
                window.removeEventListener('pointerdown', onGesture);
                if (!this.lat && this.status !== 'denied') {
                    this.requestGpsAccess(true);
                }
            };
            window.addEventListener('pointerdown', onGesture, { once: true, passive: true });
        },
        openGpsModal() {
            this.modalOpen = true;
            if (!this.lat && this.status !== 'denied') {
                this.requestGpsAccess(false);
            }
        },
        dismissBanner() {
            this.showBanner = false;
            try {
                sessionStorage.setItem('omnipharma_gps_dismissed', '1');
            } catch (e) {}
        },
        calcRoadDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
            const straight = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(Math.max(0.2, straight * 1.25) * 10) / 10;
        },
        applyCoordinates(lat, lng, accuracy, address) {
            this.lat = Number(lat);
            this.lng = Number(lng);
            this.accuracy = accuracy ? Math.round(Number(accuracy)) : null;
            if (address) {
                this.address = address;
            }
            this.distBwi = this.calcRoadDistance(this.lat, this.lng, this.branches['banyuwangi-kota'].lat, this.branches['banyuwangi-kota'].lng);
            this.distRgj = this.calcRoadDistance(this.lat, this.lng, this.branches['rogojampi'].lat, this.branches['rogojampi'].lng);
            this.nearestBranch = this.distBwi <= this.distRgj ? 'banyuwangi-kota' : 'rogojampi';
            this.status = 'granted';
            this.errorMessage = '';
        },
        requestGpsAccess(silent = false) {
            if (!('geolocation' in navigator)) {
                this.status = 'unsupported';
                this.errorMessage = 'Perangkat atau browser Anda belum mendukung akses GPS.';
                return;
            }
            this.loading = true;
            this.errorMessage = '';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.loading = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const acc = pos.coords.accuracy;
                    this.applyCoordinates(lat, lng, acc, this.address);
                    if (!silent) {
                        this.showBanner = false;
                    }
                    const payload = {
                        lat: this.lat,
                        lng: this.lng,
                        accuracy: this.accuracy,
                        address: this.address,
                        nearestBranch: this.nearestBranch,
                        ts: Date.now()
                    };
                    try {
                        localStorage.setItem('omnipharma_user_gps', JSON.stringify(payload));
                    } catch (e) {}
                    window.dispatchEvent(new CustomEvent('gps-location-updated', { detail: payload }));

                    fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&accept-language=id')
                        .then(res => res.ok ? res.json() : null)
                        .then(geo => {
                            if (geo && geo.display_name) {
                                this.address = geo.display_name;
                                const updated = { ...payload, address: this.address };
                                try {
                                    localStorage.setItem('omnipharma_user_gps', JSON.stringify(updated));
                                } catch (e) {}
                                window.dispatchEvent(new CustomEvent('gps-location-updated', { detail: updated }));
                            }
                        })
                        .catch(() => {});
                },
                (err) => {
                    this.loading = false;
                    if (err && err.code === 1) {
                        this.status = 'denied';
                        this.errorMessage = 'Akses GPS ditolak oleh browser. Klik ikon gembok di address bar, ubah Lokasi ke Izinkan, lalu klik Coba Lagi.';
                    } else if (err && err.code === 2) {
                        this.status = 'unavailable';
                        this.errorMessage = 'Sinyal GPS perangkat tidak tersedia. Pastikan Location / GPS pada perangkat Anda sudah dinyalakan.';
                    } else {
                        this.status = 'timeout';
                        this.errorMessage = 'Waktu deteksi GPS habis. Pastikan GPS perangkat aktif dan coba kembali.';
                    }
                    if (!silent) {
                        this.modalOpen = true;
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        },
        get googleMapsUrl() {
            if (this.lat === null || this.lng === null) return '#';
            return 'https://www.google.com/maps?q=' + this.lat.toFixed(5) + ',' + this.lng.toFixed(5);
        },
        get googleMapsEmbedUrl() {
            if (this.lat === null || this.lng === null) return 'about:blank';
            return 'https://maps.google.com/maps?q=' + this.lat.toFixed(5) + ',' + this.lng.toFixed(5) + '&z=15&output=embed';
        }
    };
};
</script>

<div x-data="window.omniGpsAssistantData(@js($gpsActiveBranch))" @open-gps-modal.window="openGpsModal()">
    <!-- Floating Banner Permintaan Nyalakan / Akses GPS -->
    <div
        x-show="showBanner && !modalOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed bottom-4 left-4 right-4 sm:right-auto sm:max-w-md z-40 rounded-2xl bg-[#0A231E] text-white p-3.5 sm:p-4 shadow-xl border border-emerald-500/40"
        role="region"
        aria-label="Permintaan Akses GPS Lokasi"
    >
        <div class="flex items-start gap-3">
            <span class="w-9 h-9 rounded-xl bg-[#D4F94E] text-[#0A231E] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-extrabold text-white leading-snug">Aktifkan Akses GPS Lokasi Anda</h4>
                <p class="text-[11px] text-emerald-100/90 mt-0.5 leading-relaxed">
                    Izinkan akses GPS agar OmniPharma mendeteksi gerai terdekat dan menghitung ongkir Google Maps otomatis.
                </p>
                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="requestGpsAccess(false)"
                        :disabled="loading"
                        class="px-3 py-1.5 rounded-lg bg-[#D4F94E] hover:bg-[#c5ec3b] text-[#0A231E] text-xs font-extrabold transition cursor-pointer disabled:opacity-60"
                    >
                        <span x-text="loading ? 'Mendeteksi GPS...' : 'Izinkan Akses GPS'"></span>
                    </button>
                    <button
                        type="button"
                        @click="openGpsModal()"
                        class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold transition cursor-pointer"
                    >
                        Detail Lokasi
                    </button>
                    <button
                        type="button"
                        @click="dismissBanner()"
                        class="px-2 py-1.5 text-[11px] font-semibold text-emerald-200 hover:text-white cursor-pointer"
                    >
                        Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail GPS & Integrasi Google Maps Cabang Terdekat -->
    <div
        x-show="modalOpen"
        x-cloak
        @keydown.escape.window="modalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="omni-gps-modal-title"
    >
        <div
            @click.outside="modalOpen = false"
            class="bg-white w-full max-w-lg rounded-3xl border border-slate-200 shadow-2xl overflow-hidden text-slate-900"
        >
            <div class="px-5 py-4 bg-[#0A231E] text-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-[#D4F94E] text-[#0A231E] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 id="omni-gps-modal-title" class="text-sm sm:text-base font-black leading-tight">Akses GPS &amp; Apotek Terdekat</h3>
                        <p class="text-[11px] text-emerald-200">Terintegrasi Google Maps &amp; Koordinat Perangkat</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="p-1.5 rounded-lg text-emerald-100 hover:bg-white/10 cursor-pointer" aria-label="Tutup jendela GPS">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-xs">
                        <span class="font-bold text-slate-600 block">Status Sinyal GPS:</span>
                        <span x-show="status === 'granted'" class="font-extrabold text-emerald-700">GPS Aktif &amp; Terkunci</span>
                        <span x-show="status === 'denied'" class="font-extrabold text-rose-700">Izin GPS Diblokir Browser</span>
                        <span x-show="status === 'unavailable' || status === 'timeout'" class="font-extrabold text-amber-700">Sinyal GPS Belum Ditemukan</span>
                        <span x-show="status === 'idle' || status === 'prompt'" class="font-extrabold text-slate-700">Menunggu Izin GPS Anda</span>
                    </div>
                    <button
                        type="button"
                        @click="requestGpsAccess(false)"
                        :disabled="loading"
                        class="px-3.5 py-2 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition cursor-pointer disabled:opacity-60"
                    >
                        <span x-text="loading ? 'Mendeteksi...' : (lat ? 'Perbarui Lokasi GPS' : 'Nyalakan & Izinkan GPS')"></span>
                    </button>
                </div>

                <div x-show="errorMessage" x-cloak class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium leading-relaxed" x-text="errorMessage"></div>

                <template x-if="lat !== null && lng !== null">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-1.5 text-xs">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-bold text-emerald-900">Koordinat GPS Anda:</span>
                                <span class="font-mono font-bold text-emerald-800" x-text="lat.toFixed(5) + ', ' + lng.toFixed(5)"></span>
                            </div>
                            <div x-show="accuracy" class="text-[11px] text-slate-600">
                                Akurasi posisi perangkat: &plusmn;<span x-text="accuracy"></span> meter
                            </div>
                            <div x-show="address" class="text-[11px] text-slate-700 font-medium pt-1 border-t border-emerald-200/70" x-text="address"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            <div class="p-3 rounded-2xl border" :class="nearestBranch === 'banyuwangi-kota' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 bg-white'">
                                <div class="font-bold text-slate-900">Cabang Banyuwangi Kota</div>
                                <div class="text-slate-600 mt-0.5">Jarak: <span class="font-extrabold text-emerald-700" x-text="distBwi + ' km'"></span></div>
                            </div>
                            <div class="p-3 rounded-2xl border" :class="nearestBranch === 'rogojampi' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 bg-white'">
                                <div class="font-bold text-slate-900">Cabang Rogojampi</div>
                                <div class="text-slate-600 mt-0.5">Jarak: <span class="font-extrabold text-emerald-700" x-text="distRgj + ' km'"></span></div>
                            </div>
                        </div>

                        <template x-if="nearestBranch && nearestBranch !== activeBranchSlug">
                            <form method="POST" action="{{ route('branch.switch') }}" class="p-3 rounded-2xl bg-amber-50 border border-amber-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                                @csrf
                                <input type="hidden" name="branch" :value="nearestBranch">
                                <span class="font-bold text-amber-900">
                                    Cabang terdekat dari posisi Anda adalah <span x-text="branches[nearestBranch]?.name"></span>.
                                </span>
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0A231E] text-[#D4F94E] font-bold cursor-pointer">
                                    Pindah ke Cabang Terdekat
                                </button>
                            </form>
                        </template>

                        <div class="rounded-2xl overflow-hidden border border-slate-200">
                            <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Pratinjau Titik Google Maps</span>
                                <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 hover:underline">
                                    Buka di Google Maps &rarr;
                                </a>
                            </div>
                            <iframe
                                :src="googleMapsEmbedUrl"
                                class="w-full h-44 border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Pratinjau Lokasi GPS Pengguna"
                            ></iframe>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

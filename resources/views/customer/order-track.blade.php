@php
    $currentStatus = $pesanan->status ?? $pesanan->status_pesanan;
    $cleanOrderNumber = ltrim((string) $pesanan->nomor_pesanan, '#');
    $paymentUrl = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pembayaran.show')
        ? route('customer.order.payment', ['client' => $client, 'id' => $pesanan->id_pesanan])
        : route('pembayaran.show', ['id' => $pesanan->id_pesanan]);
    $historyUrl = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.riwayat')
        ? route('customer.order.history', ['client' => $client])
        : route('pesanan.riwayat');
    $cartUrl = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('keranjang')
        ? route('customer.cart', ['client' => $client])
        : route('keranjang');
    $branchDisplayName = $pesanan->client?->nama_client ?? 'Cabang '.ucwords(str_replace('-', ' ', $client));
    $tanggalPesan = $pesanan->dibuat_pada ?? $pesanan->created_at;
    $formatTanggal = $tanggalPesan ? \Carbon\Carbon::parse($tanggalPesan)->format('d M Y, H:i') : now()->format('d M Y, H:i');
    $isPaid = in_array($currentStatus, ['diproses', 'siap_diambil', 'dikirim', 'selesai'], true)
        || ($pesanan->pembayaran && $pesanan->pembayaran->status === 'lunas');
    $customerDisplayName = $pesanan->pengguna?->nama_lengkap ?? auth()->user()?->name ?? 'Pelanggan OmniPharma';

    $trackerConfig = [
        'orderId' => $pesanan->id_pesanan,
        'initialStatus' => $currentStatus,
        'thirdStepLabel' => $pesanan->metode_layanan === 'delivery' ? 'Siap / Dikirim' : 'Siap Diambil',
    ];
@endphp

<x-layouts.customer :title="'Invoice & Pelacakan #' . $cleanOrderNumber . ' - OmniPharma'" :fullWidth="true">
    <script>
        window.omniOrderTrackerData = function(config) {
            return {
                currentStatus: config.initialStatus,
                steps: [
                    { key: 'menunggu_pembayaran', label: 'Menunggu Bayar' },
                    { key: 'diproses', label: 'Diproses Apotek' },
                    { key: 'siap_diambil', label: config.thirdStepLabel || 'Siap Diambil' },
                    { key: 'selesai', label: 'Selesai' }
                ],
                init() {
                    if (window.Echo) {
                        window.Echo.private('order.' + config.orderId)
                            .listen('.OrderStatusUpdated', (e) => {
                                this.currentStatus = e.status_pesanan || e.status;
                            });
                    }
                },
                isPassed(statusKey) {
                    const flow = ['menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai'];
                    const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
                    return flow.indexOf(normalized) > flow.indexOf(statusKey);
                },
                isCurrent(statusKey) {
                    const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
                    return normalized === statusKey;
                }
            };
        };
    </script>

    <div
        class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8"
        x-data="window.omniOrderTrackerData(@js($trackerConfig))"
    >
        <!-- Header & Stepper 3 Langkah -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 print:hidden">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition">Katalog Apotek</a>
                    <span>/</span>
                    <a href="{{ $historyUrl }}" class="hover:text-emerald-700 transition">Riwayat Pesanan</a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold">Invoice &amp; Pelacakan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Invoice &amp; Status Pesanan #{{ $cleanOrderNumber }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Dipesan pada {{ $formatTanggal }} WIB &bull; Gerai: <span class="font-semibold text-slate-900">{{ $branchDisplayName }}</span>
                </p>
            </div>

            <div class="inline-flex flex-wrap sm:flex-nowrap items-center gap-x-2.5 gap-y-1.5 sm:gap-3 bg-white border border-slate-200 rounded-2xl px-3.5 py-2.5 shadow-2xs shrink-0 self-start lg:self-auto">
                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black shrink-0">✓</span>
                    <span>Checkout</span>
                </span>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <span class="flex items-center gap-1.5 text-xs font-bold {{ $isPaid ? 'text-emerald-700' : 'text-amber-700' }} shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full {{ $isPaid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} inline-flex items-center justify-center text-[11px] font-black shrink-0">
                        {{ $isPaid ? '✓' : '2' }}
                    </span>
                    <span>Bayar QRIS</span>
                </span>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-600 text-white inline-flex items-center justify-center text-[11px] font-black shrink-0">3</span>
                    <span>Invoice &amp; Lacak</span>
                </div>
            </div>
        </div>

        @if($currentStatus === 'menunggu_pembayaran')
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-amber-900">Menunggu Pelunasan Pembayaran QRIS</div>
                    <p class="text-xs text-amber-800/90 mt-0.5">Silakan selesaikan pembayaran QRIS agar apoteker segera meracik dan mengirim pesanan Anda.</p>
                </div>
                <a href="{{ $paymentUrl }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition shadow-xs shrink-0">
                    Lanjutkan Bayar QRIS &rarr;
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri (7 Kolom): Antrean Virtual, Stepper Status Real-Time, dan Rute Kurir -->
            <div class="lg:col-span-7 space-y-5 print:hidden">
                <!-- Kartu Antrean Virtual Apotek -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="h-11 w-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tiket Antrean Virtual</span>
                                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">Nomor Antrean Anda</h2>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <div class="inline-flex items-center px-4 py-1.5 rounded-xl bg-emerald-600 text-white text-2xl sm:text-3xl font-black tracking-wider shadow-xs">
                                {{ $pesanan->nomor_antrean ?? 'A-001' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold uppercase">Antrean Berjalan</span>
                            <span class="text-base font-extrabold text-slate-900 mt-0.5 block">{{ $antreanBerjalan ?? 'A-001' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold uppercase">Sisa Giliran</span>
                            <span class="text-base font-extrabold text-slate-900 mt-0.5 block">
                                @if(($sisaAntrean ?? 0) <= 0)
                                    <span class="text-emerald-700">Giliran Anda</span>
                                @else
                                    {{ $sisaAntrean }} Antrean Lagi
                                @endif
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold uppercase">Estimasi Siap</span>
                            <span class="text-base font-extrabold text-slate-900 mt-0.5 block">
                                @if($currentStatus === 'siap_diambil' || $currentStatus === 'selesai')
                                    <span class="text-emerald-700">Siap</span>
                                @else
                                    ~{{ max(5, ($sisaAntrean ?? 0) * 5) }} Menit
                                @endif
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold uppercase">Layanan</span>
                            <span class="text-xs font-bold text-slate-900 mt-0.5 block truncate" title="{{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}">
                                {{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Progres Status Real-Time Reverb -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/90">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-5 border-b border-slate-100">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pelacakan Pesanan Real-Time</span>
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Progres Penyiapan Obat</h3>
                        </div>
                        <span class="inline-flex px-3 py-1 text-xs font-extrabold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize" x-text="currentStatus.replaceAll('_', ' ')">
                            {{ str_replace('_', ' ', $currentStatus) }}
                        </span>
                    </div>

                    <div class="relative flex items-start justify-between w-full my-5 px-1 sm:px-2">
                        <div class="absolute top-4 sm:top-[22px] left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 z-0"></div>
                        <template x-for="(step, index) in steps" :key="index">
                            <div class="flex flex-col items-center flex-1 min-w-0 relative z-10 px-0.5 sm:px-1">
                                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-black text-xs sm:text-sm shrink-0 transition-all duration-300 shadow-xs"
                                     :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400')">
                                    <span x-show="isPassed(step.key)">✓</span>
                                    <span x-show="!isPassed(step.key)" x-text="index + 1"></span>
                                </div>
                                <span class="text-[10px] sm:text-xs font-bold mt-2 text-center max-w-[74px] sm:max-w-none break-words leading-tight transition-colors"
                                      :class="isCurrent(step.key) ? 'text-slate-900' : 'text-slate-400'"
                                      x-text="step.label"></span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 flex items-start sm:items-center gap-2.5 text-xs text-emerald-900">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="leading-relaxed font-medium">Pembaruan status real-time aktif. Halaman ini otomatis terupdate saat apoteker memproses atau mengirim pesanan Anda.</span>
                    </div>
                </div>

                <!-- Rute & Titik Lokasi Pengiriman (Google Maps Terintegrasi) -->
                @if($pesanan->metode_layanan === 'delivery' && $pesanan->maps_route_url)
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase tracking-wider text-emerald-800">Rute Pengiriman Google Maps</span>
                                    @if($pesanan->jarak_km)
                                        <span class="px-2 py-0.5 rounded-full bg-sky-50 border border-sky-200 text-[11px] font-bold text-sky-800">{{ $pesanan->jarak_km }} km</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1">
                                    Pengirim / kurir cukup menekan tombol rute di bawah untuk langsung bernavigasi ke titik tujuan Anda.
                                </p>
                                @if($pesanan->alamat_pengantaran)
                                    <div class="mt-2 text-xs font-bold text-slate-900">
                                        Alamat / Patokan: <span class="font-medium text-slate-700">{{ $pesanan->alamat_pengantaran }}</span>
                                    </div>
                                @endif
                            </div>

                            <a
                                href="{{ $pesanan->maps_route_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition shadow-xs shrink-0"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Buka Rute Google Maps (Pengirim)</span>
                            </a>
                        </div>

                        @if($pesanan->koordinat_tujuan)
                            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
                                <iframe
                                    src="https://maps.google.com/maps?q={{ urlencode($pesanan->koordinat_tujuan) }}&z=15&output=embed"
                                    class="w-full h-48 border-0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Peta Titik Pengiriman"
                                ></iframe>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Kolom Kanan (5 Kolom): Kartu Invoice Resmi OmniPharma -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/90 lg:sticky lg:top-24 print:border-slate-300 print:shadow-none">
                <div class="flex items-start justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-emerald-700">
                            Invoice Resmi Apotek
                        </span>
                        <h2 class="text-lg font-black text-slate-900 mt-0.5">#{{ $cleanOrderNumber }}</h2>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-black border {{ $isPaid ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($currentStatus === 'dibatalkan' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                        {{ $isPaid ? 'LUNAS' : ($currentStatus === 'dibatalkan' ? 'DIBATALKAN' : 'BELUM LUNAS') }}
                    </span>
                </div>

                <!-- Metadata Invoice -->
                <div class="grid grid-cols-2 gap-2.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs mb-4">
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Nama Pasien</span>
                        <span class="font-bold text-slate-900 break-words">{{ $customerDisplayName }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Gerai Pelaksana</span>
                        <span class="font-bold text-emerald-800">{{ $branchDisplayName }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Tanggal Transaksi</span>
                        <span class="font-semibold text-slate-700">{{ $formatTanggal }} WIB</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Pembayaran</span>
                        <span class="font-semibold text-slate-700">{{ $pesanan->pembayaran?->metode_pembayaran ?? 'QRIS Midtrans' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                    <span>Rincian Obat Pesanan</span>
                    <span class="text-emerald-800 font-extrabold">{{ $pesanan->items->count() }} Jenis Obat</span>
                </div>

                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto pr-1">
                    @forelse($pesanan->items as $item)
                        <div class="py-3 flex items-center gap-3 text-xs">
                            <div class="w-11 h-11 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden shrink-0 flex items-center justify-center">
                                @if($item->obat?->url_gambar)
                                    <img src="{{ $item->obat->url_gambar }}" alt="{{ $item->nama_obat }}" class="w-full h-full object-contain p-1">
                                @else
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 break-words leading-snug">{{ $item->nama_obat }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $item->kuantitas }}x &times; Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                            </div>
                            <div class="font-extrabold text-slate-900 text-right shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 py-3">Tidak ada rincian item obat.</div>
                    @endforelse
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Obat</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->subtotal_obat, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>
                            Ongkos Kirim Kurir
                            @if($pesanan->jarak_km)
                                <span class="text-sky-700 font-bold">({{ $pesanan->jarak_km }} km)</span>
                            @endif
                        </span>
                        @if($pesanan->ongkos_kirim > 0)
                            <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                        @else
                            <span class="font-bold text-emerald-700">GRATIS</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-200 text-base font-black text-slate-900">
                        <span>Total Pembayaran</span>
                        <span class="text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($pesanan->catatan_bersih)
                    <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600">
                        <span class="font-bold text-slate-800">Catatan:</span> {{ $pesanan->catatan_bersih }}
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-slate-100 space-y-2.5 print:hidden">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="min-h-[44px] w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak Invoice</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[42px] py-2.5 px-3 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-xs transition text-center">
                            Kembali
                        </a>
                        <a href="{{ $cartUrl }}" class="inline-flex items-center justify-center min-h-[42px] py-2.5 px-3 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs transition text-center">
                            Lihat Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.customer>

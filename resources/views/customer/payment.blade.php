<x-layouts.customer :title="'Pembayaran QRIS - OmniPharma'" :fullWidth="true">
    @php
        $simPayload = json_encode([
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ]);
        $simSignature = hash_hmac('sha256', (string) $simPayload, (string) config('services.qris.webhook_secret', 'omnipharma_secret_key'));
        $branchDisplayName = $pesanan->client?->nama_client ?? 'Cabang ' . ucwords(str_replace('-', ' ', $client));
        $trackUrl = \Illuminate\Support\Facades\Route::has('pesanan.track')
            ? route('pesanan.track', ['id' => $pesanan->id_pesanan])
            : route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan]);
        $paymentJsConfig = [
            'orderNumber' => $pesanan->nomor_pesanan,
            'isPaid' => in_array($pesanan->status, ['diproses', 'selesai'], true),
            'qrisString' => $qris['qris_string'] ?? '',
            'webhookUrl' => route('webhooks.qris'),
            'simSignature' => $simSignature,
            'simPayload' => (string) $simPayload,
            'trackUrl' => $trackUrl,
        ];
    @endphp

    <script>
        window.omniPaymentPageData = function(config) {
            return {
                timeLeft: 900,
                timerStr: '15:00',
                orderNumber: config.orderNumber,
                isPaid: Boolean(config.isPaid),
                simulating: false,
                redirecting: false,
                showPayload: false,
                copiedPayload: false,
                qrError: false,
                init() {
                    if (this.isPaid) return;
                    const interval = setInterval(() => {
                        this.timeLeft--;
                        const m = Math.floor(this.timeLeft / 60);
                        const s = this.timeLeft % 60;
                        this.timerStr = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                        if (this.timeLeft <= 0) {
                            clearInterval(interval);
                            this.timerStr = 'KEDALUWARSA';
                        }
                    }, 1000);
                },
                copyEmvcoPayload() {
                    const text = config.qrisString || '';
                    if (navigator.clipboard && text) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.copiedPayload = true;
                            setTimeout(() => { this.copiedPayload = false; }, 2000);
                        });
                    }
                },
                simulatePayment() {
                    this.simulating = true;
                    fetch(config.webhookUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Signature': config.simSignature
                        },
                        body: config.simPayload
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(() => {
                        this.simulating = false;
                        this.isPaid = true;
                        this.redirecting = true;
                        setTimeout(() => {
                            window.location.href = config.trackUrl;
                        }, 1200);
                    })
                    .catch((err) => {
                        this.simulating = false;
                        alert('Gagal menyimulasikan pembayaran: ' + err.message);
                    });
                }
            };
        };
    </script>

    <div
        class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8"
        x-data="window.omniPaymentPageData(@js($paymentJsConfig))"
    >
        <!-- Header & Stepper 3 Langkah -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ $branchDisplayName }}</span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Pembayaran QRIS Pesanan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Nomor Pesanan: <span class="font-mono font-bold text-slate-900">{{ $pesanan->nomor_pesanan }}</span>
                    @if($pesanan->nomor_antrean)
                        &bull; Antrean: <span class="font-mono font-bold text-emerald-700">{{ $pesanan->nomor_antrean }}</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-2xl px-3.5 py-2.5 shadow-2xs overflow-x-auto">
                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black">✓</span>
                    <span>Keranjang</span>
                </span>
                <span class="text-slate-300 text-xs">/</span>
                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black">✓</span>
                    <span>Checkout</span>
                </span>
                <span class="text-slate-300 text-xs">/</span>
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 shrink-0">
                    <span class="h-5 w-5 rounded-full bg-[#0A231E] text-[#D4F94E] inline-flex items-center justify-center text-[11px] font-black">3</span>
                    <span>Bayar QRIS</span>
                </div>
            </div>
        </div>

        <div x-show="redirecting" x-cloak class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 animate-pulse">
            <svg class="w-5 h-5 animate-spin shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span>Pembayaran Berhasil Dilunasi! Mengalihkan ke Pelacakan Pesanan...</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri: Kartu QRIS / Status Pembayaran -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90 text-center">
                @if($pesanan->status === 'diproses' || $pesanan->status === 'selesai')
                    <div class="inline-flex p-4 bg-emerald-50 rounded-full text-emerald-600 mb-4 border border-emerald-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">Pembayaran Berhasil!</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span> sudah lunas dan sedang disiapkan oleh tim apotek.
                    </p>

                    <div class="my-6 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode</span>
                            <span class="font-bold text-slate-900">QRIS Dinamis (EMVCo)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Dibayar</span>
                            <span class="font-black text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                {{ strtoupper($pesanan->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ $trackUrl }}" class="inline-flex items-center justify-center min-h-[46px] w-full py-3 px-4 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-sm shadow-xs transition">
                            Lacak Pesanan Real-Time
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-2.5 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition">
                            Kembali ke Katalog Obat
                        </a>
                    </div>
                @elseif($pesanan->status === 'dibatalkan')
                    <div class="inline-flex p-4 bg-rose-50 rounded-full text-rose-600 mb-4 border border-rose-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">Pesanan Dibatalkan</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Stok obat pada pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span> telah dikembalikan ke cabang apotek.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-3 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-sm transition">
                            Kembali ke Katalog Obat
                        </a>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 rounded-full text-emerald-800 mb-3 border border-emerald-200 text-xs font-extrabold">
                        <span>Pembayaran QRIS Dinamis</span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900">Scan QR untuk Membayar</h2>

                    <!-- Countdown Timer -->
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 font-bold">
                        <span>Batas Waktu:</span>
                        <span class="font-mono font-extrabold text-sm" x-text="timerStr">15:00</span>
                    </div>

                    <!-- Box QR Code -->
                    <div class="my-5 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col items-center">
                        <img
                            x-show="!qrError"
                            src="{{ $qris['qr_image_url'] }}"
                            alt="QRIS Sandbox"
                            x-on:error="qrError = true"
                            class="w-48 h-48 sm:w-52 sm:h-52 aspect-square rounded-xl shadow-xs bg-white p-2 object-contain border border-slate-200"
                        >
                        <!-- Fallback QR SVG jika internet/QR generator eksternal offline -->
                        <div x-show="qrError" x-cloak class="w-48 h-48 sm:w-52 sm:h-52 rounded-xl bg-white p-4 border border-slate-200 flex flex-col items-center justify-center text-slate-700">
                            <svg class="w-24 h-24 text-slate-800 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2zm-2-2h2v2h-2v-2zm-2-4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                            </svg>
                            <span class="text-[11px] font-bold text-slate-500">QR Code Offline Sandbox</span>
                        </div>

                        <div class="mt-3 w-full flex items-center justify-center gap-2">
                            <button
                                type="button"
                                @click="showPayload = !showPayload"
                                class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 underline cursor-pointer"
                            >
                                <span x-text="showPayload ? 'Sembunyikan Kode EMVCo' : 'Lihat / Salin Kode QRIS EMVCo'"></span>
                            </button>
                        </div>

                        <div x-show="showPayload" x-cloak class="mt-2.5 w-full text-left bg-white border border-slate-200 rounded-xl p-2.5">
                            <div class="font-mono text-[10px] text-slate-600 break-all select-all leading-snug">{{ $qris['qris_string'] }}</div>
                            <button
                                type="button"
                                @click="copyEmvcoPayload()"
                                class="mt-2 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition cursor-pointer"
                            >
                                <span x-text="copiedPayload ? 'Berhasil Disalin!' : 'Salin Payload QRIS'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="text-lg sm:text-xl font-black text-slate-900 mb-5">
                        Total Tagihan: <span class="text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-2.5">
                        <button
                            type="button"
                            @click="simulatePayment()"
                            :disabled="simulating || redirecting"
                            class="min-h-[46px] w-full py-3 px-4 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 cursor-pointer"
                        >
                            <span x-show="!simulating && !redirecting">Simulasi Scan &amp; Bayar Sekarang (Lunas)</span>
                            <span x-show="simulating" x-cloak>Memproses Webhook Pelunasan...</span>
                            <span x-show="redirecting" x-cloak>Mengalihkan ke Pelacakan...</span>
                        </button>

                        <a href="{{ $trackUrl }}" class="inline-flex items-center justify-center min-h-[42px] w-full py-2.5 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold rounded-xl text-xs sm:text-sm border border-emerald-200 transition">
                            Pantau Status Pesanan &amp; Antrean
                        </a>

                        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('pesanan.cancel') ? route('pesanan.cancel', ['id' => $pesanan->id_pesanan]) : route('customer.order.cancel', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok obat akan otomatis dikembalikan ke apotek.');" class="w-full">
                            @csrf
                            <button type="submit" class="min-h-[42px] w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center border border-slate-200 cursor-pointer">
                                Batalkan Pesanan (Kembalikan Stok)
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Kolom Kanan: Rincian Obat (dengan Cover), Ongkir & Peta Rute Pengiriman -->
            <div class="lg:col-span-7 space-y-5">
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/90">
                    <div class="flex items-center justify-between gap-2 pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Rincian Obat &amp; Tagihan</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                            {{ $pesanan->metode_layanan === 'delivery' ? 'Kurir Pengantaran' : 'Ambil di Apotek' }}
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($pesanan->items as $item)
                            <div class="py-3 flex items-center gap-3.5">
                                <div class="w-14 h-14 rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($item->obat?->url_gambar)
                                        <img src="{{ $item->obat->url_gambar }}" alt="{{ $item->nama_obat }}" class="w-full h-full object-contain p-1">
                                    @else
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if($item->obat?->kategori?->nama_kategori)
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full mb-1">
                                            {{ $item->obat->kategori->nama_kategori }}
                                        </span>
                                    @endif
                                    <div class="font-bold text-xs sm:text-sm text-slate-900 break-words">{{ $item->nama_obat }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $item->kuantitas }}x &times; Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-extrabold text-xs sm:text-sm text-slate-900 shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Obat</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->subtotal_obat, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>
                                Ongkos Kirim Kurir
                                @if($pesanan->jarak_km)
                                    <span class="text-xs font-bold text-sky-700">({{ $pesanan->jarak_km }} km)</span>
                                @endif
                            </span>
                            @if($pesanan->ongkos_kirim > 0)
                                <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                            @else
                                <span class="font-bold text-emerald-700">GRATIS</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                            <span>Total Pembayaran</span>
                            <span class="text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                @if($pesanan->metode_layanan === 'delivery' && $pesanan->maps_route_url)
                    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/90 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-sky-700">Integrasi Google Maps Kurir</span>
                                <h3 class="text-base font-extrabold text-slate-900 mt-0.5">Lokasi Tujuan &amp; Rute Pengiriman</h3>
                                @if($pesanan->alamat_pengantaran)
                                    <p class="text-xs text-slate-600 mt-1">{{ $pesanan->alamat_pengantaran }}</p>
                                @endif
                            </div>
                            <a
                                href="{{ $pesanan->maps_route_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition shadow-xs"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Buka Rute Kurir di Google Maps</span>
                            </a>
                        </div>

                        @if($pesanan->koordinat_tujuan)
                            <div class="rounded-2xl overflow-hidden border border-slate-200">
                                <iframe
                                    src="https://maps.google.com/maps?q={{ urlencode($pesanan->koordinat_tujuan) }}&z=15&output=embed"
                                    class="w-full h-44 border-0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Peta Lokasi Tujuan Pengantaran"
                                ></iframe>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.customer>

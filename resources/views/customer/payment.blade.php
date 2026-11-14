<x-layouts.customer :title="'Pembayaran QRIS - OmniPharma'" :fullWidth="true">
    @php
        $simPayload = json_encode([
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ]);
        $simSignature = hash_hmac('sha256', (string) $simPayload, (string) config('services.qris.webhook_secret', 'omnipharma_secret_key'));
        $branchDisplayName = $pesanan->client?->nama_client ?? 'Cabang ' . ucwords(str_replace('-', ' ', $client));
        $trackUrl = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.track')
            ? route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan])
            : route('pesanan.track', ['id' => $pesanan->id_pesanan]);
        $checkStatusUrl = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.cek-status')
            ? route('customer.order.check-status', ['client' => $client, 'id' => $pesanan->id_pesanan])
            : route('pesanan.cek-status', ['id' => $pesanan->id_pesanan]);
        $isMidtransSandbox = ($qris['gateway'] ?? '') === 'midtrans_qris_sandbox';
        $remainingSeconds = isset($qris['remaining_seconds'])
            ? max(0, (int) $qris['remaining_seconds'])
            : ($pesanan->dibuat_pada ? max(0, (int) now()->diffInSeconds($pesanan->dibuat_pada->copy()->addMinutes(30), false)) : 1800);
        $initialTimerStr = sprintf('%02d:%02d', intdiv($remainingSeconds, 60), $remainingSeconds % 60);
        $paymentJsConfig = [
            'orderNumber' => $pesanan->nomor_pesanan,
            'isPaid' => in_array($pesanan->status, ['diproses', 'siap_diambil', 'dikirim', 'selesai'], true),
            'qrisString' => $qris['qris_string'] ?? '',
            'qrImageUrl' => $qris['qr_image_url'] ?? '',
            'checkStatusUrl' => $checkStatusUrl,
            'webhookUrl' => route('webhooks.qris'),
            'simSignature' => $simSignature,
            'simPayload' => (string) $simPayload,
            'trackUrl' => $trackUrl,
            'isMidtransSandbox' => $isMidtransSandbox,
            'remainingSeconds' => $remainingSeconds,
            'expiresAtTs' => (int) ($qris['expires_at_ts'] ?? (now()->timestamp + $remainingSeconds)),
        ];
    @endphp

    <script>
        window.omniPaymentPageData = function(config) {
            const storageKey = 'omni_qris_deadline_' + (config.orderNumber || '');
            const serverRemaining = Math.max(0, Number(config.remainingSeconds ?? 1800));
            const serverDeadlineMs = Date.now() + (serverRemaining * 1000);
            let deadlineMs = serverDeadlineMs;

            try {
                const storedRaw = window.sessionStorage.getItem(storageKey);
                const storedMs = Number(storedRaw);
                if (storedRaw && Number.isFinite(storedMs) && storedMs > 0) {
                    if ((serverDeadlineMs - storedMs) < (31 * 60 * 1000)) {
                        deadlineMs = Math.min(storedMs, serverDeadlineMs);
                    }
                }
                window.sessionStorage.setItem(storageKey, String(deadlineMs));
            } catch (e) {}

            const formatSeconds = (sec) => {
                const clamped = Math.max(0, Math.floor(sec));
                const m = Math.floor(clamped / 60);
                const s = clamped % 60;
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            };

            const initialLeft = Math.max(0, Math.ceil((deadlineMs - Date.now()) / 1000));

            return {
                deadlineMs,
                timeLeft: initialLeft,
                timerStr: formatSeconds(initialLeft),
                expiredChecked: false,
                orderNumber: config.orderNumber,
                isPaid: Boolean(config.isPaid),
                polling: false,
                simulating: false,
                redirecting: false,
                showPayload: false,
                copiedPayload: false,
                copiedQrUrl: false,
                qrError: false,
                statusMessage: '',
                statusType: 'info',
                syncCountdown() {
                    const left = Math.max(0, Math.ceil((this.deadlineMs - Date.now()) / 1000));
                    this.timeLeft = left;
                    this.timerStr = formatSeconds(left);
                    if (left <= 0 && !this.expiredChecked) {
                        this.expiredChecked = true;
                        this.cekStatusPembayaran(true, true);
                    }
                },
                init() {
                    if (this.isPaid) {
                        try { window.sessionStorage.removeItem(storageKey); } catch (e) {}
                        return;
                    }

                    this.syncCountdown();

                    const countdownTimer = setInterval(() => {
                        if (this.isPaid || this.redirecting) {
                            clearInterval(countdownTimer);
                            return;
                        }
                        this.syncCountdown();
                    }, 1000);

                    const pollTimer = setInterval(() => {
                        if (this.isPaid || this.redirecting || this.expiredChecked) {
                            clearInterval(pollTimer);
                            return;
                        }
                        this.cekStatusPembayaran(true, false);
                    }, 3000);

                    setTimeout(() => {
                        if (!this.isPaid && !this.redirecting) {
                            this.cekStatusPembayaran(true, false);
                        }
                    }, 600);

                    const triggerImmediateCheck = () => {
                        if (!this.isPaid && !this.redirecting && !document.hidden) {
                            this.syncCountdown();
                            this.cekStatusPembayaran(true, this.timeLeft <= 0);
                        }
                    };

                    window.addEventListener('focus', triggerImmediateCheck);
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            triggerImmediateCheck();
                        }
                    });
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
                copyQrImageUrl() {
                    const url = config.qrImageUrl || '';
                    if (navigator.clipboard && url) {
                        navigator.clipboard.writeText(url).then(() => {
                            this.copiedQrUrl = true;
                            setTimeout(() => { this.copiedQrUrl = false; }, 2000);
                        });
                    }
                },
                cekStatusPembayaran(isAutoTimer = false, isTimerExpired = false) {
                    if (this.redirecting) return;
                    if (isAutoTimer && (this.polling || this.simulating)) return;
                    if (!isAutoTimer && this.simulating) return;

                    if (isAutoTimer) {
                        this.polling = true;
                    } else {
                        this.simulating = true;
                        this.statusMessage = '';
                    }

                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

                    fetch(config.checkStatusUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            auto_timer: Boolean(isAutoTimer),
                            timer_expired: Boolean(isTimerExpired)
                        })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));
                        return data;
                    })
                    .then((data) => {
                        if (isAutoTimer) {
                            this.polling = false;
                        } else {
                            this.simulating = false;
                        }

                        if (data.paid) {
                            this.isPaid = true;
                            this.redirecting = true;
                            this.statusType = 'success';
                            this.statusMessage = data.message || 'Pembayaran berhasil.';
                            try { window.sessionStorage.removeItem(storageKey); } catch (e) {}
                            setTimeout(() => {
                                window.location.replace(data.track_url || config.trackUrl);
                            }, 800);
                        } else if (data.expired) {
                            this.expiredChecked = true;
                            this.statusType = 'error';
                            this.statusMessage = data.message || 'Waktu pembayaran habis. Silakan batalkan dan pesan ulang.';
                        } else if (data.cancelled) {
                            window.location.reload();
                        } else if (!isAutoTimer) {
                            this.statusType = 'info';
                            this.statusMessage = data.message || 'Belum ada pembayaran masuk.';
                        }
                    })
                    .catch((err) => {
                        if (isAutoTimer) {
                            this.polling = false;
                        } else {
                            this.simulating = false;
                            this.statusType = 'error';
                            this.statusMessage = 'Gagal mengecek status pembayaran: ' + err.message;
                        }
                    });
                },
                simulatePayment() {
                    this.cekStatusPembayaran(false, false);
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
            <div class="min-w-0">
                <span class="text-xs font-semibold text-slate-500">{{ $branchDisplayName }}</span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Pembayaran QRIS</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pesanan <span class="font-mono font-semibold text-slate-800">{{ $pesanan->nomor_pesanan }}</span>
                </p>
            </div>

            <div class="inline-flex flex-wrap sm:flex-nowrap items-center gap-x-2.5 gap-y-1.5 sm:gap-3 bg-white border border-slate-200 rounded-2xl px-3.5 py-2.5 shadow-2xs shrink-0 self-start lg:self-auto">
                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black shrink-0">✓</span>
                    <span>Keranjang</span>
                </span>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center text-[11px] font-black shrink-0">✓</span>
                    <span>Checkout</span>
                </span>
                <span class="text-slate-300 text-xs shrink-0">/</span>
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 shrink-0 whitespace-nowrap">
                    <span class="h-5 w-5 rounded-full bg-emerald-600 text-white inline-flex items-center justify-center text-[11px] font-black shrink-0">3</span>
                    <span>Bayar QRIS</span>
                </div>
            </div>
        </div>

        <div x-show="redirecting" x-cloak class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 animate-pulse">
            <svg class="w-5 h-5 animate-spin shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span>Pembayaran berhasil. Mengalihkan ke halaman pesanan...</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri: Kartu QRIS / Status Pembayaran -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90 text-center">
                @if(in_array($pesanan->status, ['diproses', 'siap_diambil', 'dikirim', 'selesai'], true))
                    <div class="inline-flex p-4 bg-emerald-50 rounded-full text-emerald-600 mb-4 border border-emerald-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">Pembayaran Berhasil</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span> sudah lunas dan sedang disiapkan apotek.
                    </p>

                    <div class="my-6 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode</span>
                            <span class="font-bold text-slate-900">QRIS</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Dibayar</span>
                            <span class="font-black text-slate-900">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                {{ strtoupper($pesanan->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ $trackUrl }}" class="inline-flex items-center justify-center min-h-[46px] w-full py-3 px-4 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-sm shadow-xs transition">
                            Lihat Pesanan &amp; Invoice
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-2.5 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition">
                            Kembali ke Katalog
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
                            Kembali ke Katalog
                        </a>
                    </div>
                @else
                    <span class="sr-only">Pembayaran QRIS Dinamis MIDTRANS SANDBOX</span>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Scan QRIS</h2>

                    <div class="mt-1.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs text-slate-700 font-semibold">
                        <span>Batas waktu</span>
                        <span class="font-mono font-bold text-slate-900" x-text="timerStr">{{ $initialTimerStr }}</span>
                    </div>

                    <!-- Box QR Code -->
                    <div class="my-5 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col items-center">
                        <img
                            x-show="!qrError"
                            src="{{ $qris['qr_image_url'] }}"
                            alt="QRIS {{ $isMidtransSandbox ? 'Midtrans Sandbox' : 'Sandbox' }}"
                            x-on:error="qrError = true"
                            class="w-48 h-48 sm:w-52 sm:h-52 aspect-square rounded-xl shadow-xs bg-white p-2 object-contain border border-slate-200"
                        >
                        <!-- Fallback QR SVG jika internet/QR generator eksternal offline -->
                        <div x-show="qrError" x-cloak class="w-48 h-48 sm:w-52 sm:h-52 rounded-xl bg-white p-4 border border-slate-200 flex flex-col items-center justify-center text-slate-700">
                            <svg class="w-24 h-24 text-slate-800 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2zm-2-2h2v2h-2v-2zm-2-4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                            </svg>
                            <span class="text-[11px] font-bold text-slate-500">QR Code Offline</span>
                        </div>

                        @if($isMidtransSandbox)
                            <div class="mt-3 w-full flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5">
                                <button
                                    type="button"
                                    @click="copyQrImageUrl()"
                                    class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline cursor-pointer"
                                >
                                    <span x-text="copiedQrUrl ? 'Tersalin' : 'Salin URL QR'"></span>
                                </button>
                                <span class="text-slate-300 text-[10px]">&bull;</span>
                                <a
                                    href="{{ $qris['simulator_url'] ?? 'https://simulator.sandbox.midtrans.com/v2/qris/index' }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 underline"
                                >
                                    Simulator Midtrans
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <span class="block text-xs text-slate-500">Total Pembayaran</span>
                        <span class="text-xl font-black text-slate-900">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>

                    <div
                        x-show="statusMessage !== ''"
                        x-cloak
                        :class="statusType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : (statusType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-700')"
                        class="mb-4 p-3 rounded-xl border text-xs font-semibold text-left leading-relaxed"
                        x-text="statusMessage"
                    ></div>

                    <div class="pt-4 border-t border-slate-100 space-y-2.5">
                        <button
                            type="button"
                            @click="cekStatusPembayaran(false)"
                            :disabled="simulating || redirecting"
                            class="min-h-[46px] w-full py-3 px-4 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 cursor-pointer"
                        >
                            <span x-show="!simulating && !redirecting">Cek Status</span>
                            <span x-show="simulating" x-cloak>Mengecek...</span>
                            <span x-show="redirecting" x-cloak>Mengalihkan...</span>
                        </button>

                        <form method="POST" action="{{ request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.cancel') ? route('customer.order.cancel', ['client' => $client, 'id' => $pesanan->id_pesanan]) : route('pesanan.cancel', ['id' => $pesanan->id_pesanan]) }}" onsubmit="return confirm('Batalkan pesanan ini?');" class="w-full">
                            @csrf
                            <button type="submit" class="min-h-[42px] w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center border border-rose-200 cursor-pointer">
                                Batalkan Pesanan
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
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full mb-1">
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
                            <span class="text-slate-900">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                @if($pesanan->metode_layanan === 'delivery' && $pesanan->maps_route_url)
                    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/90 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Integrasi Google Maps Kurir</span>
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

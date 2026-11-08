<x-layouts.customer :title="'Pembayaran QRIS - OmniPharma'">
    @php
        $simPayload = json_encode([
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ]);
        $simSignature = hash_hmac('sha256', (string) $simPayload, (string) config('services.qris.webhook_secret', 'omnipharma_secret_key'));
    @endphp
    <div class="max-w-md mx-auto px-4 py-10" x-data="{
        timeLeft: 900,
        timerStr: '15:00',
        orderNumber: '{{ $pesanan->nomor_pesanan }}',
        isPaid: {{ $pesanan->status === 'diproses' || $pesanan->status === 'selesai' ? 'true' : 'false' }},
        simulating: false,
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
        simulatePayment() {
            this.simulating = true;
            fetch('{{ route('webhooks.qris') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Signature': '{{ $simSignature }}'
                },
                body: `{!! addcslashes($simPayload, "`\\$") !!}`
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                this.simulating = false;
                this.isPaid = true;
                window.location.reload();
            })
            .catch((err) => {
                this.simulating = false;
                alert('Gagal menyimulasikan webhook: ' + err.message);
            });
        }
    }">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 sm:p-8 shadow-xl border border-zinc-200 dark:border-zinc-800 text-center">
            @if($pesanan->status === 'diproses' || $pesanan->status === 'selesai')
                <!-- Tampilan LUNAS -->
                <div class="inline-flex p-4 bg-emerald-100 dark:bg-emerald-950/60 rounded-full text-emerald-600 mb-4 animate-bounce">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Pembayaran Berhasil!</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Pesanan <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $pesanan->nomor_pesanan }}</span> telah lunas dan sedang disiapkan oleh apoteker.
                </p>

                <div class="my-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 text-left space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Metode</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">QRIS Dinamis (EMVCo)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total Dibayar</span>
                        <span class="font-bold text-emerald-600">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Status</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                            {{ strtoupper($pesanan->status) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                        Lacak Pesanan Real-Time (Reverb)
                    </a>
                    <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-3 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-semibold rounded-xl text-sm transition">
                        Kembali ke Beranda Katalog
                    </a>
                </div>
            @elseif($pesanan->status === 'dibatalkan')
                <!-- Tampilan DIBATALKAN -->
                <div class="inline-flex p-4 bg-zinc-100 dark:bg-zinc-800 rounded-full text-zinc-500 mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Pesanan Dibatalkan</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Pesanan <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $pesanan->nomor_pesanan }}</span> telah dibatalkan dan stok obat telah dikembalikan ke cabang apotek.
                </p>

                <div class="my-6 p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-left space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Status</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                            DIBATALKAN
                        </span>
                    </div>
                </div>

                <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-3 px-4 bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 text-white font-bold rounded-xl text-sm transition">
                    Kembali ke Beranda Katalog
                </a>
            @else
                <!-- Tampilan MENUNGGU PEMBAYARAN -->
                <div class="inline-flex p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-full text-emerald-600 mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Pembayaran QRIS Dinamis</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Pesanan <span class="font-mono font-bold">{{ $pesanan->nomor_pesanan }}</span>
                </p>

                <!-- Box QR Code -->
                <div class="my-5 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl inline-block border border-zinc-200 dark:border-zinc-700 shadow-inner">
                    <img src="{{ $qris['qr_image_url'] }}" alt="QRIS Sandbox" class="w-56 h-56 mx-auto rounded-xl shadow-sm bg-white p-2">
                    <div class="mt-3 font-mono text-[10px] text-zinc-400 break-all select-all max-w-[240px] mx-auto text-left leading-tight">
                        {{ $qris['qris_string'] }}
                    </div>
                </div>

                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                    Total Tagihan: <span class="text-emerald-600">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>

                <!-- Countdown Timer 15 Menit -->
                <div class="mt-2 text-xs text-amber-600 dark:text-amber-400 font-medium">
                    Batas Waktu Bayar: <span class="font-mono font-bold text-sm" x-text="timerStr">15:00</span>
                </div>

                <!-- Tombol Simulator Pelunasan Sandbox & Pembatalan untuk Demo Pak Devit -->
                <div class="mt-6 pt-5 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                    <button type="button" @click="simulatePayment()" :disabled="simulating" class="min-h-[44px] w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                        <span x-show="!simulating">⚡ Simulasi Bayar Sekarang (Sandbox Demo)</span>
                        <span x-show="simulating">Memproses Webhook...</span>
                    </button>

                    <form method="POST" action="{{ route('customer.order.cancel', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok obat akan otomatis dikembalikan ke apotek.');">
                        @csrf
                        <button type="submit" class="min-h-[44px] w-full py-2.5 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 font-semibold rounded-xl text-xs transition">
                            Batalkan Pesanan (Kembalikan Stok)
                        </button>
                    </form>

                    <p class="text-[10px] text-zinc-400">
                        Simulasi mengirimkan webhook HMAC-SHA256 untuk mendemonstrasikan auto-settlement.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.customer>

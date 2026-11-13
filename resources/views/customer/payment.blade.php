<x-layouts.customer :title="'Pembayaran QRIS - OmniPharma'">
    @php
        $simPayload = json_encode([
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ]);
        $simSignature = hash_hmac('sha256', (string) $simPayload, (string) config('services.qris.webhook_secret', 'omnipharma_secret_key'));
    @endphp
    <div class="max-w-md mx-auto py-2 sm:py-6" x-data="{
        timeLeft: 900,
        timerStr: '15:00',
        orderNumber: '{{ $pesanan->nomor_pesanan }}',
        isPaid: {{ $pesanan->status === 'diproses' || $pesanan->status === 'selesai' ? 'true' : 'false' }},
        simulating: false,
        redirecting: false,
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
                this.redirecting = true;
                setTimeout(() => {
                    window.location.href = '{{ route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}';
                }, 1500);
            })
            .catch((err) => {
                this.simulating = false;
                alert('Gagal menyimulasikan webhook: ' + err.message);
            });
        }
    }">
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/90 text-center">
            <div x-show="redirecting" x-cloak class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 animate-pulse">
                <svg class="w-5 h-5 animate-spin shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Pembayaran Berhasil Dilunasi! Mengalihkan ke Pelacakan Pesanan Real-Time...</span>
            </div>
            @if($pesanan->status === 'diproses' || $pesanan->status === 'selesai')
                <!-- Tampilan LUNAS -->
                <div class="inline-flex p-4 bg-emerald-50 rounded-full text-emerald-600 mb-4 border border-emerald-200 shadow-2xs">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Pembayaran Berhasil!</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span> telah lunas dan sedang disiapkan oleh apoteker.
                </p>

                <div class="my-6 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-2 text-sm">
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
                    <a href="{{ route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-sm shadow-xs transition">
                        Lacak Pesanan Real-Time (Reverb)
                    </a>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-2 w-full">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold rounded-xl text-xs sm:text-sm border border-emerald-200 transition">
                            Dashboard Utama
                        </a>
                        <a href="{{ route('customer.catalog', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm shadow-2xs transition">
                            Katalog Cabang
                        </a>
                    </div>
                </div>
            @elseif($pesanan->status === 'dibatalkan')
                <!-- Tampilan DIBATALKAN -->
                <div class="inline-flex p-4 bg-rose-50 rounded-full text-rose-600 mb-4 border border-rose-200">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Pesanan Dibatalkan</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span> telah dibatalkan dan stok obat telah dikembalikan ke cabang apotek.
                </p>

                <div class="my-6 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-800">
                            DIBATALKAN
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-2 w-full">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold rounded-xl text-xs sm:text-sm border border-emerald-200 transition">
                        Dashboard Utama
                    </a>
                    <a href="{{ route('customer.catalog', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm shadow-2xs transition">
                        Katalog Obat
                    </a>
                </div>
            @else
                <!-- Tampilan MENUNGGU PEMBAYARAN -->
                <div class="inline-flex p-3 bg-emerald-50 rounded-2xl text-emerald-600 mb-3 border border-emerald-200/60 shadow-2xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900">Pembayaran QRIS Dinamis</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Pesanan <span class="font-mono font-bold text-slate-800">{{ $pesanan->nomor_pesanan }}</span>
                </p>

                <!-- Box QR Code -->
                <div class="my-5 p-4 bg-slate-50 rounded-2xl inline-block max-w-full border border-slate-200 shadow-2xs">
                    <img src="{{ $qris['qr_image_url'] }}" alt="QRIS Sandbox" class="w-52 h-52 sm:w-56 sm:h-56 max-w-full aspect-square mx-auto rounded-xl shadow-xs bg-white p-2 object-contain border border-slate-200">
                    <div class="mt-3 font-mono text-[10px] text-slate-500 break-all select-all max-w-[208px] sm:max-w-[224px] mx-auto text-left leading-tight">
                        {{ $qris['qris_string'] }}
                    </div>
                </div>

                <div class="text-lg font-black text-slate-900">
                    Total Tagihan: <span class="text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>

                <!-- Countdown Timer 15 Menit -->
                <div class="mt-2 text-xs text-amber-700 font-bold">
                    Batas Waktu Bayar: <span class="font-mono font-extrabold text-sm" x-text="timerStr">15:00</span>
                </div>

                <!-- Tombol Simulator Pelunasan Sandbox & Pembatalan -->
                <div class="mt-6 pt-5 border-t border-slate-100 space-y-3">
                    <button type="button" @click="simulatePayment()" :disabled="simulating || redirecting" class="min-h-[44px] w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-xs disabled:opacity-50 cursor-pointer">
                        <span x-show="!simulating && !redirecting">Simulasi Bayar Sekarang (Sandbox Demo)</span>
                        <span x-show="simulating">Memproses Webhook...</span>
                        <span x-show="redirecting">Mengalihkan ke Pelacakan...</span>
                    </button>

                    <form method="POST" action="{{ route('customer.order.cancel', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok obat akan otomatis dikembalikan ke apotek.');" class="w-full">
                        @csrf
                        <button type="submit" class="min-h-[44px] w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center border border-slate-200 cursor-pointer">
                            Batalkan Pesanan (Kembalikan Stok)
                        </button>
                    </form>

                    <p class="text-[10px] text-slate-400 font-medium">
                        Simulasi mengirimkan webhook HMAC-SHA256 untuk mendemonstrasikan auto-settlement.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.customer>

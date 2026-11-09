@php
    $currentStatus = $pesanan->status ?? $pesanan->status_pesanan;
@endphp

<x-layouts.customer :title="'Pelacakan Pesanan ' . $pesanan->nomor_pesanan">
    <div class="max-w-3xl mx-auto py-6 sm:py-10 px-4" x-data="orderTracker('{{ $pesanan->id_pesanan }}', '{{ $currentStatus }}')">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-5 mb-6 gap-3">
                <div>
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Status Transaksi</span>
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-zinc-100">Pesanan #{{ $pesanan->nomor_pesanan }}</h1>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-1">Dipesan pada {{ $pesanan->created_at->format('d M Y, H:i') }} WIB</p>
                </div>
                <div>
                    <span class="inline-flex px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize" x-text="currentStatus.replace('_', ' ')">
                        {{ str_replace('_', ' ', $currentStatus) }}
                    </span>
                </div>
            </div>

            {{-- Stepper Status Reverb --}}
            <div class="relative flex items-center justify-between w-full my-8 px-2">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex flex-col items-center flex-1 relative z-10">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                             :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-zinc-900 text-white ring-4 ring-emerald-100 dark:ring-emerald-950' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400')">
                            <span x-show="isPassed(step.key)">✓</span>
                            <span x-show="!isPassed(step.key)" x-text="index + 1"></span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-bold mt-2.5 text-center transition-colors"
                              :class="isCurrent(step.key) ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400'"
                              x-text="step.label"></span>
                    </div>
                </template>
            </div>

            {{-- Reverb Live Connection Indicator --}}
            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800 rounded-2xl p-4 flex items-center gap-3 text-xs sm:text-sm text-emerald-900 dark:text-emerald-200">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                </span>
                <span>Koneksi WebSocket Reverb aktif pada port 8080. Perubahan status pesanan otomatis diperbarui tanpa muat ulang halaman.</span>
            </div>

            {{-- Back to catalog button --}}
            <div class="mt-8 pt-5 border-t border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                    🏠 Dashboard Utama
                </a>
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] flex-1 py-3 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-bold rounded-xl text-sm transition">
                    Katalog Cabang
                </a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderTracker', (orderId, initialStatus) => ({
            currentStatus: initialStatus,
            steps: [
                { key: 'menunggu_pembayaran', label: 'Menunggu Bayar' },
                { key: 'diproses', label: 'Diproses Apotek' },
                { key: 'siap_diambil', label: 'Siap Diambil' },
                { key: 'selesai', label: 'Selesai' }
            ],
            init() {
                if (window.Echo) {
                    window.Echo.private(`order.${orderId}`)
                        .listen('.OrderStatusUpdated', (e) => {
                            this.currentStatus = e.status_pesanan || e.status;
                        });
                }
            },
            isPassed(statusKey) {
                const flow = ['menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai'];
                return flow.indexOf(this.currentStatus) > flow.indexOf(statusKey);
            },
            isCurrent(statusKey) {
                return this.currentStatus === statusKey;
            }
        }));
    });
    </script>
</x-layouts.customer>
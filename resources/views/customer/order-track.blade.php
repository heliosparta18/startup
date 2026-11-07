@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4" x-data="orderTracker('{{ $pesanan->id_pesanan }}', '{{ $pesanan->status_pesanan }}')">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pesanan #{{ $pesanan->nomor_pesanan }}</h1>
                <p class="text-sm text-gray-500">Dipesan pada {{ $pesanan->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-50 text-blue-700 capitalize" x-text="currentStatus.replace('_', ' ')">
                {{ str_replace('_', ' ', $pesanan->status_pesanan) }}
            </span>
        </div>

        {{-- Stepper Pelacakan Pesanan --}}
        <div class="relative flex items-center justify-between w-full mb-8">
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300"
                         :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500')">
                        <span x-text="index + 1"></span>
                    </div>
                    <span class="text-xs font-medium mt-2 text-center text-gray-600" x-text="step.label"></span>
                </div>
            </template>
        </div>

        {{-- Notifikasi WebSocket Live Update --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center gap-3 text-sm text-emerald-800">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span>Koneksi WebSocket Reverb aktif. Perubahan status pesanan akan diperbarui secara langsung tanpa refresh halaman.</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderTracker', (orderId, initialStatus) => ({
        currentStatus: initialStatus,
        steps: [
            { key: 'menunggu_pembayaran', label: 'Menunggu Pembayaran' },
            { key: 'diproses', label: 'Diproses' },
            { key: 'siap_diambil', label: 'Siap Diambil' },
            { key: 'selesai', label: 'Selesai' }
        ],
        init() {
            if (window.Echo) {
                window.Echo.private(`private-order.${orderId}`)
                    .listen('.OrderStatusUpdated', (e) => {
                        this.currentStatus = e.status_pesanan;
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
@endsection
@php
    $currentStatus = $pesanan->status ?? $pesanan->status_pesanan;
@endphp

<x-layouts.customer :title="'Pelacakan Pesanan ' . $pesanan->nomor_pesanan">
    <div class="max-w-3xl mx-auto py-4 sm:py-8 px-0 sm:px-4" x-data="orderTracker('{{ $pesanan->id_pesanan }}', '{{ $currentStatus }}')">
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/90 p-5 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-5 mb-6 gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Transaksi</span>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 break-words">Pesanan #{{ $pesanan->nomor_pesanan }}</h1>
                    @php
                        $tanggalPesan = $pesanan->dibuat_pada ?? $pesanan->created_at;
                        $formatTanggal = $tanggalPesan ? \Carbon\Carbon::parse($tanggalPesan)->format('d M Y, H:i') : now()->format('d M Y, H:i');
                    @endphp
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Dipesan pada {{ $formatTanggal }} WIB • Cabang: <span class="font-bold text-emerald-800">{{ ucwords(str_replace('-', ' ', $client)) }}</span></p>
                </div>
                <div class="self-start sm:self-auto">
                    <span class="inline-flex px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize shadow-2xs" x-text="currentStatus.replace('_', ' ')">
                        {{ str_replace('_', ' ', $currentStatus) }}
                    </span>
                </div>
            </div>

            {{-- Kartu Antrean Virtual Apotek & Slot Pengambilan (FR-PEL-06 & Fitur Unggulan 2.3) --}}
            <div class="mb-6 rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 shadow-md border border-emerald-500/30 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-emerald-800/60">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-[#D4F94E] text-[#0A231E] flex items-center justify-center font-black text-sm shadow-xs shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#D4F94E]">Tiket Antrean Virtual Pasien</span>
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Nomor Antrean Anda</h2>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-2xl sm:text-3xl font-black text-[#D4F94E] tracking-wider">
                            {{ $pesanan->nomor_antrean ?? 'A-001' }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 text-xs">
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Antrean Berjalan</span>
                        <span class="text-base font-extrabold text-[#D4F94E] mt-0.5 block">{{ $antreanBerjalan ?? 'A-001' }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Sisa Giliran</span>
                        <span class="text-base font-extrabold text-white mt-0.5 block">
                            @if(($sisaAntrean ?? 0) <= 0)
                                <span class="text-[#D4F94E]">Giliran Anda</span>
                            @else
                                {{ $sisaAntrean }} Antrean Lagi
                            @endif
                        </span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Estimasi Penyiapan</span>
                        <span class="text-base font-extrabold text-white mt-0.5 block">
                            @if($currentStatus === 'siap_diambil' || $currentStatus === 'selesai')
                                <span class="text-[#D4F94E]">Siap Diambil</span>
                            @else
                                ~{{ max(5, ($sisaAntrean ?? 0) * 5) }} Menit
                            @endif
                        </span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Jadwal Ambil</span>
                        <span class="text-xs font-bold text-white mt-0.5 block truncate" title="{{ $pesanan->slot_waktu ?? '15 Menit Lagi (Segera)' }}">
                            {{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stepper Status Reverb --}}
            <div class="relative flex items-start justify-between w-full my-6 sm:my-8 px-1 sm:px-2">
                <!-- Connecting line behind circles -->
                <div class="absolute top-[18px] sm:top-[22px] left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 z-0"></div>
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex flex-col items-center flex-1 min-w-0 relative z-10 px-0.5 sm:px-1">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-black text-xs sm:text-sm shrink-0 transition-all duration-300 shadow-xs"
                             :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-[#0A231E] text-[#D4F94E] ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400')">
                            <span x-show="isPassed(step.key)">✓</span>
                            <span x-show="!isPassed(step.key)" x-text="index + 1"></span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold mt-2 text-center max-w-[70px] sm:max-w-none break-words leading-tight transition-colors"
                              :class="isCurrent(step.key) ? 'text-slate-900' : 'text-slate-400'"
                              x-text="step.label"></span>
                    </div>
                </template>
            </div>

            {{-- Ringkasan Obat yang Sedang Dipersiapkan --}}
            <div class="my-6 rounded-3xl border border-slate-200/90 p-5 sm:p-6 bg-slate-50/70">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-between">
                    <span>Rincian Obat Pesanan</span>
                    <span class="text-emerald-800 font-extrabold">{{ $pesanan->items->count() }} Jenis Obat</span>
                </h3>
                <div class="divide-y divide-slate-200">
                    @forelse($pesanan->items as $item)
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 break-words">{{ $item->nama_obat }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item->kuantitas }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                            </div>
                            <div class="font-extrabold text-slate-900 text-right shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 py-2">Tidak ada rincian item obat.</div>
                    @endforelse
                </div>
                <div class="pt-3 mt-2 border-t border-slate-200 flex justify-between items-center text-xs sm:text-sm font-black text-slate-900">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-800">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Reverb Live Connection Indicator --}}
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start sm:items-center gap-3 text-xs sm:text-sm text-emerald-900">
                <span class="relative flex h-3 w-3 shrink-0 mt-0.5 sm:mt-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                </span>
                <span class="leading-relaxed font-medium">Koneksi WebSocket Reverb aktif pada port 8080. Perubahan status pesanan otomatis diperbarui tanpa muat ulang halaman.</span>
            </div>

            {{-- Back to catalog button --}}
            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5 sm:gap-3 w-full">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition">
                    Dashboard Utama
                </a>
                <a href="{{ route('customer.catalog', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm shadow-2xs transition">
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

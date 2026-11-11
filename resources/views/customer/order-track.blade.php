@php
    $currentStatus = $pesanan->status ?? $pesanan->status_pesanan;
@endphp

<x-layouts.customer :title="'Pelacakan Pesanan ' . $pesanan->nomor_pesanan">
    <div class="max-w-3xl mx-auto py-4 sm:py-10 px-0 sm:px-4" x-data="orderTracker('{{ $pesanan->id_pesanan }}', '{{ $currentStatus }}')">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl sm:rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 p-4 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-5 mb-6 gap-3">
                <div>
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Status Transaksi</span>
                    <h1 class="text-lg sm:text-2xl font-black text-zinc-900 dark:text-zinc-100 break-words">Pesanan #{{ $pesanan->nomor_pesanan }}</h1>
                    @php
                        $tanggalPesan = $pesanan->dibuat_pada ?? $pesanan->created_at;
                        $formatTanggal = $tanggalPesan ? \Carbon\Carbon::parse($tanggalPesan)->format('d M Y, H:i') : now()->format('d M Y, H:i');
                    @endphp
                    <p class="text-xs sm:text-sm text-zinc-500 mt-1">Dipesan pada {{ $formatTanggal }} WIB • Cabang: <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ ucwords(str_replace('-', ' ', $client)) }}</span></p>
                </div>
                <div class="self-start sm:self-auto">
                    <span class="inline-flex px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize shadow-xs" x-text="currentStatus.replace('_', ' ')">
                        {{ str_replace('_', ' ', $currentStatus) }}
                    </span>
                </div>
            </div>

            {{-- Kartu Antrean Virtual Apotek & Slot Pengambilan (FR-PEL-06 & Fitur Unggulan 2.3) --}}
            <div class="mb-6 rounded-2xl bg-gradient-to-br from-emerald-950 via-[#0E352E] to-[#0A231E] text-white p-5 sm:p-6 shadow-md border border-emerald-500/30">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-emerald-800/60">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-[#D4F94E] text-[#0A231E] flex items-center justify-center font-black text-xl shadow-xs">
                            🎟️
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
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <span class="text-zinc-400 block text-[10px] font-semibold uppercase">Antrean Berjalan</span>
                        <span class="text-base font-extrabold text-[#D4F94E] mt-0.5 block">{{ $antreanBerjalan ?? 'A-001' }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <span class="text-zinc-400 block text-[10px] font-semibold uppercase">Sisa Giliran</span>
                        <span class="text-base font-extrabold text-white mt-0.5 block">
                            @if(($sisaAntrean ?? 0) <= 0)
                                <span class="text-[#D4F94E]">Giliran Anda!</span>
                            @else
                                {{ $sisaAntrean }} Antrean Lagi
                            @endif
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <span class="text-zinc-400 block text-[10px] font-semibold uppercase">Estimasi Penyiapan</span>
                        <span class="text-base font-extrabold text-white mt-0.5 block">
                            @if($currentStatus === 'siap_diambil' || $currentStatus === 'selesai')
                                <span class="text-emerald-400">Siap Diambil</span>
                            @else
                                ~{{ max(5, ($sisaAntrean ?? 0) * 5) }} Menit
                            @endif
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <span class="text-zinc-400 block text-[10px] font-semibold uppercase">Jadwal Ambil</span>
                        <span class="text-xs font-bold text-white mt-0.5 block truncate" title="{{ $pesanan->slot_waktu ?? '15 Menit Lagi (Segera)' }}">
                            {{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stepper Status Reverb --}}
            <div class="relative flex items-start justify-between w-full my-6 sm:my-8 px-1 sm:px-2">
                <!-- Connecting line behind circles -->
                <div class="absolute top-[18px] sm:top-[22px] left-[12.5%] right-[12.5%] h-0.5 bg-zinc-200 dark:bg-zinc-800 z-0"></div>
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex flex-col items-center flex-1 min-w-0 relative z-10 px-0.5 sm:px-1">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-black text-xs sm:text-sm shrink-0 transition-all duration-300 shadow-sm"
                             :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-zinc-900 text-white ring-4 ring-emerald-100 dark:ring-emerald-950' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400')">
                            <span x-show="isPassed(step.key)">✓</span>
                            <span x-show="!isPassed(step.key)" x-text="index + 1"></span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold mt-2 text-center max-w-[70px] sm:max-w-none break-words leading-tight transition-colors"
                              :class="isCurrent(step.key) ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400'"
                              x-text="step.label"></span>
                    </div>
                </template>
            </div>

            {{-- Ringkasan Obat yang Sedang Dipersiapkan --}}
            <div class="my-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 sm:p-5 bg-zinc-50/70 dark:bg-zinc-800/40">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3 flex items-center justify-between">
                    <span>💊 Rincian Obat Pesanan</span>
                    <span class="text-emerald-700 dark:text-emerald-400 font-extrabold">{{ $pesanan->items->count() }} Jenis Obat</span>
                </h3>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700/60">
                    @forelse($pesanan->items as $item)
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 break-words">{{ $item->nama_obat }}</div>
                                <div class="text-[11px] text-zinc-500">{{ $item->kuantitas }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                            </div>
                            <div class="font-extrabold text-zinc-900 dark:text-zinc-100 text-right shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-zinc-400 py-2">Tidak ada rincian item obat.</div>
                    @endforelse
                </div>
                <div class="pt-3 mt-2 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center text-xs sm:text-sm font-extrabold text-zinc-900 dark:text-zinc-100">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-700 dark:text-emerald-400">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Reverb Live Connection Indicator --}}
            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800 rounded-2xl p-3.5 sm:p-4 flex items-start sm:items-center gap-3 text-xs sm:text-sm text-emerald-900 dark:text-emerald-200">
                <span class="relative flex h-3 w-3 shrink-0 mt-0.5 sm:mt-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                </span>
                <span class="leading-relaxed">Koneksi WebSocket Reverb aktif pada port 8080. Perubahan status pesanan otomatis diperbarui tanpa muat ulang halaman.</span>
            </div>

            {{-- Back to catalog button --}}
            <div class="mt-8 pt-5 border-t border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row gap-2.5 sm:gap-3 w-full">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                    🏠 Dashboard Utama
                </a>
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-bold rounded-xl text-sm transition">
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
@php
    $currentStatus = $pesanan->status ?? $pesanan->status_pesanan;
    $paymentUrl = \Illuminate\Support\Facades\Route::has('pembayaran.show')
        ? route('pembayaran.show', ['id' => $pesanan->id_pesanan])
        : route('customer.order.payment', ['client' => $client, 'id' => $pesanan->id_pesanan]);
    $branchDisplayName = $pesanan->client?->nama_client ?? ucwords(str_replace('-', ' ', $client));
@endphp

<x-layouts.customer :title="'Pelacakan Pesanan ' . $pesanan->nomor_pesanan" :fullWidth="true">
    <div class="max-w-4xl mx-auto py-4 sm:py-8 px-3 sm:px-6" x-data="orderTracker('{{ $pesanan->id_pesanan }}', '{{ $currentStatus }}')">
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/90 p-4 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-5 mb-6 gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Transaksi</span>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 break-words">Pesanan #{{ $pesanan->nomor_pesanan }}</h1>
                    @php
                        $tanggalPesan = $pesanan->dibuat_pada ?? $pesanan->created_at;
                        $formatTanggal = $tanggalPesan ? \Carbon\Carbon::parse($tanggalPesan)->format('d M Y, H:i') : now()->format('d M Y, H:i');
                    @endphp
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Dipesan pada {{ $formatTanggal }} WIB &bull; Gerai: <span class="font-bold text-emerald-800">{{ $branchDisplayName }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                    <span class="inline-flex px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize shadow-2xs" x-text="currentStatus.replace('_', ' ')">
                        {{ str_replace('_', ' ', $currentStatus) }}
                    </span>
                </div>
            </div>

            @if($currentStatus === 'menunggu_pembayaran')
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs sm:text-sm font-extrabold text-amber-900">Menunggu Pelunasan Pembayaran QRIS</div>
                        <p class="text-xs text-amber-800/90 mt-0.5">Silakan selesaikan pembayaran QRIS agar apoteker segera meracik dan mengirim pesanan Anda.</p>
                    </div>
                    <a href="{{ $paymentUrl }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition shadow-xs shrink-0">
                        Lanjutkan Bayar QRIS &rarr;
                    </a>
                </div>
            @endif

            {{-- Kartu Antrean Virtual Apotek & Jadwal / Pengiriman --}}
            <div class="mb-6 rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-4 sm:p-6 shadow-md border border-emerald-500/30 relative overflow-hidden">
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

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 text-xs">
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
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Estimasi Siap</span>
                        <span class="text-base font-extrabold text-white mt-0.5 block">
                            @if($currentStatus === 'siap_diambil' || $currentStatus === 'selesai')
                                <span class="text-[#D4F94E]">Siap</span>
                            @else
                                ~{{ max(5, ($sisaAntrean ?? 0) * 5) }} Menit
                            @endif
                        </span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-emerald-200/70 block text-[10px] font-bold uppercase">Layanan</span>
                        <span class="text-xs font-bold text-white mt-0.5 block truncate" title="{{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}">
                            {{ $pesanan->slot_waktu ?? '15 Menit Lagi' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stepper Status Reverb --}}
            <div class="relative flex items-start justify-between w-full my-6 sm:my-8 px-1 sm:px-2">
                <div class="absolute top-4 sm:top-[22px] left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 z-0"></div>
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex flex-col items-center flex-1 min-w-0 relative z-10 px-0.5 sm:px-1">
                        <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-black text-xs sm:text-sm shrink-0 transition-all duration-300 shadow-xs"
                             :class="isPassed(step.key) ? 'bg-emerald-600 text-white' : (isCurrent(step.key) ? 'bg-[#0A231E] text-[#D4F94E] ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400')">
                            <span x-show="isPassed(step.key)">✓</span>
                            <span x-show="!isPassed(step.key)" x-text="index + 1"></span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold mt-2 text-center max-w-[74px] sm:max-w-none break-words leading-tight transition-colors"
                              :class="isCurrent(step.key) ? 'text-slate-900' : 'text-slate-400'"
                              x-text="step.label"></span>
                    </div>
                </template>
            </div>

            {{-- Rute & Titik Lokasi Pengiriman (Google Maps Terintegrasi) --}}
            @if($pesanan->metode_layanan === 'delivery' && $pesanan->maps_route_url)
                <div class="my-6 rounded-3xl border border-emerald-200 bg-emerald-50/40 p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-800">Rute Pengiriman Google Maps</span>
                                @if($pesanan->jarak_km)
                                    <span class="px-2 py-0.5 rounded-full bg-white border border-emerald-200 text-[11px] font-bold text-sky-800">{{ $pesanan->jarak_km }} km</span>
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

            {{-- Ringkasan Obat dengan Cover & Breakdown Ongkir --}}
            <div class="my-6 rounded-3xl border border-slate-200/90 p-4 sm:p-6 bg-slate-50/70">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-between">
                    <span>Rincian Obat Pesanan</span>
                    <span class="text-emerald-800 font-extrabold">{{ $pesanan->items->count() }} Jenis Obat</span>
                </h3>
                <div class="divide-y divide-slate-200">
                    @forelse($pesanan->items as $item)
                        <div class="py-3 flex items-center gap-3.5 text-xs">
                            <div class="w-12 h-12 rounded-xl border border-slate-200 bg-white overflow-hidden shrink-0 flex items-center justify-center">
                                @if($item->obat?->url_gambar)
                                    <img src="{{ $item->obat->url_gambar }}" alt="{{ $item->nama_obat }}" class="w-full h-full object-contain p-1">
                                @else
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 break-words">{{ $item->nama_obat }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $item->kuantitas }}x &times; Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                            </div>
                            <div class="font-extrabold text-slate-900 text-right shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 py-2">Tidak ada rincian item obat.</div>
                    @endforelse
                </div>

                <div class="pt-3 mt-2 border-t border-slate-200 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Obat</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->subtotal_obat, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>
                            Ongkos Kirim Kurir
                            @if($pesanan->jarak_km)
                                ({{ $pesanan->jarak_km }} km)
                            @endif
                        </span>
                        @if($pesanan->ongkos_kirim > 0)
                            <span class="font-bold text-slate-900">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                        @else
                            <span class="font-bold text-emerald-700">GRATIS</span>
                        @endif
                    </div>
                </div>

                <div class="pt-3 mt-2 border-t border-slate-200 flex justify-between items-center text-sm font-black text-slate-900">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-800">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>

                @if($pesanan->catatan_bersih)
                    <div class="mt-3 pt-2.5 border-t border-slate-200 text-xs text-slate-600">
                        <span class="font-bold text-slate-800">Catatan:</span> {{ $pesanan->catatan_bersih }}
                    </div>
                @endif
            </div>

            {{-- Reverb Live Connection Indicator --}}
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start sm:items-center gap-3 text-xs sm:text-sm text-emerald-900">
                <span class="relative flex h-3 w-3 shrink-0 mt-0.5 sm:mt-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                </span>
                <span class="leading-relaxed font-medium">Pembaruan status real-time aktif. Halaman ini otomatis terupdate saat apoteker memproses atau mengirim pesanan Anda.</span>
            </div>

            <div class="mt-7 pt-5 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5 sm:gap-3 w-full">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition">
                    Kembali ke Katalog Obat
                </a>
                <a href="{{ \Illuminate\Support\Facades\Route::has('keranjang') ? route('keranjang') : route('customer.cart', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] w-full sm:flex-1 py-3 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm shadow-2xs transition">
                    Lihat Keranjang
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
                { key: 'siap_diambil', label: '{{ $pesanan->metode_layanan === "delivery" ? "Siap / Dikirim" : "Siap Diambil" }}' },
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
                const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
                return flow.indexOf(normalized) > flow.indexOf(statusKey);
            },
            isCurrent(statusKey) {
                const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
                return normalized === statusKey;
            }
        }));
    });
    </script>
</x-layouts.customer>

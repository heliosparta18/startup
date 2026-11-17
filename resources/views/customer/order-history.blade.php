<x-layouts.customer :title="'Riwayat Pesanan - OmniPharma'" :fullWidth="true">
    <div
        class="max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-8 pb-20 md:pb-8"
        x-data="{
            activeStatus: 'semua',
            matchesFilter(status) {
                if (this.activeStatus === 'semua') return true;
                return this.activeStatus === status;
            }
        }"
    >
        {{-- Breadcrumb & Header Responsif --}}
        <div class="mb-5 sm:mb-6">
            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[11px] sm:text-xs font-semibold text-slate-500 mb-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition">Katalog Apotek</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-bold">Riwayat Pesanan</span>
            </nav>

            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Riwayat Pesanan &amp; Invoice</h1>
                    <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">Pantau status pemesanan obat, antrean apotek, dan cetak invoice resmi.</p>
                </div>

                <a
                    href="{{ route('dashboard') }}"
                    class="min-h-[40px] sm:min-h-[44px] inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs font-bold transition shadow-xs shrink-0 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="hidden sm:inline">Belanja Obat Lagi</span>
                    <span class="sm:hidden">Belanja Lagi</span>
                </a>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/90 shadow-xs text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h2 class="text-lg font-extrabold text-slate-900">Belum Ada Riwayat Pesanan</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Anda belum memiliki transaksi pesanan pada akun atau sesi ini. Silakan pilih obat atau suplemen di katalog utama.
                </p>
                <a href="{{ route('dashboard') }}" class="mt-5 min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-700 text-white font-bold text-xs sm:text-sm hover:bg-emerald-800 transition">
                    Buka Katalog Obat
                </a>
            </div>
        @else
            {{-- Filter Status Cepat Tab (Tokopedia Style) --}}
            @php
                $statusCounts = [
                    'semua' => $orders->count(),
                    'menunggu_pembayaran' => $orders->where('status', 'menunggu_pembayaran')->count(),
                    'diproses' => $orders->where('status', 'diproses')->count(),
                    'selesai' => $orders->where('status', 'selesai')->count(),
                    'dibatalkan' => $orders->where('status', 'dibatalkan')->count(),
                ];
            @endphp
            <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-4 text-xs no-scrollbar">
                <button
                    type="button"
                    @click="activeStatus = 'semua'"
                    :class="activeStatus === 'semua' ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs font-extrabold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 font-semibold'"
                    class="min-h-[36px] px-3.5 py-1.5 rounded-full border transition shrink-0 cursor-pointer flex items-center gap-1.5"
                >
                    <span>Semua</span>
                    <span class="text-[11px] opacity-85">({{ $statusCounts['semua'] }})</span>
                </button>

                @if($statusCounts['menunggu_pembayaran'] > 0)
                    <button
                        type="button"
                        @click="activeStatus = 'menunggu_pembayaran'"
                        :class="activeStatus === 'menunggu_pembayaran' ? 'bg-amber-600 text-white border-amber-600 shadow-2xs font-extrabold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 font-semibold'"
                        class="min-h-[36px] px-3.5 py-1.5 rounded-full border transition shrink-0 cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Menunggu Pembayaran</span>
                        <span class="text-[11px] opacity-85">({{ $statusCounts['menunggu_pembayaran'] }})</span>
                    </button>
                @endif

                @if($statusCounts['diproses'] > 0)
                    <button
                        type="button"
                        @click="activeStatus = 'diproses'"
                        :class="activeStatus === 'diproses' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs font-extrabold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 font-semibold'"
                        class="min-h-[36px] px-3.5 py-1.5 rounded-full border transition shrink-0 cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Diproses</span>
                        <span class="text-[11px] opacity-85">({{ $statusCounts['diproses'] }})</span>
                    </button>
                @endif

                @if($statusCounts['selesai'] > 0)
                    <button
                        type="button"
                        @click="activeStatus = 'selesai'"
                        :class="activeStatus === 'selesai' ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs font-extrabold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 font-semibold'"
                        class="min-h-[36px] px-3.5 py-1.5 rounded-full border transition shrink-0 cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Selesai</span>
                        <span class="text-[11px] opacity-85">({{ $statusCounts['selesai'] }})</span>
                    </button>
                @endif

                @if($statusCounts['dibatalkan'] > 0)
                    <button
                        type="button"
                        @click="activeStatus = 'dibatalkan'"
                        :class="activeStatus === 'dibatalkan' ? 'bg-rose-600 text-white border-rose-600 shadow-2xs font-extrabold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 font-semibold'"
                        class="min-h-[36px] px-3.5 py-1.5 rounded-full border transition shrink-0 cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Dibatalkan</span>
                        <span class="text-[11px] opacity-85">({{ $statusCounts['dibatalkan'] }})</span>
                    </button>
                @endif
            </div>

            <div class="space-y-3.5 sm:space-y-4">
                @foreach($orders as $order)
                    @php
                        $cleanNo = ltrim((string) $order->nomor_pesanan, '#');
                        $tanggal = $order->dibuat_pada ? \Carbon\Carbon::parse($order->dibuat_pada)->format('d M Y, H:i') : '-';
                        $branchName = $order->client?->nama_client ?? 'Cabang Apotek';
                        $isUnpaid = $order->status === 'menunggu_pembayaran';
                        $payLink = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pembayaran.show')
                            ? route('customer.order.payment', ['client' => $client, 'id' => $order->id_pesanan])
                            : route('pembayaran.show', ['id' => $order->id_pesanan]);
                        $invoiceLink = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.track')
                            ? route('customer.order.track', ['client' => $client, 'id' => $order->id_pesanan])
                            : route('pesanan.track', ['id' => $order->id_pesanan]);

                        $statusBadgeClass = match($order->status) {
                            'selesai' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90',
                            'diproses' => 'bg-sky-50 text-sky-800 border-sky-200/90',
                            'menunggu_pembayaran' => 'bg-amber-50 text-amber-800 border-amber-200/90',
                            'siap_diambil', 'dikirim' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90',
                            'dibatalkan' => 'bg-rose-50 text-rose-800 border-rose-200/90',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };

                        $itemCount = $order->items->count();
                        $totalQty = $order->items->sum('kuantitas') ?: $itemCount;
                        $firstItem = $order->items->first();
                        $firstObat = $firstItem?->obat;
                        $imageUrl = $firstObat?->url_gambar ?? ($firstObat?->gambar_url ?: '/images/placeholder-product.svg');
                        $firstItemName = $firstItem?->nama_obat ?: ($firstObat?->nama_obat ?: 'Produk Obat / Farmasi');
                        $firstItemQty = $firstItem?->kuantitas ?: 1;
                        $firstItemPrice = (float) ($firstItem?->harga_satuan ?: ($firstItem?->subtotal ?: 0));
                        $otherItemsCount = max(0, $itemCount - 1);
                    @endphp

                    <article
                        x-show="matchesFilter('{{ $order->status }}')"
                        x-transition.opacity.duration.150ms
                        class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-2xs sm:shadow-xs transition hover:border-slate-300 space-y-3 sm:space-y-3.5"
                    >
                        {{-- Baris 1: Kode Pesanan, Antrean & Badge Status --}}
                        <div class="flex items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
                            <div class="flex items-center gap-2 min-w-0 flex-wrap">
                                <span class="font-mono text-xs sm:text-sm font-black text-slate-900 tracking-tight">#{{ $cleanNo }}</span>
                                @if($order->nomor_antrean)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60 shrink-0">
                                        Antrean {{ $order->nomor_antrean }}
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold capitalize border shrink-0 {{ $statusBadgeClass }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>

                        {{-- Baris 2: Waktu Pemesanan & Cabang --}}
                        <div class="text-[11px] sm:text-xs text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $tanggal }} WIB</span>
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-semibold text-emerald-800">{{ $branchName }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-bold text-slate-700">{{ $itemCount }} item</span>
                        </div>

                        {{-- Baris 3: Ringkasan Produk Tokopedia Style (1 Gambar Produk + Info Produk Utama + Sisa Produk) --}}
                        <div class="flex items-start gap-3 sm:gap-3.5 bg-slate-50/70 hover:bg-slate-50 rounded-2xl p-2.5 sm:p-3 border border-slate-100/90 transition">
                            {{-- Gambar Produk Pertama --}}
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white border border-slate-200/90 p-1.5 shrink-0 flex items-center justify-center overflow-hidden shadow-2xs">
                                @if($itemCount > 0)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $firstItemName }}"
                                        class="w-full h-full object-contain"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='/images/placeholder-product.svg';"
                                    />
                                @else
                                    <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                @endif
                            </div>

                            {{-- Detail Produk --}}
                            <div class="min-w-0 flex-1">
                                @if($itemCount > 0)
                                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1 leading-snug">
                                        {{ $firstItemName }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $firstItemQty }} barang &bull; Rp {{ number_format($firstItemPrice, 0, ',', '.') }}
                                    </p>

                                    @if($otherItemsCount > 0)
                                        <p class="text-[11px] font-semibold text-slate-600 mt-1 flex items-center gap-1.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-bold border border-emerald-200/70 text-[10px] sm:text-[11px]">+{{ $otherItemsCount }}</span>
                                            <span>produk lainnya</span>
                                        </p>
                                    @endif
                                @else
                                    <h4 class="font-bold text-slate-700 text-xs sm:text-sm">
                                        Pesanan Layanan Apotek / Resep Dokter
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Konsultasi &amp; Penyerahan Resep Obat di Cabang
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Baris 4: Footer Total Pembayaran & Tombol Aksi Responsif --}}
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center justify-between sm:block sm:text-left">
                                <span class="text-[11px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Pembayaran</span>
                                <span class="text-base sm:text-lg font-black text-emerald-700 sm:block sm:mt-0.5">
                                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                @if($isUnpaid)
                                    <a
                                        href="{{ $payLink }}"
                                        class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold transition shadow-xs text-center cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>Bayar QRIS</span>
                                    </a>
                                @endif

                                <a
                                    href="{{ $invoiceLink }}"
                                    class="{{ $isUnpaid ? 'flex-1 sm:flex-initial' : 'w-full sm:w-auto' }} min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs font-bold transition shadow-xs text-center cursor-pointer"
                                >
                                    <span>Lihat Invoice &amp; Lacak</span>
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.customer>

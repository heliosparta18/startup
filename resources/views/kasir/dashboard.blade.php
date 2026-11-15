<x-layouts.kasir :title="'Kasir & POS Loket - ' . ($client->nama_client ?? 'OmniPharma')" :branch-name="'Kasir ' . ($client->nama_client ?? 'Cabang')">
    <div class="space-y-6 sm:space-y-8">
        <!-- Header Card -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>Loket Pembayaran &amp; POS Resmi</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3">
                    Kasir &amp; Point of Sale Cabang
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl">
                    {{ $client->nama_client ?? 'Cabang Apotek' }} ({{ $client->alamat ?? '-' }}) | Operasional: {{ $client->jam_buka ?? '07:00 - 22:00 WIB' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('kasir.ledger') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs transition shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span>Buku Ledger &amp; Riwayat</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @if(session('struk_id'))
                    <a href="{{ route('kasir.struk', ['id' => session('struk_id')]) }}" class="inline-flex min-h-[44px] items-center justify-center px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Cetak Struk Pembayaran
                    </a>
                @endif
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 space-y-1">
                @if(session('error'))
                    <p>{{ session('error') }}</p>
                @endif
                @foreach($errors->all() as $err)
                    <p>{{ $err }}</p>
                @endforeach
            </div>
        @endif

        <!-- 4 KPI Real Database Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pemasukan Lunas Hari Ini</span>
                <div class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</div>
                <span class="text-xs font-semibold text-emerald-700 mt-1 inline-block">Terverifikasi lunas di sistem</span>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Menunggu Pembayaran Loket</span>
                <div class="mt-2 text-2xl font-black text-amber-700">{{ $unpaidOrders->count() }} Order</div>
                <span class="text-xs font-medium text-slate-500 mt-1 inline-block">Siap diproses pelunasan</span>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Struk Terverifikasi</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $jumlahStrukLunas }} Struk</div>
                <span class="text-xs font-medium text-slate-500 mt-1 inline-block">Akumulasi: Rp {{ number_format($totalOmzetLunas, 0, ',', '.') }}</span>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rincian Tunai &amp; QRIS</span>
                <div class="mt-2 text-sm font-black text-slate-900 space-y-0.5">
                    <div>Tunai: <span class="text-emerald-700">Rp {{ number_format($totalTunai, 0, ',', '.') }}</span></div>
                    <div>QRIS: <span class="text-sky-700">Rp {{ number_format($totalQris, 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>

        <!-- POS Walk-in + Antrean Pelunasan -->
        @php
            $posItemsJs = $stokObat->map(fn ($s) => [
                'id' => $s->id_obat,
                'nama' => $s->obat->nama_obat ?? 'Obat',
                'harga' => (float) ($s->obat->harga ?? 0),
                'stok' => (int) $s->jumlah_stok,
                'sediaan' => $s->obat->jenis_sediaan ?? 'Sediaan',
                'golongan' => $s->obat->badge_status ?? 'Obat Bebas',
            ])->values();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form POS Penjualan Langsung -->
            <div
                class="lg:col-span-5 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-5"
                x-data='{
                    initialItems: @json($posItemsJs),
                    items: @json($posItemsJs),
                    searchQuery: "",
                    searching: false,
                    searchTimer: null,
                    selectedObat: "{{ $posItemsJs->first()['id'] ?? '' }}",
                    qty: 1,
                    get currentItem() {
                        return this.items.find(i => i.id === this.selectedObat) || { harga: 0, stok: 0, nama: "-", golongan: "Obat Bebas" };
                    },
                    get totalHarga() {
                        return Math.max(0, Number(this.qty || 0)) * Number(this.currentItem.harga || 0);
                    },
                    formatRupiah(num) {
                        return "Rp " + Number(num || 0).toLocaleString("id-ID");
                    },
                    onSearchInput() {
                        clearTimeout(this.searchTimer);
                        const q = this.searchQuery.trim();
                        if (q === "") {
                            this.items = this.initialItems;
                            if (this.items.length && !this.items.some(i => i.id === this.selectedObat)) {
                                this.selectedObat = this.items[0].id;
                            }
                            return;
                        }
                        this.searchTimer = setTimeout(async () => {
                            this.searching = true;
                            try {
                                const res = await fetch("{{ route('kasir.pos.search') }}?q=" + encodeURIComponent(q), {
                                    headers: { "Accept": "application/json" }
                                });
                                if (res.ok) {
                                    const data = await res.json();
                                    this.items = data.items || [];
                                    if (this.items.length) {
                                        this.selectedObat = this.items[0].id;
                                    }
                                }
                            } finally {
                                this.searching = false;
                            }
                        }, 220);
                    }
                }'
            >
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-extrabold text-slate-900">POS Penjualan Langsung (Walk-In)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Catat pembelian pasien di loket, kurangi stok cabang, dan cetak struk instan.</p>
                </div>

                <form method="POST" action="{{ route('kasir.walkin.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label for="pos-obat-search" class="block font-bold text-slate-700 mb-1.5">Cari &amp; Pilih Obat Tersedia di Cabang</label>
                        <input
                            id="pos-obat-search"
                            type="search"
                            x-model="searchQuery"
                            @input="onSearchInput()"
                            placeholder="Ketik nama obat (mis. Paracetamol, Amoxicillin)..."
                            class="mb-2 w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                        >
                        <select id="pos-obat" name="id_obat" x-model="selectedObat" x-effect="if (items.length && !items.some(i => i.id === selectedObat)) { selectedObat = items[0].id; } $nextTick(() => { $el.value = selectedObat; })" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            <template x-for="item in items" :key="item.id">
                                <option :value="item.id" x-text="item.nama + ' (Stok: ' + item.stok + ' | ' + formatRupiah(item.harga) + ')'"></option>
                            </template>
                            <template x-if="items.length === 0">
                                <option value="" disabled x-text="searching ? 'Mencari obat...' : 'Obat tidak ditemukan di stok cabang'"></option>
                            </template>
                        </select>
                        <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Golongan: <strong class="text-slate-700" x-text="currentItem.golongan"></strong></span>
                            <span>Stok Rak: <strong class="text-emerald-700" x-text="currentItem.stok + ' unit'"></strong></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="pos-qty" class="block font-bold text-slate-700 mb-1.5">Jumlah Unit</label>
                            <input id="pos-qty" type="number" name="jumlah" x-model.number="qty" min="1" :max="Math.max(1, currentItem.stok)" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                        </div>
                        <div>
                            <label for="pos-metode" class="block font-bold text-slate-700 mb-1.5">Metode Pembayaran</label>
                            <select id="pos-metode" name="metode_pembayaran" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                                <option value="tunai">Tunai Kasir</option>
                                <option value="qris">QRIS Loket</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="pos-pasien" class="block font-bold text-slate-700 mb-1.5">Nama Pasien / Pembeli</label>
                        <input id="pos-pasien" type="text" name="nama_pasien" placeholder="Pelanggan Walk-in" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    </div>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer min-h-[44px]">
                        <input type="checkbox" name="langsung_selesai" value="1" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                        <span class="font-semibold text-slate-700">Langsung serahkan obat di loket (Status Selesai)</span>
                    </label>

                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900">Total Tagihan Kasir</span>
                        <span class="text-xl font-black text-emerald-800" x-text="formatRupiah(totalHarga)">Rp 0</span>
                    </div>

                    <button
                        type="submit"
                        @if($stokObat->isEmpty()) disabled @endif
                        class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                    >
                        <span>Proses Pembayaran &amp; Buat Struk</span>
                    </button>
                </form>
            </div>

            <!-- Antrean Tagihan Menunggu Pelunasan -->
            <div class="lg:col-span-7 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Antrean Tagihan Menunggu Bayar</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Konfirmasi pembayaran tunai atau QRIS untuk pesanan online maupun ambil di tempat.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $unpaidOrders->count() }} Menunggu
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($unpaidOrders as $order)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono font-black text-xs text-slate-900">{{ $order->nomor_pesanan }}</span>
                                    @if($order->nomor_antrean)
                                        <span class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-[11px] font-bold text-slate-700">{{ $order->nomor_antrean }}</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold uppercase">Menunggu Bayar</span>
                                </div>
                                <div class="text-xs text-slate-700 font-semibold">
                                    {{ $order->nama_pembeli ?? 'Pelanggan' }}
                                </div>
                                <div class="text-[11px] text-slate-500 truncate">
                                    {{ $order->items->map(fn ($i) => $i->nama_obat.' x'.$i->kuantitas)->implode(', ') ?: 'Pesanan Resep/Obat' }}
                                </div>
                                <div class="text-sm font-black text-emerald-700">
                                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('kasir.pesanan.lunasi', ['id' => $order->id_pesanan]) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <select name="metode_pembayaran" aria-label="Metode bayar" class="min-h-[44px] rounded-xl border border-slate-300 bg-white px-2.5 text-xs font-semibold text-slate-800">
                                        <option value="tunai">Tunai</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                    <button type="submit" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                                        Terima Bayar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('kasir.pesanan.batalkan', ['id' => $order->id_pesanan]) }}" onsubmit="return confirm('Batalkan pesanan ini dan kembalikan stok ke cabang?')">
                                    @csrf
                                    <button type="submit" class="min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition cursor-pointer">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500">
                            Semua tagihan loket cabang telah diselesaikan. Gunakan form POS di samping untuk transaksi walk-in baru.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Riwayat Pembayaran Cabang -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Riwayat Pembayaran Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rekam transaksi pembayaran terbaru pada cabang aktif.</p>
                </div>
                <a href="{{ route('kasir.ledger') }}" class="inline-flex min-h-[44px] items-center text-xs font-bold text-emerald-700 hover:underline">Lihat Buku Ledger Lengkap &rarr;</a>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden space-y-3">
                @forelse($recentPayments as $pay)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3 text-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-mono font-bold text-slate-900">{{ $pay->pesanan->nomor_pesanan ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $pay->dibuat_pada ? $pay->dibuat_pada->format('d M Y H:i') : '-' }}</div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 {{ $pay->status === 'lunas' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ strtoupper($pay->status) }}
                            </span>
                        </div>
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900">{{ $pay->pesanan->nama_pembeli ?? 'Pelanggan' }}</div>
                            <div class="text-[11px] text-slate-600 line-clamp-2">{{ $pay->pesanan?->items?->map(fn ($i) => $i->nama_obat.' x'.$i->kuantitas)->implode(', ') ?: '-' }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/80">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-slate-500 mr-1">{{ strtoupper($pay->metode_pembayaran) }}</span>
                                <span class="font-black text-sm text-slate-900">Rp {{ number_format((float) $pay->jumlah, 0, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('kasir.struk', ['id' => $pay->id_pembayaran]) }}" class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold transition">
                                Struk
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500">
                        Belum ada riwayat pembayaran di cabang ini.
                    </div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[44rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Pesanan</th>
                            <th class="p-3.5">Pasien / Item</th>
                            <th class="p-3.5">Metode</th>
                            <th class="p-3.5 text-right">Nominal</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-right">Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentPayments as $pay)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-mono font-bold text-slate-900">
                                    {{ $pay->pesanan->nomor_pesanan ?? '-' }}
                                    <div class="text-[10px] font-normal text-slate-500">{{ $pay->dibuat_pada ? $pay->dibuat_pada->format('d M Y H:i') : '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $pay->pesanan->nama_pembeli ?? 'Pelanggan' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $pay->pesanan?->items?->map(fn ($i) => $i->nama_obat.' x'.$i->kuantitas)->implode(', ') ?: '-' }}</div>
                                </td>
                                <td class="p-3.5 uppercase font-bold text-slate-700">{{ $pay->metode_pembayaran }}</td>
                                <td class="p-3.5 text-right font-black text-slate-900">Rp {{ number_format((float) $pay->jumlah, 0, ',', '.') }}</td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $pay->status === 'lunas' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ strtoupper($pay->status) }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('kasir.struk', ['id' => $pay->id_pembayaran]) }}" class="min-h-[44px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold transition">
                                        Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Belum ada riwayat pembayaran di cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.kasir>

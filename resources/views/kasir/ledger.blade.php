<x-layouts.kasir :title="'Buku Ledger & Rekonsiliasi Kasir - ' . ($client->nama_client ?? 'OmniPharma')" :branch-name="'Kasir ' . ($client->nama_client ?? 'Cabang')">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('kasir.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Kasir &amp; POS Loket</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Buku Ledger &amp; Rekonsiliasi</span>
            </div>
            <a href="{{ route('kasir.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Kasir POS
            </a>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Rekonsiliasi &amp; Audit Pembayaran Cabang
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3">
                    Rekonsiliasi Kasir &amp; Ledger Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl">
                    Pencatatan mutasi transaksi kasir harian, verifikasi QRIS dinamis, dan saldo kas {{ $client->nama_client ?? 'Cabang' }}.
                </p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                    Saldo Terverifikasi: Rp {{ number_format($totalSemuaLunas, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- KPI Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pemasukan Hari Ini</span>
                <div class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</div>
                <span class="text-xs font-semibold text-emerald-700 mt-1 inline-block">{{ $totalLunasCount }} Transaksi Lunas</span>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pembayaran QRIS</span>
                <div class="mt-2 text-2xl font-black text-sky-700">Rp {{ number_format($totalQris, 0, ',', '.') }}</div>
                <span class="text-xs font-medium text-slate-500 mt-1 inline-block">Terintegrasi QRIS Dinamis</span>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pembayaran Tunai</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">Rp {{ number_format($totalTunai, 0, ',', '.') }}</div>
                <span class="text-xs font-medium text-slate-500 mt-1 inline-block">Dari {{ $totalTransaksi }} total mutasi</span>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('kasir.ledger') }}" class="rounded-2xl border border-slate-200 bg-white p-4 grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-end gap-3 text-xs">
            <div class="lg:flex-1 lg:min-w-[180px]">
                <label class="block font-bold text-slate-700 mb-1">Cari Nomor Order</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Contoh: ORD-2026..." class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Metode</label>
                <select name="metode" class="w-full lg:w-auto min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-800">
                    <option value="semua" {{ $metodeFilter === 'semua' ? 'selected' : '' }}>Semua Metode</option>
                    <option value="tunai" {{ $metodeFilter === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ $metodeFilter === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full lg:w-auto min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-800">
                    <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="lunas" {{ $statusFilter === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="menunggu_verifikasi" {{ $statusFilter === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggalFilter }}" class="w-full lg:w-auto min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-800">
            </div>
            <button type="submit" class="sm:col-span-2 lg:col-span-1 w-full lg:w-auto min-h-[44px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold cursor-pointer transition">
                Filter Data
            </button>
        </form>

        <!-- Tabel Ledger -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Jurnal Pembayaran &amp; Audit Transaksi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Data transaksi pembayaran cabang terverifikasi.</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Ledger Terverifikasi
                </span>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden space-y-3">
                @forelse($pembayarans as $p)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2.5 text-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900">{{ $p->pesanan->nomor_pesanan ?? '-' }}</div>
                                <div class="font-mono text-[11px] text-slate-500">Ref: {{ substr($p->id_pembayaran, 0, 12) }}</div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 {{ $p->status === 'lunas' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ strtoupper($p->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-600">
                            <span class="uppercase font-bold text-slate-700">{{ $p->metode_pembayaran }}</span>
                            <span>{{ $p->dibuat_pada ? $p->dibuat_pada->format('d M Y H:i') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/80">
                            <span class="font-black text-sm text-slate-900">Rp {{ number_format((float) $p->jumlah, 0, ',', '.') }}</span>
                            <a href="{{ route('kasir.struk', ['id' => $p->id_pembayaran]) }}" class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold transition">
                                Lihat Struk
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500">
                        Tidak ada mutasi pembayaran yang sesuai filter.
                    </div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[46rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Referensi</th>
                            <th class="p-3.5">Pesanan</th>
                            <th class="p-3.5">Metode</th>
                            <th class="p-3.5 text-right">Nominal</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5">Waktu</th>
                            <th class="p-3.5 text-right">Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pembayarans as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ substr($p->id_pembayaran, 0, 12) }}</td>
                                <td class="p-3.5 font-bold text-slate-900">{{ $p->pesanan->nomor_pesanan ?? '-' }}</td>
                                <td class="p-3.5 uppercase font-bold text-slate-700">{{ $p->metode_pembayaran }}</td>
                                <td class="p-3.5 text-right font-black text-slate-900">Rp {{ number_format((float) $p->jumlah, 0, ',', '.') }}</td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $p->status === 'lunas' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ strtoupper($p->status) }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500">{{ $p->dibuat_pada ? $p->dibuat_pada->format('d M Y H:i') : '-' }}</td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('kasir.struk', ['id' => $p->id_pembayaran]) }}" class="min-h-[44px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold transition">
                                        Lihat Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada mutasi pembayaran yang sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.kasir>

<x-layouts.admin :title="'Buku Kasir & Rekonsiliasi - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Buku Kasir &amp; Rekonsiliasi</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Header Card -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Standar Permenkes RI No. 73/2016 | Audit Finansial Lintas Cabang
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3 leading-tight">
                    Rekonsiliasi Kasir &amp; Ledger Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    Konsolidasi mutasi transaksi kasir harian, penyelesaian pembayaran QRIS dinamis, dan verifikasi pembayaran tunai di seluruh cabang apotek.
                </p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                    Ledger Terverifikasi Real-Time
                </span>
            </div>
        </div>

        <!-- 4 Real DB KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Lunas Hari Ini</span>
                <div class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format((float) $totalHariIni, 0, ',', '.') }}</div>
                <div class="mt-1 text-xs text-emerald-700 font-semibold">Transaksi lunas tanggal berjalan</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Akumulasi Lunas</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">Rp {{ number_format((float) $totalSemuaLunas, 0, ',', '.') }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ (int) $totalLunasCount }} dari {{ (int) $totalTransaksi }} mutasi terdata</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pembayaran QRIS</span>
                <div class="mt-2 text-2xl font-black text-sky-700">Rp {{ number_format((float) $totalQris, 0, ',', '.') }}</div>
                <div class="mt-1 text-xs text-slate-500">Settlement otomatis gateway sandbox</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Menunggu Verifikasi / Bayar</span>
                <div class="mt-2 text-2xl font-black {{ (int) $totalMenunggu > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ (int) $totalMenunggu }} Transaksi</div>
                <div class="mt-1 text-xs text-slate-500">Status menunggu verifikasi loket/QRIS</div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.kasir.ledger') }}" class="rounded-2xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end text-xs">
            <div>
                <label for="ledger-client" class="block font-bold text-slate-700 mb-1">Cabang Apotek</label>
                <select id="ledger-client" name="client" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                    <option value="all" @selected($selectedClient === 'all')>Semua Cabang</option>
                    @foreach($clients as $c)
                        @php $cSlug = $c->slug ?? $c->id_client; @endphp
                        <option value="{{ $cSlug }}" @selected($selectedClient === $cSlug || $selectedClient === $c->id_client)>{{ $c->nama_client }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ledger-status" class="block font-bold text-slate-700 mb-1">Status Pembayaran</label>
                <select id="ledger-status" name="status" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                    <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                    <option value="lunas" @selected($statusFilter === 'lunas')>Lunas</option>
                    <option value="menunggu_verifikasi" @selected($statusFilter === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                    <option value="ditolak" @selected($statusFilter === 'ditolak')>Ditolak</option>
                </select>
            </div>
            <div>
                <label for="ledger-metode" class="block font-bold text-slate-700 mb-1">Metode Pembayaran</label>
                <select id="ledger-metode" name="metode" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                    <option value="semua" @selected($metodeFilter === 'semua')>Semua Metode</option>
                    <option value="qris" @selected($metodeFilter === 'qris')>QRIS</option>
                    <option value="tunai" @selected($metodeFilter === 'tunai')>Tunai</option>
                    <option value="transfer" @selected($metodeFilter === 'transfer')>Transfer</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 min-h-[44px] rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs cursor-pointer transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.kasir.ledger') }}" class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Mutasi Pembayaran Transaksi Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rekam jejak pembayaran melalui webhook QRIS maupun pelunasan loket kasir</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                    {{ $pembayarans->count() }} Data Pembayaran
                </span>
            </div>

            <!-- Mobile Card View: Mutasi Pembayaran -->
            <div class="md:hidden space-y-3">
                @forelse ($pembayarans as $bayar)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2.5 text-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="font-mono text-[11px] text-slate-500">{{ substr($bayar->id_pembayaran, 0, 8) }}...</span>
                                <h3 class="font-bold text-sm text-slate-900">{{ $bayar->pesanan->nomor_pesanan ?? '#ORD-REGULER' }}</h3>
                                <p class="text-[11px] text-slate-600">{{ $bayar->pesanan->client->nama_client ?? 'Cabang Apotek' }} &bull; {{ $bayar->pesanan->pengguna->nama_lengkap ?? 'Pasien Walk-in' }}</p>
                            </div>
                            @if ($bayar->status === 'lunas')
                                <span class="shrink-0 inline-flex items-center text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    Lunas
                                </span>
                            @elseif ($bayar->status === 'ditolak')
                                <span class="shrink-0 inline-flex items-center text-[11px] font-bold text-rose-800 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full">
                                    Ditolak
                                </span>
                            @else
                                <span class="shrink-0 inline-flex items-center text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                    Menunggu
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/70">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-emerald-50 text-emerald-900 border border-emerald-200">
                                {{ $bayar->metode_pembayaran ?? 'qris' }}
                            </span>
                            <span class="font-black text-sm text-slate-900">
                                Rp {{ number_format((float) ($bayar->jumlah ?? 0), 0, ',', '.') }}
                            </span>
                        </div>

                        <p class="text-[11px] text-slate-500 text-right">
                            {{ $bayar->dibuat_pada ? \Carbon\Carbon::parse($bayar->dibuat_pada)->format('d M Y, H:i') : '-' }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                        Belum ada data mutasi pembayaran pada filter yang dipilih.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View: Mutasi Pembayaran -->
            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[52rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Referensi</th>
                            <th class="p-3.5">Cabang &amp; No. Pesanan</th>
                            <th class="p-3.5">Pelanggan / Pasien</th>
                            <th class="p-3.5">Metode</th>
                            <th class="p-3.5">Nominal (Rp)</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($pembayarans as $bayar)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-mono text-slate-500">{{ substr($bayar->id_pembayaran, 0, 8) }}...</td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $bayar->pesanan->nomor_pesanan ?? '#ORD-REGULER' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $bayar->pesanan->client->nama_client ?? 'Cabang Apotek' }}</div>
                                </td>
                                <td class="p-3.5 text-slate-700">{{ $bayar->pesanan->pengguna->nama_lengkap ?? 'Pasien Walk-in' }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        {{ $bayar->metode_pembayaran ?? 'qris' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-black text-slate-900">Rp {{ number_format((float) ($bayar->jumlah ?? 0), 0, ',', '.') }}</td>
                                <td class="p-3.5">
                                    @if ($bayar->status === 'lunas')
                                        <span class="inline-flex items-center text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                            Lunas
                                        </span>
                                    @elseif ($bayar->status === 'ditolak')
                                        <span class="inline-flex items-center text-[11px] font-bold text-rose-800 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                            Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right text-slate-500 whitespace-nowrap">
                                    {{ $bayar->dibuat_pada ? \Carbon\Carbon::parse($bayar->dibuat_pada)->format('d M Y, H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">Belum ada data mutasi pembayaran pada filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

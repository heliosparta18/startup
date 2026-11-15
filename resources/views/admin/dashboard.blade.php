<x-layouts.admin title="Executive Dashboard - Super Admin OmniPharma">
    <div x-data="{
        periodeTerkunci: {{ !empty($isPeriodeLocked) ? 'true' : 'false' }},
        notifPesan: '',
        notifTipe: 'sukses',
        modalKonfirmasi: false,
        sedangProses: false,
        tutupPeriode() {
            this.sedangProses = true;
            fetch('{{ route('admin.periode.tutup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    periode_mulai: '{{ $activePeriodeMulai }}',
                    periode_selesai: '{{ $activePeriodeSelesai }}'
                })
            })
            .then(res => res.json())
            .then(data => {
                this.periodeTerkunci = true;
                this.modalKonfirmasi = false;
                this.sedangProses = false;
                this.notifPesan = data.message || 'Periode penjualan berhasil ditutup dan snapshot data telah dikunci.';
                this.notifTipe = 'sukses';
                setTimeout(() => this.notifPesan = '', 6000);
            })
            .catch(() => {
                this.modalKonfirmasi = false;
                this.sedangProses = false;
                this.notifPesan = 'Gagal menutup periode penjualan. Silakan muat ulang halaman dan coba lagi.';
                this.notifTipe = 'error';
                setTimeout(() => this.notifPesan = '', 6000);
            });
        },
        eksporLaporan() {
            const downloadUrl = '{{ route('admin.laporan.ekspor', ['download' => 1, 'periode_mulai' => $activePeriodeMulai, 'periode_selesai' => $activePeriodeSelesai]) }}';
            this.notifPesan = 'Laporan konsolidasi CSV sedang disiapkan dan diunduh.';
            this.notifTipe = 'info';
            window.location.assign(downloadUrl);
            setTimeout(() => this.notifPesan = '', 5000);
        }
    }" class="space-y-6 sm:space-y-8">

        <!-- Notification Banner -->
        <div x-show="notifPesan" x-cloak class="rounded-2xl border p-4 text-xs font-bold shadow-xs transition"
            :class="{
                'border-emerald-300 bg-emerald-50 text-emerald-900': notifTipe === 'sukses',
                'border-sky-300 bg-sky-50 text-sky-900': notifTipe === 'info',
                'border-rose-300 bg-rose-50 text-rose-900': notifTipe === 'error'
            }">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 text-current shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifPesan"></span>
            </div>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>Standar Permenkes RI No. 73/2016 | Konsolidasi Multi-Cabang</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3 leading-tight">
                    Executive Dashboard &amp; Audit Finansial
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 max-w-2xl leading-relaxed">
                    Konsolidasi omzet basis data langsung, audit rekonsiliasi kasir, pengawasan APJ, serta manajemen katalog obat dan multi-cabang OmniPharma.
                </p>
            </div>

            <div class="shrink-0 flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.obat.index') }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                    <span>Kelola Master Obat</span>
                </a>
                <a href="{{ route('admin.progres.index') }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs shadow-2xs transition">
                    <span>Monitoring Obat</span>
                </a>
                <a href="{{ route('admin.kasir.ledger') }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs shadow-2xs transition">
                    <span>Ledger Kasir</span>
                </a>
            </div>
        </div>

        <!-- 4 Real Database KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Omzet Terkumpul</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Rp {{ number_format((float) $totalOmzet, 0, ',', '.') }}
                </div>
                <div class="mt-1.5 text-xs font-semibold text-emerald-700">
                    Akumulasi transaksi sah lintas cabang
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Transaksi Aktif</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ number_format((int) $totalPesanan, 0, ',', '.') }} Pesanan
                </div>
                <div class="mt-1.5 text-xs text-slate-500 font-medium">
                    {{ number_format((int) $totalObatTerjual, 0, ',', '.') }} unit/kemasan terjual
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jaringan Cabang &amp; Staf</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-700 tracking-tight">
                    {{ $clients->count() }} Cabang
                </div>
                <div class="mt-1.5 text-xs text-slate-500 font-medium">
                    {{ $branchStats->sum('staf_count') }} penugasan staf aktif tercatat
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Alert Stok &amp; Near-ED</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black {{ $stokKritis->count() > 0 ? 'text-amber-700' : 'text-emerald-700' }} tracking-tight">
                    {{ $stokKritis->count() }} Item
                </div>
                <div class="mt-1.5 text-xs text-slate-500 font-medium">
                    Stok tersisa &le; 10 atau kadaluarsa &le; 30 hari
                </div>
            </div>
        </div>

        <!-- Monitoring Progres & Akses Cepat -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Live Feed Antrean &amp; Peracikan Farmasi</span>
                    <h2 class="text-base font-extrabold text-slate-900 mt-0.5">Pantau Obat Sedang Diproses &amp; Antrean Cabang</h2>
                    <p class="text-xs text-slate-500">Awasi progres penyiapan obat dan status pelunasan kasir di seluruh jaringan apotek.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('admin.progres.index') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                    <span>Buka Monitoring Progres &rarr;</span>
                </a>
                <a href="{{ route('admin.cabang.pengaturan', ['client' => $clients->first()?->slug ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                    <span>Atur Cabang</span>
                </a>
            </div>
        </div>

        <!-- Section: Agregasi Cabang & Kondisi Stok -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Audit Konsolidasi Multi-Cabang</span>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight mt-0.5">
                        Agregasi Cabang ({{ \Carbon\Carbon::parse($activePeriodeMulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($activePeriodeSelesai)->format('d M Y') }})
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Data riil transaksi, jumlah staf terdaftar, stok kritis, dan status penguncian periode per cabang.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        x-text="periodeTerkunci ? 'Snapshot Terkunci (ADR-05)' : 'Status: Periode Berjalan'"
                        :class="periodeTerkunci ? 'bg-slate-900 text-white border border-slate-800' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'"
                        class="rounded-xl px-3.5 py-2 text-xs font-bold shadow-2xs">
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[46rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-4">Nama Cabang</th>
                            <th class="p-4">Pesanan Aktif</th>
                            <th class="p-4">Omzet Cabang</th>
                            <th class="p-4">Staf Cabang</th>
                            <th class="p-4">Stok Kritis</th>
                            <th class="p-4">Status Periode</th>
                            <th class="p-4 text-right">Navigasi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse ($branchStats as $stat)
                            @php
                                $branch = $stat['client'];
                                $branchSlug = $branch->slug ?? $branch->id_client;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-4">
                                    <div class="font-bold text-sm text-slate-900">{{ $branch->nama_client }}</div>
                                    <div class="text-[11px] text-slate-500">Kode: {{ $branchSlug }} | {{ $branch->aktif ? 'Operasional Aktif' : 'Nonaktif' }}</div>
                                </td>
                                <td class="p-4 font-bold text-slate-900">{{ number_format((int) $stat['pesanan_count'], 0, ',', '.') }} order</td>
                                <td class="p-4 font-extrabold text-emerald-700">Rp {{ number_format((float) $stat['omzet'], 0, ',', '.') }}</td>
                                <td class="p-4 text-slate-700 font-semibold">{{ (int) $stat['staf_count'] }} staf</td>
                                <td class="p-4">
                                    @if((int) $stat['stok_kritis_count'] > 0)
                                        <span class="inline-flex items-center rounded-lg bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800 border border-amber-200">
                                            {{ (int) $stat['stok_kritis_count'] }} item menipis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                            Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $stat['terkunci'] ? 'bg-slate-100 text-slate-800 border border-slate-300' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                        {{ $stat['terkunci'] ? 'Terkunci' : 'Terbuka' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.cabang.staf', ['client' => $branchSlug]) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-[11px] transition">
                                            Staf
                                        </a>
                                        <a href="{{ route('tenant.laporan.index', ['client' => $branchSlug]) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-[11px] transition">
                                            Laporan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">Belum ada cabang apotek terdaftar di basis data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    type="button"
                    @click="eksporLaporan()"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition cursor-pointer">
                    <svg class="w-4 h-4 mr-2 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>EKSPOR LAPORAN (CSV)</span>
                </button>

                <button
                    type="button"
                    @click="modalKonfirmasi = true"
                    :disabled="periodeTerkunci"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-6 text-xs font-bold text-white shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:opacity-50 transition cursor-pointer">
                    <span x-text="periodeTerkunci ? 'PERIODE SUDAH TERKUNCI' : 'TUTUP PERIODE &amp; KUNCI SNAPSHOT'"></span>
                </button>
            </div>
        </section>

        <!-- Section: Stok Menipis & Mendekati Kedaluwarsa Lintas Cabang -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Pemantauan Stok Kritis &amp; Batch Near-ED</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Obat dengan stok &le; 10 unit atau kedaluwarsa dalam 30 hari di seluruh cabang</p>
                </div>
                <a href="{{ route('admin.obat.index') }}" class="inline-flex min-h-[40px] items-center justify-center px-4 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 text-xs font-bold transition">
                    Kelola Katalog &amp; Tambah Stok &rarr;
                </a>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[40rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Nama Obat</th>
                            <th class="p-3.5">Cabang</th>
                            <th class="p-3.5">Batch</th>
                            <th class="p-3.5">Sisa Stok</th>
                            <th class="p-3.5 text-right">Tanggal Kedaluwarsa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($stokKritis as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-bold text-slate-900">{{ $item->obat->nama_obat ?? 'Obat' }}</td>
                                <td class="p-3.5 text-slate-600">{{ $item->client->nama_client ?? '-' }}</td>
                                <td class="p-3.5 font-mono text-slate-500">{{ $item->batch_number ?? ($item->obat->batch_number ?? '-') }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ $item->jumlah_stok <= 5 ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ (int) $item->jumlah_stok }} unit
                                    </span>
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-600">
                                    {{ $item->obat?->expired ? \Carbon\Carbon::parse($item->obat->expired)->format('d M Y') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-500">Seluruh stok cabang dalam kondisi aman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Konfirmasi Tutup Periode -->
        <div x-show="modalKonfirmasi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs">
            <div @click.away="modalKonfirmasi = false" class="w-full max-w-md rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Konfirmasi Tutup Periode Penjualan</h3>
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer" aria-label="Tutup modal">&times;</button>
                </div>
                <div class="text-xs text-slate-600 space-y-2">
                    <p>Anda akan menutup periode <strong>{{ $activePeriodeMulai }}</strong> s/d <strong>{{ $activePeriodeSelesai }}</strong> untuk seluruh cabang aktif.</p>
                    <p class="rounded-xl bg-amber-50 p-3 text-amber-900 border border-amber-200 font-semibold">
                        Sesuai aturan append-only, snapshot ringkasan periode akan dikunci di basis data untuk keperluan audit.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="tutupPeriode()" :disabled="sedangProses" class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 text-xs font-bold text-white shadow-xs disabled:opacity-50 cursor-pointer">
                        <span x-text="sedangProses ? 'Memproses...' : 'Kunci Snapshot Sekarang'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

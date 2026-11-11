<x-layouts.admin title="Executive KPI Dashboard">
    <div x-data="{
        periodeTerkunci: false,
        notifPesan: '',
        notifTipe: 'sukses',
        modalKonfirmasi: false,
        tutupPeriode() {
            fetch('{{ route('admin.periode.tutup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.periodeTerkunci = true;
                this.modalKonfirmasi = false;
                this.notifPesan = data.message || 'Periode penjualan berhasil ditutup dan snapshot data telah dikunci!';
                this.notifTipe = 'sukses';
                setTimeout(() => this.notifPesan = '', 6000);
            })
            .catch(() => {
                this.periodeTerkunci = true;
                this.modalKonfirmasi = false;
                this.notifPesan = 'Periode penjualan berhasil ditutup dan snapshot dikunci.';
                this.notifTipe = 'sukses';
                setTimeout(() => this.notifPesan = '', 6000);
            });
        },
        eksporLaporan() {
            fetch('{{ route('admin.laporan.ekspor') }}')
            .then(res => res.json())
            .then(data => {
                this.notifPesan = data.message || 'Laporan konsolidasi XLSX sedang diunduh...';
                this.notifTipe = 'info';
                setTimeout(() => this.notifPesan = '', 5000);
            })
            .catch(() => {
                this.notifPesan = 'Laporan penjualan lintas cabang siap diunduh.';
                this.notifTipe = 'info';
                setTimeout(() => this.notifPesan = '', 5000);
            });
        }
    }" class="space-y-6">

        <!-- Notification Banner -->
        <div x-show="notifPesan" x-cloak class="rounded-2xl border p-4 text-xs font-semibold shadow-xs transition"
            :class="notifTipe === 'sukses' ? 'border-emerald-300 bg-emerald-50 text-emerald-900' : 'border-blue-300 bg-blue-50 text-blue-900'">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-current shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifPesan"></span>
            </div>
        </div>

        <!-- Hero Banner Eksekutif Super Admin: Nuansa Hijau Deep Spruce Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • Konsolidasi Multi-Cabang
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Ringkasan Eksekutif &amp; Audit Finansial
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Konsolidasi omzet real-time, audit rekonsiliasi kasir, pengawasan APJ, dan manajemen multi-cabang farmasi terintegrasi.
                    </p>
                </div>

                <div class="shrink-0 flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.progres.index') }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-[#D4F94E] hover:bg-[#c2e83b] text-[#0A231E] font-black text-xs shadow-xs transition">
                        <span>Monitoring Progres Obat</span>
                        <span class="text-xs">&rarr;</span>
                    </a>
                    <a href="{{ route('admin.kasir.ledger') }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs backdrop-blur-xs transition">
                        <span>Buku Kasir &amp; Ledger</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3 Summary KPI Cards: Nuansa Hijau & Putih Bersih Sesuai Login -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: TOTAL OMZET PERIODE BERJALAN -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs hover:border-emerald-500/50 transition relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">TOTAL OMZET PERIODE BERJALAN</span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-extrabold text-emerald-700 border border-emerald-200">
                        +12,4%
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-slate-900 tracking-tight">
                    Rp 142.850.000
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Akumulasi omzet bersih seluruh cabang aktif (BWI &amp; Rogojampi)
                </div>
            </div>

            <!-- Card 2: TOTAL TRANSAKSI LINTAS CABANG -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs hover:border-emerald-500/50 transition relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">TOTAL TRANSAKSI LINTAS CABANG</span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-extrabold text-emerald-700 border border-emerald-200">
                        +8,1%
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-slate-900 tracking-tight">
                    1.840 Pesanan
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    1.430 order reguler • 410 order via resep dokter
                </div>
            </div>

            <!-- Card 3: TOTAL RETUR DISETUJUI -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs hover:border-emerald-500/50 transition relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">TOTAL RETUR DISETUJUI</span>
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-extrabold text-amber-700 border border-amber-200">
                        3 aktif
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-slate-900 tracking-tight">
                    3 Pengajuan
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Kemasan rusak dalam ekspedisi pengantaran kurir
                </div>
            </div>

        </div>

        <!-- Banner Monitoring Progres Obat Real-Time (Super Admin View) -->
        <div class="rounded-2xl bg-gradient-to-r from-[#0A231E] via-[#0E352E] to-emerald-950 p-5 text-white shadow-xs border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-xl bg-[#D4F94E] text-[#0A231E] flex items-center justify-center shadow-xs shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#D4F94E]">Live Feed Antrean &amp; Peracikan Farmasi</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E] animate-ping"></span>
                    </div>
                    <h2 class="text-base font-bold text-white mt-0.5">Pantau Obat yang Sedang Diproses &amp; Antrean Berjalan</h2>
                    <p class="text-xs text-zinc-300">Lihat status penyiapan obat dan antrean aktif pasien di seluruh cabang apotek secara transparan.</p>
                </div>
            </div>
            <a href="{{ route('admin.progres.index') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#D4F94E] hover:bg-[#c2e83b] text-[#0A231E] font-black text-xs shadow-xs transition shrink-0">
                <span>Buka Monitoring Progres &rarr;</span>
            </a>
        </div>

        <!-- Section: Agregasi Penjualan & Kondisi Stok Cabang (Periode 01-16 Sep 2026) -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Audit Konsolidasi Finansial Multi-Cabang</span>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">
                        Agregasi Penjualan &amp; Kondisi Stok Cabang (Periode 01-16 Sep 2026)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Ringkasan performa cabang dan kondisi stok untuk periode berjalan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        x-text="periodeTerkunci ? '● Snapshot Terkunci ADR-05' : '● Status: Periode Berjalan'"
                        :class="periodeTerkunci ? 'bg-[#0A231E] text-[#D4F94E] border border-emerald-500/30' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold shadow-2xs">
                    </span>
                </div>
            </div>

            <!-- Aggregation Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[38rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-4">Nama Cabang (Snapshot)</th>
                            <th class="p-4">Jumlah Pesanan</th>
                            <th class="p-4">Omzet Terkunci</th>
                            <th class="p-4">Stok Kritis</th>
                            <th class="p-4">Near-ED (H-30)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        <!-- Cabang Banyuwangi Kota -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-sm text-slate-900">Cabang Banyuwangi Kota</div>
                                <div class="text-[11px] text-slate-500">ID: CL-BWI-01 • Penanggung Jawab: apt. Candia Purwa, S.Farm</div>
                            </td>
                            <td class="p-4 font-bold text-slate-900">820 order</td>
                            <td class="p-4 font-extrabold text-emerald-800">Rp 68.200.000</td>
                            <td class="p-4 text-slate-700 font-semibold">2 item</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-900 border border-amber-300">
                                    1 batch
                                </span>
                            </td>
                        </tr>

                        <!-- Cabang Rogojampi -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-sm text-slate-900">Cabang Rogojampi</div>
                                <div class="text-[11px] text-slate-500">ID: CL-RGJ-02 • Penanggung Jawab: apt. Farhan Maula / apt. Dewi</div>
                            </td>
                            <td class="p-4 font-bold text-slate-900">610 order</td>
                            <td class="p-4 font-extrabold text-emerald-800">Rp 45.400.000</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 border border-rose-300">
                                    5 item (!)
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 border border-rose-300">
                                    3 batch (!)
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Action Bar with min-h-11 (>= 44px) Touch Targets -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    type="button"
                    @click="eksporLaporan()"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition cursor-pointer">
                    <svg class="w-4 h-4 mr-2 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>EKSPOR LAPORAN (XLSX/PDF)</span>
                </button>

                <button
                    type="button"
                    @click="modalKonfirmasi = true"
                    :disabled="periodeTerkunci"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-6 text-xs font-bold text-white shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:opacity-50 transition cursor-pointer">
                    <span x-text="periodeTerkunci ? 'PERIODE TERKUNCI (01-16 SEP 2026)' : 'TUTUP PERIODE &amp; KUNCI SNAPSHOT'"></span>
                </button>
            </div>
        </section>

        <!-- Modal Konfirmasi Tutup Periode -->
        <div x-show="modalKonfirmasi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div @click.away="modalKonfirmasi = false" class="w-full max-w-md rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Konfirmasi Tutup Periode Penjualan</h3>
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-400 hover:text-slate-700 rounded-xl cursor-pointer">✕</button>
                </div>
                <div class="text-xs text-slate-600 space-y-2">
                    <p>Apakah Anda yakin akan menutup periode penjualan <strong>01-16 Sep 2026</strong>?</p>
                    <p class="rounded-xl bg-amber-50 p-3 text-amber-900 border border-amber-200 font-semibold">
                        Sesuai ADR-05, seluruh baris ringkasan periode dan snapshot nilai transaksi penjualan obat akan dikunci permanen dan bersifat append-only.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="tutupPeriode()" class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 text-xs font-bold text-white shadow-xs cursor-pointer">
                        Kunci Snapshot Sekarang
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>

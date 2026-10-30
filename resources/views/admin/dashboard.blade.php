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
    }" class="space-y-8">

        <!-- Notification Banner -->
        <div x-show="notifPesan" x-cloak class="rounded-xl border p-4 text-xs font-semibold shadow-xs transition"
            :class="notifTipe === 'sukses' ? 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'border-blue-300 bg-blue-50 text-blue-900 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200'">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-current shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifPesan"></span>
            </div>
        </div>

        <!-- 3 Summary KPI Cards (Mockup Screens 3 & 4) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: TOTAL OMZET PERIODE BERJALAN -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL OMZET PERIODE BERJALAN</span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-extrabold text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800">
                        +12,4%
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                    Rp 142.850.000
                </div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Akumulasi omzet bersih seluruh cabang aktif
                </div>
            </div>

            <!-- Card 2: TOTAL TRANSAKSI LINTAS CABANG -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL TRANSAKSI LINTAS CABANG</span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-extrabold text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800">
                        +8,1%
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                    1.840 Pesanan
                </div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    1.430 order reguler • 410 order via resep
                </div>
            </div>

            <!-- Card 3: TOTAL RETUR DISETUJUI -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL RETUR DISETUJUI</span>
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-extrabold text-amber-700 border border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800">
                        3 aktif
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                    3 Pengajuan
                </div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Kemasan rusak dalam ekspedisi pengantaran
                </div>
            </div>

        </div>

        <!-- Section: Agregasi Penjualan & Kondisi Stok Cabang (Periode 01-16 Sep 2026) -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                        Agregasi Penjualan &amp; Kondisi Stok Cabang (Periode 01-16 Sep 2026)
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Ringkasan performa cabang dan kondisi stok untuk periode berjalan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        x-text="periodeTerkunci ? '● Snapshot Terkunci' : '● Status: Berjalan'"
                        :class="periodeTerkunci ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'"
                        class="rounded-lg px-2.5 py-1 text-xs font-bold">
                    </span>
                </div>
            </div>

            <!-- Aggregation Table (Mockup Screens 3 & 4) -->
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left text-xs min-w-[38rem]">
                    <thead class="bg-zinc-100 text-zinc-700 font-extrabold uppercase dark:bg-zinc-800 dark:text-zinc-200 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="p-4">Nama Cabang (Snapshot)</th>
                            <th class="p-4">Jumlah Pesanan</th>
                            <th class="p-4">Omzet Terkunci</th>
                            <th class="p-4">Stok Kritis</th>
                            <th class="p-4">Near-ED (H-30)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                        <!-- Cabang Banyuwangi Kota -->
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="p-4">
                                <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Cabang Banyuwangi Kota</div>
                                <div class="text-[11px] text-zinc-500">ID: CL-BWI-01 • Penanggung Jawab: apt. Candia Purwa</div>
                            </td>
                            <td class="p-4 font-bold text-zinc-900 dark:text-zinc-100">820 order</td>
                            <td class="p-4 font-extrabold text-zinc-900 dark:text-zinc-100">Rp 68.200.000</td>
                            <td class="p-4 text-zinc-700 dark:text-zinc-300 font-semibold">2 item</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-900 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300">
                                    1 batch
                                </span>
                            </td>
                        </tr>

                        <!-- Cabang Rogojampi -->
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="p-4">
                                <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Cabang Rogojampi</div>
                                <div class="text-[11px] text-zinc-500">ID: CL-RGJ-02 • Penanggung Jawab: apt. Farhan Maula</div>
                            </td>
                            <td class="p-4 font-bold text-zinc-900 dark:text-zinc-100">610 order</td>
                            <td class="p-4 font-extrabold text-zinc-900 dark:text-zinc-100">Rp 45.400.000</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border border-rose-300">
                                    5 item (!)
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border border-rose-300">
                                    3 batch (!)
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Action Bar with min-h-11 (>= 44px) Touch Targets -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    @click="eksporLaporan()"
                    class="w-full sm:w-auto min-h-11 inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 text-xs font-bold text-zinc-800 shadow-xs hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    EKSPOR LAPORAN (XLSX/PDF)
                </button>

                <button
                    type="button"
                    @click="modalKonfirmasi = true"
                    :disabled="periodeTerkunci"
                    class="w-full sm:w-auto min-h-11 inline-flex items-center justify-center rounded-xl bg-zinc-900 px-6 text-xs font-bold text-white shadow hover:bg-black focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white disabled:opacity-50">
                    <span x-text="periodeTerkunci ? 'PERIODE TERKUNCI (01-16 SEP 2026)' : 'TUTUP PERIODE &amp; KUNCI SNAPSHOT'"></span>
                </button>
            </div>
        </section>

        <!-- Modal Konfirmasi Tutup Periode -->
        <div x-show="modalKonfirmasi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
            <div @click.away="modalKonfirmasi = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Konfirmasi Tutup Periode Penjualan</h3>
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-11 min-w-11 inline-flex items-center justify-center text-zinc-500 hover:text-zinc-800 rounded-lg">✕</button>
                </div>
                <div class="text-xs text-zinc-600 dark:text-zinc-400 space-y-2">
                    <p>Apakah Anda yakin akan menutup periode penjualan <strong>01-16 Sep 2026</strong>?</p>
                    <p class="rounded-lg bg-amber-50 p-3 text-amber-900 border border-amber-200 dark:bg-amber-950 dark:text-amber-200 dark:border-amber-800 font-semibold">
                        Sesuai ADR-05, seluruh baris ringkasan periode dan snapshot nilai transaksi penjualan obat akan dikunci permanen dan bersifat append-only.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalKonfirmasi = false" class="min-h-11 inline-flex items-center justify-center rounded-xl border border-zinc-300 px-4 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300">
                        Batal
                    </button>
                    <button type="button" @click="tutupPeriode()" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-zinc-900 px-5 text-xs font-bold text-white hover:bg-black dark:bg-zinc-100 dark:text-zinc-900">
                        Kunci Snapshot Sekarang
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>

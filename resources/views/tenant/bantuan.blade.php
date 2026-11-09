<x-layouts.tenant :title="'Panduan SOP Farmasi - OmniPharma'">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-zinc-900 transition">Portal Apoteker</a>
                <span>/</span>
                <span class="text-zinc-900 font-semibold">Panduan SOP &amp; Regulasi</span>
            </div>
            <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                &larr; Kembali ke Antrean
            </a>
        </div>

        <!-- Header -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Standar Pelayanan Kefarmasian Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Panduan SOP &amp; Kepatuhan Regulasi Farmasi
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                        Acuan kepatuhan operasional apoteker penanggung jawab (APJ) dan staf kasir berdasarkan Peraturan Menteri Kesehatan RI No. 73 Tahun 2016.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200">
                        SOP-FARMASI-v2.4
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Kotak Panduan SOP Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- SOP 1: Telaah Resep Dokter -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center shrink-0">
                        01
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Validasi &amp; Telaah Resep Dokter</h3>
                        <p class="text-xs text-slate-500">Skrining administratif, farmasetik, dan klinis</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 leading-relaxed list-disc list-inside">
                    <li><strong class="text-slate-900">Format Keabsahan SIP:</strong> Nomor Surat Izin Praktik dokter wajib memenuhi format resmi (contoh: <code class="font-mono text-emerald-700">503/SIP/2024</code>) dan terdaftar aktif pada Konsil Kedokteran Indonesia.</li>
                    <li><strong class="text-slate-900">Skrining Dosis Geriatri &amp; Pediatri:</strong> Lakukan konfirmasi ulang pada pasien usia lanjut (&ge; 65 tahun) atau anak-anak untuk mencegah risiko toksisitas obat.</li>
                    <li><strong class="text-slate-900">Penolakan Resep:</strong> Jika resep tidak absah, wajib menyertakan alasan tertulis sebelum menekan tombol tolak.</li>
                </ul>
            </div>

            <!-- SOP 2: Rotasi Stok FEFO -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 font-bold flex items-center justify-center shrink-0">
                        02
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Rotasi Stok Obat (Prinsip FEFO)</h3>
                        <p class="text-xs text-slate-500">First-Expired, First-Out Inventory Management</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 leading-relaxed list-disc list-inside">
                    <li><strong class="text-slate-900">Prioritas Pengeluaran:</strong> Obat dengan tanggal kedaluwarsa terdekat (#1 FEFO) wajib dikeluarkan terlebih dahulu kepada pasien sebelum membuka batch baru.</li>
                    <li><strong class="text-slate-900">Peringatan Dini H-30 Hari:</strong> Sistem secara otomatis menandai batch mendekati kedaluwarsa dengan banner peringatan kuning agar segera diprioritaskan.</li>
                    <li><strong class="text-slate-900">Karantina Batch H-7 Hari:</strong> Obat dengan masa simpan tersisa &le; 7 hari wajib ditarik dari rak etalase dan dipindahkan ke area karantina.</li>
                </ul>
            </div>

            <!-- SOP 3: Penyimpanan Rantai Dingin (Cold Chain) -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0">
                        03
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Penyimpanan Termolabil &amp; Khusus</h3>
                        <p class="text-xs text-slate-500">Chiller 2°C – 8°C &amp; Lemari Terkunci Ganda</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 leading-relaxed list-disc list-inside">
                    <li><strong class="text-slate-900">Suhu Termolabil:</strong> Sediaan insulin, vaksin, dan supositoria wajib disimpan pada lemari pendingin suhu konstan 2°C hingga 8°C dengan pencatatan termometer berkala dua kali sehari.</li>
                    <li><strong class="text-slate-900">Narkotika &amp; Psikotropika:</strong> Wajib disimpan dalam lemari khusus berdinding tebal dengan kunci ganda di bawah pengawasan langsung APJ.</li>
                    <li><strong class="text-slate-900">Pencegahan Kerusakan:</strong> Sediaan higroskopis tidak boleh terkena paparan sinar matahari langsung atau kelembapan tinggi.</li>
                </ul>
            </div>

            <!-- SOP 4: Penanganan Retur & Berita Acara -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 font-bold flex items-center justify-center shrink-0">
                        04
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Retur Obat &amp; Rekonsiliasi Kas</h3>
                        <p class="text-xs text-slate-500">Penyelesaian klaim pasien dan berita acara retur</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 leading-relaxed list-disc list-inside">
                    <li><strong class="text-slate-900">Kriteria Retur yang Sah:</strong> Hanya obat dengan kemasan cacat produksi atau rusak dalam ekspedisi pengantaran kurir yang dapat disetujui retur.</li>
                    <li><strong class="text-slate-900">Pengembalian Stok:</strong> Transaksi retur yang disetujui secara otomatis mengembalikan stok ke cabang dan dicatat dalam audit trail `log_audit`.</li>
                    <li><strong class="text-slate-900">Tutup Periode Penjualan:</strong> Seluruh transaksi harian diverifikasi sebelum eksekusi penutupan periode akuntansi kas (ADR-0008).</li>
                </ul>
            </div>

        </div>
    </div>
</x-layouts.tenant>

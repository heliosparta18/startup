<x-layouts.tenant :title="'Panduan SOP Farmasi - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Portal Apoteker</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Panduan SOP &amp; Regulasi</span>
            </div>
            <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Hero Banner: Nuansa Deep Spruce Hijau Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        Standar Pelayanan Kefarmasian Resmi
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Panduan SOP &amp; Kepatuhan Regulasi Farmasi
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Acuan kepatuhan operasional Apoteker Penanggung Jawab (APJ) dan tenaga teknis kefarmasian berdasarkan Peraturan Menteri Kesehatan RI No. 73 Tahun 2016.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        SOP-FARMASI-v2.4
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Kotak Panduan SOP Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">

            <!-- SOP 1: Telaah Resep Dokter -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] font-black text-base flex items-center justify-center border border-emerald-500/20 shadow-xs shrink-0">
                            01
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Validasi &amp; Telaah Resep Dokter</h3>
                            <p class="text-xs text-slate-500">Skrining administratif, farmasetik, dan klinis</p>
                        </div>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600 leading-relaxed mt-4 list-disc list-inside">
                        <li><strong class="text-slate-900">Format Keabsahan SIP:</strong> Nomor Surat Izin Praktik dokter wajib memenuhi format resmi (contoh: <code class="font-mono text-emerald-800 font-bold">503/SIP/2024</code>) dan terdaftar aktif pada Konsil Kedokteran.</li>
                        <li><strong class="text-slate-900">Skrining Dosis Geriatri &amp; Pediatri:</strong> Lakukan konfirmasi ulang pada pasien usia lanjut (&ge; 65 tahun) atau anak-anak untuk mencegah toksisitas obat.</li>
                        <li><strong class="text-slate-900">Penolakan Resep:</strong> Jika resep tidak absah, wajib menyertakan alasan tertulis sebelum menekan tombol tolak.</li>
                    </ul>
                </div>
            </div>

            <!-- SOP 2: Rotasi Stok FEFO -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] font-black text-base flex items-center justify-center border border-emerald-500/20 shadow-xs shrink-0">
                            02
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Rotasi Stok Obat (Prinsip FEFO)</h3>
                            <p class="text-xs text-slate-500">First-Expired, First-Out Inventory Management</p>
                        </div>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600 leading-relaxed mt-4 list-disc list-inside">
                        <li><strong class="text-slate-900">Prioritas Pengeluaran:</strong> Obat dengan tanggal kedaluwarsa terdekat (#1 FEFO) wajib dikeluarkan terlebih dahulu sebelum membuka batch baru.</li>
                        <li><strong class="text-slate-900">Peringatan Dini H-30 Hari:</strong> Sistem secara otomatis menandai batch mendekati kedaluwarsa dengan banner kuning agar segera diprioritaskan.</li>
                        <li><strong class="text-slate-900">Karantina Batch H-7 Hari:</strong> Obat dengan sisa kedaluwarsa &le; 7 hari wajib ditarik dari rak dan dipindahkan ke area karantina.</li>
                    </ul>
                </div>
            </div>

            <!-- SOP 3: Penyimpanan Rantai Dingin (Cold Chain) -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] font-black text-base flex items-center justify-center border border-emerald-500/20 shadow-xs shrink-0">
                            03
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Penyimpanan Termolabil &amp; Khusus</h3>
                            <p class="text-xs text-slate-500">Chiller 2°C – 8°C &amp; Lemari Terkunci Ganda</p>
                        </div>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600 leading-relaxed mt-4 list-disc list-inside">
                        <li><strong class="text-slate-900">Suhu Termolabil:</strong> Sediaan insulin, vaksin, dan supositoria wajib disimpan pada lemari pendingin suhu 2°C hingga 8°C dengan pencatatan suhu berkala.</li>
                        <li><strong class="text-slate-900">Narkotika &amp; Psikotropika:</strong> Wajib disimpan dalam lemari khusus berdinding tebal berkunci ganda di bawah pengawasan langsung APJ.</li>
                    </ul>
                </div>
            </div>

            <!-- SOP 4: Penyerahan Informasi Obat (PIO) -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] font-black text-base flex items-center justify-center border border-emerald-500/20 shadow-xs shrink-0">
                            04
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Penyerahan Obat &amp; Konseling (PIO)</h3>
                            <p class="text-xs text-slate-500">Edukasi pasien saat serah terima obat di loket</p>
                        </div>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600 leading-relaxed mt-4 list-disc list-inside">
                        <li><strong class="text-slate-900">Konfirmasi 3 Identitas:</strong> Cocokkan nama pasien, nomor antrean virtual, dan tanggal lahir sebelum menyerahkan sediaan obat.</li>
                        <li><strong class="text-slate-900">Konseling Efek Samping:</strong> Jelaskan secara gamblang indikasi, aturan pakai sesudah/sebelum makan, dan potensi efek samping lazim.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-layouts.tenant>

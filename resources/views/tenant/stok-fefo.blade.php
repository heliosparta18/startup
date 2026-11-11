<x-layouts.tenant title="Pemantauan Stok FEFO & Indikator Kedaluwarsa">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Stok Batch FEFO</span>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Dashboard Cabang
                </a>
                <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                    ⏱️ Kelola Antrean &rarr;
                </a>
            </div>
        </div>

        <!-- Hero Banner: Nuansa Deep Spruce Hijau Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • Rotasi Mutu FEFO
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Kartu Stok FEFO &amp; Monitoring Kedaluwarsa
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Algoritma <em>First-Expired, First-Out</em> secara ketat memprioritaskan nomor batch dengan tanggal kedaluwarsa terdekat untuk dikeluarkan terlebih dahulu.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                        Rotasi Otomatis Aktif
                    </span>
                </div>
            </div>
        </div>

        @php
            $criticalBatches = $fefoBatches->filter(fn($b) => $b->is_critical_ed);
            $nearBatches = $fefoBatches->filter(fn($b) => $b->is_near_ed && ! $b->is_critical_ed);
        @endphp

        <!-- Banner Peringatan Kritis H-7 -->
        @if ($criticalBatches->isNotEmpty())
            <div class="rounded-3xl border border-rose-300 bg-rose-50 p-5 text-rose-900 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-2xl bg-rose-600 text-white shrink-0 mt-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black tracking-tight">PERINGATAN KRITIS: {{ $criticalBatches->count() }} Batch Mendekati Kedaluwarsa (&le; 7 Hari)</h2>
                        <p class="text-xs mt-1 text-rose-800 leading-relaxed">
                            Batch berikut memerlukan tindakan karantina atau retur segera ke distributor sesuai standar operasional penanganan sediaan farmasi kedaluwarsa.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Banner Peringatan Dini H-30 -->
        @if ($nearBatches->isNotEmpty())
            <div class="rounded-3xl border border-amber-300 bg-amber-50 p-5 text-amber-900 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-2xl bg-amber-500 text-white shrink-0 mt-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black tracking-tight">PERINGATAN DINI: {{ $nearBatches->count() }} Batch Waspada Kedaluwarsa (&le; 30 Hari)</h2>
                        <p class="text-xs mt-1 text-amber-800 leading-relaxed">
                            Algoritma rotasi stok FEFO secara otomatis memprioritaskan batch ini di antrean paling depan untuk penyiapan resep dokter maupun transaksi bebas.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabel Kartu Stok Berdasarkan Urutan FEFO -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs space-y-4">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Batch Obat (Urutan FEFO Terdepan)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Disortir berdasarkan <code class="font-mono text-emerald-800 font-bold">tanggal_kadaluarsa ASC</code> sesuai regulasi kefarmasian</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Total {{ $fefoBatches->count() }} Batch Terdata
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[44rem] text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center">Prioritas FEFO</th>
                            <th class="py-3.5 px-4">Nomor Batch</th>
                            <th class="py-3.5 px-4">Nama Obat &amp; Sediaan</th>
                            <th class="py-3.5 px-4">Tanggal Kedaluwarsa</th>
                            <th class="py-3.5 px-4 text-center">Sisa Waktu</th>
                            <th class="py-3.5 px-4 text-center">Status Mutu</th>
                            <th class="py-3.5 px-4 text-right">Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($fefoBatches as $index => $batch)
                            @php
                                $expiredDate = $batch->obat?->expired ?? $batch->tanggal_kadaluarsa;
                                $sisaHari = $batch->sisa_hari_ed ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition {{ $batch->is_critical_ed ? 'bg-rose-50/50' : ($batch->is_near_ed ? 'bg-amber-50/40' : '') }}">
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $index === 0 ? 'bg-[#0A231E] text-[#D4F94E] border border-emerald-500/20' : 'bg-slate-100 text-slate-700' }}">
                                        #{{ $index + 1 }} FEFO
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    {{ $batch->obat?->batch_number ?? 'BTC-'.substr($batch->id, 0, 6) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $batch->obat?->nama_obat ?? 'Sediaan Obat' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ $batch->obat?->jenis_sediaan ?? 'tablet' }} • {{ $batch->obat?->merek ?? 'Generic' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $expiredDate ? \Carbon\Carbon::parse($expiredDate)->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    @if ($sisaHari < 0)
                                        <span class="text-rose-700">Expired (Lewat {{ abs($sisaHari) }} hari)</span>
                                    @elseif ($sisaHari <= 7)
                                        <span class="text-rose-700">H-{{ $sisaHari }} hari</span>
                                    @elseif ($sisaHari <= 30)
                                        <span class="text-amber-700">H-{{ $sisaHari }} hari</span>
                                    @else
                                        <span class="text-emerald-800 font-extrabold">&gt; 30 hari</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($sisaHari < 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white">
                                            KEDALUWARSA
                                        </span>
                                    @elseif ($batch->is_critical_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Kritis (H-7)
                                        </span>
                                    @elseif ($batch->is_near_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            Waspada (H-30)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                            ✓ Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-slate-900">
                                    {{ $batch->jumlah_stok }} unit
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data batch stok obat untuk cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.tenant>

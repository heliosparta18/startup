<x-layouts.tenant title="Pemantauan Stok FEFO & Indikator Kedaluwarsa">
    <div class="space-y-6">
        <!-- Top Title & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Manajemen Mutu Sediaan Farmasi</div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-zinc-100 tracking-tight">Kartu Stok FEFO &amp; Peringatan Kedaluwarsa</h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Algoritma First-Expired, First-Out memprioritaskan batch obat mendekati kedaluwarsa keluar terlebih dahulu</p>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-zinc-700 text-xs font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                    &larr; Dashboard Cabang
                </a>
                <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-zinc-700 text-xs font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                    📋 Antrean Resep
                </a>
            </div>
        </div>

        @php
            $criticalBatches = $fefoBatches->filter(fn($b) => $b->is_critical_ed);
            $nearBatches = $fefoBatches->filter(fn($b) => $b->is_near_ed && ! $b->is_critical_ed);
        @endphp

        <!-- Banner Peringatan Kritis H-7 -->
        @if ($criticalBatches->isNotEmpty())
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 sm:p-5 text-rose-900 dark:border-rose-800 dark:bg-rose-950/70 dark:text-rose-200 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-rose-600 text-white shrink-0 mt-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black tracking-tight">PERINGATAN KRITIS: {{ $criticalBatches->count() }} Batch Mendekati Kedaluwarsa (&le; 7 Hari)</h2>
                        <p class="text-xs mt-1 text-rose-800 dark:text-rose-300">
                            Batch berikut memerlukan tindakan karantina atau retur segera ke distributor sesuai standar operasional penanganan obat kedaluwarsa.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Banner Peringatan Dini H-30 -->
        @if ($nearBatches->isNotEmpty())
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5 text-amber-900 dark:border-amber-800 dark:bg-amber-950/70 dark:text-amber-200 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-amber-500 text-white shrink-0 mt-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black tracking-tight">PERINGATAN DINI: {{ $nearBatches->count() }} Batch Waspada Kedaluwarsa (&le; 30 Hari)</h2>
                        <p class="text-xs mt-1 text-amber-800 dark:text-amber-300">
                            Algoritma rotasi stok FEFO secara otomatis memprioritaskan batch ini di antrean paling depan untuk resep atau transaksi keluar.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabel Kartu Stok Berdasarkan Urutan FEFO -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-zinc-100">Daftar Batch Obat (Urutan FEFO Terdepan)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Disortir berdasarkan <code>tanggal_kadaluarsa ASC</code> sesuai regulasi kefarmasian</p>
                </div>
                <div class="text-xs font-semibold text-slate-500">
                    Total {{ $fefoBatches->count() }} batch terdata
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 border-b border-slate-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4 font-bold text-center">Prioritas FEFO</th>
                            <th class="py-3 px-4 font-bold">Nomor Batch</th>
                            <th class="py-3 px-4 font-bold">Nama Obat &amp; Sediaan</th>
                            <th class="py-3 px-4 font-bold">Tanggal Kedaluwarsa</th>
                            <th class="py-3 px-4 font-bold text-center">Sisa Waktu</th>
                            <th class="py-3 px-4 font-bold text-center">Status Mutu</th>
                            <th class="py-3 px-4 font-bold text-right">Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-800 dark:text-zinc-200">
                        @forelse ($fefoBatches as $index => $batch)
                            @php
                                $expiredDate = $batch->obat?->expired ?? $batch->tanggal_kadaluarsa;
                                $sisaHari = $batch->sisa_hari_ed ?? 0;
                            @endphp
                            <tr class="{{ $batch->is_critical_ed ? 'bg-rose-50/50 dark:bg-rose-950/20' : ($batch->is_near_ed ? 'bg-amber-50/40 dark:bg-amber-950/20' : '') }}">
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black {{ $index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                        #{{ $index + 1 }} FEFO
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-zinc-300">
                                    {{ $batch->obat?->batch_number ?? 'BTC-'.substr($batch->id, 0, 6) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-zinc-100">{{ $batch->obat?->nama_obat ?? 'Sediaan Obat' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ $batch->obat?->jenis_sediaan ?? 'tablet' }} • {{ $batch->obat?->merek ?? 'Generic' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-zinc-300">
                                    {{ $expiredDate ? \Carbon\Carbon::parse($expiredDate)->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    @if ($sisaHari < 0)
                                        <span class="text-rose-700 dark:text-rose-400">Expired (Lewat {{ abs($sisaHari) }} hari)</span>
                                    @elseif ($sisaHari <= 7)
                                        <span class="text-rose-700 dark:text-rose-400">H-{{ $sisaHari }} hari</span>
                                    @elseif ($sisaHari <= 30)
                                        <span class="text-amber-700 dark:text-amber-400">H-{{ $sisaHari }} hari</span>
                                    @else
                                        <span class="text-emerald-700 dark:text-emerald-400">&gt; 30 hari</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($sisaHari < 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white">
                                            KEDALUWARSA
                                        </span>
                                    @elseif ($batch->is_critical_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                            Kritis (H-7)
                                        </span>
                                    @elseif ($batch->is_near_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                            Waspada (H-30)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-zinc-100">
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

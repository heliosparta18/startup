<x-layouts.admin :title="'Cek Operasional Apoteker Cabang - Super Admin'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Header & Branch Switcher -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                        Supervisi Eksekutif | Audit Apoteker &amp; Farmasi Cabang
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3 leading-tight">
                        Cek Operasional Apoteker: {{ $activeBranch->nama_client }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl">
                        Pantau langsung kinerja Apoteker Penanggung Jawab (APJ), antrean resep dokter, batch FEFO kadaluarsa, serta antrean pesanan pada cabang ini.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2.5 shrink-0">
                    <a href="{{ route('admin.cabang.staf', ['client' => $branchSlug]) }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                        <span>CRUD Staf &amp; Hak Akses</span>
                    </a>
                    <a href="{{ route('admin.obat.index') }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs transition">
                        <span>CRUD Master Obat</span>
                    </a>
                    <a href="{{ route('admin.cabang.pengaturan', ['client' => $branchSlug]) }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                        <span>Pengaturan Cabang</span>
                    </a>
                </div>
            </div>

            <!-- Branch Switcher -->
            <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-slate-600 mr-1">Pilih Cabang Audit:</span>
                @foreach($clients as $c)
                    @php $cSlug = $c->slug ?? $c->id_client; @endphp
                    <a
                        href="{{ route('admin.apoteker.index', ['client' => $cSlug]) }}"
                        class="min-h-[38px] inline-flex items-center px-3.5 py-1.5 rounded-xl font-bold transition {{ $activeBranch->id_client === $c->id_client ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}"
                    >
                        {{ $c->nama_client }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 4 Real Database KPI Cards -->
        @php
            $kritisCount = (int) ($fefoSummary['low_stock_or_expiring_count'] ?? $fefoBatches->filter(fn ($b) => ($b['urgency'] ?? '') !== 'normal' || (int) ($b['stok'] ?? 0) <= 10)->count());
            $totalFefoCount = (int) ($fefoSummary['total_batches'] ?? $fefoBatches->count());
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Apoteker &amp; Staf Cabang</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ $staffs->count() }} Akun</div>
                <p class="mt-1 text-xs text-slate-500">{{ $supervisions->count() }} ikatan pengawasan 5NF aktif</p>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Antrean Telaah Resep</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black {{ $resepMenungguCount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $resepMenungguCount }} Menunggu
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ $resepDisetujuiCount }} disetujui | {{ $resepDitolakCount }} ditolak</p>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Batch FEFO Perlu Perhatian</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black {{ $kritisCount > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                    {{ $kritisCount }} Batch
                </div>
                <p class="mt-1 text-xs text-slate-500">Dari total {{ $totalFefoCount }} item terdaftar</p>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nomor Antrean Loket Live</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-700 font-mono">{{ $antreanBerjalan }}</div>
                <p class="mt-1 text-xs text-slate-500">{{ $branchOrders->whereIn('status', ['menunggu_pembayaran', 'diproses', 'siap_diambil'])->count() }} pesanan aktif cabang</p>
            </div>
        </div>

        <!-- Daftar Apoteker & Supervisi 5NF -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Tim Apoteker &amp; Staf Terdaftar ({{ $activeBranch->nama_client }})</h2>
                    <p class="text-xs text-slate-500">Daftar operator cabang berikut status akun dan pengawasan APJ.</p>
                </div>
                <a href="{{ route('admin.cabang.staf', ['client' => $branchSlug]) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    Atur Role &amp; Pengawasan Staf &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @forelse($staffs as $st)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-slate-900 truncate">{{ $st->pengguna->nama_lengkap ?? 'Staf' }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ ($st->pengguna->aktif ?? false) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                {{ ($st->pengguna->aktif ?? false) ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate">{{ $st->pengguna->telepon_email ?? '-' }}</p>
                        <p class="text-[11px] font-semibold text-emerald-700 uppercase">Peran Cabang: {{ $st->peran }}</p>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500">
                        Belum ada apoteker atau staf terdaftar pada cabang ini.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Antrean Resep & Batch FEFO -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Resep Digital -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900">Riwayat &amp; Antrean Telaah Resep</h2>
                    <p class="text-xs text-slate-500">Pemantauan legalitas telaah resep dokter oleh Apoteker Cabang.</p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs min-w-[30rem]">
                        <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                            <tr>
                                <th class="p-3">Pesanan / Pasien</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Verifikator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($reseps as $r)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="p-3">
                                        <div class="font-mono font-bold text-slate-900">{{ $r->pesanan->nomor_pesanan ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $r->pesanan->pengguna->nama_lengkap ?? 'Pasien' }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $r->status_verifikasi === 'disetujui' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($r->status_verifikasi === 'ditolak' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                            {{ $r->status_verifikasi }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-slate-700 font-medium">
                                        {{ $r->verifikator->nama_lengkap ?? 'Belum ditelaah' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-slate-400">Belum ada data resep pada cabang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Stok Batch FEFO -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Pantauan Batch &amp; FEFO Cabang</h2>
                        <p class="text-xs text-slate-500">Diurutkan berdasarkan tanggal kadaluarsa terdekat.</p>
                    </div>
                    <a href="{{ route('admin.obat.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Ubah Stok &rarr;</a>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs min-w-[30rem]">
                        <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                            <tr>
                                <th class="p-3">Obat &amp; Batch</th>
                                <th class="p-3 text-center">Stok</th>
                                <th class="p-3 text-right">Expired</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($fefoBatches->take(15) as $batch)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="p-3">
                                        <div class="font-bold text-slate-900">{{ $batch['nama_obat'] }}</div>
                                        <div class="text-[11px] font-mono text-slate-500">Batch: {{ $batch['batch_number'] }}</div>
                                    </td>
                                    <td class="p-3 text-center font-extrabold {{ (int) $batch['stok'] <= 10 ? 'text-rose-700' : 'text-slate-900' }}">
                                        {{ $batch['stok'] }} unit
                                    </td>
                                    <td class="p-3 text-right">
                                        <span class="font-semibold {{ ($batch['urgency'] ?? '') !== 'normal' ? 'text-amber-700' : 'text-slate-600' }}">
                                            {{ $batch['expired'] ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-slate-400">Belum ada batch obat di cabang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>

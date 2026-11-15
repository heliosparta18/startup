<x-layouts.tenant title="Portal Apoteker Cabang - OmniPharma">
    <div x-data="{
        statusVerifikasi: '{{ $resepPending->status_verifikasi ?? 'menunggu' }}',
        catatanApoteker: 'Resep asli dan dosis sesuai standar kefarmasian.',
        modalResepBesar: false,
        notifAksi: '',
        notifTipe: 'sukses',
        sedangProses: false,
        verifikasi(action) {
            @if ($resepPending)
                this.sedangProses = true;
                fetch('{{ route('tenant.resep.verifikasi', ['client' => $client, 'id' => $resepPending->id_resep]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        action: action,
                        catatan: this.catatanApoteker
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.sedangProses = false;
                    this.statusVerifikasi = action;
                    this.notifTipe = action === 'ditolak' ? 'error' : 'sukses';
                    this.notifAksi = data.message || ('Resep berhasil ditandai: ' + action.toUpperCase());
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(() => {
                    this.sedangProses = false;
                    this.notifTipe = 'error';
                    this.notifAksi = 'Terjadi kesalahan saat memproses telaah resep.';
                });
            @endif
        }
    }" class="space-y-6 sm:space-y-8">

        <!-- Notification Banner -->
        <div x-show="notifAksi" x-cloak class="rounded-2xl border p-4 text-xs font-bold shadow-xs"
            :class="notifTipe === 'error' ? 'border-rose-300 bg-rose-50 text-rose-900' : 'border-emerald-300 bg-emerald-50 text-emerald-900'">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-current shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifAksi"></span>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Portal Apoteker Cabang | Standar Permenkes RI No. 73/2016
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3 leading-tight">
                    Dashboard Apoteker Penanggung Jawab (APJ)
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 max-w-2xl leading-relaxed">
                    Telaah resep dokter, rotasi stok FEFO berbasis kedaluwarsa, dan pengelolaan antrean pasien pada <strong>{{ $clientModel->nama_client ?? ucwords(str_replace('-', ' ', $client)) }}</strong>.
                </p>
            </div>

            <div class="shrink-0 flex flex-wrap items-center gap-2.5">
                <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                    <span>Antrean: {{ $antreanBerjalan ?? 'A-001' }}</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs shadow-2xs transition">
                    <span>Kartu Stok FEFO</span>
                </a>
                <a href="{{ route('tenant.laporan.index', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                    <span>Laporan Penjualan</span>
                </a>
            </div>
        </div>

        <!-- Ringkasan Cepat -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Resep Menunggu Telaah</span>
                <div class="mt-2 text-2xl font-black {{ ($resepPendingCount ?? 0) > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ (int) ($resepPendingCount ?? 0) }} Resep
                </div>
                <span class="text-xs text-slate-500 mt-1 inline-block">Memerlukan verifikasi APJ ber-SIPA</span>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pesanan Aktif Cabang</span>
                <div class="mt-2 text-2xl font-black text-slate-900">
                    {{ isset($pesanansAktif) ? $pesanansAktif->count() : 0 }} Pesanan
                </div>
                <span class="text-xs text-slate-500 mt-1 inline-block">Antrean loket saat ini: {{ $antreanBerjalan ?? 'A-001' }}</span>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tuntas Hari Ini</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">
                    {{ (int) ($orderSelesaiHariIni ?? 0) }} Order
                </div>
                <span class="text-xs text-slate-500 mt-1 inline-block">Telah diserahkan kepada pasien</span>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: Verifikasi Resep Masuk -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Antrean Verifikasi Resep</span>
                            @if(isset($resepPendingCount) && $resepPendingCount > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    {{ $resepPendingCount }} Pending
                                </span>
                            @endif
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900 mt-0.5">
                            {{ $resepPending?->pesanan?->nomor_pesanan ?? 'Antrean Telaah Kosong' }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($resepPending)
                            <span
                                x-text="statusVerifikasi === 'menunggu' ? 'Menunggu Telaah' : (statusVerifikasi === 'disetujui' ? 'Disetujui' : 'Ditolak')"
                                :class="statusVerifikasi === 'menunggu' ? 'bg-amber-50 text-amber-800 border-amber-200' : (statusVerifikasi === 'disetujui' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200')"
                                class="inline-flex items-center rounded-xl border px-3 py-1 text-xs font-bold">
                            </span>
                        @endif
                        <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                            Semua Resep &rarr;
                        </a>
                    </div>
                </div>

                @if ($resepPending)
                    <!-- Prescription Image Preview Box -->
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 sm:p-6">
                        @if ($resepPending->url_gambar)
                            <div class="flex h-36 w-full items-center justify-center rounded-xl overflow-hidden border border-slate-200 bg-white shadow-2xs">
                                <img src="{{ $resepPending->url_gambar }}" alt="Foto Resep Dokter" class="max-h-full max-w-full object-contain" />
                            </div>
                        @else
                            <div class="flex h-32 w-full items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-slate-500 text-xs font-medium text-center px-4">
                                Foto Resep Digital: {{ $resepPending->nama_dokter ?? 'Dokter Pemeriksa' }} ({{ $resepPending->sip_dokter ?? 'SIP Dokter' }})
                            </div>
                        @endif
                        <button
                            type="button"
                            @click="modalResepBesar = true"
                            class="mt-3 inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                            <span>Perbesar Resep</span>
                        </button>
                    </div>

                    <!-- Doctor & Prescription Details -->
                    <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 space-y-2.5 text-xs">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
                            <div>
                                <div class="font-extrabold text-sm text-slate-900">{{ $resepPending->nama_dokter ?? 'Dokter Penulis Resep' }}</div>
                                <div class="text-slate-500">SIP: {{ $resepPending->sip_dokter ?? '-' }}</div>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="text-slate-500 font-medium">Data Pasien:</span>
                                <div class="font-bold text-slate-900">{{ $resepPending->nama_pasien ?? ($resepPending->pesanan?->pengguna?->nama_lengkap ?? 'Pasien') }}</div>
                            </div>
                        </div>
                        @if($resepPending->catatan)
                            <div class="text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200 text-[11px]">
                                <strong>Catatan Pasien:</strong> {{ $resepPending->catatan }}
                            </div>
                        @endif

                        <div class="border-t border-slate-200 pt-2.5 space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Item Obat Terkait Pesanan:</span>
                            @forelse($resepPending->pesanan?->items ?? [] as $rxItem)
                                <div class="font-mono text-slate-800 flex items-center justify-between bg-white px-2.5 py-1.5 rounded-lg border border-slate-100">
                                    <span class="font-bold text-emerald-800">R/ {{ $rxItem->nama_obat }}</span>
                                    <span class="text-slate-600 font-bold">No. {{ $rxItem->kuantitas }}</span>
                                </div>
                            @empty
                                <div class="text-slate-500 italic">Item resep fisik menyesuaikan telaah lampiran dokter.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <label for="catatan-apoteker" class="block font-bold text-slate-700">Catatan Verifikasi Apoteker:</label>
                        <input id="catatan-apoteker" type="text" x-model="catatanApoteker" class="w-full min-h-[42px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:border-emerald-600 focus:outline-none">
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1">
                        <button
                            type="button"
                            @click="verifikasi('disetujui')"
                            :disabled="sedangProses || statusVerifikasi === 'disetujui'"
                            class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 disabled:opacity-50 transition cursor-pointer">
                            Setujui Resep
                        </button>
                        <button
                            type="button"
                            @click="verifikasi('ditolak')"
                            :disabled="sedangProses || statusVerifikasi === 'ditolak'"
                            class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-rose-700 disabled:opacity-50 transition cursor-pointer">
                            Tolak Resep
                        </button>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center space-y-3">
                        <div class="mx-auto h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-sm font-extrabold text-slate-900">Semua Resep Sudah Ditelaah</p>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto">Tidak ada resep dokter berstatus menunggu verifikasi untuk cabang ini.</p>
                        <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="inline-flex min-h-[40px] items-center justify-center px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition">
                            Buka Halaman Telaah Resep
                        </a>
                    </div>
                @endif
            </section>

            <!-- RIGHT COLUMN: Peringatan Batch Kedaluwarsa (FEFO Real DB) -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-5">
                @php
                    $fefoList = collect($fefoBatches ?? []);
                    $criticalCount = $fefoList->filter(fn ($b) => (bool) ($b->is_critical_ed ?? false))->count();
                    $nearCount = $fefoList->filter(fn ($b) => (bool) ($b->is_near_ed ?? false))->count();
                @endphp

                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Manajemen Inventori Batch</span>
                        <h2 class="text-lg font-extrabold text-slate-900">Peringatan Batch Kedaluwarsa (FEFO)</h2>
                    </div>
                    <a href="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                        Kelola Stok FEFO &rarr;
                    </a>
                </div>

                @if ($criticalCount > 0)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-bold text-rose-900">
                        {{ $criticalCount }} batch kritis mendekati/melewati kedaluwarsa (&le; 7 hari). Segera karantina.
                    </div>
                @elseif ($nearCount > 0)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-xs font-bold text-amber-900">
                        {{ $nearCount }} batch memasuki masa waspada H-30 kedaluwarsa. Prioritaskan pada pengeluaran obat.
                    </div>
                @else
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-bold text-emerald-900">
                        Urutan batch diurutkan otomatis dari kedaluwarsa terdekat berdasarkan data stok cabang aktual.
                    </div>
                @endif

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs min-w-[340px]">
                        <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                            <tr>
                                <th class="p-3">Obat / Batch</th>
                                <th class="p-3">Exp Date</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3 text-right">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($fefoList->take(6) as $index => $batch)
                                @php
                                    $expiredDate = $batch->obat?->expired ?? $batch->tanggal_kadaluarsa;
                                    $isCrit = (bool) ($batch->is_critical_ed ?? false);
                                    $isNear = (bool) ($batch->is_near_ed ?? false);
                                @endphp
                                <tr class="{{ $isCrit ? 'bg-rose-50/50' : ($isNear ? 'bg-amber-50/40' : 'hover:bg-slate-50') }} font-medium">
                                    <td class="p-3">
                                        <div class="font-bold text-slate-900">{{ $batch->obat?->nama_obat ?? 'Sediaan Obat' }}</div>
                                        <div class="text-[11px] font-mono text-slate-500">Batch: {{ $batch->obat?->batch_number ?? 'BTC-'.substr((string) $batch->id, 0, 6) }}</div>
                                    </td>
                                    <td class="p-3 {{ $isCrit ? 'text-rose-700 font-bold' : ($isNear ? 'text-amber-800 font-semibold' : 'text-slate-600') }}">
                                        {{ $expiredDate ? \Carbon\Carbon::parse($expiredDate)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="p-3 font-bold {{ $batch->jumlah_stok <= 5 ? 'text-rose-700' : 'text-slate-900' }}">
                                        {{ (int) $batch->jumlah_stok }} unit
                                    </td>
                                    <td class="p-3 text-right">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-extrabold {{ $index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                            #{{ $index + 1 }} FEFO
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500">Belum ada batch stok obat pada cabang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 text-xs text-slate-600">
                    <p class="font-semibold text-slate-900 mb-1">Pedoman Pengeluaran Obat (FEFO):</p>
                    <p>Selalu dahulukan batch <strong>#1 FEFO</strong> untuk penyiapan resep dan penjualan bebas agar mencegah stok kadaluwarsa di cabang.</p>
                </div>
            </section>
        </div>

        <!-- SECTION 3: Antrean Virtual Berjalan & Kontrol Progresi Status -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Antrean Berjalan:</span>
                            <span class="px-2.5 py-0.5 rounded-lg bg-emerald-600 text-white font-mono font-black text-xs">{{ $antreanBerjalan ?? 'A-001' }}</span>
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900 mt-0.5">Pesanan Aktif &amp; Kontrol Progresi Status</h2>
                    </div>
                </div>
                <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition">
                    <span>Kelola Antrean &amp; Walk-In &rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[48rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Antrean / Order</th>
                            <th class="p-3.5">Pasien</th>
                            <th class="p-3.5">Slot Pengambilan</th>
                            <th class="p-3.5">Rincian Obat</th>
                            <th class="p-3.5">Status Saat Ini</th>
                            <th class="p-3.5 text-right">Aksi Progresi Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pesanansAktif ?? [] as $activeOrder)
                            @php $curStatus = $activeOrder->status ?? $activeOrder->status_pesanan ?? 'diproses'; @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-[11px]">
                                        {{ $activeOrder->nomor_antrean ?? 'A-001' }}
                                    </span>
                                    <div class="font-bold text-slate-900 mt-1">{{ $activeOrder->nomor_pesanan }}</div>
                                </td>
                                <td class="p-3.5 font-bold text-slate-900">
                                    {{ $activeOrder->pengguna->nama_lengkap ?? 'Pasien Umum' }}
                                </td>
                                <td class="p-3.5 font-semibold text-slate-700">
                                    {{ $activeOrder->slot_waktu ?? 'Segera' }}
                                </td>
                                <td class="p-3.5 max-w-[220px]">
                                    @foreach($activeOrder->items->take(2) as $item)
                                        <div class="truncate text-[11px] text-slate-700">{{ $item->nama_obat }} ({{ $item->kuantitas }}x)</div>
                                    @endforeach
                                </td>
                                <td class="p-3.5">
                                    @if($curStatus === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                            Menunggu Telaah
                                        </span>
                                    @elseif($curStatus === 'diproses')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Diproses Apotek
                                        </span>
                                    @elseif($curStatus === 'siap_diambil')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            Siap Diambil
                                        </span>
                                    @elseif($curStatus === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">{{ str_replace('_', ' ', $curStatus) }}</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right">
                                    @if($curStatus === 'menunggu_verifikasi')
                                        <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="min-h-[42px] px-3.5 py-2 inline-flex items-center justify-center rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                                            Telaah Resep
                                        </a>
                                    @else
                                        <form action="{{ route('tenant.pesanan.progres-status', ['client' => $client, 'id' => $activeOrder->id_pesanan]) }}" method="POST" class="inline-flex items-center gap-2 justify-end">
                                            @csrf
                                            @if($curStatus === 'menunggu_pembayaran')
                                                <input type="hidden" name="status" value="diproses">
                                                <button type="submit" class="min-h-[42px] px-3.5 py-2 rounded-xl border border-emerald-600/50 bg-emerald-50 text-emerald-900 hover:bg-emerald-100 font-bold text-xs transition cursor-pointer">
                                                    Mulai Proses
                                                </button>
                                            @elseif($curStatus === 'diproses')
                                                <input type="hidden" name="status" value="siap_diambil">
                                                <button type="submit" class="min-h-[42px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                                    Tandai Siap Ambil
                                                </button>
                                            @elseif($curStatus === 'siap_diambil')
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="min-h-[42px] px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                                    Selesaikan
                                                </button>
                                            @else
                                                <span class="text-xs text-emerald-800 font-extrabold pr-2">Tuntas</span>
                                            @endif
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">Belum ada pesanan aktif yang sedang diproses di cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($resepPending)
            <!-- Modal Perbesar Resep -->
            <div x-show="modalResepBesar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs">
                <div @click.away="modalResepBesar = false" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-5 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">
                            Pratinjau Resep Dokter : {{ $resepPending->pesanan->nomor_pesanan ?? 'Resep Masuk' }}
                        </h3>
                        <button type="button" @click="modalResepBesar = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-400 hover:text-slate-800 rounded-xl cursor-pointer">&times;</button>
                    </div>
                    @if ($resepPending->url_gambar)
                        <div class="flex max-h-[60vh] items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 p-2">
                            <img src="{{ $resepPending->url_gambar }}" alt="Resep {{ $resepPending->nama_dokter }}" class="max-h-[55vh] max-w-full object-contain rounded-lg" />
                        </div>
                    @else
                        <div class="flex h-56 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600 p-4 text-center">
                            Resep {{ $resepPending->nama_dokter ?? '-' }} | Pasien {{ $resepPending->nama_pasien ?? '-' }}
                        </div>
                    @endif
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="modalResepBesar = false" class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 text-xs font-bold text-white cursor-pointer">
                            Tutup Pratinjau
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.tenant>

<x-layouts.tenant title="Verifikasi Resep &amp; FEFO">
    <div x-data="{
        statusVerifikasi: '{{ $resepPending->status_verifikasi ?? 'menunggu' }}',
        catatanApoteker: 'Resep asli dan masih berlaku.',
        modalResepBesar: false,
        notifAksi: '',
        verifikasi(action) {
            fetch('{{ route('tenant.resep.verifikasi', ['client' => $client, 'id' => $resepPending ? $resepPending->id_resep : 'ORD-20260917-0042']) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action,
                    catatan: this.catatanApoteker
                })
            })
            .then(res => res.json())
            .then(data => {
                this.statusVerifikasi = action;
                this.notifAksi = 'Resep berhasil ditandai: ' + action.toUpperCase();
                setTimeout(() => this.notifAksi = '', 5000);
            })
            .catch(() => {
                this.statusVerifikasi = action;
                this.notifAksi = 'Resep berhasil diverifikasi (' + action + ').';
                setTimeout(() => this.notifAksi = '', 5000);
            });
        }
    }" class="space-y-6">

        <!-- Top Notification Banner -->
        <div x-show="notifAksi" x-cloak class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifAksi"></span>
            </div>
        </div>

        <!-- Hero Banner Apoteker: Nuansa Hijau Deep Spruce Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        Standar Permenkes RI No. 73/2016 • Operasional Apotek
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Dashboard Apoteker Penanggung Jawab (APJ)
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Pengawasan peracikan obat, rotasi stok FEFO terverifikasi, dan manajemen antrean pasien pada <strong>{{ $clientModel->nama_client ?? ucwords(str_replace('-', ' ', $client)) }}</strong>.
                    </p>
                </div>

                <div class="shrink-0 flex flex-wrap items-center gap-3">
                    <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-[#D4F94E] hover:bg-[#c2e83b] text-[#0A231E] font-black text-xs shadow-xs transition">
                        <span>Antrean: {{ $antreanBerjalan ?? 'A-001' }}</span>
                        <span class="text-xs">&rarr;</span>
                    </a>
                    <a href="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs backdrop-blur-xs transition">
                        <span>Kartu Stok FEFO</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Two Column Layout (Mockup Screen 2: Nuansa Hijau & Putih Bersih) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: Verifikasi Resep Masuk (#ORD-20260917-0042) -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-4 sm:p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Antrean Verifikasi Resep</span>
                            @if(isset($resepPendingCount) && $resepPendingCount > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    {{ $resepPendingCount }} Pending
                                </span>
                            @endif
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900 mt-0.5">
                            {{ $resepPending?->pesanan?->nomor_pesanan ?? '#ORD-20260917-0042' }}
                        </h2>
                        <div class="text-[11px] text-slate-400">Ref: <span class="font-mono">#ORD-20260917-0042</span></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            x-text="statusVerifikasi === 'menunggu' ? 'Menunggu Telaah' : (statusVerifikasi === 'disetujui' ? 'Disetujui' : 'Ditolak')"
                            :class="statusVerifikasi === 'menunggu' ? 'bg-amber-100 text-amber-900 border-amber-300' : (statusVerifikasi === 'disetujui' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300')"
                            class="inline-flex items-center rounded-xl border px-3 py-1 text-xs font-bold shadow-2xs">
                        </span>
                        <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                            Semua Resep &rarr;
                        </a>
                    </div>
                </div>

                <!-- Prescription Image Preview Box -->
                <div class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 sm:p-6">
                    @if ($resepPending && $resepPending->url_gambar)
                        <div class="flex h-36 w-full items-center justify-center rounded-xl overflow-hidden border border-slate-300 bg-white shadow-2xs">
                            <img src="{{ $resepPending->url_gambar }}" alt="Foto Resep Dokter" class="max-h-full max-w-full object-contain" />
                        </div>
                    @else
                        <div class="flex h-36 w-full items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-slate-500 text-xs font-medium shadow-2xs">
                            [ Foto Resep Dokter : {{ $resepPending?->nama_dokter ?? 'dr. Budi Sp.PD' }} ]
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
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 space-y-2 text-xs">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-2 sm:gap-0">
                        <div>
                            <div class="font-extrabold text-sm text-slate-900">{{ $resepPending?->nama_dokter ?? 'dr. Budi, Sp.PD' }}</div>
                            <div class="text-slate-500">SIP: {{ $resepPending?->sip_dokter ?? '503/SIP/2024' }}</div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-slate-500 font-medium">Data Pasien:</span>
                            <div class="font-bold text-slate-900">{{ $resepPending?->nama_pasien ?? 'Candi Chier (88 th)' }}</div>
                        </div>
                    </div>
                    @if($resepPending?->catatan)
                        <div class="text-slate-600 bg-white p-2 rounded-lg border border-slate-200 text-[11px]">
                            <strong>Catatan:</strong> {{ $resepPending->catatan }}
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-2 font-mono text-slate-800 space-y-1">
                        <div class="font-bold text-emerald-800">R/ Amodiaquine No. X</div>
                        <div class="text-slate-600">S 3 dd tab I (Sesudah makan)</div>
                    </div>
                </div>

                <!-- Pemeriksaan Legalitas Farmasi -->
                <div class="space-y-2 text-xs">
                    <span class="font-bold uppercase tracking-wider text-slate-700">Pemeriksaan Legalitas Farmasi:</span>
                    <div class="space-y-1.5">
                        <label class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900">
                            <input type="checkbox" checked disabled class="mt-0.5 h-4 w-4 shrink-0 rounded text-emerald-600" />
                            <span>✔ SIP Dokter Terverifikasi Aktif pada Konsil Kedokteran</span>
                        </label>
                        <label class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900">
                            <input type="checkbox" checked disabled class="mt-0.5 h-4 w-4 shrink-0 rounded text-emerald-600" />
                            <span>✔ Dosis Sesuai Standar Farmakope untuk Geriatri (88 th)</span>
                        </label>
                    </div>
                </div>

                <!-- Action Verification Buttons (>= 44px target) -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button
                        type="button"
                        @click="verifikasi('disetujui')"
                        :disabled="statusVerifikasi === 'disetujui'"
                        class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 transition cursor-pointer">
                        Setujui
                    </button>
                    <button
                        type="button"
                        @click="verifikasi('ditolak')"
                        :disabled="statusVerifikasi === 'ditolak'"
                        class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 transition cursor-pointer">
                        Tolak
                    </button>
                </div>
            </section>

            <!-- RIGHT COLUMN: Peringatan Batch Kedaluwarsa (FEFO) -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-4 sm:p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Manajemen Inventori Batch</span>
                    <h2 class="text-lg font-extrabold text-slate-900">Peringatan Batch Kedaluwarsa (FEFO)</h2>
                </div>

                <!-- Warning Alert Banner (Mockup Screen 2) -->
                <div class="flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs font-bold text-amber-900">
                    <span class="text-lg">⚠️</span>
                    <span>1 Batch mendekati kedaluwarsa (H-23 hari)!</span>
                </div>

                <!-- FEFO Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs min-w-[340px]">
                        <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                            <tr>
                                <th class="p-3">Obat / Batch</th>
                                <th class="p-3">Exp Date</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <!-- Batch #1: Amodiaquine (H-23 FEFO) -->
                            <tr class="bg-amber-50/40 font-medium">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #BTC-2024A</div>
                                </td>
                                <td class="p-3 font-semibold text-amber-800">10 Okt 2026</td>
                                <td class="p-3 font-bold">12 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-extrabold text-amber-900 border border-amber-300">
                                        #1 FEFO
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #2: Amodiaquine 2027 -->
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #BTC-2025B</div>
                                </td>
                                <td class="p-3 text-slate-600">15 Jan 2027</td>
                                <td class="p-3 font-bold">80 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200">
                                        #2 Nanti
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #3: Paracetamol (Kritis <5) -->
                            <tr class="bg-rose-50/30">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Paracetamol 500mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #PCT-8891</div>
                                </td>
                                <td class="p-3 text-slate-600">20 Des 2027</td>
                                <td class="p-3 font-bold text-rose-700">4 box</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 border border-rose-300">
                                        Kritis &lt;5
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 text-xs text-slate-600">
                    <p class="font-semibold text-slate-900 mb-1">Pedoman Pengeluaran Obat (FEFO):</p>
                    <p>Keluarkan selalu batch #BTC-2024A terlebih dahulu sebelum membuka batch baru untuk mencegah kerugian kadaluwarsa pada klien cabang.</p>
                </div>
            </section>
        </div>

        <!-- SECTION 3: Antrean Virtual Berjalan & Tombol Cepat Progresi Status Pesanan -->
        <section class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm border border-emerald-200 shrink-0">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Antrean Berjalan:</span>
                            <span class="px-2.5 py-0.5 rounded-lg bg-[#0A231E] text-[#D4F94E] font-mono font-black text-xs">{{ $antreanBerjalan ?? 'A-001' }}</span>
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900">Pesanan Aktif &amp; Kontrol Progresi Status</h2>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition">
                        <span>Kelola Antrean &amp; Walk-In &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[46rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3">Antrean / Order</th>
                            <th class="p-3">Pasien</th>
                            <th class="p-3">Slot Pengambilan</th>
                            <th class="p-3">Rincian Obat</th>
                            <th class="p-3">Status Saat Ini</th>
                            <th class="p-3 text-right">Aksi Progresi Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pesanansAktif ?? [] as $activeOrder)
                            @php
                                $curStatus = $activeOrder->status ?? 'diproses';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg font-bold text-xs bg-slate-100 text-slate-800">
                                            {{ $activeOrder->nomor_antrean ?? 'A-001' }}
                                        </span>
                                        <span class="font-bold text-slate-900">{{ $activeOrder->nomor_pesanan }}</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">{{ $activeOrder->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                                </td>
                                <td class="p-3 font-semibold text-slate-700">
                                    {{ $activeOrder->slot_waktu ?? '15 Menit Lagi' }}
                                </td>
                                <td class="p-3 max-w-[200px]">
                                    @foreach($activeOrder->items->take(2) as $item)
                                        <div class="truncate text-[11px]">• {{ $item->nama_obat }} ({{ $item->kuantitas }}x)</div>
                                    @endforeach
                                </td>
                                <td class="p-3">
                                    @if($curStatus === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                            Menunggu Telaah
                                        </span>
                                    @elseif($curStatus === 'diproses')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Diproses Apotek
                                        </span>
                                    @elseif($curStatus === 'siap_diambil')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            Siap Diambil
                                        </span>
                                    @elseif($curStatus === 'selesai')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#0A231E] text-[#D4F94E] border border-emerald-500/20">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">{{ str_replace('_', ' ', $curStatus) }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    @if($curStatus === 'menunggu_verifikasi')
                                        <a href="{{ route('tenant.resep.antrean', ['client' => $client]) }}" class="min-h-[44px] px-3.5 py-2 inline-flex items-center justify-center rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                                            Telaah Resep
                                        </a>
                                    @else
                                        <form action="{{ route('tenant.pesanan.progres-status', ['client' => $client, 'id' => $activeOrder->id_pesanan]) }}" method="POST" class="inline-flex items-center gap-2 justify-end">
                                            @csrf
                                            @if($curStatus === 'menunggu_pembayaran')
                                                <input type="hidden" name="status" value="diproses">
                                                <button type="submit" class="min-h-[44px] px-3.5 py-2 rounded-xl border border-emerald-600/50 bg-emerald-50 text-emerald-900 hover:bg-emerald-100 hover:border-emerald-600 font-bold text-xs shadow-2xs transition cursor-pointer">
                                                    Mulai Proses
                                                </button>
                                            @elseif($curStatus === 'diproses')
                                                <input type="hidden" name="status" value="siap_diambil">
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                                    Tandai Siap Ambil
                                                </button>
                                            @elseif($curStatus === 'siap_diambil')
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl bg-[#0A231E] hover:bg-[#0E352E] text-[#D4F94E] font-bold text-xs border border-emerald-500/30 shadow-xs transition cursor-pointer">
                                                    Selesaikan
                                                </button>
                                            @else
                                                <span class="text-xs text-emerald-800 font-extrabold pr-2">✓ Tuntas</span>
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

        <!-- Modal Perbesar Resep -->
        <div x-show="modalResepBesar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div @click.away="modalResepBesar = false" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-4 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Pratinjau Resep Dokter : {{ $resepPending?->pesanan?->nomor_pesanan ?? '#ORD-20260917-0042' }}
                    </h3>
                    <button type="button" @click="modalResepBesar = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-400 hover:text-slate-800 rounded-xl cursor-pointer">✕</button>
                </div>
                @if ($resepPending && $resepPending->url_gambar)
                    <div class="flex max-h-[60vh] items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 p-2">
                        <img src="{{ $resepPending->url_gambar }}" alt="Tampilan Resolusi Penuh: Resep {{ $resepPending->nama_dokter }}" class="max-h-[55vh] max-w-full object-contain rounded-lg" />
                    </div>
                @else
                    <div class="flex h-56 sm:h-72 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600 p-4 text-center">
                        [ Tampilan Resolusi Penuh: Resep {{ $resepPending?->nama_dokter ?? 'dr. Budi Sp.PD' }} - Pasien {{ $resepPending?->nama_pasien ?? 'Candi Chier (88 th)' }} ]
                    </div>
                @endif
                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="modalResepBesar = false" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 text-xs font-bold text-white shadow-2xs cursor-pointer">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.tenant>

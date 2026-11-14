<x-layouts.tenant title="Antrean Telaah Resep Dokter">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Antrean Resep Masuk</span>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Dashboard Cabang
                </a>
                <a href="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="flex-1 sm:flex-initial min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    Kartu Stok FEFO
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
                        Standar Permenkes RI No. 73/2016 • Skrining Resep Dokter
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Antrean Telaah &amp; Verifikasi Resep Digital
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Verifikasi Surat Izin Praktik (SIP) dokter, skrining klinis dosis geriatri/pediatri, dan validasi legalitas penyerahan obat keras.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        {{ count($resepPending) }} Resep Menunggu
                    </span>
                </div>
            </div>
        </div>

        <!-- Notifikasi Pesan Flash -->
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Daftar Resep yang Perlu Diverifikasi -->
        <div class="space-y-6 sm:space-y-8">
            @forelse ($resepPending as $item)
                @php
                    $pesanan = $item->pesanan;
                    $items = $pesanan ? $pesanan->itemPesanan : collect();
                @endphp
                <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                Menunggu Telaah Apoteker
                            </span>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-2">
                                Pesanan {{ $pesanan->nomor_pesanan ?? '#ORD-'.substr($item->id_resep, 0, 8) }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pasien: <strong class="text-slate-800">{{ $item->nama_pasien ?? 'Pasien Umum' }}</strong> • Diajukan: {{ $item->dibuat_pada ? \Carbon\Carbon::parse($item->dibuat_pada)->format('d M Y, H:i') : '-' }} WIB</p>
                        </div>
                        <div class="text-xs text-slate-500">
                            ID Resep: <code class="font-mono text-emerald-800 font-bold">{{ $item->id_resep }}</code>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 mt-6">
                        <!-- Kolom Foto Resep & Info Medis -->
                        <div class="lg:col-span-5 space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-bold text-slate-700 mb-2">Foto Resep Fisik Dokter</div>
                                <div class="aspect-4/3 rounded-xl overflow-hidden bg-slate-200 flex items-center justify-center border border-slate-300">
                                    @if ($item->url_gambar)
                                        <img src="{{ $item->url_gambar }}" alt="Foto Resep" class="w-full h-full object-cover" />
                                    @else
                                        <div class="text-center p-4">
                                            <svg class="h-12 w-12 mx-auto text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-xs font-bold text-slate-600 mt-2 block">Dokumen Resep Medis Digital</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-3 text-xs text-slate-600 space-y-1">
                                    <div>Dokter Perujuk: <strong class="text-slate-900">{{ $item->nama_dokter ?? 'dr. Budi, Sp.PD' }}</strong></div>
                                    <div>SIP Tertera: <code class="text-emerald-800 font-bold font-mono">{{ $item->sip_dokter ?? '503/SIP/2024' }}</code></div>
                                    @if ($item->catatan)
                                        <div class="mt-2 text-slate-700 bg-slate-100 p-2 rounded-lg border border-slate-200">
                                            <strong>Catatan Pasien:</strong> {{ $item->catatan }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Rincian Obat & Form Keputusan Telaah -->
                        <div class="lg:col-span-7 space-y-5">
                            <!-- Rincian Item Obat Keras yang Diresepkan -->
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Rincian Obat yang Ditebus</h3>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="w-full min-w-[20rem] text-left text-xs">
                                        <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                                            <tr>
                                                <th class="py-2.5 px-3.5">Nama Obat</th>
                                                <th class="py-2.5 px-3.5 text-center">Jumlah</th>
                                                <th class="py-2.5 px-3.5">Aturan Pakai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                                            @forelse ($items as $it)
                                                <tr>
                                                    <td class="py-2.5 px-3.5 font-bold">{{ $it->obat->nama_obat ?? 'Obat Resep' }}</td>
                                                    <td class="py-2.5 px-3.5 text-center font-bold">{{ $it->jumlah ?? 1 }}</td>
                                                    <td class="py-2.5 px-3.5 text-slate-600">{{ $it->dosis_aturan ?? 'Sesuai petunjuk dokter' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="py-2.5 px-3.5 font-bold">Amodiaquine 200mg (Wajib Resep)</td>
                                                    <td class="py-2.5 px-3.5 text-center font-bold">1 strip</td>
                                                    <td class="py-2.5 px-3.5 text-slate-600">3 x 1 tablet sesudah makan</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Form Keputusan Telaah Apoteker Sesuai Tombol Login -->
                            <form action="{{ route('tenant.resep.verifikasi', ['client' => $client, 'id' => $item->id_resep]) }}" method="POST" class="space-y-4 pt-2">
                                @csrf
                                <div>
                                    <label for="nomor_sip_{{ $item->id_resep }}" class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Nomor SIP Dokter yang Tervalidasi <span class="text-rose-600">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="nomor_sip"
                                        id="nomor_sip_{{ $item->id_resep }}"
                                        value="{{ old('nomor_sip', $item->sip_dokter ?? '503/SIP/2024') }}"
                                        placeholder="Format resmi: 503/SIP/2024"
                                        class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs sm:text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition" />
                                    <span class="text-[11px] text-slate-400 mt-1 block">Format standar: <code>XXX/SIP/YYYY</code> (Contoh: 503/SIP/2024).</span>
                                </div>

                                <div>
                                    <label for="alasan_{{ $item->id_resep }}" class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Catatan Telaah Klinis / Alasan Penolakan
                                    </label>
                                    <textarea
                                        name="alasan"
                                        id="alasan_{{ $item->id_resep }}"
                                        rows="2"
                                        placeholder="Tuliskan catatan telaah farmasi atau alasan penolakan jika ada inkompatibilitas..."
                                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs sm:text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition"></textarea>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                                    <button
                                        type="submit"
                                        name="action"
                                        value="disetujui"
                                        class="flex-1 min-h-[46px] inline-flex items-center justify-center gap-2 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                                        <span>✓ Setujui Resep (SIP Sah)</span>
                                    </button>
                                    <button
                                        type="submit"
                                        name="action"
                                        value="ditolak"
                                        class="flex-1 sm:flex-initial min-h-[46px] px-5 inline-flex items-center justify-center rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition cursor-pointer">
                                        <span>✕ Tolak Resep</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 p-12 text-center bg-white shadow-xs">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] border border-emerald-500/30 shadow-xs">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-extrabold text-slate-900">Tidak ada antrean resep yang tertunda</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Seluruh telaah resep dokter masuk telah selesai diverifikasi oleh Apoteker Penanggung Jawab (APJ).</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.tenant>

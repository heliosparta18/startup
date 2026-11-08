<x-layouts.tenant title="Antrean Telaah Resep Dokter">
    <div class="space-y-6">
        <!-- Top Title & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Regulasi Pelayanan Resep Farmasi (Permenkes 73/2016)</div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-zinc-100 tracking-tight">Antrean Telaah &amp; Verifikasi Resep Digital</h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Verifikasi Surat Izin Praktik (SIP) dokter, keabsahan klinis dosis, dan persetujuan pengeluaran obat keras</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300 dark:border-zinc-700 text-xs font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                    📦 Kartu Stok FEFO
                </a>
            </div>
        </div>

        <!-- Flash Message Notification -->
        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-xs font-semibold text-rose-900 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Daftar Resep yang Perlu Diverifikasi -->
        <div class="space-y-6">
            @forelse ($resepPending as $item)
                @php
                    $pesanan = $item->pesanan;
                    $items = $pesanan ? $pesanan->itemPesanan : collect();
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-zinc-800">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                Menunggu Telaah Apoteker
                            </span>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-zinc-100 mt-1">
                                Pesanan {{ $pesanan->nomor_pesanan ?? '#ORD-'.substr($item->id_resep, 0, 8) }}
                            </h2>
                            <p class="text-xs text-slate-500">Pasien: <strong class="text-slate-800 dark:text-zinc-200">{{ $item->nama_pasien ?? 'Pasien Umum' }}</strong> • Diajukan: {{ $item->dibuat_pada ? \Carbon\Carbon::parse($item->dibuat_pada)->format('d M Y, H:i') : '-' }}</p>
                        </div>
                        <div class="text-xs text-slate-500">
                            ID Resep: <code class="font-mono text-slate-700 dark:text-zinc-300">{{ $item->id_resep }}</code>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Kolom Foto Resep & Info Medis -->
                        <div class="lg:col-span-5 space-y-4">
                            <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50 p-4">
                                <div class="text-xs font-bold text-slate-700 dark:text-zinc-300 mb-2">Foto Resep Fisik Dokter</div>
                                <div class="aspect-4/3 rounded-lg overflow-hidden bg-slate-200 dark:bg-zinc-800 flex items-center justify-center border border-slate-300 dark:border-zinc-700">
                                    @if ($item->url_gambar)
                                        <img src="{{ $item->url_gambar }}" alt="Foto Resep" class="w-full h-full object-cover" />
                                    @else
                                        <div class="text-center p-4">
                                            <svg class="h-10 w-10 mx-auto text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-xs text-slate-500 mt-1 block">Dokumen Resep Medis Digital</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-3 text-[11px] text-slate-500 space-y-1">
                                    <div>Dokter Perujuk: <strong class="text-slate-800 dark:text-zinc-200">{{ $item->nama_dokter ?? 'dr. Spesialis Klinis' }}</strong></div>
                                    <div>SIP Tertera: <code class="text-slate-700 dark:text-zinc-300">{{ $item->sip_dokter ?? 'Belum tervalidasi' }}</code></div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Rincian Obat & Form Keputusan Telaah -->
                        <div class="lg:col-span-7 space-y-5">
                            <!-- Rincian Item Obat Keras yang Diresepkan -->
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Rincian Obat Keras yang Ditebus</h3>
                                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 border-b border-slate-200 dark:border-zinc-800">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Obat</th>
                                                <th class="py-2.5 px-3 text-center">Jumlah</th>
                                                <th class="py-2.5 px-3">Aturan Pakai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-800 dark:text-zinc-200">
                                            @forelse ($items as $it)
                                                <tr>
                                                    <td class="py-2.5 px-3 font-semibold">{{ $it->obat->nama_obat ?? 'Obat Resep' }}</td>
                                                    <td class="py-2.5 px-3 text-center">{{ $it->jumlah ?? 1 }}</td>
                                                    <td class="py-2.5 px-3 text-slate-500">{{ $it->dosis_aturan ?? 'Sesuai petunjuk dokter' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="py-2.5 px-3 font-semibold">Amodiaquine 200mg (Contoh Wajib Resep)</td>
                                                    <td class="py-2.5 px-3 text-center">1 strip</td>
                                                    <td class="py-2.5 px-3 text-slate-500">3 x 1 tablet sesudah makan</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Form Keputusan Telaah Apoteker -->
                            <form action="{{ route('tenant.resep.verifikasi', ['client' => $client, 'id' => $item->id_resep]) }}" method="POST" class="space-y-4 pt-2">
                                @csrf
                                <div>
                                    <label for="nomor_sip_{{ $item->id_resep }}" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">
                                        Nomor SIP Dokter yang Valid <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="nomor_sip"
                                        id="nomor_sip_{{ $item->id_resep }}"
                                        value="{{ old('nomor_sip', $item->sip_dokter ?? '503/SIP/2024') }}"
                                        placeholder="Contoh format: 503/SIP/2024"
                                        class="w-full min-h-[44px] rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 text-xs font-medium text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                    <span class="text-[11px] text-slate-400 mt-1 block">Wajib format resmi: <code>XXX/SIP/YYYY</code> (Contoh: 503/SIP/2024).</span>
                                </div>

                                <div>
                                    <label for="alasan_{{ $item->id_resep }}" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">
                                        Catatan / Alasan Penolakan (Wajib jika ditolak)
                                    </label>
                                    <textarea
                                        name="alasan"
                                        id="alasan_{{ $item->id_resep }}"
                                        rows="2"
                                        placeholder="Tuliskan catatan telaah klinis atau alasan penolakan jika resep tidak absah..."
                                        class="w-full rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3 text-xs font-medium text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                                </div>

                                <div class="flex items-center gap-3 pt-2">
                                    <button
                                        type="submit"
                                        name="action"
                                        value="disetujui"
                                        class="flex-1 min-h-[44px] rounded-xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 transition active:scale-[0.98]">
                                        ✓ Setujui Resep (SIP Sah)
                                    </button>
                                    <button
                                        type="submit"
                                        name="action"
                                        value="ditolak"
                                        class="min-h-[44px] px-5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 transition active:scale-[0.98]">
                                        ✕ Tolak Resep
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 p-12 text-center bg-white dark:bg-zinc-900">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-sm font-bold text-slate-800 dark:text-zinc-200">Tidak ada antrean resep</h3>
                    <p class="mt-1 text-xs text-slate-500">Seluruh telaah resep dokter masuk telah selesai diverifikasi oleh Apoteker Penanggung Jawab.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.tenant>

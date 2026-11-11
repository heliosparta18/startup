<x-layouts.admin :title="'Monitoring Progres Obat & Antrean - Super Admin'">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Monitoring Progres Obat Lintas Cabang</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Hero Banner: Nuansa Hijau Deep Spruce Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • Pengawasan Eksekutif
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Monitoring Progres Obat &amp; Antrean Cabang
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Pengawasan eksekutif terhadap obat yang sedang diracik dan diproses di seluruh cabang apotek secara <em>real-time</em> (Mode Baca &amp; Audit Pengawasan Super Admin).
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-ping"></span>
                        Monitoring Aktif Live
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 KPI Cards Ringkasan Progres Obat Berjalan Sesuai Palet Login -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">OBAT SEDANG DIRACIK / DIPROSES</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-extrabold text-emerald-800 border border-emerald-200">
                        Live Farmasi
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-slate-900 tracking-tight">
                    {{ $totalObatDiproses }} Item
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Akumulasi butir/strip obat dalam antrean peracikan apoteker
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">PESANAN SEDANG DIPROSES</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-extrabold text-emerald-900 border border-emerald-300">
                        Antrean Aktif
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-emerald-800 tracking-tight">
                    {{ $totalDiproses }} Transaksi
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Sedang ditelaah &amp; disiapkan di meja farmasi cabang
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">SIAP DIAMBIL PASIEN</span>
                    <span class="inline-flex items-center rounded-full bg-[#0A231E] px-2.5 py-0.5 text-xs font-extrabold text-[#D4F94E] border border-emerald-500/20">
                        Siap Serah
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-[#0A231E] tracking-tight">
                    {{ $totalSiap }} Pesanan
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Obat telah selesai dikemas dan menunggu pengambilan pasien di loket
                </div>
            </div>
        </div>

        <!-- Filter Cabang -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                <span>Filter Cabang Apotek:</span>
                <a href="{{ route('admin.progres.index', ['client' => 'all']) }}" class="px-3 py-1.5 rounded-xl {{ empty($selectedClient) || $selectedClient === 'all' ? 'bg-[#0A231E] text-[#D4F94E]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Semua Cabang
                </a>
                @foreach($clients as $c)
                    <a href="{{ route('admin.progres.index', ['client' => $c->slug ?? $c->id_client]) }}" class="px-3 py-1.5 rounded-xl {{ $selectedClient === ($c->slug ?? $c->id_client) ? 'bg-[#0A231E] text-[#D4F94E]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                        {{ $c->nama_client }}
                    </a>
                @endforeach
            </div>
            <div class="text-xs text-slate-500">
                Total Data: <strong class="text-slate-800">{{ $pesanans->count() }} Pesanan Terpantau</strong>
            </div>
        </div>

        <!-- Tabel Live Monitoring Progres Obat & Antrean (Read-Only Mode) -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Status Progres Obat &amp; Antrean Berjalan Lintas Cabang
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan rincian obat yang sedang diproses di tiap loket cabang (Super Admin Pengawasan Mode).
                    </p>
                </div>
                <div class="inline-flex items-center gap-1.5 text-xs text-slate-400">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Aksi perubahan status hanya dapat dilakukan oleh Apoteker Cabang</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[55rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Cabang &amp; Antrean</th>
                            <th class="p-3.5">No. Pesanan &amp; Pasien</th>
                            <th class="p-3.5">Obat yang Sedang Diproses</th>
                            <th class="p-3.5">Slot Jam Pengambilan</th>
                            <th class="p-3.5">Status Progres</th>
                            <th class="p-3.5">Antrean Berjalan Cabang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($pesanans as $order)
                            @php
                                $status = $order->status ?? 'diproses';
                                $branchQueue = $antreanMap[$order->id_client] ?? 'A-001';
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-sm text-slate-900">{{ $order->client->nama_client ?? 'Cabang Apotek' }}</div>
                                    <div class="mt-1 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-[#0A231E] text-[#D4F94E] font-mono font-bold text-xs">
                                        Antrean: {{ $order->nomor_antrean ?? 'A-001' }}
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $order->nomor_pesanan }}</div>
                                    <div class="text-[11px] text-slate-500">Pasien: {{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '-' }} WIB</div>
                                </td>
                                <td class="p-3.5 max-w-[280px]">
                                    <div class="space-y-1">
                                        @forelse($order->items as $item)
                                            <div class="flex items-center justify-between text-xs font-semibold text-slate-800 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                                <span class="truncate">{{ $item->nama_obat }}</span>
                                                <span class="font-bold text-emerald-700 ml-2 shrink-0">{{ $item->kuantitas }}x</span>
                                            </div>
                                        @empty
                                            <div class="text-xs text-slate-400">Tidak ada rincian item.</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-semibold text-slate-700">{{ $order->slot_waktu ?? '15 Menit Lagi (Segera)' }}</div>
                                    <div class="text-[11px] text-slate-400 capitalize">{{ str_replace('_', ' ', $order->metode_layanan) }}</div>
                                </td>
                                <td class="p-3.5">
                                    @if($status === 'diproses')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Diproses Apotek
                                        </span>
                                    @elseif($status === 'siap_diambil')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-950 border border-emerald-300">
                                            Siap Diambil
                                        </span>
                                    @elseif($status === 'selesai')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-[#0A231E] text-[#D4F94E] border border-emerald-500/20">
                                            Selesai Diserahkan
                                        </span>
                                    @elseif($status === 'menunggu_pembayaran')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                            Menunggu Bayar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            {{ str_replace('_', ' ', $status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-slate-800">Loket: {{ $branchQueue }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">Sedang melayani di cabang</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada pesanan atau antrean obat aktif pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.admin>

<x-layouts.admin :title="'Monitoring Progres Obat & Antrean - Super Admin'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center min-h-[44px] px-1 hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Monitoring Progres Obat Lintas Cabang</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Standar Permenkes RI No. 73/2016 | Pengawasan Eksekutif
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3 leading-tight">
                    Monitoring Progres Obat &amp; Antrean Cabang
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    Pengawasan eksekutif terhadap obat yang sedang diracik dan diproses di seluruh cabang apotek secara langsung.
                </p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                    Monitoring Aktif Live
                </span>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
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
                    Akumulasi item obat dalam antrean peracikan apoteker
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pesanan Sedang Diproses</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-extrabold text-emerald-800 border border-emerald-200">
                        Antrean Aktif
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-emerald-700 tracking-tight">
                    {{ $totalDiproses }} Transaksi
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Sedang ditelaah &amp; disiapkan di meja farmasi cabang
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Siap Diambil Pasien</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-700 px-2.5 py-0.5 text-xs font-extrabold text-white">
                        Siap Serah
                    </span>
                </div>
                <div class="mt-3 text-3xl font-black text-slate-900 tracking-tight">
                    {{ $totalSiap }} Pesanan
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Obat selesai dikemas dan siap diserahkan di loket
                </div>
            </div>
        </div>

        <!-- Filter Cabang -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-700">
                <span>Filter Cabang Apotek:</span>
                <a href="{{ route('admin.progres.index', ['client' => 'all']) }}" class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl {{ empty($selectedClient) || $selectedClient === 'all' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition">
                    Semua Cabang
                </a>
                @foreach($clients as $c)
                    @php $cSlug = $c->slug ?? $c->id_client; @endphp
                    <a href="{{ route('admin.progres.index', ['client' => $cSlug]) }}" class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl {{ $selectedClient === $cSlug || $selectedClient === $c->id_client ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition">
                        {{ $c->nama_client }}
                    </a>
                @endforeach
            </div>
            <div class="text-xs text-slate-500">
                Total Data: <strong class="text-slate-800">{{ $pesanans->count() }} Pesanan Terpantau</strong>
            </div>
        </div>

        <!-- Tabel Live Monitoring Progres Obat & Antrean -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Status Progres Obat &amp; Antrean Berjalan Lintas Cabang
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan rincian obat yang sedang diproses di setiap cabang apotek
                    </p>
                </div>
                <div class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Aksi perubahan status dilakukan oleh Apoteker Cabang</span>
                </div>
            </div>

            <!-- Mobile Card View: Live Monitoring -->
            <div class="md:hidden space-y-3 text-xs">
                @forelse($pesanans as $order)
                    @php
                        $statusMobile = $order->status ?? $order->status_pesanan ?? 'diproses';
                        $branchQueueMobile = $antreanMap[$order->id_client] ?? 'A-001';
                        $waktuOrderMobile = $order->dibuat_pada ?? $order->created_at;
                    @endphp
                    <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-slate-900">{{ $order->client->nama_client ?? 'Cabang Apotek' }}</h3>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $order->nomor_pesanan }} &bull; {{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $waktuOrderMobile ? \Carbon\Carbon::parse($waktuOrderMobile)->format('d M Y, H:i') : '-' }} WIB</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-[11px]">
                                {{ $order->nomor_antrean ?? 'A-001' }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            @forelse($order->items as $item)
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-800 bg-white px-2.5 py-1.5 rounded-lg border border-slate-200/70">
                                    <span class="truncate">{{ $item->nama_obat ?? $item->obat?->nama_obat ?? 'Obat' }}</span>
                                    <span class="font-bold text-emerald-700 ml-2 shrink-0">{{ $item->kuantitas }}x</span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-500">Tidak ada rincian item.</div>
                            @endforelse
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200/70">
                            <div class="text-[11px] text-slate-600">
                                <span class="font-semibold">{{ $order->slot_waktu ?? 'Segera' }}</span>
                                <span> &bull; Loket: <strong>{{ $branchQueueMobile }}</strong></span>
                            </div>
                            @if($statusMobile === 'diproses')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Diproses Apotek
                                </span>
                            @elseif($statusMobile === 'siap_diambil')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-950 border border-emerald-300">
                                    Siap Diambil
                                </span>
                            @elseif($statusMobile === 'selesai')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-700 text-white">
                                    Selesai Diserahkan
                                </span>
                            @elseif($statusMobile === 'menunggu_pembayaran')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    Menunggu Bayar
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    {{ str_replace('_', ' ', $statusMobile) }}
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                        Belum ada pesanan atau antrean obat aktif pada filter ini.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View: Live Monitoring -->
            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[55rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Cabang &amp; Antrean</th>
                            <th class="p-3.5">No. Pesanan &amp; Pasien</th>
                            <th class="p-3.5">Obat yang Sedang Diproses</th>
                            <th class="p-3.5">Slot Pengambilan</th>
                            <th class="p-3.5">Status Progres</th>
                            <th class="p-3.5">Antrean Berjalan Cabang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($pesanans as $order)
                            @php
                                $status = $order->status ?? $order->status_pesanan ?? 'diproses';
                                $branchQueue = $antreanMap[$order->id_client] ?? 'A-001';
                                $waktuOrder = $order->dibuat_pada ?? $order->created_at;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-sm text-slate-900">{{ $order->client->nama_client ?? 'Cabang Apotek' }}</div>
                                    <div class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-xs">
                                        Antrean: {{ $order->nomor_antrean ?? 'A-001' }}
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $order->nomor_pesanan }}</div>
                                    <div class="text-[11px] text-slate-500">Pasien: {{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $waktuOrder ? \Carbon\Carbon::parse($waktuOrder)->format('d M Y, H:i') : '-' }} WIB</div>
                                </td>
                                <td class="p-3.5 max-w-[280px]">
                                    <div class="space-y-1">
                                        @forelse($order->items as $item)
                                            <div class="flex items-center justify-between text-xs font-semibold text-slate-800 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                                <span class="truncate">{{ $item->nama_obat ?? $item->obat?->nama_obat ?? 'Obat' }}</span>
                                                <span class="font-bold text-emerald-700 ml-2 shrink-0">{{ $item->kuantitas }}x</span>
                                            </div>
                                        @empty
                                            <div class="text-xs text-slate-500">Tidak ada rincian item.</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-semibold text-slate-700">{{ $order->slot_waktu ?? 'Segera' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ str_replace('_', ' ', $order->metode_layanan ?? '-') }}</div>
                                </td>
                                <td class="p-3.5">
                                    @if($status === 'diproses')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Diproses Apotek
                                        </span>
                                    @elseif($status === 'siap_diambil')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-950 border border-emerald-300">
                                            Siap Diambil
                                        </span>
                                    @elseif($status === 'selesai')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-700 text-white">
                                            Selesai Diserahkan
                                        </span>
                                    @elseif($status === 'menunggu_pembayaran')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu Bayar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                            {{ str_replace('_', ' ', $status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-800">Loket: {{ $branchQueue }}</div>
                                    <div class="text-[11px] text-slate-500">Sedang melayani di cabang</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Belum ada pesanan atau antrean obat aktif pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

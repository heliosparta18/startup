@php
    $adminOrderDetailMap = $pesanans->mapWithKeys(function ($o) use ($antreanMap) {
        $branchQueue = $antreanMap[$o->id_client] ?? 'A-001';
        return [
            $o->id_pesanan => [
                'id' => $o->id_pesanan,
                'nomor_pesanan' => $o->nomor_pesanan,
                'nomor_antrean' => $o->nomor_antrean ?? 'A-'.substr($o->nomor_pesanan, -3),
                'cabang' => $o->client->nama_client ?? 'Cabang Apotek',
                'antrean_berjalan' => $branchQueue,
                'status' => $o->status ?? ($o->status_pesanan ?? 'diproses'),
                'metode_layanan' => $o->metode_layanan === 'delivery' ? 'Pengantaran Kurir' : 'Ambil di Apotek (Click & Collect)',
                'slot_waktu' => $o->slot_waktu ?? 'Segera (15 Menit Lagi)',
                'catatan_pesanan' => $o->catatan_pesanan ?: null,
                'waktu' => $o->created_at ? $o->created_at->format('d M Y, H:i') . ' WIB' : ($o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y, H:i') . ' WIB' : '-'),
                'pasien_nama' => $o->pengguna->nama_lengkap ?? 'Pasien Umum',
                'pasien_kontak' => $o->pengguna->telepon_email ?? '-',
                'items' => $o->items->map(fn ($it) => [
                    'nama' => $it->nama_obat ?? ($it->obat?->nama_obat ?? 'Obat'),
                    'qty' => $it->kuantitas,
                    'harga' => 'Rp ' . number_format((float) $it->harga_satuan, 0, ',', '.'),
                    'subtotal' => 'Rp ' . number_format((float) $it->subtotal, 0, ',', '.'),
                ])->values(),
                'total' => 'Rp ' . number_format((float) $o->items->sum('subtotal'), 0, ',', '.'),
            ]
        ];
    });
@endphp
<x-layouts.admin :title="'Monitoring Progres Obat & Antrean - Super Admin'">
    <div x-data="{
        modalDetailPesanan: false,
        activeDetail: null,
        orderDetailMap: @js($adminOrderDetailMap),
        bukaDetail(id) {
            this.activeDetail = this.orderDetailMap[id] || null;
            this.modalDetailPesanan = true;
        }
    }" class="space-y-6 sm:space-y-8">
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
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-2 leading-tight">
                    Monitoring Progres Obat &amp; Antrean Cabang
                </h1>
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

                        @if (!empty($order->catatan_pesanan))
                            <div class="p-2.5 rounded-xl bg-amber-50/90 border border-amber-200/90 text-amber-950 text-xs">
                                <div class="flex items-center gap-1.5 font-bold text-[11px] text-amber-900 mb-0.5">
                                    <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    <span>Catatan Pelanggan:</span>
                                </div>
                                <p class="leading-relaxed font-medium break-words">{{ $order->catatan_pesanan }}</p>
                            </div>
                        @endif

                        <button
                            type="button"
                            @click="bukaDetail('{{ $order->id_pesanan }}')"
                            class="min-h-[44px] w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold transition shadow-2xs cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Lihat Detail Pemesanan</span>
                        </button>

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
                                    <div class="space-y-1.5">
                                        @forelse($order->items as $item)
                                            <div class="flex items-center justify-between text-xs font-semibold text-slate-800 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                                <span class="truncate">{{ $item->nama_obat ?? $item->obat?->nama_obat ?? 'Obat' }}</span>
                                                <span class="font-bold text-emerald-700 ml-2 shrink-0">{{ $item->kuantitas }}x</span>
                                            </div>
                                        @empty
                                            <div class="text-xs text-slate-500">Tidak ada rincian item.</div>
                                        @endforelse

                                        @if(!empty($order->catatan_pesanan))
                                            <div class="p-1.5 rounded-lg bg-amber-50/90 border border-amber-200/90 text-amber-950 text-[11px] leading-tight">
                                                <span class="font-bold text-amber-900 flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                    Catatan:
                                                </span>
                                                <span class="block truncate font-medium mt-0.5" title="{{ $order->catatan_pesanan }}">{{ $order->catatan_pesanan }}</span>
                                            </div>
                                        @endif

                                        <button
                                            type="button"
                                            @click="bukaDetail('{{ $order->id_pesanan }}')"
                                            class="min-h-[44px] inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-emerald-800 font-bold text-[11px] shadow-2xs transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Detail Pemesanan</span>
                                        </button>
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
        <!-- MODAL DETAIL PEMESANAN & CATATAN PELANGGAN (SUPER ADMIN) -->
        <div x-show="modalDetailPesanan" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalDetailPesanan = false" class="omni-bottom-sheet-panel w-full max-w-lg max-h-[90dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white border border-slate-200 p-5 sm:p-7 shadow-2xl space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-emerald-700 text-white font-black text-xs" x-text="activeDetail?.nomor_antrean"></span>
                        <div>
                            <h3 class="text-base font-black text-slate-900" x-text="'Pesanan ' + (activeDetail?.nomor_pesanan || '')"></h3>
                            <p class="text-[11px] text-slate-500" x-text="(activeDetail?.cabang || '') + ' • ' + (activeDetail?.waktu || '')"></p>
                        </div>
                    </div>
                    <button @click="modalDetailPesanan = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl cursor-pointer">&times;</button>
                </div>

                <template x-if="activeDetail">
                    <div class="space-y-4 text-xs">
                        <!-- Info Pasien, Cabang & Layanan -->
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pasien</span>
                                <span class="font-bold text-slate-900 block" x-text="activeDetail.pasien_nama"></span>
                                <span class="text-[11px] text-slate-500 truncate block" x-text="activeDetail.pasien_kontak"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Cabang &amp; Layanan</span>
                                <span class="font-bold text-slate-900 block" x-text="activeDetail.cabang"></span>
                                <span class="text-[11px] text-emerald-800 font-semibold block" x-text="activeDetail.metode_layanan + ' (' + activeDetail.slot_waktu + ')'"></span>
                            </div>
                        </div>

                        <!-- CATATAN PELANGGAN (PROMINENT HIGHLIGHT) -->
                        <div class="p-3.5 rounded-2xl border transition-colors" :class="activeDetail.catatan_pesanan ? 'bg-amber-50/90 border-amber-200 text-amber-950 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-500'">
                            <div class="flex items-center gap-1.5 font-bold text-xs mb-1" :class="activeDetail.catatan_pesanan ? 'text-amber-900' : 'text-slate-600'">
                                <svg class="w-4 h-4 shrink-0" :class="activeDetail.catatan_pesanan ? 'text-amber-700' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>Catatan Pelanggan:</span>
                            </div>
                            <p class="leading-relaxed font-medium" :class="activeDetail.catatan_pesanan ? 'text-slate-900' : 'italic text-slate-400'" x-text="activeDetail.catatan_pesanan || 'Pelanggan tidak meninggalkan catatan khusus pada pesanan ini.'"></p>
                        </div>

                        <!-- DAFTAR ITEM OBAT -->
                        <div class="space-y-2">
                            <span class="font-bold text-slate-800 block">Rincian Obat yang Dipesan:</span>
                            <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden bg-white">
                                <template x-for="(item, idx) in activeDetail.items" :key="idx">
                                    <div class="flex items-center justify-between p-3 gap-2">
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate" x-text="item.nama"></div>
                                            <div class="text-[11px] text-slate-500" x-text="item.qty + 'x @ ' + item.harga"></div>
                                        </div>
                                        <div class="font-extrabold text-slate-900 shrink-0" x-text="item.subtotal"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Total Biaya -->
                        <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-2xl border border-emerald-200">
                            <span class="font-bold text-emerald-950">Total Transaksi</span>
                            <span class="text-base font-black text-emerald-800" x-text="activeDetail.total"></span>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="button" @click="modalDetailPesanan = false" class="min-h-[44px] px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer">
                                Tutup Detail
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-layouts.admin>

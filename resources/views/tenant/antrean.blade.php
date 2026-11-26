@php
    $orderDetailMap = $pesanans->mapWithKeys(function ($o) {
        return [
            $o->id_pesanan => [
                'id' => $o->id_pesanan,
                'nomor_pesanan' => $o->nomor_pesanan,
                'nomor_antrean' => $o->nomor_antrean ?? 'A-'.substr($o->nomor_pesanan, -3),
                'status' => $o->status ?? 'menunggu_pembayaran',
                'metode_layanan' => $o->metode_layanan === 'delivery' ? 'Pengantaran Kurir' : 'Ambil di Apotek (Click & Collect)',
                'slot_waktu' => $o->slot_waktu ?? 'Segera (15 Menit Lagi)',
                'catatan_pesanan' => $o->catatan_pesanan ?: null,
                'waktu' => $o->created_at ? $o->created_at->format('d M Y, H:i') . ' WIB' : ($o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y, H:i') . ' WIB' : '-'),
                'pasien_nama' => $o->pengguna->nama_lengkap ?? 'Pasien Umum',
                'pasien_kontak' => $o->pengguna->telepon_email ?? '-',
                'alamat' => $o->alamat_pengantaran ?? '-',
                'items' => $o->items->map(fn ($it) => [
                    'nama' => $it->nama_obat,
                    'qty' => $it->kuantitas,
                    'harga' => 'Rp ' . number_format((float) $it->harga_satuan, 0, ',', '.'),
                    'subtotal' => 'Rp ' . number_format((float) $it->subtotal, 0, ',', '.'),
                ])->values(),
                'total' => 'Rp ' . number_format((float) $o->items->sum('subtotal'), 0, ',', '.'),
            ]
        ];
    });
@endphp
<x-layouts.tenant :title="'Manajemen Antrean & Progres Status - OmniPharma'">
    <div x-data="{
        modalWalkin: false,
        modalManualAntrean: false,
        modalDetailPesanan: false,
        activeDetail: null,
        orderDetailMap: @js($orderDetailMap),
        bukaDetail(id) {
            this.activeDetail = this.orderDetailMap[id] || null;
            this.modalDetailPesanan = true;
        },
        nomorManual: '{{ $antreanBerjalan }}',
        notifSukses: '{{ session('success') ?? '' }}',
        notifError: '{{ session('error') ?? '' }}',
        loadingId: null,
        rowStatuses: @js($pesanans->mapWithKeys(fn ($o) => [$o->id_pesanan => ($o->status ?? 'menunggu_pembayaran')])),
        statusBadgeText(st) {
            if (st === 'diproses') return 'Diproses Apotek';
            if (st === 'siap_diambil') return 'Siap Diambil';
            if (st === 'selesai') return 'Selesai';
            if (st === 'menunggu_pembayaran') return 'Menunggu Bayar';
            return String(st || '').replaceAll('_', ' ');
        },
        statusBadgeClass(st) {
            if (st === 'diproses') return 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200';
            if (st === 'siap_diambil') return 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-950 border border-emerald-300';
            if (st === 'selesai') return 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-700 text-white';
            if (st === 'menunggu_pembayaran') return 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200';
            return 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200';
        },
        updateStatus(orderId, status) {
            this.loadingId = orderId;
            this.notifError = '';
            fetch('{{ url('/cabang/'.$client.'/pesanan') }}/' + orderId + '/progres-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(res => res.json())
            .then(data => {
                this.loadingId = null;
                if (data.success) {
                    this.rowStatuses[orderId] = data.status || status;
                    this.notifSukses = data.message;
                } else {
                    this.notifError = data.message || 'Gagal mengubah status.';
                }
            })
            .catch(() => {
                this.loadingId = null;
                this.notifError = 'Terjadi gangguan koneksi saat memperbarui status antrean.';
            });
        }
    }" class="space-y-6">

        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Antrean &amp; Progresi Status</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="modalWalkin = true"
                    class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <span>Tambah Antrean Walk-In</span>
                </button>
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Dashboard Cabang
                </a>
            </div>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Standar Permenkes RI No. 73/2016 | Antrean &amp; Progresi Layanan
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-2 leading-tight">
                    Antrean Virtual &amp; Tombol Progresi Status
                </h1>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <button
                    type="button"
                    @click="modalManualAntrean = true"
                    class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <span>Antrean Aktif: <strong class="text-sm" x-text="nomorManual"></strong></span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        <div x-show="notifSukses" x-cloak class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifSukses"></span>
            </div>
            <button @click="notifSukses = ''" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
        </div>

        <div x-show="notifError" x-cloak class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-xs flex items-center justify-between">
            <span x-text="notifError"></span>
            <button @click="notifError = ''" class="text-rose-700 hover:text-rose-900 font-bold">&times;</button>
        </div>

        <!-- Panel Kontrol Antrean Berjalan (Apoteker Control Panel): Bersih Putih, Tanpa Tema Gelap -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left: Info Antrean Berjalan Saat Ini -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-3xl bg-emerald-700 text-white flex flex-col items-center justify-center border border-emerald-800 shadow-xs shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Sedang Dipanggil</span>
                        <span class="text-2xl sm:text-3xl font-black tracking-tight">{{ $antreanBerjalan }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                Loket Penyerahan Aktif
                            </span>
                            <span class="text-xs text-slate-500">Cabang {{ ucwords(str_replace('-', ' ', $client)) }}</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-1">
                            Antrean Farmasi Berjalan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pasien dengan nomor antrean ini sedang dilayani di loket pengambilan obat.
                        </p>
                    </div>
                </div>

                <!-- Right: Action Buttons to advance queue -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <form action="{{ route('tenant.antrean.update', ['client' => $client]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="panggil_berikutnya">
                        <button
                            type="submit"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            <span>Panggil Berikutnya (+1)</span>
                        </button>
                    </form>

                    <button
                        type="button"
                        @click="modalManualAntrean = true"
                        class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition cursor-pointer">
                        <span>Atur Nomor Antrean</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel Antrean Pesanan & Progresi Status (Role Apoteker Control): Bersih Putih, Bebas Tema Gelap -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        Daftar Antrean &amp; Kontrol Progresi Status Pesanan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Apoteker dapat memajukan siklus penyiapan obat dari status diproses, siap diambil, hingga selesai.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span>Total: {{ $pesanans->count() }} Pesanan</span>
                </div>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden space-y-3">
                @forelse($pesanans as $order)
                    @php
                        $status = $order->status ?? 'menunggu_pembayaran';
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3 text-xs" x-data="{ get st() { return rowStatuses['{{ $order->id_pesanan }}'] ?? '{{ $status }}'; } }">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl font-black text-xs shrink-0 {{ $order->nomor_antrean === $antreanBerjalan ? 'bg-emerald-700 text-white' : 'bg-slate-200/80 text-slate-800' }}">
                                    {{ $order->nomor_antrean ?? 'A-'.substr($order->nomor_pesanan, -3) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate">{{ $order->nomor_pesanan }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $order->created_at ? $order->created_at->format('H:i') : '-' }} WIB</div>
                                </div>
                            </div>
                            <span :class="statusBadgeClass(st)" x-text="statusBadgeText(st)"></span>
                        </div>

                        <div class="space-y-1">
                            <div class="font-bold text-slate-900">{{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $order->pengguna->telepon_email ?? '-' }}</div>
                            @if ($order->metode_layanan === 'delivery')
                                <div class="pt-1 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        Kurir ({{ $order->jarak_km ?? 2 }} km)
                                    </span>
                                    @if ($order->maps_route_url)
                                        <a href="{{ $order->maps_route_url }}" target="_blank" rel="noopener noreferrer" class="min-h-[44px] inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold transition shadow-xs">
                                            Buka Rute Maps
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="text-[11px] font-semibold text-slate-700">Slot: {{ $order->slot_waktu ?? '15 Menit Lagi' }}</div>
                            @endif
                        </div>

                        <div class="space-y-1 bg-white rounded-xl p-2.5 border border-slate-200/80">
                            @foreach($order->items->take(2) as $item)
                                <div class="text-xs text-slate-800 truncate font-semibold">&bull; {{ $item->nama_obat }} ({{ $item->kuantitas }}x)</div>
                            @endforeach
                            @if($order->items->count() > 2)
                                <div class="text-[10px] text-emerald-700 font-bold">+{{ $order->items->count() - 2 }} obat lainnya</div>
                            @endif
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

                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/80">
                            <button
                                x-show="st === 'menunggu_pembayaran'"
                                x-cloak
                                type="button"
                                @click="updateStatus('{{ $order->id_pesanan }}', 'diproses')"
                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                class="flex-1 min-h-[44px] px-3.5 py-2 rounded-xl border border-emerald-600/50 bg-emerald-50 text-emerald-900 hover:bg-emerald-100 font-bold text-xs transition cursor-pointer disabled:opacity-50">
                                Mulai Proses
                            </button>
                            <button
                                x-show="st === 'diproses'"
                                x-cloak
                                type="button"
                                @click="updateStatus('{{ $order->id_pesanan }}', 'siap_diambil')"
                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                class="flex-1 min-h-[44px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50">
                                Tandai Siap Ambil
                            </button>
                            <button
                                x-show="st === 'siap_diambil'"
                                x-cloak
                                type="button"
                                @click="updateStatus('{{ $order->id_pesanan }}', 'selesai')"
                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                class="flex-1 min-h-[44px] px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50">
                                Selesaikan Penyerahan
                            </button>
                            <span x-show="st === 'selesai'" x-cloak class="text-xs text-emerald-800 font-extrabold py-1">Transaksi Tuntas</span>
                            <button
                                x-show="st !== 'selesai' && st !== 'dibatalkan'"
                                x-cloak
                                type="button"
                                @click="if(confirm('Batalkan pesanan ini?')) updateStatus('{{ $order->id_pesanan }}', 'dibatalkan')"
                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                class="min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-bold transition cursor-pointer disabled:opacity-50">
                                Batalkan
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500 font-semibold">
                        Belum ada transaksi atau antrean masuk di cabang ini hari ini.
                    </div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[50rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Antrean &amp; Pesanan</th>
                            <th class="p-3.5">Pasien</th>
                            <th class="p-3.5">Slot Jam Ambil</th>
                            <th class="p-3.5">Obat yang Disiapkan</th>
                            <th class="p-3.5">Status Progres</th>
                            <th class="p-3.5 text-right">Tombol Progresi Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pesanans as $order)
                            @php
                                $status = $order->status ?? 'menunggu_pembayaran';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl font-black text-xs {{ $order->nomor_antrean === $antreanBerjalan ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-800' }}">
                                            {{ $order->nomor_antrean ?? 'A-'.substr($order->nomor_pesanan, -3) }}
                                        </span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $order->nomor_pesanan }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $order->created_at ? $order->created_at->format('H:i') : '-' }} WIB</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $order->pengguna->telepon_email ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    @if ($order->metode_layanan === 'delivery')
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                Kurir ({{ $order->jarak_km ?? 2 }} km)
                                            </span>
                                            @if ($order->alamat_pengantaran)
                                                <span class="text-[11px] text-slate-600 max-w-[200px] truncate" title="{{ $order->alamat_pengantaran }}">
                                                    {{ $order->alamat_pengantaran }}
                                                </span>
                                            @endif
                                            @if ($order->maps_route_url)
                                                <a
                                                    href="{{ $order->maps_route_url }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="min-h-[44px] inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold transition shadow-xs"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span>Buka Rute Maps</span>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            <span>{{ $order->slot_waktu ?? '15 Menit Lagi' }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 max-w-[260px]">
                                    <div class="space-y-1.5">
                                        @foreach($order->items->take(2) as $item)
                                            <div class="text-xs text-slate-800 truncate font-semibold">
                                                • {{ $item->nama_obat }} ({{ $item->kuantitas }}x)
                                            </div>
                                        @endforeach
                                        @if($order->items->count() > 2)
                                            <div class="text-[10px] text-emerald-700 font-bold">
                                                +{{ $order->items->count() - 2 }} obat lainnya
                                            </div>
                                        @endif

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
                                <td class="p-3.5" x-data="{ get st() { return rowStatuses['{{ $order->id_pesanan }}'] ?? '{{ $status }}'; } }">
                                    <span :class="statusBadgeClass(st)" x-text="statusBadgeText(st)">
                                        @if($status === 'diproses') Diproses Apotek
                                        @elseif($status === 'siap_diambil') Siap Diambil
                                        @elseif($status === 'selesai') Selesai
                                        @elseif($status === 'menunggu_pembayaran') Menunggu Bayar
                                        @else {{ str_replace('_', ' ', $status) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="p-3.5 text-right" x-data="{ get st() { return rowStatuses['{{ $order->id_pesanan }}'] ?? '{{ $status }}'; } }">
                                    <div class="inline-flex items-center justify-end gap-2 flex-wrap">
                                        <button
                                            x-show="st === 'menunggu_pembayaran'"
                                            x-cloak
                                            type="button"
                                            @click="updateStatus('{{ $order->id_pesanan }}', 'diproses')"
                                            :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                            class="min-h-[44px] px-3.5 py-2 rounded-xl border border-emerald-600/50 bg-emerald-50 text-emerald-900 hover:bg-emerald-100 hover:border-emerald-600 font-bold text-xs shadow-2xs transition cursor-pointer disabled:opacity-50">
                                            Mulai Proses
                                        </button>
                                        <button
                                            x-show="st === 'diproses'"
                                            x-cloak
                                            type="button"
                                            @click="updateStatus('{{ $order->id_pesanan }}', 'siap_diambil')"
                                            :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                            class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50">
                                            Tandai Siap Ambil
                                        </button>
                                        <button
                                            x-show="st === 'siap_diambil'"
                                            x-cloak
                                            type="button"
                                            @click="updateStatus('{{ $order->id_pesanan }}', 'selesai')"
                                            :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                            class="min-h-[44px] px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50">
                                            Selesaikan Penyerahan
                                        </button>
                                        <span x-show="st === 'selesai'" x-cloak class="text-xs text-emerald-800 font-extrabold pr-2">Transaksi Tuntas</span>

                                        <button
                                            x-show="st !== 'selesai' && st !== 'dibatalkan'"
                                            x-cloak
                                            type="button"
                                            @click="if(confirm('Batalkan pesanan ini?')) updateStatus('{{ $order->id_pesanan }}', 'dibatalkan')"
                                            :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                            class="min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-bold transition cursor-pointer disabled:opacity-50"
                                            title="Batalkan Pesanan">
                                            Batalkan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 font-semibold">Belum ada transaksi atau antrean masuk di cabang ini hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL 1: Tambah Antrean Walk-In (Pasien Datang Langsung) -->
        <div x-show="modalWalkin" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalWalkin = false" class="omni-bottom-sheet-panel w-full max-w-md max-h-[90dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white border border-slate-200 p-5 sm:p-8 shadow-2xl space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Tambah Antrean Walk-In</h3>
                        <p class="text-xs text-slate-500">Penerbitan tiket antrean pasien datang langsung di apotek</p>
                    </div>
                    <button @click="modalWalkin = false" class="text-slate-500 hover:text-slate-800 text-xl font-bold min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl cursor-pointer">&times;</button>
                </div>

                <form action="{{ route('tenant.antrean.walkin', ['client' => $client]) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pasien / Tamu</label>
                        <input type="text" name="nama_pasien" required placeholder="Contoh: Ny. Sri Wahyuni" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Obat / Keluhan</label>
                        <textarea name="catatan" rows="2" placeholder="Contoh: Pembelian obat batuk sirup langsung di kasir" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700 focus:outline-none"></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalWalkin = false" class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs cursor-pointer">Cetak &amp; Terbitkan Tiket</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Atur Nomor Antrean Berjalan Manual -->
        <div x-show="modalManualAntrean" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalManualAntrean = false" class="omni-bottom-sheet-panel w-full max-w-sm max-h-[85dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white border border-slate-200 p-5 sm:p-8 shadow-2xl space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">Atur Nomor Antrean Berjalan</h3>
                    <button @click="modalManualAntrean = false" class="text-slate-500 hover:text-slate-800 text-xl font-bold min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl cursor-pointer">&times;</button>
                </div>

                <form action="{{ route('tenant.antrean.update', ['client' => $client]) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set_nomor">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Antrean Aktif</label>
                        <input type="text" name="nomor_antrean" value="{{ $antreanBerjalan }}" placeholder="Contoh: A-005" required class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base font-mono font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700 focus:outline-none text-center text-slate-900">
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalManualAntrean = false" class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs cursor-pointer">Simpan Nomor</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Detail Lengkap Pemesanan & Catatan Pelanggan -->
        <div x-show="modalDetailPesanan" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalDetailPesanan = false" class="omni-bottom-sheet-panel w-full max-w-lg max-h-[90dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white border border-slate-200 p-5 sm:p-7 shadow-2xl space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-emerald-700 text-white font-black text-xs" x-text="activeDetail?.nomor_antrean"></span>
                        <div>
                            <h3 class="text-base font-black text-slate-900" x-text="'Pesanan ' + (activeDetail?.nomor_pesanan || '')"></h3>
                            <p class="text-[11px] text-slate-500" x-text="activeDetail?.waktu"></p>
                        </div>
                    </div>
                    <button @click="modalDetailPesanan = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl cursor-pointer">&times;</button>
                </div>

                <template x-if="activeDetail">
                    <div class="space-y-4 text-xs">
                        <!-- Info Pasien & Layanan -->
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pasien</span>
                                <span class="font-bold text-slate-900 block" x-text="activeDetail.pasien_nama"></span>
                                <span class="text-[11px] text-slate-500 truncate block" x-text="activeDetail.pasien_kontak"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Layanan &amp; Slot</span>
                                <span class="font-bold text-emerald-800 block" x-text="activeDetail.metode_layanan"></span>
                                <span class="text-[11px] text-slate-600 block" x-text="activeDetail.slot_waktu"></span>
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
</x-layouts.tenant>

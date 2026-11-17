<x-layouts.tenant title="Rekam Medis &amp; Daftar Pasien Cabang - OmniPharma">
    <div x-data="{
        historyModal: false,
        selectedPasien: { nama: '', kontak: '', orders: [] },
        openHistory(nama, kontak, orders) {
            this.selectedPasien = { nama: nama, kontak: kontak, orders: orders || [] };
            this.historyModal = true;
        }
    }" class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Rekam Medis &amp; Pasien</span>
            </div>
            <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Dashboard Cabang
            </a>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Kepatuhan Privasi Cabang | Standar Permenkes RI No. 73/2016
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-2 leading-tight">
                    Rekam Pasien &amp; Riwayat Transaksi Cabang
                </h1>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                    {{ count($pasiens) }} Pasien Cabang Terdata
                </span>
            </div>
        </div>

        <!-- Tabel Pasien Terisolasi Cabang -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Pasien Terlayani Cabang</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Hanya menampilkan pasien dengan riwayat pesanan pada cabang ini</p>
                </div>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($pasiens as $pasien)
                    @php
                        $ordersCollection = collect($pasien->pesanan_terbaru ?? []);
                        $latestOrder = $ordersCollection->first();
                        $ordersPayload = $ordersCollection->map(fn ($o) => [
                            'nomor' => $o->nomor_pesanan,
                            'tanggal' => $o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y H:i') : '-',
                            'status' => ucfirst(str_replace('_', ' ', (string) ($o->status ?? $o->status_pesanan ?? '-'))),
                            'total' => number_format((float) ($o->total_harga ?? 0), 0, ',', '.'),
                            'items' => $o->items->count(),
                        ])->values();
                    @endphp
                    <div class="p-4 space-y-2.5 text-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900">{{ $pasien->nama_lengkap }}</div>
                                <div class="text-[11px] font-mono text-slate-500 truncate">{{ $pasien->telepon_email ?? '-' }}</div>
                            </div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                {{ (int) ($pasien->pesanan_count ?? 0) }} order
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-[11px] text-slate-600">
                            <span>{{ (int) ($pasien->resep_count ?? 0) }} resep</span>
                            @if ($latestOrder)
                                <span class="font-semibold text-slate-800">{{ $latestOrder->nomor_pesanan }}</span>
                            @endif
                        </div>
                        <button type="button"
                            @click='openHistory(@json($pasien->nama_lengkap), @json($pasien->telepon_email ?? "-"), @json($ordersPayload))'
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 font-bold text-xs transition cursor-pointer">
                            Lihat Riwayat
                        </button>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">Belum ada pasien dengan riwayat transaksi di cabang apotek ini.</div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[46rem] text-left text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Nama Pasien</th>
                            <th class="p-3.5">Kontak / Akun</th>
                            <th class="p-3.5 text-center">Order Cabang</th>
                            <th class="p-3.5 text-center">Order Resep</th>
                            <th class="p-3.5">Transaksi Terakhir</th>
                            <th class="p-3.5 text-right">Riwayat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($pasiens as $pasien)
                            @php
                                $ordersCollection = collect($pasien->pesanan_terbaru ?? []);
                                $latestOrder = $ordersCollection->first();
                                $ordersPayload = $ordersCollection->map(fn ($o) => [
                                    'nomor' => $o->nomor_pesanan,
                                    'tanggal' => $o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y H:i') : '-',
                                    'status' => ucfirst(str_replace('_', ' ', (string) ($o->status ?? $o->status_pesanan ?? '-'))),
                                    'total' => number_format((float) ($o->total_harga ?? 0), 0, ',', '.'),
                                    'items' => $o->items->count(),
                                ])->values();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-3.5 font-bold text-slate-900">
                                    {{ $pasien->nama_lengkap }}
                                </td>
                                <td class="p-3.5 text-slate-600 font-mono">
                                    {{ $pasien->telepon_email ?? '-' }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ (int) ($pasien->pesanan_count ?? 0) }} order
                                    </span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ (int) ($pasien->resep_count ?? 0) }} resep
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500">
                                    @if ($latestOrder)
                                        <span class="font-bold text-slate-800">{{ $latestOrder->nomor_pesanan }}</span>
                                        <span class="block text-[11px]">{{ $latestOrder->dibuat_pada ? \Carbon\Carbon::parse($latestOrder->dibuat_pada)->format('d M Y H:i') : '-' }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-3.5 text-right">
                                    <button type="button"
                                        @click='openHistory(@json($pasien->nama_lengkap), @json($pasien->telepon_email ?? "-"), @json($ordersPayload))'
                                        class="min-h-[44px] px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-[11px] transition cursor-pointer">
                                        Lihat Riwayat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Belum ada pasien dengan riwayat transaksi di cabang apotek ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Riwayat Order Pasien Cabang -->
        <div x-show="historyModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/60 p-0 sm:p-4 backdrop-blur-xs">
            <div @click.away="historyModal = false" class="omni-bottom-sheet-panel w-full max-w-lg max-h-[88dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900" x-text="selectedPasien.nama"></h3>
                        <p class="text-xs text-slate-500 font-mono" x-text="selectedPasien.kontak"></p>
                    </div>
                    <button type="button" @click="historyModal = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer">&times;</button>
                </div>

                <div class="space-y-2.5 max-h-[55vh] overflow-y-auto text-xs">
                    <template x-for="ord in selectedPasien.orders" :key="ord.nomor">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900" x-text="ord.nomor"></div>
                                <div class="text-[11px] text-slate-500" x-text="ord.tanggal + ' | ' + ord.items + ' item'"></div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-emerald-700" x-text="'Rp ' + ord.total"></div>
                                <div class="text-[10px] font-bold text-slate-600" x-text="ord.status"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="historyModal = false" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.tenant>

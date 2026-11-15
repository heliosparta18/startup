<x-layouts.tenant :title="'Analitik Penjualan & Rekonsiliasi - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Header & Filter Periode -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>Audit Finansial &amp; Pelaporan Cabang {{ ucwords(str_replace('-', ' ', $client)) }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3">
                    Analitik Penjualan &amp; Rekonsiliasi Cabang
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Laporan transaksi lunas dan periode penjualan berdasarkan data basis data riil.
                </p>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-2 text-xs w-full lg:w-auto">
                <input type="date" name="periode_mulai" value="{{ $periodeMulai }}"
                    class="w-full sm:w-auto min-h-[44px] px-3 py-2 border border-slate-300 bg-white rounded-xl text-slate-900 focus:border-emerald-600 focus:outline-none">
                <span class="text-slate-500 font-bold text-center sm:text-left">s/d</span>
                <input type="date" name="periode_selesai" value="{{ $periodeSelesai }}"
                    class="w-full sm:w-auto min-h-[44px] px-3 py-2 border border-slate-300 bg-white rounded-xl text-slate-900 focus:border-emerald-600 focus:outline-none">
                <button type="submit"
                    class="w-full sm:w-auto min-h-[44px] px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(auth()->user()?->hasRole('super_admin'))
                    <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold transition">
                        &larr; Pusat Admin
                    </a>
                @endif
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs font-bold flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Kartu Metrik Penjualan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pendapatan Penjualan</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">Rp {{ number_format((float) ($summary['total_pendapatan'] ?? 0), 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-700 font-semibold mt-1">Akumulasi nominal transaksi pada periode ini</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Volume Pesanan</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ (int) ($summary['total_pesanan'] ?? 0) }} Pesanan</p>
                <p class="text-xs text-slate-500 mt-1">Transaksi selesai &amp; siap diambil</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rata-rata Keranjang (AOV)</p>
                <p class="text-2xl sm:text-3xl font-black text-emerald-700 mt-2">Rp {{ number_format((float) ($summary['rata_rata_transaksi'] ?? 0), 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Estimasi rata-rata per pesanan</p>
            </div>
        </div>

        <!-- Panel Kendali Periode Buku & Ekspor Data -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2.5">
                @if(isset($ringkasanTerkunci) && $ringkasanTerkunci)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">
                        STATUS: PERIODE TERKUNCI (IMMUTABLE)
                    </span>
                    <span class="text-xs text-slate-600">
                        Dikunci sejak <strong>{{ \Carbon\Carbon::parse($ringkasanTerkunci->dikunci_pada ?? $ringkasanTerkunci->created_at)->format('d M Y H:i') }}</strong>
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        STATUS: PERIODE TERBUKA (OPEN)
                    </span>
                    <span class="text-xs text-slate-500">Data masih dapat berubah hingga periode ditutup oleh manajemen.</span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                    class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Ekspor CSV</span>
                </a>

                @if(!isset($ringkasanTerkunci) || !$ringkasanTerkunci)
                    <form method="POST" action="{{ route('tenant.laporan.tutup-periode', ['client' => $client]) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin mengunci periode buku ini? Data yang telah terkunci tidak dapat diubah lagi.')">
                        @csrf
                        <input type="hidden" name="periode_mulai" value="{{ $periodeMulai }}">
                        <input type="hidden" name="periode_selesai" value="{{ $periodeSelesai }}">
                        <button type="submit"
                            class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition cursor-pointer shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <span>Tutup &amp; Kunci Periode Buku</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Tabel Rincian Transaksi -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-2">
                <h2 class="text-sm font-extrabold text-slate-900">Rincian Transaksi Pesanan</h2>
                <span class="text-xs text-slate-500">Maksimal 100 baris terbaru</span>
            </div>

            <!-- Mobile Card View: Rincian Transaksi -->
            <div class="md:hidden divide-y divide-slate-100 text-xs">
                @forelse($summary['daftar_pesanan'] as $order)
                    <article class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-slate-900">{{ $order->nomor_pesanan }}</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $order->dibuat_pada ? \Carbon\Carbon::parse($order->dibuat_pada)->format('d M Y H:i') : '-' }}</p>
                            </div>
                            <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize">
                                {{ str_replace('_', ' ', $order->status ?? $order->status_pesanan ?? '-') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-100">
                            <span class="capitalize text-slate-600">{{ str_replace('_', ' ', $order->metode_layanan ?? '-') }}</span>
                            <span class="font-extrabold text-slate-900">Rp {{ number_format((float) ($order->total_harga ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-10 text-center text-slate-500">
                        Belum ada transaksi pesanan tercatat pada rentang tanggal ini.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View: Rincian Transaksi -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[40rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Nomor Pesanan</th>
                            <th class="px-6 py-3.5">Tanggal</th>
                            <th class="px-6 py-3.5">Metode Layanan</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($summary['daftar_pesanan'] as $order)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $order->nomor_pesanan }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $order->dibuat_pada ? \Carbon\Carbon::parse($order->dibuat_pada)->format('d M Y H:i') : '-' }}</td>
                                <td class="px-6 py-4 capitalize text-slate-700">{{ str_replace('_', ' ', $order->metode_layanan ?? '-') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize">
                                        {{ str_replace('_', ' ', $order->status ?? $order->status_pesanan ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-extrabold text-slate-900">
                                    Rp {{ number_format((float) ($order->total_harga ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    Belum ada transaksi pesanan tercatat pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.tenant>

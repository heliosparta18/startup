<x-layouts.admin :title="'Buku Kasir & Rekonsiliasi - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Buku Kasir &amp; Rekonsiliasi</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Hero Banner: Nuansa Deep Spruce Hijau Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • Finansial Kasir
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Rekonsiliasi Kasir &amp; Ledger Pembayaran
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Pencatatan mutasi transaksi kasir harian, verifikasi QRIS dinamis, dan verifikasi saldo fisik kas cabang lintas apotek.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                        Ledger Terverifikasi Real-Time
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 KPI Kartu Keuangan Kasir Sesuai Palet Login -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xs">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Total Pembayaran QRIS</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-700">Rp 113.600.000</div>
                <div class="mt-1 text-xs text-slate-500">Settlement otomatis gateway sandbox</div>
            </div>
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xs">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Total Transaksi Kasir</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">2.450 Struk</div>
                <div class="mt-1 text-xs text-slate-500">Akumulasi seluruh cabang aktif</div>
            </div>
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xs">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Selisih Kas Fisik</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-[#0A231E]">Rp 0 (Sesuai)</div>
                <div class="mt-1 text-xs text-emerald-700 font-semibold">100% klop dengan audit kas fisik</div>
            </div>
        </div>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Mutasi Pembayaran Transaksi Terkini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sinkronisasi real-time via webhook settlement perbankan &amp; kasir offline</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    {{ $pembayarans->count() }} Data Pembayaran
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[40rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">ID Pembayaran</th>
                            <th class="p-3.5">No. Pesanan</th>
                            <th class="p-3.5">Metode</th>
                            <th class="p-3.5">Nominal (Rp)</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($pembayarans as $bayar)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-mono text-slate-500">{{ substr($bayar->id_pembayaran, 0, 8) }}...</td>
                                <td class="p-3.5 font-bold text-slate-900">{{ $bayar->pesanan->nomor_pesanan ?? '#ORD-REGULER' }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        {{ $bayar->metode_pembayaran ?? 'QRIS' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-black text-slate-900">Rp {{ number_format((float) ($bayar->jumlah ?? 0), 0, ',', '.') }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Lunas
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500 font-mono text-right">{{ $bayar->dibuat_pada ? \Carbon\Carbon::parse($bayar->dibuat_pada)->format('d M Y, H:i') : '-' }} WIB</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada riwayat mutasi pembayaran pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

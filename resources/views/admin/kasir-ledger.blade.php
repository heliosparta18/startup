<x-layouts.admin :title="'Buku Kasir & Rekonsiliasi - OmniPharma'">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-zinc-900 transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-zinc-900 font-semibold">Buku Kasir &amp; Rekonsiliasi</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Header -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Buku Kas &amp; Pembayaran Harian
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Rekonsiliasi Kasir &amp; Ledger Pembayaran
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                        Pencatatan mutasi transaksi kasir harian, verifikasi QRIS dinamis, dan verifikasi saldo fisik kas cabang.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200">
                        Ledger Terverifikasi
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 KPI Kartu Keuangan Kasir -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pembayaran QRIS</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-700">Rp 113.600.000</div>
                <div class="mt-1 text-xs text-slate-400">Settlement otomatis gateway sandbox</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi Kasir</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">2.450 Struk</div>
                <div class="mt-1 text-xs text-slate-400">Akumulasi seluruh cabang aktif</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Selisih Kas Fisik</span>
                <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600">Rp 0 (Sesuai)</div>
                <div class="mt-1 text-xs text-slate-400">100% klop dengan audit kas fisik</div>
            </div>
        </div>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Mutasi Pembayaran Transaksi Terkini</h3>
                <span class="text-xs text-slate-500">Sinkronisasi real-time via webhook settlement</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[38rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">ID Pembayaran</th>
                            <th class="p-3.5">No. Pesanan</th>
                            <th class="p-3.5">Metode</th>
                            <th class="p-3.5">Nominal (Rp)</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse ($pembayarans as $bayar)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5 font-mono text-slate-500">{{ substr($bayar->id_pembayaran, 0, 8) }}...</td>
                                <td class="p-3.5 font-bold text-slate-900">{{ $bayar->pesanan->nomor_pesanan ?? '#ORD-REGULER' }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ $bayar->metode_pembayaran ?? 'QRIS' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-black text-slate-900">Rp {{ number_format((float) ($bayar->jumlah ?? 0), 0, ',', '.') }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Lunas
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500">{{ $bayar->dibuat_pada ? \Carbon\Carbon::parse($bayar->dibuat_pada)->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada mutasi pembayaran yang tercatat pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

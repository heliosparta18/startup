<x-layouts.tenant :title="'Rekam Pasien - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Portal Apoteker</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Rekam Data Pasien</span>
            </div>
            <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
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
                        Rekam Medis Farmasi Digital
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Buku Induk &amp; Riwayat Pasien Cabang
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Daftar pasien yang terhubung dan memiliki riwayat transaksi atau penebusan resep di Cabang {{ ucwords(str_replace('-', ' ', $client)) }}.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        {{ count($pasiens ?? []) }} Pasien Terdaftar
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rekam Pasien -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Daftar Pasien Terdaftar</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kerahasiaan data pasien terlindungi standar etika kefarmasian</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Total: {{ count($pasiens ?? []) }} Data
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[38rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Nama Pasien</th>
                            <th class="p-3.5">Kontak (Email/HP)</th>
                            <th class="p-3.5">Riwayat Pesanan</th>
                            <th class="p-3.5">Terdaftar Sejak</th>
                            <th class="p-3.5 text-right">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($pasiens as $pasien)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-sm text-slate-900">{{ $pasien->nama_lengkap ?? $pasien->name ?? 'Pasien' }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">ID: {{ substr($pasien->id_pengguna ?? $pasien->id ?? '', 0, 8) }}...</div>
                                </td>
                                <td class="p-3.5 text-slate-600 font-mono">
                                    <div>{{ $pasien->telepon_email ?? $pasien->email ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        {{ $pasien->pesanan_count ?? 1 }} Transaksi
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500">
                                    {{ $pasien->terdaftar_pada ?? now()->format('Y-m-d') }}
                                </td>
                                <td class="p-3.5 text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">Belum ada pasien yang terdaftar di cabang apotek ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.tenant>

<x-layouts.tenant :title="'Rekam Pasien - OmniPharma'">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-zinc-900 transition">Portal Apoteker</a>
                <span>/</span>
                <span class="text-zinc-900 font-semibold">Rekam Data Pasien</span>
            </div>
            <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                &larr; Kembali ke Antrean
            </a>
        </div>

        <!-- Header -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Rekam Medis Farmasi Digital
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Buku Induk &amp; Riwayat Pasien Cabang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                        Daftar pasien yang terhubung dan memiliki riwayat transaksi atau penebusan resep di Cabang {{ ucwords(str_replace('-', ' ', $client)) }}.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200">
                        Total: {{ count($pasiens ?? []) }} Pasien
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rekam Pasien -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Daftar Pasien Terdaftar</h3>
                <span class="text-xs font-semibold text-slate-500">Kerahasiaan data terlindungi standar medis</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs min-w-[36rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Nama Pasien</th>
                            <th class="p-3.5">Kontak (Email/HP)</th>
                            <th class="p-3.5">Riwayat Pesanan</th>
                            <th class="p-3.5">Terdaftar Sejak</th>
                            <th class="p-3.5">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse ($pasiens as $pasien)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-sm text-slate-900">{{ $pasien->nama_lengkap ?? $pasien->name ?? 'Pasien' }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">ID: {{ substr($pasien->id_pengguna ?? $pasien->id ?? '', 0, 8) }}...</div>
                                </td>
                                <td class="p-3.5 text-slate-600">
                                    <div>{{ $pasien->telepon_email ?? $pasien->email ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $pasien->pesanan_count ?? 1 }} Transaksi
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500">
                                    {{ $pasien->terdaftar_pada ?? now()->format('Y-m-d') }}
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Aktif
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

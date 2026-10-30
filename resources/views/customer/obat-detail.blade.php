<x-layouts.customer :title="'Detail Obat : '.($obat->nama_obat ?? 'Amodiaquine 200mg')">
    <div class="space-y-4">
        <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex min-h-11 items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400">
            ← Kembali ke Katalog
        </a>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-850 space-y-4">
            <div class="flex h-36 w-full items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700">
                <span class="text-xs font-semibold">[ Foto Kemasan Obat Resmi ]</span>
            </div>

            <div>
                <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                    {{ ($obat->kategori->wajib_resep ?? true) ? 'WAJIB RESEP DOKTER' : 'OBAT BEBAS' }}
                </span>
                <h1 class="mt-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $obat->nama_obat ?? 'Amodiaquine 200mg' }}</h1>
                <p class="text-xs text-zinc-500">{{ $obat->merek ?? 'Kimia Farma' }} • Strip 10 tablet</p>
                <div class="mt-3 text-lg font-extrabold text-zinc-900 dark:text-zinc-100">
                    Rp {{ number_format((float) ($obat->harga ?? 45000), 0, ',', '.') }}
                </div>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-xs border border-zinc-100 dark:bg-zinc-800/50 dark:border-zinc-800 space-y-2">
                <div class="font-bold text-zinc-800 dark:text-zinc-200">Indikasi &amp; Dosis:</div>
                <p class="text-zinc-600 dark:text-zinc-400">Terapi infeksi protozoa dan lini profilaksis sesuai anjuran dan resep dokter spesialis.</p>
                <div class="font-bold text-zinc-800 dark:text-zinc-200 pt-1">Ketersediaan Stok Cabang Banyuwangi Kota:</div>
                <p class="text-emerald-700 dark:text-emerald-400 font-medium">Tersedia (12 strip) • Batch #BTC-2024A (Exp: 10 Okt 2026)</p>
            </div>

            <div class="pt-2">
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="w-full min-h-11 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    Pesan Lewat Katalog
                </a>
            </div>
        </article>
    </div>
</x-layouts.customer>

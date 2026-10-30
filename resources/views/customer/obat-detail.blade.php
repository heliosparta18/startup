<x-layouts.customer :title="'Detail Obat - '.($obat->nama_obat ?? 'Amodiaquine 200mg')">
    <div class="space-y-4 max-w-4xl mx-auto">
        <a href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center gap-2 text-xs font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Katalog Obat</span>
        </a>

        @php
            $isResep = $obat->kategori->wajib_resep ?? true;
        @endphp

        <article class="grid grid-cols-1 md:grid-cols-12 gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs dark:border-zinc-800 dark:bg-zinc-900">
            <!-- Left Column: Visual & Identitas Dasar -->
            <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                <div class="flex h-48 w-full items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-400 border border-slate-200 dark:border-zinc-700">
                    <svg class="h-16 w-16 text-slate-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>

                <div>
                    @if($isResep)
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                            WAJIB RESEP DOKTER
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            OBAT BEBAS
                        </span>
                    @endif

                    <h1 class="mt-2 text-xl font-bold text-slate-900 dark:text-zinc-100">
                        {{ $obat->nama_obat ?? 'Amodiaquine 200mg' }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        {{ $obat->merek ?? 'Kimia Farma' }} • {{ $obat->jenis_sediaan ?? 'Strip 10 tablet' }}
                    </p>
                    <div class="mt-3 text-2xl font-extrabold text-slate-900 dark:text-zinc-100">
                        Rp {{ number_format((float) ($obat->harga ?? 45000), 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Detail Farmasi & Ketersediaan -->
            <div class="md:col-span-7 flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="rounded-xl bg-slate-50 p-4 text-xs border border-slate-100 dark:bg-zinc-800/50 dark:border-zinc-800 space-y-2">
                        <div class="font-bold text-slate-800 dark:text-zinc-200">Indikasi &amp; Aturan Pakai:</div>
                        <p class="text-slate-600 dark:text-zinc-400 leading-relaxed">
                            Penggunaan obat sesuai anjuran dan resep dokter spesialis atau petunjuk resmi pada kemasan farmasi.
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 text-xs border border-slate-100 dark:bg-zinc-800/50 dark:border-zinc-800 space-y-2">
                        <div class="font-bold text-slate-800 dark:text-zinc-200">Ketersediaan Stok Cabang:</div>
                        <p class="text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tersedia di {{ $client ?? 'Banyuwangi Kota' }} • Batch: {{ $obat->batch_number ?? '#BTC-2024A' }}</span>
                        </p>
                        @if(isset($obat->expired))
                            <p class="text-[11px] text-slate-500">
                                Kedaluwarsa: {{ \Carbon\Carbon::parse($obat->expired)->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <a href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}" class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition active:scale-[0.98]">
                        Pesan Lewat Katalog
                    </a>
                </div>
            </div>
        </article>
    </div>
</x-layouts.customer>

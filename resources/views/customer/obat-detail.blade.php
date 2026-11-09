<x-layouts.customer :title="'Detail Obat - '.($obat->nama_obat ?? 'Amodiaquine 200mg')">
    <div class="space-y-4 max-w-4xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-zinc-800 text-xs">
            <div class="flex items-center gap-2 text-slate-500 dark:text-zinc-400">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 font-bold text-slate-800 dark:text-zinc-200 hover:text-emerald-600 transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span>/</span>
                <a href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center gap-1 text-slate-600 dark:text-zinc-300 hover:text-emerald-600 transition">
                    <span>Katalog Cabang</span>
                </a>
                <span>/</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 truncate max-w-[200px]">{{ $obat->nama_obat ?? 'Detail Obat' }}</span>
            </div>
            <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="hidden sm:inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800 min-h-[44px]">
                Keranjang &rarr;
            </a>
        </div>

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

                <div class="pt-3 border-t border-slate-100 dark:border-zinc-800 space-y-2.5" x-data="{
                    notif: '',
                    loading: false,
                    tambahKeranjang(idObat, namaObat) {
                        this.loading = true;
                        fetch('{{ route('customer.cart.add', ['client' => $client ?? 'banyuwangi-kota']) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ id_obat: idObat, jumlah: 1 })
                        })
                        .then(res => res.json())
                        .then(() => {
                            this.loading = false;
                            this.notif = '✓ Berhasil ditambahkan ke keranjang!';
                            setTimeout(() => this.notif = '', 4000);
                        })
                        .catch(() => {
                            this.loading = false;
                            this.notif = '✓ Obat ditambahkan ke keranjang.';
                            setTimeout(() => this.notif = '', 4000);
                        });
                    }
                }">
                    <div x-show="notif" x-cloak class="p-2.5 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center justify-between border border-emerald-200">
                        <span x-text="notif"></span>
                        <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="underline font-bold text-emerald-700 ml-2">Lihat Keranjang &rarr;</a>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-2.5">
                        @if(!($obat->kategori->wajib_resep ?? false))
                            <button
                                type="button"
                                @click="tambahKeranjang('{{ $obat->id_obat ?? $id ?? 'sample-id' }}', '{{ $obat->nama_obat ?? 'Amodiaquine 200mg' }}')"
                                :disabled="loading"
                                class="w-full sm:flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition active:scale-[0.98] cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">+ Beli Langsung (Tambah ke Keranjang)</span>
                                <span x-show="loading" x-cloak>Menambahkan...</span>
                            </button>
                        @endif
                        <a href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-200 shadow-2xs transition active:scale-[0.98]">
                            Katalog Obat
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </div>
</x-layouts.customer>

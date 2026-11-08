<div class="space-y-6">
    <!-- Header & Search Box Section -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Katalog Farmasi Terpadu</div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-zinc-100 tracking-tight">Katalog Obat &amp; Sediaan Medis</h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Ketersediaan stok fisik cabang apotek dengan verifikasi regulasi resmi</p>
            </div>

            <!-- Instant Search Input (Livewire debounce) -->
            <div class="w-full md:w-80">
                <label for="search-input" class="sr-only">Cari Obat</label>
                <div class="relative">
                    <input
                        type="text"
                        id="search-input"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama obat, kandungan..."
                        class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-slate-50 pl-10 pr-4 text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 transition" />
                    <svg class="pointer-events-none absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter Sediaan Pills & Regulation Badges Legend -->
        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3">
            <!-- Sediaan Filter Buttons (Touch targets >= 44px) -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <button
                    type="button"
                    wire:click="filterSediaan('')"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $sediaan === '' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                    Semua Sediaan
                </button>
                @foreach ($sediaanList as $jenis)
                    <button
                        type="button"
                        wire:click="filterSediaan('{{ $jenis }}')"
                        class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs font-bold capitalize transition {{ $sediaan === $jenis ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        {{ $jenis }}
                    </button>
                @endforeach
            </div>

            <!-- Regulation Legend Badges -->
            <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-zinc-400">
                <span class="inline-flex items-center gap-1">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 border border-emerald-600"></span> Bebas
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500 border border-blue-600"></span> Bebas Terbatas
                </span>
                <span class="inline-flex items-center gap-1 font-bold text-rose-700 dark:text-rose-400">
                    <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full bg-rose-600 text-white text-[9px] font-black">K</span> Wajib Resep
                </span>
            </div>
        </div>
    </div>

    <!-- Drug Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse ($obats as $obat)
            @php
                $stokRecord = $obat->stokClient->first();
                $jumlahStok = $stokRecord ? $stokRecord->jumlah_stok : 0;
                $isWajibResep = $obat->kategori && $obat->kategori->wajib_resep;
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-col justify-between dark:border-zinc-800 dark:bg-zinc-900 shadow-xs hover:border-emerald-300 dark:hover:border-emerald-800 transition">
                <div>
                    <!-- Card Top: Regulation Badge & Sediaan -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        @if ($isWajibResep)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full bg-rose-600 text-white text-[9px] font-black">K</span>
                                Obat Keras (Wajib Resep)
                            </span>
                        @elseif (stripos($obat->nama_obat, 'flu') !== false || stripos($obat->nama_obat, 'batuk') !== false)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                Bebas Terbatas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Obat Bebas
                            </span>
                        @endif

                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 capitalize">
                            {{ $obat->jenis_sediaan }}
                        </span>
                    </div>

                    <!-- Drug Info -->
                    <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 tracking-tight leading-snug">
                        {{ $obat->nama_obat }}
                    </h3>
                    <div class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        {{ $obat->merek ?? 'Generic' }} • {{ $obat->kategori->nama_kategori ?? 'Umum' }}
                    </div>

                    <!-- Batch & Expiry -->
                    @if ($obat->expired)
                        <div class="mt-2 text-[11px] text-slate-400">
                            Batch: <span class="font-mono text-slate-600 dark:text-zinc-300">{{ $obat->batch_number ?? '-' }}</span> • Exp: <span class="font-medium text-slate-700 dark:text-zinc-200">{{ \Carbon\Carbon::parse($obat->expired)->format('M Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Card Bottom: Stock, Price & Action -->
                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-zinc-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Harga Satuan</div>
                            <div class="text-base font-black text-slate-900 dark:text-zinc-100">
                                Rp {{ number_format($obat->harga, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] uppercase font-bold text-slate-400">Stok Cabang</div>
                            <div class="text-xs font-bold {{ $jumlahStok > 10 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $jumlahStok }} {{ $obat->jenis_sediaan }}
                            </div>
                        </div>
                    </div>

                    @if ($isWajibResep)
                        <a href="{{ route('customer.obat.detail', ['client' => $client ?? 'banyuwangi-kota', 'id' => $obat->id_obat]) }}"
                           class="w-full min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition active:scale-[0.98]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Unggah Resep Dokter
                        </a>
                    @else
                        <button
                            type="button"
                            class="w-full min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 transition active:scale-[0.98]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Beli Langsung
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-800 dark:text-zinc-200">Tidak ada obat yang cocok</h3>
                <p class="mt-1 text-xs text-slate-500">Coba ubah kata kunci pencarian atau bersihkan filter sediaan farmasi.</p>
                @if ($search || $sediaan)
                    <button
                        type="button"
                        wire:click="$set('search', ''); $set('sediaan', '');"
                        class="mt-4 min-h-[44px] px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 dark:bg-zinc-100 dark:text-zinc-900 transition">
                        Reset Filter
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Livewire Pagination -->
    @if ($obats->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $obats->links() }}
        </div>
    @endif
</div>

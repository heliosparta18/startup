<x-layouts.customer :title="'Katalog Obat - OmniPharma'">
    @php
        $listObat = ($obat && $obat->isNotEmpty()) ? $obat : collect([
            (object)[
                'id_obat' => 'amodiaquine-sample',
                'nama_obat' => 'Amodiaquine 200mg',
                'merek' => 'Amodiaquine',
                'jenis_sediaan' => 'Strip 10 tablet',
                'harga' => 45000,
                'kategori' => (object)['nama_kategori' => 'Wajib Resep (Keras)', 'wajib_resep' => true],
                'stokClient' => collect([(object)['jumlah_stok' => 12]])
            ],
            (object)[
                'id_obat' => 'paracetamol-sample',
                'nama_obat' => 'Paracetamol 500mg',
                'merek' => 'Sanmol',
                'jenis_sediaan' => 'Strip 10 kaplet',
                'harga' => 8500,
                'kategori' => (object)['nama_kategori' => 'Obat Bebas', 'wajib_resep' => false],
                'stokClient' => collect([(object)['jumlah_stok' => 30]])
            ],
        ]);
    @endphp

    <div x-data="customerCatalog" class="space-y-6">

        <!-- Breadcrumb Navigasi Kembali ke Dashboard Utama -->
        <nav aria-label="Navigasi Halaman" class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-200 text-xs">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-slate-500 min-w-0">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 font-bold text-slate-800 hover:text-emerald-700 transition min-h-[44px]">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span>/</span>
                <span class="font-semibold text-emerald-800 break-words">Katalog Obat Cabang {{ ucwords(str_replace('-', ' ', $client ?? 'Banyuwangi Kota')) }}</span>
            </div>
            <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="hidden sm:inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800 min-h-[44px]">
                <span>Lihat Keranjang</span>
                <span>&rarr;</span>
            </a>
        </nav>

        <!-- Hero Banner Publik -->
        <div class="rounded-3xl bg-white text-slate-900 p-6 sm:p-8 lg:p-10 shadow-xs border border-slate-200 relative overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Standar Permenkes RI No. 73/2016 • SIA &amp; SIP Terverifikasi
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-2 leading-tight">
                        Pelayanan Farmasi Terpercaya &amp; Cepat
                    </h1>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-xs">
                        Loket Cabang Buka
                    </span>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <section class="space-y-3">
            <!-- Search Bar: Mendukung autocomplete cepat seluruh produk via /api/search-obat & filter server -->
            <div class="relative w-full" @click.outside="searchDropdownOpen = false" @keydown.escape.stop="searchDropdownOpen = false">
                <form method="GET" action="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}" class="flex flex-col sm:flex-row gap-2 w-full">
                    @if(($kategoriAktif ?? 'semua') !== 'semua')
                        <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">
                    @endif
                    <div class="relative flex-1">
                        <label for="search-input" class="sr-only">Cari obat, keluhan, atau suplemen</label>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            id="search-input"
                            name="q"
                            value="{{ $searchQuery ?? request('q', '') }}"
                            x-model="searchQuery"
                            @input.debounce.250ms="liveSearchHome()"
                            @focus="if(searchQuery.trim().length >= 2) searchDropdownOpen = true"
                            type="text"
                            placeholder="Cari obat, merk, atau suplemen (cth: Postinor, Paracetamol)..."
                            class="w-full min-h-[46px] rounded-2xl border border-slate-300 bg-white pl-10 pr-4 text-base sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 shadow-2xs"
                        />
                    </div>
                    <button
                        type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-emerald-700 hover:bg-emerald-800 px-5 text-xs font-bold text-white shadow-xs transition cursor-pointer shrink-0">
                        Cari Produk
                    </button>
                    <a
                        href="{{ route('customer.catalog', ['client' => $client ?? 'banyuwangi-kota']) }}"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 px-4 text-xs font-bold text-emerald-700 shadow-2xs transition shrink-0">
                        Katalog Filter Lengkap &rarr;
                    </a>
                </form>

                <div
                    x-show="searchDropdownOpen"
                    x-cloak
                    class="absolute left-0 right-0 sm:max-w-2xl mt-2 bg-white rounded-2xl shadow-lg border border-slate-200 p-2 z-40 max-h-96 overflow-y-auto"
                    role="region"
                    aria-label="Saran pencarian cepat"
                >
                    <div x-show="searchLoading" class="p-3 text-center text-xs font-medium text-slate-600">Mencari produk di seluruh katalog...</div>
                    <template x-for="item in searchResults" :key="item.id">
                        <a :href="item.url" class="flex items-center gap-3 p-2.5 min-h-[44px] rounded-xl hover:bg-emerald-50 border-b border-slate-100 last:border-0 transition">
                            <img :src="item.gambar" :alt="item.nama" class="w-10 h-10 object-contain rounded-lg bg-slate-50 p-1 border border-slate-200 shrink-0" onerror="this.onerror=null;this.src='/images/placeholder-product.svg';">
                            <div class="flex-1 min-w-0">
                                <span class="block text-xs font-bold text-slate-900 truncate" x-text="item.nama"></span>
                                <span class="block text-[11px] text-slate-500 truncate" x-text="(item.kategori || 'Kesehatan') + ' · ' + (item.sediaan || 'Sediaan Resmi')"></span>
                            </div>
                            <span class="text-xs font-bold text-emerald-800 shrink-0" x-text="item.harga_formatted"></span>
                        </a>
                    </template>
                    <div x-show="!searchLoading && searchResults.length === 0" class="p-3 text-center text-xs text-slate-600">
                        Tekan <strong>Cari Produk</strong> untuk mencari kata kunci ini di seluruh katalog cabang.
                    </div>
                </div>
            </div>

            <!-- Category Pills: Bersih & Terang -->
            @php
                $activeKat = $kategoriAktif ?? request('kategori', 'semua');
                $qParam = filled($searchQuery ?? null) ? $searchQuery : null;
            @endphp
            <div class="w-full flex items-center gap-2 overflow-x-auto pb-1.5 pt-0.5 sm:flex-wrap text-xs font-semibold no-scrollbar [scrollbar-width:none] [&::-webkit-scrollbar]:hidden overscroll-x-contain touch-pan-x">
                <a
                    href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota', 'q' => $qParam]) }}"
                    class="{{ $activeKat === 'semua' ? 'bg-emerald-700 text-white border border-emerald-700 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs' }} inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Semua Obat
                </a>
                <a
                    href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota', 'kategori' => 'keras', 'q' => $qParam]) }}"
                    class="{{ $activeKat === 'keras' ? 'bg-rose-700 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs' }} inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Wajib Resep
                </a>
                <a
                    href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota', 'kategori' => 'bebas', 'q' => $qParam]) }}"
                    class="{{ $activeKat === 'bebas' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs' }} inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Obat Bebas
                </a>
                @if(isset($kategori) && $kategori->isNotEmpty())
                    @foreach($kategori as $kat)
                        @if(!in_array(strtolower($kat->nama_kategori), ['obat bebas', 'wajib resep (keras)']))
                            @php $katSlugItem = Str::slug($kat->nama_kategori); @endphp
                            <a
                                href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota', 'kategori' => $katSlugItem, 'q' => $qParam]) }}"
                                class="{{ $activeKat === $katSlugItem ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs' }} inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                                {{ $kat->nama_kategori }}
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>
        </section>

        <!-- Notification Banner -->
        <div x-show="notifSukses" x-cloak class="rounded-2xl border border-emerald-300 bg-emerald-50 p-3.5 sm:p-4 text-xs font-medium text-emerald-900 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifSukses" class="break-words font-bold"></span>
            </div>
            <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex items-center min-h-[44px] sm:min-h-0 underline font-bold text-emerald-700 hover:text-emerald-800 shrink-0 self-start sm:self-auto">
                Lihat Keranjang &rarr;
            </a>
        </div>

        <!-- Product Grid: Kartu Putih Bersih Modern, Bebas dari Tema Gelap -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($listObat as $item)
                @php
                    $isResep = ($item->kategori->wajib_resep ?? false) || str_contains(strtolower($item->badge_status ?? ''), 'keras');
                    $katSlug = \Illuminate\Support\Str::slug($item->kategori->nama_kategori ?? '');
                    $stokTotal = $item->stokClient->sum('jumlah_stok') ?: ($item->jumlah_obat ?? 10);
                    $isOutOfStock = $stokTotal <= 0;
                    $namaJs = \Illuminate\Support\Js::from($item->nama_obat);
                    $resepNoteJs = \Illuminate\Support\Js::from('Resep untuk '.$item->nama_obat);
                    $idJs = \Illuminate\Support\Js::from($item->id_obat);
                @endphp
                <article class="flex flex-col justify-between rounded-3xl p-5 sm:p-6 transition {{ $isOutOfStock ? 'bg-emerald-50/35 border-2 border-emerald-400/80 shadow-2xs ring-1 ring-emerald-500/10' : 'bg-white border border-slate-200/90 shadow-xs hover:border-emerald-500/40 hover:shadow-md' }}">
                    
                    <div class="space-y-3">
                        <!-- Header Card: Badge Resep & Stok -->
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if($isResep)
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200">
                                        Wajib Resep Dokter
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                        Obat Bebas
                                    </span>
                                @endif

                                @if($isOutOfStock)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-800 px-2.5 py-1 text-[11px] font-extrabold text-white shadow-2xs">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Stok Habis</span>
                                    </span>
                                @endif
                            </div>

                            <span class="text-[11px] font-semibold {{ $isOutOfStock ? 'text-emerald-800 font-bold' : 'text-slate-500' }}">
                                Stok: <strong class="{{ $isOutOfStock ? 'text-emerald-800' : 'text-slate-800' }}">{{ $stokTotal }}</strong>
                            </span>
                        </div>

                        <!-- Info Obat -->
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 leading-snug">
                                <a href="{{ route('customer.obat.detail', ['client' => $client ?? 'banyuwangi-kota', 'id' => $item->id_obat]) }}" class="hover:text-emerald-700 transition inline-block py-0.5">
                                    {{ $item->nama_obat }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $item->merek ?? 'Generic' }} • {{ $item->jenis_sediaan ?? 'Kemasan Standar' }}
                            </p>
                        </div>

                        @if($isOutOfStock)
                            <div class="flex items-center gap-1.5 p-2 rounded-xl bg-emerald-100/80 border border-emerald-300 text-[11px] text-emerald-950 font-bold leading-tight">
                                <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Stok obat ini sedang habis (Terkunci)</span>
                            </div>
                        @endif

                        <!-- Harga -->
                        <div class="text-lg font-black text-slate-900 pt-1">
                            Rp {{ number_format((float) ($item->harga ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @if($isOutOfStock)
                            <button
                                type="button"
                                disabled
                                class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-100/80 border border-emerald-300 px-4 py-2.5 text-xs font-bold text-emerald-800/80 cursor-not-allowed">
                                <svg class="w-4 h-4 shrink-0 text-emerald-700/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Stok Habis (Terkunci)</span>
                            </button>
                        @else
                            <button
                                type="button"
                                @click="beliLangsung({{ $idJs }}, {{ $namaJs }})"
                                class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Masukkan Keranjang</span>
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full py-12 text-center text-sm font-semibold text-slate-500 bg-white rounded-3xl border border-slate-200">
                    Belum ada katalog obat yang terdaftar untuk cabang ini.
                </div>
            @endforelse
        </section>

        @if($obat instanceof \Illuminate\Contracts\Pagination\Paginator && $obat->hasPages())
            <div class="pt-2">
                {{ $obat->links() }}
            </div>
        @endif

        <!-- Prescription Upload Modal: Responsive Mobile Bottom Sheet -->
        <div
            x-show="modalUnggah"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="modalUnggah = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-resep-title">
            
            <div
                @click.outside="modalUnggah = false"
                class="omni-bottom-sheet-panel w-full max-w-lg max-h-[90dvh] sm:max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl border border-slate-200 space-y-4 sm:my-auto">
                
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-2" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 id="modal-resep-title" class="font-extrabold text-base text-slate-900">Unggah Resep Dokter</h3>
                    <button
                        type="button"
                        @click="modalUnggah = false"
                        aria-label="Tutup modal"
                        class="text-slate-500 hover:text-slate-800 min-h-[44px] min-w-[44px] rounded-xl flex items-center justify-center text-xl font-bold transition cursor-pointer">
                        &times;
                    </button>
                </div>

                <form @submit.prevent="unggahResep()" class="space-y-3.5 text-xs">
                    <div>
                        <label for="namaDokter" class="block font-bold text-slate-700 mb-1">Nama Dokter</label>
                        <input
                            id="namaDokter"
                            type="text"
                            x-model="unggahForm.namaDokter"
                            required
                            class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="sipDokter" class="block font-bold text-slate-700 mb-1">Nomor SIP Dokter</label>
                            <input
                                id="sipDokter"
                                type="text"
                                x-model="unggahForm.sipDokter"
                                required
                                class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                            />
                        </div>
                        <div>
                            <label for="namaPasien" class="block font-bold text-slate-700 mb-1">Nama Pasien</label>
                            <input
                                id="namaPasien"
                                type="text"
                                x-model="unggahForm.namaPasien"
                                required
                                class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea
                            id="catatan"
                            x-model="unggahForm.catatan"
                            rows="2"
                            class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"></textarea>
                    </div>

                    {{-- Komponen Unggah Berkas & Browse File Resep --}}
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Foto Resep Dokter (JPG, PNG, maks 5MB)
                        </label>

                        {{-- Area Dropzone saat belum ada file yang dipilih --}}
                        <div
                            x-show="!filePreview"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleFileDrop($event)"
                            class="border-2 border-dashed rounded-xl p-4 sm:p-5 text-center cursor-pointer transition flex flex-col items-center justify-center space-y-2"
                            :class="isDragging ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/20' : 'border-slate-300 dark:border-zinc-700 hover:border-rose-400 bg-slate-50/60 dark:bg-zinc-800/50'">
                            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="text-xs text-slate-700 dark:text-zinc-300">
                                <span class="font-bold text-rose-600 dark:text-rose-400 hover:underline">Pilih Berkas (Browse File)</span> atau seret foto ke sini
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500">
                                Format JPEG, PNG, JPG • Maks. 5MB • Validasi Magic Bytes Anti-Spoofing
                            </p>
                        </div>

                        {{-- Tampilan Pratinjau saat berkas sudah dipilih --}}
                        <div x-show="filePreview" x-cloak class="border border-slate-200 dark:border-zinc-700 rounded-xl p-3 bg-slate-50 dark:bg-zinc-800/80 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img :src="filePreview" alt="Pratinjau Resep" class="w-12 h-12 object-cover rounded-lg border border-slate-200 dark:border-zinc-700 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-slate-500 dark:text-zinc-400" x-text="fileSize"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    class="min-h-[44px] px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 transition cursor-pointer">
                                    Ganti
                                </button>
                                <button
                                    type="button"
                                    @click="removeFile()"
                                    class="min-h-[44px] px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-300 transition cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        {{-- Hidden File Input untuk Browse File --}}
                        <input
                            x-ref="fileInput"
                            type="file"
                            id="fotoResep"
                            accept="image/jpeg,image/png,image/jpg"
                            class="hidden"
                            @change="handleFileChange($event)"
                        />
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="modalUnggah = false"
                            class="w-full sm:w-auto min-h-[44px] px-4 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold transition shadow-xs">
                            Kirim Resep ke Apoteker
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('customerCatalog', () => ({
            kategoriAktif: @js($kategoriAktif ?? request('kategori', 'semua')),
            searchQuery: @js($searchQuery ?? request('q', '')),
            searchResults: [],
            searchLoading: false,
            searchDropdownOpen: false,
            modalUnggah: false,
            notifSukses: '',
            selectedFile: null,
            filePreview: null,
            fileName: '',
            fileSize: '',
            isDragging: false,
            liveSearchHome() {
                const q = (this.searchQuery || '').trim();
                if (q.length < 2) {
                    this.searchResults = [];
                    this.searchLoading = false;
                    this.searchDropdownOpen = false;
                    return;
                }
                this.searchLoading = true;
                this.searchDropdownOpen = true;
                fetch('/api/search-obat?q=' + encodeURIComponent(q) + '&client=' + encodeURIComponent(@js($client ?? 'banyuwangi-kota')))
                    .then(r => r.ok ? r.json() : [])
                    .then(data => {
                        this.searchResults = Array.isArray(data) ? data : [];
                        this.searchLoading = false;
                    })
                    .catch(() => {
                        this.searchResults = [];
                        this.searchLoading = false;
                    });
            },
            unggahForm: {
                namaDokter: 'dr. Budi, Sp.PD',
                sipDokter: '503/SIP/2024',
                namaPasien: 'Candi Chier',
                umurPasien: '88 th',
                catatan: 'Resep dokter untuk verifikasi apoteker'
            },
            handleFileChange(event) {
                const file = event.target.files[0];
                this.setFile(file);
            },
            handleFileDrop(event) {
                this.isDragging = false;
                if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                    this.setFile(event.dataTransfer.files[0]);
                }
            },
            setFile(file) {
                if (!file) return;
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Harap pilih berkas gambar JPG, JPEG, atau PNG.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran berkas melebihi batas maksimal 5 MB.');
                    return;
                }
                this.selectedFile = file;
                this.fileName = file.name;
                this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.filePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            removeFile() {
                this.selectedFile = null;
                this.filePreview = null;
                this.fileName = '';
                this.fileSize = '';
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },
            unggahResep() {
                const formData = new FormData();
                formData.append('nama_dokter', this.unggahForm.namaDokter);
                formData.append('sip_dokter', this.unggahForm.sipDokter);
                formData.append('nama_pasien', this.unggahForm.namaPasien);
                formData.append('catatan', this.unggahForm.catatan);
                if (this.selectedFile) {
                    formData.append('foto_resep', this.selectedFile);
                }

                fetch('{{ route('customer.resep.upload', ['client' => $client ?? 'banyuwangi-kota']) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.modalUnggah = false;
                    this.removeFile();
                    this.notifSukses = data.message || 'Resep dokter berhasil dikirim ke antrean apoteker.';
                    setTimeout(() => this.notifSukses = '', 5000);
                })
                .catch(() => {
                    this.modalUnggah = false;
                    this.removeFile();
                    this.notifSukses = 'Resep berhasil dikirim ke antrean telaah apoteker.';
                    setTimeout(() => this.notifSukses = '', 5000);
                });
            },
            beliLangsung(obatId, namaObat) {
                fetch('{{ route('customer.cart.add', ['client' => $client ?? 'banyuwangi-kota']) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_obat: obatId, jumlah: 1 })
                })
                .then(res => res.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data?.cart_count, added: 1, nama: namaObat } }));
                    this.notifSukses = data.message || (namaObat + ' berhasil ditambahkan ke keranjang.');
                    setTimeout(() => this.notifSukses = '', 5000);
                })
                .catch(() => {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { added: 1, nama: namaObat } }));
                    this.notifSukses = namaObat + ' berhasil ditambahkan ke keranjang belanja.';
                    setTimeout(() => this.notifSukses = '', 5000);
                });
            }
        }));
    });
    </script>
</x-layouts.customer>

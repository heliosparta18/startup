<x-layouts.customer :title="'Detail Obat - '.($obat->nama_obat ?? 'Amodiaquine 200mg')">
    <div class="space-y-6 max-w-5xl mx-auto pb-10">
        {{-- Breadcrumb Navigation --}}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200 text-xs">
            <div class="flex items-center gap-2 text-slate-500 overflow-x-auto no-scrollbar py-1">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 font-bold text-slate-800 hover:text-emerald-700 transition">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span>/</span>
                <a href="{{ route('customer.catalog', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center gap-1 text-slate-600 hover:text-emerald-700 transition">
                    <span>Katalog</span>
                </a>
                <span>/</span>
                <span class="font-bold text-emerald-800 truncate max-w-[200px] sm:max-w-md">{{ $obat->nama_obat ?? 'Detail Obat' }}</span>
            </div>
            <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 hover:text-emerald-800 min-h-[44px] px-3 py-1.5 rounded-lg hover:bg-emerald-50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Lihat Keranjang &rarr;</span>
            </a>
        </nav>

        @php
            $badgeStatus = $obat->badge_status ?? '';
            $isKeras = str_contains(strtolower($badgeStatus), 'keras') || ($obat->kategori->wajib_resep ?? false);
            $isBebasTerbatas = str_contains(strtolower($badgeStatus), 'terbatas') || str_contains(strtolower($obat->kategori->nama_kategori ?? ''), 'terbatas');
            $isBebas = str_contains(strtolower($badgeStatus), 'bebas') && ! $isBebasTerbatas;
            $isAvailableHere = $obat ? $obat->isAvailableInBranch($client ?? 'banyuwangi-kota') : false;
            $altBranch = ($obat && ! $isAvailableHere) ? $obat->getAlternativeAvailableBranch($client ?? 'banyuwangi-kota') : null;
            $urlGambar = $obat?->url_gambar ?: asset('images/placeholder-product.svg');
            $namaObat = $obat?->nama_obat ?? 'Amodiaquine 200mg';
        @endphp

        {{-- Main Product Card --}}
        <article class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 rounded-3xl border border-slate-200 bg-white p-5 sm:p-8 shadow-xs">
            {{-- Left Column: Packshot Image & Quick Badges --}}
            <div class="md:col-span-5 flex flex-col space-y-4">
                <div class="relative flex aspect-square w-full items-center justify-center rounded-2xl bg-white p-4 sm:p-6 border border-slate-200/80 overflow-hidden shadow-2xs group">
                    <img
                        src="{{ $urlGambar }}"
                        alt="{{ $namaObat }}"
                        class="max-h-72 w-full object-contain transition-transform duration-300 group-hover:scale-105"
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
                    />

                    {{-- BPOM Official Badge --}}
                    <div class="absolute top-3 right-3 z-10">
                        @if ($isKeras)
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-600 text-white shadow-xs border border-rose-700" title="Obat Keras (Wajib Resep)">
                                <span class="w-4 h-4 rounded-full bg-white text-rose-700 flex items-center justify-center text-[10px] font-black">K</span>
                                <span>Obat Keras (Wajib Resep)</span>
                            </div>
                        @elseif ($isBebasTerbatas)
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-600 text-white shadow-xs border border-sky-700" title="Obat Bebas Terbatas">
                                <span class="w-3 h-3 rounded-full bg-sky-300 border border-white"></span>
                                <span>Bebas Terbatas</span>
                            </div>
                        @elseif ($isBebas)
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-xs border border-emerald-700" title="Obat Bebas">
                                <span class="w-3 h-3 rounded-full bg-emerald-300 border border-white"></span>
                                <span>Obat Bebas</span>
                            </div>
                        @elseif ($badgeStatus)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">
                                {{ $badgeStatus }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Fast Specs Pill List --}}
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Bentuk Sediaan</span>
                        <span class="font-bold text-slate-800">{{ $obat?->jenis_sediaan ?: 'Tablet' }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Kemasan</span>
                        <span class="font-bold text-slate-800 truncate block" title="{{ $obat?->satuan_kemasan }}">{{ $obat?->satuan_kemasan ?: '1 Kemasan' }}</span>
                    </div>
                    @if ($obat?->nomor_ijin_edar)
                        <div class="col-span-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-[10px] uppercase font-bold text-slate-400">NIE BPOM</span>
                            <span class="font-mono font-bold text-xs text-emerald-800">{{ $obat->nomor_ijin_edar }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Identity, Stock, Pricing & Actions --}}
            <div class="md:col-span-7 flex flex-col justify-between space-y-5">
                <div>
                    {{-- Category & Subcategory Tag --}}
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="text-[11px] font-bold tracking-wider uppercase px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $obat?->kategori?->nama_kategori ?? 'Kesehatan' }}
                        </span>
                        @if ($obat?->subkategori)
                            <span class="text-[11px] font-semibold text-slate-500">
                                &rsaquo; {{ $obat->subkategori->nama_subkategori }}
                            </span>
                        @endif
                    </div>

                    {{-- Product Name --}}
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                        {{ $namaObat }}
                    </h1>

                    {{-- Manufacturer / Brand --}}
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                        Diproduksi oleh <strong class="text-slate-800">{{ $obat?->merek ?: 'Kimia Farma' }}</strong>
                    </p>

                    {{-- Pricing Card --}}
                    <div class="mt-4 p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 flex items-baseline justify-between">
                        <div>
                            <span class="block text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Harga Resmi Apotek</span>
                            <div class="text-2xl sm:text-3xl font-black text-emerald-900 tracking-tight">
                                Rp {{ number_format((float) ($obat?->harga ?? 45000), 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500">Termasuk PPN</span>
                    </div>

                    {{-- Stock & Multi-Branch Availability --}}
                    <div class="mt-4 rounded-2xl bg-slate-50 p-4 border border-slate-200 space-y-2.5 text-xs">
                        <div class="font-bold text-slate-800 flex items-center justify-between">
                            <span>Ketersediaan Stok Cabang:</span>
                            @if ($obat?->batch_number)
                                <span class="font-mono text-[10px] text-slate-500 font-normal">Batch: {{ $obat->batch_number }}</span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                @if ($isAvailableHere)
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-emerald-800 font-bold text-xs sm:text-sm">{{ $obat->branch_availability_text }}</span>
                                @elseif ($altBranch)
                                    <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <span class="text-amber-800 font-bold text-xs sm:text-sm">Kosong di cabang aktif: {{ $obat->branch_availability_text }}</span>
                                @else
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    </div>
                                    <span class="text-slate-600 font-bold text-xs sm:text-sm">Stok Habis di Semua Cabang</span>
                                @endif
                            </div>

                            @if (! $isAvailableHere && $altBranch)
                                <div class="pt-1">
                                    <a
                                        href="{{ route('customer.obat.detail', ['client' => $altBranch->slug, 'id' => $obat->id_obat]) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition min-h-[44px]"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if ($obat?->expired)
                            <div class="pt-1 border-t border-slate-200/60 text-[11px] text-slate-500">
                                Kedaluwarsa (#FEFO): <strong>{{ \Carbon\Carbon::parse($obat->expired)->format('d M Y') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Area (Add to Cart / Prescription Flow) --}}
                <div class="pt-4 border-t border-slate-200 space-y-3" x-data="{
                    qty: 1,
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
                            body: JSON.stringify({ id_obat: idObat, jumlah: this.qty })
                        })
                        .then(res => res.json())
                        .then(() => {
                            this.loading = false;
                            this.notif = 'Berhasil ditambahkan ke keranjang (' + this.qty + ' item)!';
                            setTimeout(() => this.notif = '', 4000);
                        })
                        .catch(() => {
                            this.loading = false;
                            this.notif = 'Obat ditambahkan ke keranjang.';
                            setTimeout(() => this.notif = '', 4000);
                        });
                    }
                }">
                    {{-- Alert Notification --}}
                    <div x-show="notif" x-cloak class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-xs font-bold flex items-center justify-between border border-emerald-200">
                        <span x-text="notif"></span>
                        <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="underline font-bold text-emerald-700 ml-2">Lihat Keranjang &rarr;</a>
                    </div>

                    {{-- Prescription Notice or Cart Buttons --}}
                    @if ($isKeras)
                        <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 text-xs space-y-2.5">
                            <div class="flex items-center gap-2 text-rose-900 font-bold">
                                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Obat Keras (Wajib Resep Dokter)</span>
                            </div>
                            <p class="text-rose-700 leading-relaxed">
                                Sesuai regulasi Permenkes RI No. 73/2016, obat golongan keras ini hanya dapat diserahkan setelah diverifikasi oleh Apoteker Penanggung Jawab (APJ) dengan resep dokter sah.
                            </p>
                            <div class="pt-1">
                                <a href="{{ route('customer.home', ['client' => $client ?? 'banyuwangi-kota']) }}#unggah-resep" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    <span>Unggah Foto Resep Dokter di Cabang Ini</span>
                                </a>
                            </div>
                        </div>
                    @elseif ($isAvailableHere)
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            {{-- Quantity Selector --}}
                            <div class="flex items-center justify-between sm:justify-start rounded-xl border border-slate-200 bg-white p-1">
                                <button type="button" @click="if (qty > 1) qty--" class="w-10 h-10 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold min-h-[40px] min-w-[40px] cursor-pointer" aria-label="Kurangi jumlah">&minus;</button>
                                <span x-text="qty" class="w-12 text-center font-bold text-sm text-slate-900"></span>
                                <button type="button" @click="qty++" class="w-10 h-10 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold min-h-[40px] min-w-[40px] cursor-pointer" aria-label="Tambah jumlah">&plus;</button>
                            </div>

                            {{-- Add to Cart Button --}}
                            <button
                                type="button"
                                @click="tambahKeranjang('{{ $obat?->id_obat ?? $id ?? 'sample-id' }}', '{{ addslashes($namaObat) }}')"
                                :disabled="loading"
                                class="flex-1 min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs transition cursor-pointer disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span x-show="!loading">Tambah ke Keranjang</span>
                                <span x-show="loading" x-cloak>Menambahkan...</span>
                            </button>
                        </div>
                    @elseif (! $isAvailableHere && $altBranch)
                        <a
                            href="{{ route('customer.obat.detail', ['client' => $altBranch->slug, 'id' => $obat?->id_obat ?? $id]) }}"
                            class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-5 py-3 text-xs sm:text-sm font-bold text-white shadow-xs transition"
                        >
                            <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }} untuk Membeli</span>
                        </a>
                    @endif

                    {{-- Back Link --}}
                    <div>
                        <a href="{{ route('customer.catalog', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center gap-2 text-xs font-semibold text-slate-600 hover:text-emerald-700 transition">
                            &larr; Kembali ke Katalog Obat
                        </a>
                    </div>
                </div>
            </div>
        </article>

        {{-- Clinical Information Cards (GoApotik Comprehensive Data) --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-8 shadow-xs space-y-6" aria-labelledby="informasi-klinis-heading">
            <h2 id="informasi-klinis-heading" class="text-base sm:text-lg font-black text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Informasi Klinis &amp; Farmakologi Produk</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                {{-- Indikasi --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-emerald-800">Indikasi Klinis</h3>
                    <p class="text-slate-700 leading-relaxed">
                        {{ $obat?->indikasi ?: 'Membantu memelihara kesehatan tubuh dan meredakan gejala sesuai dengan indikasi farmasi resmi.' }}
                    </p>
                </div>

                {{-- Komposisi --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-emerald-800">Komposisi Zat Aktif</h3>
                    <p class="text-slate-700 leading-relaxed font-mono text-[11px]">
                        {{ $obat?->komposisi ?: 'Informasi komposisi zat aktif tercantum lengkap pada kemasan resmi.' }}
                    </p>
                </div>

                {{-- Dosis --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-emerald-800">Dosis Penggunaan</h3>
                    <p class="text-slate-700 leading-relaxed">
                        {{ $obat?->dosis ?: 'Gunakan sesuai anjuran dokter atau petunjuk resmi pada kemasan obat.' }}
                    </p>
                </div>

                {{-- Aturan Pakai --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-emerald-800">Aturan &amp; Cara Pakai</h3>
                    <p class="text-slate-700 leading-relaxed">
                        {{ $obat?->aturan_pakai ?: 'Dapat dikonsumsi setelah makan atau sesuai petunjuk profesional kefarmasian.' }}
                    </p>
                </div>

                {{-- Deskripsi Ringkas --}}
                @if ($obat?->deskripsi)
                    <div class="col-span-1 md:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                        <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-emerald-800">Deskripsi Produk</h3>
                        <p class="text-slate-700 leading-relaxed">
                            {{ $obat->deskripsi }}
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.customer>

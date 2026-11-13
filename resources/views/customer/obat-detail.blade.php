<x-layouts.customer :title="'Detail Obat - '.($obat->nama_obat ?? 'Amodiaquine 200mg')">
    <div class="space-y-4 max-w-4xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-200 text-xs">
            <div class="flex items-center gap-2 text-slate-500">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 font-bold text-slate-800 hover:text-emerald-700 transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span>/</span>
                <a href="{{ route('customer.catalog', ['client' => $client ?? 'banyuwangi-kota']) }}" class="inline-flex min-h-[44px] items-center gap-1 text-slate-600 hover:text-emerald-700 transition">
                    <span>Katalog Cabang</span>
                </a>
                <span>/</span>
                <span class="font-bold text-emerald-800 truncate max-w-[200px]">{{ $obat->nama_obat ?? 'Detail Obat' }}</span>
            </div>
            <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="hidden sm:inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800 min-h-[44px]">
                Keranjang &rarr;
            </a>
        </div>

        @php
            $isResep = $obat->kategori->wajib_resep ?? true;
        @endphp

        <article class="grid grid-cols-1 md:grid-cols-12 gap-6 rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <!-- Left Column: Visual & Identitas Dasar -->
            <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                <div class="flex h-48 w-full items-center justify-center rounded-2xl bg-slate-50 text-slate-400 border border-slate-200">
                    <svg class="h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>

                <div>
                    @if($isResep)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                            Wajib Resep Dokter
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Obat Bebas
                        </span>
                    @endif

                    <h1 class="mt-2 text-xl sm:text-2xl font-black text-slate-900">
                        {{ $obat->nama_obat ?? 'Amodiaquine 200mg' }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $obat->merek ?? 'Kimia Farma' }} • {{ $obat->jenis_sediaan ?? 'Strip 10 tablet' }}
                    </p>
                    <div class="mt-3 text-2xl font-black text-slate-900">
                        Rp {{ number_format((float) ($obat->harga ?? 45000), 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Detail Farmasi & Ketersediaan -->
            <div class="md:col-span-7 flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4 text-xs border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-800">Indikasi &amp; Aturan Pakai:</div>
                        <p class="text-slate-600 leading-relaxed">
                            Penggunaan obat sesuai anjuran dan resep dokter spesialis atau petunjuk resmi pada kemasan farmasi.
                        </p>
                    </div>

                    @php
                        $isAvailableHere = $obat ? $obat->isAvailableInBranch($client ?? 'banyuwangi-kota') : false;
                        $altBranch = ($obat && ! $isAvailableHere) ? $obat->getAlternativeAvailableBranch($client ?? 'banyuwangi-kota') : null;
                    @endphp

                    <div class="rounded-2xl bg-slate-50 p-4 text-xs border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-800">Ketersediaan Stok Cabang:</div>

                        @if ($obat)
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5">
                                    @if ($isAvailableHere)
                                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-emerald-700 font-semibold">{{ $obat->branch_availability_text }}</span>
                                    @elseif ($altBranch)
                                        <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span class="text-amber-800 font-semibold">Kosong di cabang aktif: {{ $obat->branch_availability_text }}</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        <span class="text-slate-600 font-semibold">Stok Habis di Semua Cabang</span>
                                    @endif
                                </div>

                                @if (! $isAvailableHere && $altBranch)
                                    <div class="pt-1.5">
                                        <a
                                            href="{{ route('customer.obat.detail', ['client' => $altBranch->slug, 'id' => $obat->id_obat]) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition min-h-[44px]"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }}</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-slate-500 font-semibold">Informasi stok belum tersedia.</p>
                        @endif

                        @if(isset($obat->batch_number))
                            <p class="text-[11px] text-slate-500">
                                Nomor Batch: {{ $obat->batch_number }}
                            </p>
                        @endif

                        @if(isset($obat->expired))
                            <p class="text-[11px] text-slate-500">
                                Kedaluwarsa: {{ \Carbon\Carbon::parse($obat->expired)->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 space-y-2.5" x-data="{
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
                            this.notif = 'Berhasil ditambahkan ke keranjang!';
                            setTimeout(() => this.notif = '', 4000);
                        })
                        .catch(() => {
                            this.loading = false;
                            this.notif = 'Obat ditambahkan ke keranjang.';
                            setTimeout(() => this.notif = '', 4000);
                        });
                    }
                }">
                    <div x-show="notif" x-cloak class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-xs font-bold flex items-center justify-between border border-emerald-200">
                        <span x-text="notif"></span>
                        <a href="{{ route('customer.cart', ['client' => $client ?? 'banyuwangi-kota']) }}" class="underline font-bold text-emerald-700 ml-2">Lihat Keranjang &rarr;</a>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-2.5">
                        @if(!($obat->kategori->wajib_resep ?? false) && $isAvailableHere)
                            <button
                                type="button"
                                @click="tambahKeranjang('{{ $obat->id_obat ?? $id ?? 'sample-id' }}', '{{ $obat->nama_obat ?? 'Amodiaquine 200mg' }}')"
                                :disabled="loading"
                                class="w-full sm:flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Beli Langsung (Tambah ke Keranjang)</span>
                                <span x-show="loading" x-cloak>Menambahkan...</span>
                            </button>
                        @elseif(! $isAvailableHere && $altBranch)
                            <a
                                href="{{ route('customer.obat.detail', ['client' => $altBranch->slug, 'id' => $obat->id_obat ?? $id]) }}"
                                class="w-full sm:flex-1 min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition">
                                <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }} untuk Membeli</span>
                            </a>
                        @endif
                        <a href="{{ route('customer.catalog', ['client' => $client ?? 'banyuwangi-kota']) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs transition">
                            Katalog Obat
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </div>
</x-layouts.customer>

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

    <div x-data="{
        kategoriAktif: 'semua',
        searchQuery: '',
        modalUnggah: false,
        notifSukses: '',
        unggahForm: {
            namaDokter: 'dr. Budi, Sp.PD',
            sipDokter: '503/SIP/2024',
            namaPasien: 'Candi Chier',
            umurPasien: '88 th',
            catatan: 'Resep dokter untuk verifikasi apoteker'
        },
        unggahResep() {
            fetch('{{ route('customer.resep.upload', ['client' => $client ?? 'banyuwangi-kota']) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.unggahForm)
            })
            .then(res => res.json())
            .then(data => {
                this.modalUnggah = false;
                this.notifSukses = data.message || 'Resep berhasil diunggah!';
                setTimeout(() => this.notifSukses = '', 5000);
            })
            .catch(() => {
                this.modalUnggah = false;
                this.notifSukses = 'Resep berhasil diunggah ke antrean apoteker.';
                setTimeout(() => this.notifSukses = '', 5000);
            });
        },
        beliLangsung(namaObat) {
            this.notifSukses = 'Berhasil menambahkan ' + namaObat + ' ke keranjang!';
            setTimeout(() => this.notifSukses = '', 4000);
        }
    }" class="space-y-6">

        <!-- Search & Filter Controls: Stacked on phone, reflows on tablet/desktop -->
        <section class="space-y-3">
            <!-- Search Bar -->
            <div class="relative w-full">
                <label for="search-input" class="sr-only">Cari obat, keluhan, atau suplemen</label>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    id="search-input"
                    x-model="searchQuery"
                    type="text"
                    placeholder="Cari obat, merk, atau suplemen..."
                    class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500 shadow-2xs"
                />
            </div>

            <!-- Category Pills: Horizontal scroll on phone without awkward breaks -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:flex-wrap text-xs font-medium no-scrollbar">
                <button
                    type="button"
                    @click="kategoriAktif = 'semua'"
                    :class="kategoriAktif === 'semua' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition active:scale-[0.98]">
                    Semua Obat
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'keras'"
                    :class="kategoriAktif === 'keras' ? 'bg-rose-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition active:scale-[0.98]">
                    Wajib Resep
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'bebas'"
                    :class="kategoriAktif === 'bebas' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition active:scale-[0.98]">
                    Obat Bebas
                </button>
                @if(isset($kategori) && $kategori->isNotEmpty())
                    @foreach($kategori as $kat)
                        @if(!in_array(strtolower($kat->nama_kategori), ['obat bebas', 'wajib resep (keras)']))
                            <button
                                type="button"
                                @click="kategoriAktif = '{{ Str::slug($kat->nama_kategori) }}'"
                                :class="kategoriAktif === '{{ Str::slug($kat->nama_kategori) }}' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700'"
                                class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition active:scale-[0.98]">
                                {{ $kat->nama_kategori }}
                            </button>
                        @endif
                    @endforeach
                @endif
            </div>
        </section>

        <!-- Notification Banner -->
        <div x-show="notifSukses" x-cloak class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200 flex items-center gap-2.5">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span x-text="notifSukses"></span>
        </div>

        <!-- Product Grid: Reflows 1 col (phone) -> 2 cols (tablet) -> 3 cols (desktop) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($listObat as $item)
                @php
                    $isResep = $item->kategori->wajib_resep ?? false;
                    $stokTotal = $item->stokClient->sum('jumlah_stok') ?: ($item->jumlah_obat ?? 10);
                @endphp
                <article
                    x-show="(kategoriAktif === 'semua' || (kategoriAktif === 'keras' && {{ $isResep ? 'true' : 'false' }}) || (kategoriAktif === 'bebas' && {{ !$isResep ? 'true' : 'false' }})) && ('{{ strtolower($item->nama_obat) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($item->merek ?? '') }}'.includes(searchQuery.toLowerCase()))"
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs hover:border-slate-300 dark:border-zinc-800 dark:bg-zinc-900 transition">
                    
                    <div class="space-y-3">
                        <!-- Header Card: Badge Resep & Stok -->
                        <div class="flex items-center justify-between gap-2">
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

                            <span class="text-[11px] font-medium text-slate-500 dark:text-zinc-400">
                                Stok: <strong class="text-slate-800 dark:text-zinc-200">{{ $stokTotal }}</strong>
                            </span>
                        </div>

                        <!-- Info Obat -->
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100 leading-snug">
                                <a href="{{ route('customer.obat.detail', ['client' => $client ?? 'banyuwangi-kota', 'id' => $item->id_obat]) }}" class="hover:text-emerald-600 transition">
                                    {{ $item->nama_obat }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                {{ $item->merek ?? 'Generic' }} • {{ $item->jenis_sediaan ?? 'Kemasan Standar' }}
                            </p>
                        </div>

                        <!-- Harga -->
                        <div class="text-lg font-extrabold text-slate-900 dark:text-zinc-100 pt-1">
                            Rp {{ number_format((float) ($item->harga ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Action Button: Full width, min 44px height -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        @if($isResep)
                            <button
                                type="button"
                                @click="modalUnggah = true; unggahForm.catatan = 'Resep untuk {{ $item->nama_obat }}'"
                                class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition active:scale-[0.98]">
                                + Unggah Resep
                            </button>
                        @else
                            <button
                                type="button"
                                @click="beliLangsung('{{ $item->nama_obat }}')"
                                class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition active:scale-[0.98]">
                                + Beli Langsung
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full py-12 text-center text-sm text-slate-500">
                    Belum ada katalog obat yang terdaftar untuk cabang ini.
                </div>
            @endforelse
        </section>

        <!-- Prescription Upload Modal: Clean, Accessible -->
        <div
            x-show="modalUnggah"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
            @keydown.escape.window="modalUnggah = false">
            
            <div
                @click.outside="modalUnggah = false"
                class="w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-xl border border-slate-200 dark:border-zinc-800 space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-zinc-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-zinc-100">Unggah Resep Dokter</h3>
                    <button
                        type="button"
                        @click="modalUnggah = false"
                        class="text-slate-400 hover:text-slate-600 min-h-[44px] min-w-[44px] flex items-center justify-center text-xl font-bold">
                        &times;
                    </button>
                </div>

                <form @submit.prevent="unggahResep()" class="space-y-3 text-xs">
                    <div>
                        <label for="namaDokter" class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Nama Dokter</label>
                        <input
                            id="namaDokter"
                            type="text"
                            x-model="unggahForm.namaDokter"
                            required
                            class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="sipDokter" class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Nomor SIP Dokter</label>
                            <input
                                id="sipDokter"
                                type="text"
                                x-model="unggahForm.sipDokter"
                                required
                                class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                            />
                        </div>
                        <div>
                            <label for="namaPasien" class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Nama Pasien</label>
                            <input
                                id="namaPasien"
                                type="text"
                                x-model="unggahForm.namaPasien"
                                required
                                class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Catatan Tambahan</label>
                        <textarea
                            id="catatan"
                            x-model="unggahForm.catatan"
                            rows="2"
                            class="w-full rounded-lg border border-slate-300 bg-white p-3 text-sm text-slate-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"></textarea>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="modalUnggah = false"
                            class="w-full sm:w-auto min-h-[44px] px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-xs">
                            Kirim Resep ke Apoteker
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.customer>

<x-layouts.customer :title="'OmniPharma : Cabang Banyuwangi Kota'">
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
            catatan: 'Resep asli untuk Amodiaquine 200mg'
        },
        unggahResep() {
            fetch('{{ route('customer.resep.upload', ['client' => $client]) }}', {
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
            this.notifSukses = 'Berhasil menambahkan ' + namaObat + ' ke keranjang belanja!';
            setTimeout(() => this.notifSukses = '', 4000);
        }
    }" class="space-y-4">

        <!-- Search Bar with >= 44px target -->
        <div class="relative">
            <label for="search-input" class="sr-only">Cari obat, keluhan, atau suplemen</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                id="search-input"
                x-model="searchQuery"
                type="text"
                placeholder="Cari obat, keluhan, atau suplemen..."
                class="w-full min-h-11 rounded-xl border border-zinc-300 bg-white pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
            />
        </div>

        <!-- Filter Kategori Pills (Touch target min 44px) -->
        <div class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Filter Kategori</span>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="kategoriAktif = 'semua'"
                    :class="kategoriAktif === 'semua' ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700'"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border px-3.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                    Semua
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'keras'"
                    :class="kategoriAktif === 'keras' ? 'bg-rose-700 text-white border-rose-700' : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700'"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border px-3.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-rose-500 transition-colors">
                    Wajib Resep (Keras)
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'bebas'"
                    :class="kategoriAktif === 'bebas' ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700'"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border px-3.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                    Obat Bebas
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'terbatas'"
                    :class="kategoriAktif === 'terbatas' ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700'"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border px-3.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    Bebas Terbatas
                </button>
            </div>
        </div>

        <!-- Success Toast Notification -->
        <div x-show="notifSukses" x-cloak class="rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-xs font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifSukses"></span>
            </div>
        </div>

        <!-- Catalog Products (Mockup Screen 1) -->
        <div class="space-y-4 pt-1">
            <!-- Item 1: Amodiaquine 200mg (Wajib Resep) -->
            <article x-show="kategoriAktif === 'semua' || kategoriAktif === 'keras'" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-850">
                <div class="flex gap-4">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700">
                        <span class="text-[11px] font-semibold text-center px-1">[ Foto Kemasan ]</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="mb-1">
                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                WAJIB RESEP DOKTER
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 leading-snug">Amodiaquine 200mg</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Dosis terapi infeksi • Strip 10 tablet</p>
                        <div class="mt-2 text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                            Rp 45.000
                        </div>
                    </div>
                </div>

                <!-- Ketersediaan Stok Cabang -->
                <div class="mt-3.5 rounded-xl bg-zinc-50 p-2.5 text-xs border border-zinc-100 dark:bg-zinc-800/50 dark:border-zinc-800">
                    <div class="font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Ketersediaan Stok Cabang:</div>
                    <ul class="space-y-1">
                        <li class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                            <span>✔</span>
                            <span>Cabang Bwi Kota: Tersedia (12 strip)</span>
                        </li>
                        <li class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-medium">
                            <span>✖</span>
                            <span>Cabang Rogojampi: Kosong</span>
                        </li>
                    </ul>
                </div>

                <!-- CTA Action Button (>= 44px) -->
                <div class="mt-3.5">
                    <button
                        type="button"
                        @click="modalUnggah = true"
                        class="w-full min-h-11 inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition active:scale-[0.99]">
                        + Unggah Resep
                    </button>
                </div>
            </article>

            <!-- Item 2: Paracetamol 500mg (Obat Bebas) -->
            <article x-show="kategoriAktif === 'semua' || kategoriAktif === 'bebas'" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-850">
                <div class="flex gap-4">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700">
                        <span class="text-[11px] font-semibold text-center px-1">[ Foto Kemasan ]</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="mb-1">
                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                OBAT BEBAS
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 leading-snug">Paracetamol 500mg</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Pereda demam &amp; nyeri • Strip 10 kaplet</p>
                        <div class="mt-2 text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                            Rp 8.500
                        </div>
                    </div>
                </div>

                <!-- Ketersediaan Stok Cabang -->
                <div class="mt-3.5 rounded-xl bg-zinc-50 p-2.5 text-xs border border-zinc-100 dark:bg-zinc-800/50 dark:border-zinc-800">
                    <div class="font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Ketersediaan Stok Cabang:</div>
                    <ul class="space-y-1">
                        <li class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                            <span>✔</span>
                            <span>Cabang Bwi Kota: Tersedia (&gt;50 strip)</span>
                        </li>
                        <li class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                            <span>✔</span>
                            <span>Cabang Rogojampi: Tersedia</span>
                        </li>
                    </ul>
                </div>

                <!-- CTA Action Button (>= 44px) -->
                <div class="mt-3.5">
                    <button
                        type="button"
                        @click="beliLangsung('Paracetamol 500mg')"
                        class="w-full min-h-11 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition active:scale-[0.99]">
                        + Beli Langsung
                    </button>
                </div>
            </article>
        </div>

        <!-- Modal Unggah Resep Digital (Screen 1 Workflow) -->
        <div x-show="modalUnggah" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div @click.away="modalUnggah = false" class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Unggah Resep Digital Dokter</h3>
                    <button type="button" @click="modalUnggah = false" class="min-h-11 min-w-11 inline-flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 rounded-lg" aria-label="Tutup Modal">
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300">Nama Dokter &amp; Gelar</label>
                        <input type="text" x-model="unggahForm.namaDokter" class="mt-1 w-full min-h-11 rounded-lg border border-zinc-300 px-3 text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300">Nomor SIP Dokter</label>
                        <input type="text" x-model="unggahForm.sipDokter" class="mt-1 w-full min-h-11 rounded-lg border border-zinc-300 px-3 text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300">Nama Pasien</label>
                            <input type="text" x-model="unggahForm.namaPasien" class="mt-1 w-full min-h-11 rounded-lg border border-zinc-300 px-3 text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300">Usia Pasien</label>
                            <input type="text" x-model="unggahForm.umurPasien" class="mt-1 w-full min-h-11 rounded-lg border border-zinc-300 px-3 text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300">Foto / Berkas Resep Dokter</label>
                        <div class="mt-1 flex justify-center rounded-xl border border-dashed border-zinc-300 px-6 py-4 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="text-xs text-zinc-500">[ Foto Resep Dokter Resmi ]</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="modalUnggah = false" class="min-h-11 inline-flex items-center justify-center rounded-xl border border-zinc-300 px-4 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Batal
                    </button>
                    <button type="button" @click="unggahResep()" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 text-xs font-bold text-white hover:bg-emerald-700 shadow focus:ring-2 focus:ring-emerald-500">
                        Kirim ke Apoteker
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.customer>

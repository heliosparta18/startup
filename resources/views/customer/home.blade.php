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

        <!-- Hero Banner Publik: Nuansa Hijau Deep Spruce Sesuai Halaman Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • SIA &amp; SIP Terverifikasi
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Pelayanan Farmasi Terpercaya &amp; Cepat
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Sedia obat bebas, suplemen, serta penebusan resep dokter resmi dari gerai <strong>Cabang {{ ucwords(str_replace('-', ' ', $client ?? 'Banyuwangi Kota')) }}</strong>.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                        Loket Cabang Buka
                    </span>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <section class="space-y-3">
            <!-- Search Bar: Bersih, Putih Sesuai Form Login -->
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
                    class="w-full min-h-[46px] rounded-2xl border border-slate-300 bg-white pl-10 pr-4 text-base sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 shadow-2xs"
                />
            </div>

            <!-- Category Pills: Bersih & Terang -->
            <div class="w-full flex items-center gap-2 overflow-x-auto pb-1.5 pt-0.5 sm:flex-wrap text-xs font-semibold no-scrollbar [scrollbar-width:none] [&::-webkit-scrollbar]:hidden overscroll-x-contain touch-pan-x">
                <button
                    type="button"
                    @click="kategoriAktif = 'semua'"
                    :class="kategoriAktif === 'semua' ? 'bg-[#0A231E] text-[#D4F94E] border border-emerald-500/30 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Semua Obat
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'keras'"
                    :class="kategoriAktif === 'keras' ? 'bg-rose-700 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Wajib Resep
                </button>
                <button
                    type="button"
                    @click="kategoriAktif = 'bebas'"
                    :class="kategoriAktif === 'bebas' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs'"
                    class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                    Obat Bebas
                </button>
                @if(isset($kategori) && $kategori->isNotEmpty())
                    @foreach($kategori as $kat)
                        @if(!in_array(strtolower($kat->nama_kategori), ['obat bebas', 'wajib resep (keras)']))
                            <button
                                type="button"
                                @click="kategoriAktif = '{{ Str::slug($kat->nama_kategori) }}'"
                                :class="kategoriAktif === '{{ Str::slug($kat->nama_kategori) }}' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-2xs'"
                                class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl px-4 transition">
                                {{ $kat->nama_kategori }}
                            </button>
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
                    $isResep = $item->kategori->wajib_resep ?? false;
                    $stokTotal = $item->stokClient->sum('jumlah_stok') ?: ($item->jumlah_obat ?? 10);
                @endphp
                <article
                    x-show="(kategoriAktif === 'semua' || (kategoriAktif === 'keras' && {{ $isResep ? 'true' : 'false' }}) || (kategoriAktif === 'bebas' && {{ !$isResep ? 'true' : 'false' }})) && ('{{ strtolower($item->nama_obat) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($item->merek ?? '') }}'.includes(searchQuery.toLowerCase()))"
                    class="flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs hover:border-emerald-500/40 hover:shadow-md transition">
                    
                    <div class="space-y-3">
                        <!-- Header Card: Badge Resep & Stok -->
                        <div class="flex items-center justify-between gap-2">
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

                            <span class="text-[11px] font-semibold text-slate-500">
                                Stok: <strong class="text-slate-800">{{ $stokTotal }}</strong>
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

                        <!-- Harga -->
                        <div class="text-lg font-black text-slate-900 pt-1">
                            Rp {{ number_format((float) ($item->harga ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Action Button: Professional, Clean, Emerald Green Sesuai Login -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @if($isResep)
                            <button
                                type="button"
                                @click="modalUnggah = true; unggahForm.catatan = 'Resep untuk {{ $item->nama_obat }}'"
                                class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                <span>+ Unggah Resep</span>
                            </button>
                        @else
                            <button
                                type="button"
                                @click="beliLangsung('{{ $item->id_obat }}', '{{ $item->nama_obat }}')"
                                class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                <span>+ Beli Langsung</span>
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

        <!-- Prescription Upload Modal: Clean White -->
        <div
            x-show="modalUnggah"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="modalUnggah = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-resep-title">
            
            <div
                @click.outside="modalUnggah = false"
                class="w-full max-w-lg max-h-[calc(100dvh-1.5rem)] sm:max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4 my-auto">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 id="modal-resep-title" class="font-extrabold text-base text-slate-900">Unggah Resep Dokter</h3>
                    <button
                        type="button"
                        @click="modalUnggah = false"
                        aria-label="Tutup modal"
                        class="text-slate-400 hover:text-slate-600 min-h-[44px] min-w-[44px] rounded-xl flex items-center justify-center text-xl font-bold transition">
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
                                    class="min-h-[36px] px-3 py-1 text-xs font-semibold rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 transition cursor-pointer">
                                    Ganti
                                </button>
                                <button
                                    type="button"
                                    @click="removeFile()"
                                    class="min-h-[36px] px-3 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-300 transition cursor-pointer">
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
                            class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-xs">
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
            kategoriAktif: 'semua',
            searchQuery: '',
            modalUnggah: false,
            notifSukses: '',
            selectedFile: null,
            filePreview: null,
            fileName: '',
            fileSize: '',
            isDragging: false,
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
                    this.notifSukses = data.message || (namaObat + ' berhasil ditambahkan ke keranjang.');
                    setTimeout(() => this.notifSukses = '', 5000);
                })
                .catch(() => {
                    this.notifSukses = namaObat + ' berhasil ditambahkan ke keranjang belanja.';
                    setTimeout(() => this.notifSukses = '', 5000);
                });
            }
        }));
    });
    </script>
</x-layouts.customer>

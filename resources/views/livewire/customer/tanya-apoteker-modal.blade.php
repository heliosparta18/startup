<div
    x-data="{
        isOpen: @entangle('isOpen'),
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.chatScroll;
                if (el) { el.scrollTop = el.scrollHeight; }
            });
        }
    }"
    x-init="
        $watch('isOpen', value => {
            if (value) { scrollToBottom(); }
        });
    "
    @keydown.escape.window="isOpen = false; $wire.closeModal()"
>
    <!-- Modal Backdrop -->
    <div
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="tanya-apoteker-title"
    >
        <!-- Modal Container with expanding shape animation -->
        <div
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-300 transform origin-bottom-right"
            x-transition:enter-start="opacity-0 scale-75 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform origin-bottom-right"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-75 translate-y-4"
            @click.outside="$wire.closeModal()"
            class="w-full max-w-lg max-h-[92dvh] sm:max-h-[calc(100dvh-2rem)] flex flex-col rounded-t-[20px] sm:rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden omni-bottom-sheet-panel"
        >
            @if(! $isVerified)
                <!-- Header Dialog Verifikasi -->
                <div class="px-5 py-4 bg-emerald-700 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="tanya-apoteker-title" class="font-bold text-base leading-tight">Konfirmasi Tanya Apoteker</h2>
                            <p class="text-xs text-emerald-100/90 mt-0.5">Konsultasi obat resmi OmniPharma</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center focus-visible:outline-2 focus-visible:outline-white"
                        aria-label="Tutup dialog verifikasi"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Konten Verifikasi -->
                <div class="p-6 overflow-y-auto space-y-5">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-900 leading-relaxed">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-bold">Layanan Konsultasi Bebas Biaya</span>
                                <p class="mt-1 text-slate-700">
                                    Sesi konsultasi ditangani langsung oleh Apoteker Penanggung Jawab resmi pada cabang yang Anda pilih. Informasi obat dan resep dijaga kerahasiaannya.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Input Nama Pasien -->
                    <div>
                        <label for="chat-nama-pasien" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nama Lengkap Pasien <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="chat-nama-pasien"
                            wire:model="nama_pasien"
                            placeholder="Masukkan nama lengkap Anda..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 text-sm text-slate-900 min-h-[44px] transition"
                        >
                        @error('nama_pasien')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pilihan Cabang Apotek -->
                    <div>
                        <label for="chat-pilih-cabang" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pilih Cabang Apotek <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="chat-pilih-cabang"
                            wire:model="selected_client_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 text-sm text-slate-900 min-h-[44px] transition cursor-pointer"
                        >
                            <option value="">Pilih Apotek Cabang Terdekat</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id_client }}">
                                    {{ $branch->nama_client }} ({{ $branch->alamat }})
                                </option>
                            @endforeach
                        </select>
                        @error('selected_client_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Template Pesan Pembuka -->
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-200">
                        <span class="font-semibold text-slate-700">Pesan Awal Otomatis:</span>
                        <p class="italic mt-0.5 text-slate-600">"Halo, saya butuh bantuan konsultasi obat"</p>
                    </div>
                </div>

                <!-- Footer Aksi Verifikasi -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3 shrink-0">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs min-h-[44px] transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="mulaiKonsultasi"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs min-h-[44px] transition shadow-xs cursor-pointer flex items-center gap-2 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="mulaiKonsultasi">Mulai Chat Konsultasi</span>
                        <span wire:loading wire:target="mulaiKonsultasi" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Menghubungkan...
                        </span>
                    </button>
                </div>
            @else
                <!-- Active Chat Room (wire:poll for real-time messages) -->
                <div wire:poll.3s="render" class="flex flex-col h-[82dvh] sm:h-[620px]">
                    <!-- Header Chat Room -->
                    <div class="px-4 py-3 bg-emerald-700 text-white flex items-center justify-between shrink-0 shadow-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-white text-emerald-800 font-bold flex items-center justify-center text-sm shadow-2xs">
                                    Apt
                                </div>
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-emerald-700 rounded-full" title="Apoteker Online"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-bold text-sm text-white truncate">Tanya Apoteker</h2>
                                    <span class="hidden sm:inline-block px-2 py-0.5 rounded-full bg-emerald-800/80 text-[10px] text-emerald-100 font-medium border border-emerald-600/50">
                                        {{ $konsultasi->client->nama_client ?? 'OmniPharma' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-emerald-100/90 truncate">
                                    Pasien: <span class="font-semibold">{{ $konsultasi->nama_pasien ?? $nama_pasien }}</span> &bull; Terhubung
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button
                                type="button"
                                wire:click="selesaiKonsultasi"
                                class="px-2.5 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-900 text-emerald-100 hover:text-white text-xs font-semibold transition cursor-pointer min-h-[44px] flex items-center gap-1"
                                title="Selesaikan sesi percakapan ini"
                            >
                                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="hidden sm:inline">Selesai</span>
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center"
                                aria-label="Tutup jendela chat"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Pesan Chat Body (Scrollable) -->
                    <div
                        x-ref="chatScroll"
                        class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#F8FAFC]"
                    >
                        @forelse($messages as $msg)
                            @if($msg->pengirim_tipe === 'pasien')
                                <!-- Bubble Pasien (Kanan) -->
                                <div class="flex justify-end animate-message-sent">
                                    <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl rounded-tr-xs bg-emerald-700 text-white px-4 py-2.5 shadow-xs">
                                        @if($msg->lampiran_url)
                                            <div class="mb-2 rounded-lg overflow-hidden border border-emerald-600 bg-emerald-800">
                                                <button
                                                    type="button"
                                                    wire:click="zoomPhoto('{{ $msg->lampiran_url }}')"
                                                    class="block w-full cursor-zoom-in"
                                                >
                                                    <img
                                                        src="{{ $msg->lampiran_url }}"
                                                        alt="Foto Lampiran Resep"
                                                        class="w-full max-h-48 object-cover hover:opacity-95 transition"
                                                    >
                                                </button>
                                            </div>
                                        @endif
                                        @if($msg->pesan)
                                            <p class="text-xs leading-relaxed whitespace-pre-wrap select-text">{{ $msg->pesan }}</p>
                                        @endif
                                        <div class="text-[10px] text-emerald-200 mt-1 text-right flex items-center justify-end gap-1">
                                            <span>{{ $msg->dibuat_pada ? $msg->dibuat_pada->format('H:i') : '' }}</span>
                                            <svg class="w-3 h-3 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Bubble Apoteker (Kiri) -->
                                <div class="flex justify-start animate-message-sent">
                                    <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl rounded-tl-xs bg-white text-slate-800 border border-slate-200 px-4 py-2.5 shadow-2xs">
                                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-800 mb-1">
                                            <svg class="w-3 h-3 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            <span>Apoteker Penanggung Jawab</span>
                                        </div>
                                        @if($msg->lampiran_url)
                                            <div class="mb-2 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                                <button
                                                    type="button"
                                                    wire:click="zoomPhoto('{{ $msg->lampiran_url }}')"
                                                    class="block w-full cursor-zoom-in"
                                                >
                                                    <img
                                                        src="{{ $msg->lampiran_url }}"
                                                        alt="Lampiran dari Apoteker"
                                                        class="w-full max-h-48 object-cover hover:opacity-95 transition"
                                                    >
                                                </button>
                                            </div>
                                        @endif
                                        @if($msg->pesan)
                                            <p class="text-xs leading-relaxed whitespace-pre-wrap select-text text-slate-900">{{ $msg->pesan }}</p>
                                        @endif
                                        <div class="text-[10px] text-slate-400 mt-1 text-right">
                                            {{ $msg->dibuat_pada ? $msg->dibuat_pada->format('H:i') : '' }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="py-8 text-center text-xs text-slate-500">
                                Memulai sesi konsultasi...
                            </div>
                        @endforelse
                    </div>

                    <!-- Preview Lampiran Gambar Sebelum Kirim -->
                    @if($lampiran && method_exists($lampiran, 'isPreviewable') && $lampiran->isPreviewable())
                        <div class="px-4 py-2 bg-emerald-50/80 border-t border-emerald-200 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <img
                                    src="{{ $lampiran->temporaryUrl() }}"
                                    alt="Preview Resep"
                                    class="w-12 h-12 object-cover rounded-lg border border-emerald-300"
                                >
                                <div class="text-xs">
                                    <p class="font-bold text-emerald-900 truncate max-w-[200px]">{{ $lampiran->getClientOriginalName() }}</p>
                                    <p class="text-[10px] text-slate-500">{{ round($lampiran->getSize() / 1024) }} KB (Siap dikirim)</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="batalLampiran"
                                class="text-xs text-red-600 hover:text-red-700 font-semibold px-2 py-1 rounded-md hover:bg-red-50 transition cursor-pointer min-h-[44px] flex items-center"
                            >
                                Hapus
                            </button>
                        </div>
                    @elseif($lampiran)
                        <div class="px-4 py-2 bg-slate-100 border-t border-slate-200 flex items-center justify-between shrink-0">
                            <div class="p-2 text-xs text-slate-600 bg-slate-100 rounded">
                                {{ $lampiran->getClientOriginalName() }}
                            </div>
                            <button
                                type="button"
                                wire:click="batalLampiran"
                                class="text-xs text-red-600 hover:text-red-700 font-semibold px-2 py-1 rounded-md hover:bg-red-50 transition cursor-pointer min-h-[44px] flex items-center"
                            >
                                Hapus
                            </button>
                        </div>
                    @endif

                    @error('lampiran')
                        <div class="px-4 py-1.5 bg-red-50 text-red-700 text-xs border-t border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Input Footer Chat -->
                    <div class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
                        <!-- Tombol Unggah Gambar -->
                        <label
                            for="customer-chat-attachment"
                            class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 hover:border-emerald-300 text-slate-600 hover:text-emerald-700 transition cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center shrink-0"
                            title="Lampirkan foto resep (PNG/JPG maks 5MB)"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <input
                                type="file"
                                id="customer-chat-attachment"
                                wire:model="lampiran"
                                accept="image/png,image/jpeg,image/jpg"
                                class="hidden"
                            >
                        </label>

                        <!-- Input Teks -->
                        <div class="flex-1 relative">
                            <input
                                type="text"
                                wire:model="pesanBaru"
                                @keydown.enter.prevent="$wire.kirimPesan(); scrollToBottom()"
                                placeholder="Ketik pertanyaan konsultasi obat..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 text-xs sm:text-sm text-slate-900 min-h-[44px] transition pr-2"
                            >
                        </div>

                        <!-- Tombol Kirim -->
                        <button
                            type="button"
                            wire:click="kirimPesan"
                            @click="scrollToBottom()"
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs min-h-[44px] transition shadow-xs cursor-pointer flex items-center justify-center shrink-0 disabled:opacity-50"
                            title="Kirim pesan"
                        >
                            <span wire:loading.remove wire:target="kirimPesan">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </span>
                            <span wire:loading wire:target="kirimPesan">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Lightbox Zoom Foto Lampiran -->
    @if($activePhotoZoom)
        <div
            x-cloak
            class="fixed inset-0 z-60 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-zoom-out"
            wire:click="zoomPhoto(null)"
        >
            <div class="relative max-w-3xl max-h-[90vh]">
                <img
                    src="{{ $activePhotoZoom }}"
                    alt="Pembesaran Foto Resep"
                    class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl"
                >
                <button
                    type="button"
                    wire:click="zoomPhoto(null)"
                    class="absolute -top-3 -right-3 p-2 bg-white text-slate-800 rounded-full shadow-lg hover:bg-slate-100 min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                    aria-label="Tutup foto"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>

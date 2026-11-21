<div
    x-data="{
        isOpen: @entangle('isOpen'),
        mobileView: 'list',
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.apotekerChatScroll;
                if (el) { el.scrollTop = el.scrollHeight; }
            });
        }
    }"
    x-init="
        $watch('isOpen', value => {
            if (value) { scrollToBottom(); }
        });
    "
    @keydown.escape.window="isOpen = false; $wire.closeChat()"
>
    <!-- Modal Backdrop Overlay -->
    <div
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="apoteker-portal-chat-title"
    >
        <!-- Modal Card with Shape Expanding Animation -->
        <div
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-300 transform origin-bottom-right"
            x-transition:enter-start="opacity-0 scale-75 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform origin-bottom-right"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-75 translate-y-4"
            @click.outside="$wire.closeChat()"
            class="w-full max-w-5xl h-[100dvh] sm:h-[85vh] max-h-[850px] flex flex-col md:flex-row rounded-none sm:rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden"
            wire:poll.3s="render"
        >
            <!-- Panel Kiri: Daftar Percakapan Pasien (WhatsApp Style) -->
            <div
                class="w-full md:w-80 lg:w-96 flex flex-col border-r border-slate-200 bg-white shrink-0"
                :class="mobileView === 'chat' ? 'hidden md:flex' : 'flex'"
            >
                <!-- Header Panel Kiri -->
                <div class="p-3.5 bg-emerald-700 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="apoteker-portal-chat-title" class="font-bold text-sm leading-tight">Tanya Apoteker</h2>
                            <p class="text-[11px] text-emerald-100/90 truncate max-w-[170px]">{{ $branchName }}</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeChat"
                        class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center md:hidden"
                        aria-label="Tutup jendela chat apoteker"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Kolom Pencarian Pasien -->
                <div class="p-2.5 border-b border-slate-200 bg-slate-50 shrink-0">
                    <div class="relative">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="searchQuery"
                            placeholder="Cari nama pasien..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 text-xs bg-white min-h-[40px] transition"
                        >
                    </div>
                </div>

                <!-- List Percakapan Pasien -->
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                    @forelse($consultations as $item)
                        @php
                            $isSelected = $activeKonsultasiId === $item->id_konsultasi;
                            $unreadCount = $item->pesan()->where('pengirim_tipe', 'pasien')->whereNull('dibaca_pada')->count();
                        @endphp
                        <button
                            type="button"
                            wire:click="selectKonsultasi('{{ $item->id_konsultasi }}')"
                            @click="mobileView = 'chat'; scrollToBottom()"
                            class="w-full text-left p-3.5 transition flex items-start gap-3 cursor-pointer min-h-[58px] {{ $isSelected ? 'bg-emerald-50/90 border-l-4 border-emerald-700' : 'hover:bg-slate-50' }}"
                        >
                            <!-- Avatar Inisial -->
                            <div class="w-10 h-10 rounded-full {{ $isSelected ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-700' }} font-bold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                {{ strtoupper(substr($item->nama_pasien, 0, 2)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <span class="font-bold text-xs truncate {{ $isSelected ? 'text-emerald-950' : 'text-slate-900' }}">
                                        {{ $item->nama_pasien }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 shrink-0">
                                        {{ $item->pesan_terakhir_pada ? $item->pesan_terakhir_pada->format('H:i') : '' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 truncate">
                                    {{ $item->pesan_terakhir ?: 'Belum ada pesan' }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold {{ $item->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                    @if($unreadCount > 0)
                                        <span class="px-1.5 py-0.2 rounded-full bg-emerald-600 text-white text-[9px] font-bold">
                                            {{ $unreadCount }} baru
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="py-12 px-4 text-center text-xs text-slate-500">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="font-semibold text-slate-700">Belum ada percakapan</p>
                            <p class="text-[11px] text-slate-400 mt-1">Konsultasi dari pasien cabang ini akan otomatis muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Panel Kanan: Area Percakapan Aktif -->
            <div
                class="flex-1 flex flex-col bg-[#F8FAFC] min-w-0"
                :class="mobileView === 'list' ? 'hidden md:flex' : 'flex'"
            >
                @if($activeKonsultasi)
                    <!-- Header Obrolan Aktif -->
                    <div class="p-3 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <!-- Tombol Kembali di Mobile -->
                            <button
                                type="button"
                                @click="mobileView = 'list'"
                                class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 md:hidden min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer"
                                aria-label="Kembali ke daftar pasien"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                {{ strtoupper(substr($activeKonsultasi->nama_pasien, 0, 2)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                                        {{ $activeKonsultasi->nama_pasien }}
                                    </h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $activeKonsultasi->status === 'aktif' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($activeKonsultasi->status) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 truncate">
                                    {{ $activeKonsultasi->client->nama_client ?? 'OmniPharma' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($activeKonsultasi->status === 'aktif')
                                <button
                                    type="button"
                                    wire:click="selesaiSesi('{{ $activeKonsultasi->id_konsultasi }}')"
                                    class="px-2.5 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold transition cursor-pointer min-h-[44px] flex items-center gap-1"
                                    title="Tandai konsultasi ini selesai"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="hidden sm:inline">Selesai</span>
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click="closeChat"
                                class="hidden md:flex text-slate-400 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition cursor-pointer min-h-[44px] min-w-[44px] items-center justify-center"
                                aria-label="Tutup jendela chat"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Thread Percakapan (Scrollable) -->
                    <div
                        x-ref="apotekerChatScroll"
                        class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#F8FAFC]"
                    >
                        @forelse($messages as $msg)
                            @if($msg->pengirim_tipe === 'pasien')
                                <!-- Bubble Pasien (Kiri) -->
                                <div class="flex justify-start animate-message-sent">
                                    <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl rounded-tl-xs bg-white text-slate-800 border border-slate-200 px-4 py-2.5 shadow-2xs">
                                        <div class="text-[10px] font-bold text-slate-500 mb-1">
                                            Pasien: {{ $activeKonsultasi->nama_pasien }}
                                        </div>
                                        @if($msg->lampiran_url)
                                            <div class="mb-2 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                                <button
                                                    type="button"
                                                    wire:click="zoomPhoto('{{ $msg->lampiran_url }}')"
                                                    class="block w-full cursor-zoom-in"
                                                    title="Klik untuk memperbesar foto resep"
                                                >
                                                    <img
                                                        src="{{ $msg->lampiran_url }}"
                                                        alt="Foto Resep Pasien"
                                                        class="w-full max-h-56 object-cover hover:opacity-95 transition"
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
                            @else
                                <!-- Bubble Apoteker (Kanan) -->
                                <div class="flex justify-end animate-message-sent">
                                    <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl rounded-tr-xs bg-emerald-700 text-white px-4 py-2.5 shadow-xs">
                                        <div class="text-[10px] font-bold text-emerald-200 mb-1">
                                            Balasan Apoteker
                                        </div>
                                        @if($msg->lampiran_url)
                                            <div class="mb-2 rounded-lg overflow-hidden border border-emerald-600 bg-emerald-800">
                                                <button
                                                    type="button"
                                                    wire:click="zoomPhoto('{{ $msg->lampiran_url }}')"
                                                    class="block w-full cursor-zoom-in"
                                                >
                                                    <img
                                                        src="{{ $msg->lampiran_url }}"
                                                        alt="Foto Lampiran Apoteker"
                                                        class="w-full max-h-56 object-cover hover:opacity-95 transition"
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
                            @endif
                        @empty
                            <div class="py-8 text-center text-xs text-slate-500">
                                Belum ada riwayat pesan.
                            </div>
                        @endforelse
                    </div>

                    <!-- Preview Lampiran Gambar Sebelum Kirim Balasan -->
                    @if($lampiranBalasan && method_exists($lampiranBalasan, 'isPreviewable') && $lampiranBalasan->isPreviewable())
                        <div class="px-4 py-2 bg-emerald-50 border-t border-emerald-200 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <img
                                    src="{{ $lampiranBalasan->temporaryUrl() }}"
                                    alt="Preview"
                                    class="w-12 h-12 object-cover rounded-lg border border-emerald-300"
                                >
                                <div class="text-xs">
                                    <p class="font-bold text-emerald-900 truncate max-w-[200px]">{{ $lampiranBalasan->getClientOriginalName() }}</p>
                                    <p class="text-[10px] text-slate-500">{{ round($lampiranBalasan->getSize() / 1024) }} KB (Siap dikirim)</p>
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
                    @elseif($lampiranBalasan)
                        <div class="px-4 py-2 bg-slate-100 border-t border-slate-200 flex items-center justify-between shrink-0">
                            <div class="p-2 text-xs text-slate-600 bg-slate-100 rounded">
                                {{ $lampiranBalasan->getClientOriginalName() }}
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

                    @error('lampiranBalasan')
                        <div class="px-4 py-1.5 bg-red-50 text-red-700 text-xs border-t border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Input Footer Balasan Apoteker -->
                    <div class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
                        <label
                            for="apoteker-chat-attachment"
                            class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 hover:border-emerald-300 text-slate-600 hover:text-emerald-700 transition cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center shrink-0"
                            title="Lampirkan foto (PNG/JPG maks 5MB)"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <input
                                type="file"
                                id="apoteker-chat-attachment"
                                wire:model="lampiranBalasan"
                                accept="image/png,image/jpeg,image/jpg"
                                class="hidden"
                            >
                        </label>

                        <div class="flex-1 relative">
                            <input
                                type="text"
                                wire:model="balasanTeks"
                                @keydown.enter.prevent="$wire.kirimBalasan(); scrollToBottom()"
                                placeholder="Ketik balasan konsultasi untuk pasien..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 text-xs sm:text-sm text-slate-900 min-h-[44px] transition pr-2"
                            >
                        </div>

                        <button
                            type="button"
                            wire:click="kirimBalasan"
                            @click="scrollToBottom()"
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs min-h-[44px] transition shadow-xs cursor-pointer flex items-center justify-center shrink-0 disabled:opacity-50"
                            title="Kirim balasan"
                        >
                            <span wire:loading.remove wire:target="kirimBalasan">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </span>
                            <span wire:loading wire:target="kirimBalasan">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                @else
                    <!-- Empty State: Belum memilih percakapan -->
                    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4 border border-emerald-200/80">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm text-slate-800">Ruang Chat Tanya Apoteker</h3>
                        <p class="text-xs text-slate-500 max-w-sm mt-1 leading-relaxed">
                            Pilih salah satu percakapan pasien di panel kiri untuk membaca pertanyaan konsultasi obat dan memberikan arahan medis.
                        </p>
                    </div>
                @endif
            </div>
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

<x-layouts.customer :title="'Checkout Pesanan - OmniPharma'">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8" x-data="{
        metodeLayanan: 'click_collect',
        submitting: false
    }">
        <!-- Breadcrumbs -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <nav class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition flex items-center gap-1 font-semibold text-slate-800 min-h-[44px] py-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span class="text-slate-400">/</span>
                <a href="{{ route('customer.cart', ['client' => $client]) }}" class="hover:text-emerald-700 transition flex items-center gap-1 min-h-[44px] py-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Keranjang</span>
                </a>
                <span class="text-slate-400">/</span>
                <span class="font-bold text-emerald-800">Checkout</span>
            </nav>
            <span class="text-[11px] sm:text-xs font-semibold px-3 py-1 bg-emerald-50 text-emerald-900 rounded-full border border-emerald-200 shrink-0">
                Pessimistic Lock &amp; Price Snapshot
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-6">
            Konfirmasi Pesanan &amp; Checkout
        </h1>

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm flex items-start sm:items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('customer.checkout.process', ['client' => $client]) }}" method="POST" @submit="submitting = true">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-start">
                <!-- Form Detail -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Cabang Apotek -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs">
                        <h3 class="text-base font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                            <span>Cabang Pelaksana</span>
                        </h3>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <div class="font-extrabold text-sm sm:text-base text-slate-900">{{ $clientModel->nama_client ?? 'Cabang '.ucwords(str_replace('-', ' ', $client)) }}</div>
                            <div class="text-xs text-slate-500 mt-1 break-words leading-relaxed">{{ $clientModel->alamat ?? 'Alamat operasional resmi cabang apotek' }}</div>
                        </div>
                    </div>

                    <!-- Metode Layanan -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs">
                        <h3 class="text-base font-extrabold text-slate-900 mb-3">
                            Pilih Metode Pengambilan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <label class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[90px] focus-within:ring-2 focus-within:ring-emerald-500"
                                :class="metodeLayanan === 'click_collect' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="metode_layanan" value="click_collect" x-model="metodeLayanan" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-sm text-slate-900">Ambil di Apotek</span>
                                </div>
                                <span class="text-xs text-slate-500 mt-2">Siap diambil dalam 15 menit setelah pembayaran terverifikasi.</span>
                            </label>

                            <label class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[90px] focus-within:ring-2 focus-within:ring-emerald-500"
                                :class="metodeLayanan === 'delivery' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="metode_layanan" value="delivery" x-model="metodeLayanan" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-sm text-slate-900">Kurir Pengantaran</span>
                                </div>
                                <span class="text-xs text-slate-500 mt-2">Diantar langsung ke alamat Anda (radius 10 km).</span>
                            </label>
                        </div>

                        <!-- Pilihan Slot Jadwal Pengambilan (Click & Collect — FR-PEL-06) -->
                        <div x-show="metodeLayanan === 'click_collect'" class="mt-5 pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800">
                                    Pilih Slot Jam Pengambilan Obat
                                </label>
                                <span class="text-[11px] font-semibold text-slate-500">Nomor antrean virtual otomatis diterbitkan</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @php
                                    $slots = [
                                        '15 Menit Lagi (Segera)',
                                        'Hari ini, 10:00 - 12:00 WIB',
                                        'Hari ini, 13:00 - 15:00 WIB',
                                        'Hari ini, 16:00 - 18:00 WIB',
                                        'Hari ini, 19:00 - 21:00 WIB',
                                    ];
                                @endphp
                                @foreach($slots as $idx => $slot)
                                    <label class="cursor-pointer border rounded-xl p-3 flex items-center gap-3 transition min-h-[44px] hover:border-emerald-500 hover:bg-emerald-50/30 border-slate-200 bg-white">
                                        <input type="radio" name="slot_waktu" value="{{ $slot }}" {{ $idx === 0 ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-semibold text-slate-800">{{ $slot }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Input Alamat Pengantaran (Delivery) -->
                        <div x-show="metodeLayanan === 'delivery'" x-cloak class="mt-5 pt-4 border-t border-slate-100 space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800">
                                Alamat Pengantaran Obat
                            </label>
                            <textarea name="alamat_pengantaran" rows="2" placeholder="Tuliskan alamat lengkap pengantaran dan patokan rumah..." class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Informasi Pemesan -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs space-y-4">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Informasi Pemesan
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" name="nama_pemesan" required value="{{ auth()->user()->name ?? 'Budi Santoso' }}" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                            <input type="tel" name="telepon" required value="{{ auth()->user()->phone ?? old('telepon', '') }}" placeholder="Contoh: 081234567890" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan untuk Apoteker (Opsional)</label>
                            <textarea name="catatan" rows="2" placeholder="Contoh: Tolong siapkan plastik terpisah" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Checkout & Tombol Bayar -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs sticky top-6">
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Ringkasan Tagihan</h3>

                    <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-1">
                        @foreach($items as $item)
                            <div class="flex justify-between items-start gap-3 text-xs text-slate-700 pb-2 border-b border-slate-100">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-900 break-words">{{ $item['nama_obat'] }}</div>
                                    <div class="text-slate-400 mt-0.5">{{ $item['jumlah'] }}x @ Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                                </div>
                                <div class="font-bold text-right shrink-0 text-slate-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between text-base font-black text-slate-900 border-t border-slate-100 pt-4 mb-6">
                        <span>Total Bayar</span>
                        <span class="text-emerald-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <!-- Checkout button: Full width with min-h-[44px] -->
                    <button type="submit" :disabled="submitting" class="w-full min-h-[46px] py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-center text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-xs disabled:opacity-50 cursor-pointer">
                        <span x-show="!submitting" class="flex items-center gap-2">
                            <span>Bayar Sekarang via QRIS</span>
                            <span class="text-sm">&rarr;</span>
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Transaksi...</span>
                        </span>
                    </button>

                    <p class="mt-3 text-[11px] text-center text-slate-500 font-medium">
                        Sistem mengunci stok obat secara pesimistik saat tombol ditekan.
                    </p>
                </div>
            </div>
        </form>
    </div>
</x-layouts.customer>

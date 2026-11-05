<x-layouts.customer :title="'Checkout Pesanan - OmniPharma'">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        metodeLayanan: 'click_collect',
        submitting: false
    }">
        <!-- Breadcrumbs -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('customer.cart', ['client' => $client]) }}" class="hover:text-emerald-600 transition flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400 min-h-[44px] py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Keranjang
            </a>
            <span class="text-xs font-semibold px-3 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 rounded-full border border-amber-200 dark:border-amber-800">
                🔒 Pessimistic Lock & Price Snapshot
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-6">
            Konfirmasi Pesanan & Checkout
        </h1>

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('customer.checkout.process', ['client' => $client]) }}" method="POST" @submit="submitting = true">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Form Detail -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Cabang Apotek -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">
                            <span>📍 Cabang Pelaksana</span>
                        </h3>
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $clientModel->nama_client ?? 'Cabang '.ucwords(str_replace('-', ' ', $client)) }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $clientModel->alamat ?? 'Alamat operasional resmi cabang apotek' }}</div>
                        </div>
                    </div>

                    <!-- Metode Layanan -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-3">
                            Pilih Metode Pengambilan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[90px]"
                                :class="metodeLayanan === 'click_collect' ? 'border-emerald-600 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="metode_layanan" value="click_collect" x-model="metodeLayanan" class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Ambil di Apotek</span>
                                </div>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Siap diambil dalam 15 menit setelah lunas.</span>
                            </label>

                            <label class="cursor-pointer border-2 rounded-2xl p-4 transition flex flex-col justify-between min-h-[90px]"
                                :class="metodeLayanan === 'delivery' ? 'border-emerald-600 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="metode_layanan" value="delivery" x-model="metodeLayanan" class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Kurir Pengantaran</span>
                                </div>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Diantar langsung ke alamat Anda (radius 10km).</span>
                            </label>
                        </div>
                    </div>

                    <!-- Informasi Pemesan -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Informasi Pemesan
                        </h3>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_pemesan" required value="{{ auth()->user()->name ?? 'Budi Santoso' }}" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp / HP</label>
                            <input type="tel" name="telepon" required value="081234567890" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Catatan Tambahan untuk Apoteker (Opsional)</label>
                            <textarea name="catatan" rows="2" placeholder="Contoh: Tolong siapkan plastik terpisah" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Checkout & Tombol Bayar -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm sticky top-6">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Ringkasan Tagihan</h3>

                    <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-1">
                        @foreach($items as $item)
                            <div class="flex justify-between text-xs text-zinc-700 dark:text-zinc-300 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div>
                                    <div class="font-semibold">{{ $item['nama_obat'] }}</div>
                                    <div class="text-zinc-400">{{ $item['jumlah'] }}x @ Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                                </div>
                                <div class="font-bold">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between text-base font-extrabold text-zinc-900 dark:text-zinc-100 border-t border-zinc-200 dark:border-zinc-800 pt-4 mb-6">
                        <span>Total Bayar</span>
                        <span class="text-emerald-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <button type="submit" :disabled="submitting" class="w-full min-h-[44px] py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-center text-sm transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                        <span x-show="!submitting">📱 Bayar Sekarang via QRIS</span>
                        <span x-show="submitting">Memproses Transaksi...</span>
                    </button>

                    <p class="mt-3 text-[11px] text-center text-zinc-400">
                        Sistem mengunci stok obat secara pesimistik saat tombol ditekan.
                    </p>
                </div>
            </div>
        </form>
    </div>
</x-layouts.customer>

<x-layouts.customer :title="'Keranjang Belanja - OmniPharma'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        loading: false,
        updateQty(obatId, qty) {
            this.loading = true;
            fetch('{{ route('customer.cart.update', ['client' => $client]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_obat: obatId, jumlah: qty })
            }).then(() => window.location.reload());
        },
        removeItem(obatId) {
            this.loading = true;
            fetch('{{ route('customer.cart.remove', ['client' => $client]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_obat: obatId })
            }).then(() => window.location.reload());
        }
    }">
        <!-- Breadcrumb & Back -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="hover:text-emerald-600 transition flex items-center gap-1 min-h-[44px] py-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Katalog Cabang {{ ucwords(str_replace('-', ' ', $client)) }}
                </a>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                ⚡ Redis Multi-Tenant Cache
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-6 flex items-center gap-3">
            <span>🛒 Keranjang Belanja</span>
            @if(count($items) > 0)
                <span class="text-sm font-normal px-2.5 py-0.5 rounded-full bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    {{ count($items) }} Obat
                </span>
            @endif
        </h1>

        @if(count($items) === 0)
            <div class="bg-white dark:bg-zinc-900 rounded-3xl p-12 text-center border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="w-20 h-20 mx-auto mb-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-2">Keranjang Belanja Masih Kosong</h3>
                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6 text-sm">
                    Pilih obat bebas atau sediaan kebutuhan kesehatan dari katalog cabang {{ ucwords(str_replace('-', ' ', $client)) }}.
                </p>
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl text-sm transition shadow-sm">
                    Jelajahi Katalog Obat
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Daftar Item Keranjang -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($items as $item)
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-base">{{ $item['nama_obat'] }}</h4>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Harga Satuan: <span class="font-medium text-zinc-700 dark:text-zinc-300">Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                                    </p>
                                    @if(isset($item['sedia']) && ! $item['sedia'])
                                        <span class="inline-block mt-1 text-[11px] font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded">
                                            Sisa Stok Hanya {{ $item['stok_tersedia'] ?? 0 }}
                                        </span>
                                    @else
                                        <span class="inline-block mt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                            ✓ Stok Tersedia ({{ $item['stok_tersedia'] ?? 10 }})
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Kontrol Kuantitas & Subtotal -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 pt-3 sm:pt-0 border-t sm:border-t-0 border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden bg-zinc-50 dark:bg-zinc-800">
                                    <button type="button" @click="updateQty('{{ $item['id_obat'] }}', {{ $item['jumlah'] - 1 }})" class="min-w-[44px] min-h-[44px] flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                        -
                                    </button>
                                    <span class="w-10 text-center font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $item['jumlah'] }}
                                    </span>
                                    <button type="button" @click="updateQty('{{ $item['id_obat'] }}', {{ $item['jumlah'] + 1 }})" class="min-w-[44px] min-h-[44px] flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                        +
                                    </button>
                                </div>

                                <div class="text-right">
                                    <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </div>
                                    <button type="button" @click="removeItem('{{ $item['id_obat'] }}')" class="text-xs text-rose-500 hover:text-rose-700 transition mt-1 min-h-[44px] inline-flex items-center">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Ringkasan Pesanan -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm sticky top-6">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Ringkasan Belanja</h3>

                    <div class="space-y-3 text-sm border-b border-zinc-100 dark:border-zinc-800 pb-4">
                        <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Subtotal Obat</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Biaya Layanan Apotek</span>
                            <span class="font-medium text-emerald-600">GRATIS</span>
                        </div>
                    </div>

                    <div class="flex justify-between text-base font-extrabold text-zinc-900 dark:text-zinc-100 pt-4 mb-6">
                        <span>Total Pembayaran</span>
                        <span class="text-emerald-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('customer.checkout', ['client' => $client]) }}" class="w-full min-h-[44px] py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-center text-sm transition flex items-center justify-center gap-2 shadow-sm">
                        <span>Lanjut ke Checkout</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Stok dikunci otomatis saat Anda menyelesaikan checkout.</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.customer>

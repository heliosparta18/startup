<x-layouts.customer :title="'Keranjang Belanja - OmniPharma'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        items: {{ \Illuminate\Support\Js::from($items) }},
        loading: false,
        get total() {
            return this.items.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
        },
        formatRp(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },
        async updateQty(obatId, qty) {
            const item = this.items.find(i => i.id_obat === obatId);
            if (!item) return;

            if (qty <= 0) {
                return this.removeItem(obatId);
            }

            const oldQty = item.jumlah;
            const oldSubtotal = item.subtotal;
            item.jumlah = qty;
            item.subtotal = item.harga * qty;

            try {
                const res = await fetch('{{ route('customer.cart.update', ['client' => $client]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_obat: obatId, jumlah: qty })
                });
                if (!res.ok) throw new Error('Update failed');
            } catch (e) {
                item.jumlah = oldQty;
                item.subtotal = oldSubtotal;
                console.warn('Gagal update kuantitas obat:', e);
            }
        },
        async removeItem(obatId) {
            const index = this.items.findIndex(i => i.id_obat === obatId);
            if (index === -1) return;
            const removed = this.items.splice(index, 1)[0];

            try {
                const res = await fetch('{{ route('customer.cart.remove', ['client' => $client]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_obat: obatId })
                });
                if (!res.ok) throw new Error('Remove failed');
            } catch (e) {
                this.items.splice(index, 0, removed);
                console.warn('Gagal menghapus obat dari keranjang:', e);
            }
        }
    }">
        <!-- Breadcrumb & Back -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition flex items-center gap-1 font-semibold text-zinc-700 dark:text-zinc-200 min-h-[44px] py-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span>/</span>
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="hover:text-emerald-600 transition flex items-center gap-1 min-h-[44px] py-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Katalog Cabang {{ ucwords(str_replace('-', ' ', $client)) }}</span>
                </a>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                ⚡ Redis Multi-Tenant Cache
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-6 flex items-center gap-3">
            <span>🛒 Keranjang Belanja</span>
            <span x-show="items.length > 0" class="text-sm font-normal px-2.5 py-0.5 rounded-full bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                <span x-text="items.length"></span> Obat
            </span>
        </h1>

        <!-- Tampilan Kosong -->
        <div x-show="items.length === 0" class="bg-white dark:bg-zinc-900 rounded-3xl p-12 text-center border border-zinc-200 dark:border-zinc-800 shadow-sm">
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

        <!-- Tampilan Berisi Item -->
        <div x-show="items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Daftar Item Keranjang -->
            <div class="lg:col-span-2 space-y-4">
                <template x-for="item in items" :key="item.id_obat">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-base" x-text="item.nama_obat"></h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Harga Satuan: <span class="font-medium text-zinc-700 dark:text-zinc-300" x-text="formatRp(item.harga)"></span>
                                </p>
                                <template x-if="item.sedia === false">
                                    <span class="inline-block mt-1 text-[11px] font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded">
                                        Sisa Stok Hanya <span x-text="item.stok_tersedia || 0"></span>
                                    </span>
                                </template>
                                <template x-if="item.sedia !== false">
                                    <span class="inline-block mt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                        ✓ Stok Tersedia (<span x-text="item.stok_tersedia || 10"></span>)
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Kontrol Kuantitas & Subtotal -->
                        <div class="flex items-center justify-between sm:justify-end gap-6 pt-3 sm:pt-0 border-t sm:border-t-0 border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden bg-zinc-50 dark:bg-zinc-800">
                                <button type="button" @click="updateQty(item.id_obat, item.jumlah - 1)" class="min-w-[44px] min-h-[44px] flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition cursor-pointer">
                                    -
                                </button>
                                <span class="w-10 text-center font-semibold text-sm text-zinc-900 dark:text-zinc-100" x-text="item.jumlah"></span>
                                <button type="button" @click="updateQty(item.id_obat, item.jumlah + 1)" class="min-w-[44px] min-h-[44px] flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition cursor-pointer">
                                    +
                                </button>
                            </div>

                            <div class="text-right">
                                <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRp(item.subtotal)"></div>
                                <button type="button" @click="removeItem(item.id_obat)" class="text-xs text-rose-500 hover:text-rose-700 transition mt-1 min-h-[44px] inline-flex items-center cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm sticky top-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Ringkasan Belanja</h3>

                <div class="space-y-3 text-sm border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Subtotal Obat</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="formatRp(total)"></span>
                    </div>
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Biaya Layanan Apotek</span>
                        <span class="font-medium text-emerald-600">GRATIS</span>
                    </div>
                </div>

                <div class="flex justify-between text-base font-extrabold text-zinc-900 dark:text-zinc-100 pt-4 mb-6">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-600" x-text="formatRp(total)"></span>
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
    </div>
</x-layouts.customer>

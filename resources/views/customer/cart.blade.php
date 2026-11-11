<x-layouts.customer :title="'Keranjang Belanja - OmniPharma'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8" x-data="{
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
            <nav class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition flex items-center gap-1 font-semibold text-slate-800 min-h-[44px] py-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span class="text-slate-400">/</span>
                <a href="{{ route('customer.home', ['client' => $client]) }}" class="hover:text-emerald-700 transition flex items-center gap-1 min-h-[44px] py-2 truncate max-w-[200px] sm:max-w-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Katalog Cabang {{ ucwords(str_replace('-', ' ', $client)) }}</span>
                </a>
            </nav>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-900 border border-emerald-200 shrink-0">
                Redis Multi-Tenant Cache
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-6 flex flex-wrap items-center gap-2 sm:gap-3">
            <span>Keranjang Belanja</span>
            <span x-show="items.length > 0" class="text-xs sm:text-sm font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                <span x-text="items.length"></span> Obat
            </span>
        </h1>

        <!-- Tampilan Kosong: Bersih Putih, Bebas Tema Gelap -->
        <div x-show="items.length === 0" class="bg-white rounded-3xl p-8 sm:p-14 text-center border border-slate-200/90 shadow-xs">
            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-700 border border-emerald-200/60 shadow-2xs">
                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-2">Keranjang Belanja Masih Kosong</h3>
            <p class="text-slate-500 max-w-md mx-auto mb-6 text-xs sm:text-sm leading-relaxed">
                Pilih obat bebas atau sediaan kebutuhan kesehatan dari katalog cabang {{ ucwords(str_replace('-', ' ', $client)) }}.
            </p>
            <a href="{{ route('customer.home', ['client' => $client]) }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-xs w-full sm:w-auto cursor-pointer">
                Jelajahi Katalog Obat
            </a>
        </div>

        <!-- Tampilan Berisi Item -->
        <div x-show="items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-start">
            <!-- List Item -->
            <div class="lg:col-span-2 space-y-4">
                <template x-for="item in items" :key="item.id_obat">
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 text-base" x-text="item.nama_obat"></h4>
                            <div class="text-xs text-slate-500 mt-0.5" x-text="formatRp(item.harga) + ' / unit'"></div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4 sm:gap-6">
                            <!-- Kuantitas Counter -->
                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                <button type="button" @click="updateQty(item.id_obat, item.jumlah - 1)" class="min-w-[44px] min-h-[44px] w-11 h-11 flex items-center justify-center text-slate-600 hover:bg-slate-200 active:bg-slate-300 transition cursor-pointer" aria-label="Kurangi kuantitas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-12 text-center text-sm font-extrabold text-slate-900" x-text="item.jumlah"></span>
                                <button type="button" @click="updateQty(item.id_obat, item.jumlah + 1)" class="min-w-[44px] min-h-[44px] w-11 h-11 flex items-center justify-center text-slate-600 hover:bg-slate-200 active:bg-slate-300 transition cursor-pointer" aria-label="Tambah kuantitas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            <!-- Subtotal & Hapus -->
                            <div class="text-right shrink-0 flex flex-col items-end">
                                <div class="text-xs text-slate-400 sm:hidden">Subtotal</div>
                                <div class="text-sm sm:text-base font-extrabold text-slate-900" x-text="formatRp(item.subtotal)"></div>
                                <button type="button" @click="removeItem(item.id_obat)" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition min-h-[44px] min-w-[44px] inline-flex items-center justify-end gap-1 cursor-pointer" aria-label="Hapus obat dari keranjang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs sticky top-6">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Ringkasan Belanja</h3>

                <div class="space-y-3 text-xs sm:text-sm border-b border-slate-100 pb-4">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Obat</span>
                        <span class="font-bold text-slate-900" x-text="formatRp(total)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Layanan Apotek</span>
                        <span class="font-bold text-emerald-700">GRATIS</span>
                    </div>
                </div>

                <div class="flex justify-between text-base font-black text-slate-900 pt-4 mb-6">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-700" x-text="formatRp(total)"></span>
                </div>

                <a href="{{ route('customer.checkout', ['client' => $client]) }}" class="w-full min-h-[46px] py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-center text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                    <span>Lanjut ke Checkout</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>

                <div class="mt-4 p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl text-xs text-emerald-900 flex items-center gap-2 font-medium">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Stok dikunci otomatis saat Anda menyelesaikan checkout.</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.customer>

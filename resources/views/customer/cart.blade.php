@php
    $branchTitle = 'Cabang '.ucwords(str_replace('-', ' ', $client));
    $obatUrlPrefix = request()->is('klien/*')
        ? '/klien/'.rawurlencode($client).'/obat/'
        : '/obat/';
    $checkoutUrl = request()->is('klien/*')
        ? route('customer.checkout', ['client' => $client])
        : (\Illuminate\Support\Facades\Route::has('checkout.index') ? route('checkout.index') : route('customer.checkout', ['client' => $client]));
@endphp

<x-layouts.customer :title="'Keranjang Belanja - OmniPharma'" :fullWidth="true">
    <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8" x-data="{
        items: {{ \Illuminate\Support\Js::from($items) }},
        loading: false,
        obatUrlPrefix: {{ \Illuminate\Support\Js::from($obatUrlPrefix) }},
        get total() {
            return this.items.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
        },
        get totalQty() {
            return this.items.reduce((sum, item) => sum + (Number(item.jumlah) || 0), 0);
        },
        formatRp(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },
        syncNavbarCart(countOverride = null) {
            const count = typeof countOverride === 'number' ? countOverride : this.totalQty;
            window.dispatchEvent(new CustomEvent('cart-updated', {
                detail: { count: count, silent: true }
            }));
        },
        async updateQty(obatId, qty) {
            const item = this.items.find(i => i.id_obat === obatId);
            if (!item) return;

            if (qty <= 0) {
                return this.removeItem(obatId);
            }

            if (item.stok_tersedia && qty > Number(item.stok_tersedia)) {
                return;
            }

            const oldQty = item.jumlah;
            const oldSubtotal = item.subtotal;
            item.jumlah = qty;
            item.subtotal = Number(item.harga) * qty;

            try {
                const res = await fetch('{{ \Illuminate\Support\Facades\Route::has('keranjang.update') ? route('keranjang.update') : route('customer.cart.update', ['client' => $client]) }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_obat: obatId, jumlah: qty })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error('Update failed');
                this.syncNavbarCart(typeof data.cart_count === 'number' ? data.cart_count : this.totalQty);
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
                const res = await fetch('{{ \Illuminate\Support\Facades\Route::has('keranjang.remove') ? route('keranjang.remove') : route('customer.cart.remove', ['client' => $client]) }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_obat: obatId })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error('Remove failed');
                this.syncNavbarCart(typeof data.cart_count === 'number' ? data.cart_count : this.totalQty);
            } catch (e) {
                this.items.splice(index, 0, removed);
                console.warn('Gagal menghapus obat dari keranjang:', e);
            }
        }
    }">
        {{-- Breadcrumb & Active Branch Pill --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <nav class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition flex items-center gap-1 font-semibold text-slate-800 min-h-[44px] py-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <span class="text-slate-400">/</span>
                <span class="font-bold text-emerald-800">Keranjang Belanja</span>
            </nav>

            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-900 border border-emerald-200 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Gerai Aktif: {{ $branchTitle }}</span>
            </span>
        </div>

        {{-- 3-Step Order Progress Indicator --}}
        <div class="mb-6 bg-white border border-slate-200/90 rounded-2xl p-3 sm:p-4 shadow-2xs">
            <ol class="grid grid-cols-3 gap-2 text-[11px] sm:text-xs font-bold">
                <li class="flex items-center gap-2 text-emerald-900 bg-emerald-50 border border-emerald-200 rounded-xl px-2.5 py-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[11px] font-black shrink-0">1</span>
                    <span class="truncate">Keranjang Belanja</span>
                </li>
                <li class="flex items-center gap-2 text-slate-500 bg-slate-50 border border-slate-200/80 rounded-xl px-2.5 py-2">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[11px] font-black shrink-0">2</span>
                    <span class="truncate">Konfirmasi Checkout</span>
                </li>
                <li class="flex items-center gap-2 text-slate-500 bg-slate-50 border border-slate-200/80 rounded-xl px-2.5 py-2">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[11px] font-black shrink-0">3</span>
                    <span class="truncate">Pembayaran QRIS</span>
                </li>
            </ol>
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 flex flex-wrap items-center gap-2.5">
                <span>Keranjang Belanja</span>
                <span x-show="items.length > 0" class="text-xs sm:text-sm font-bold px-3 py-1 rounded-full bg-white text-slate-700 border border-slate-200 shadow-2xs">
                    <span x-text="totalQty"></span> Item (<span x-text="items.length"></span> Jenis)
                </span>
            </h1>

            <a
                x-show="items.length > 0"
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 min-h-[40px] px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 transition shadow-2xs"
            >
                <span>+ Tambah Obat Lain</span>
            </a>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm flex items-start sm:items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Empty Cart State --}}
        <div x-show="items.length === 0" x-cloak class="bg-white rounded-3xl p-8 sm:p-14 text-center border border-slate-200/90 shadow-xs">
            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-700 border border-emerald-200/60 shadow-2xs">
                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-2">Keranjang Belanja Masih Kosong</h3>
            <p class="text-slate-500 max-w-md mx-auto mb-6 text-xs sm:text-sm leading-relaxed">
                Pilih obat, suplemen, atau kebutuhan kesehatan dari katalog {{ $branchTitle }}.
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-xs w-full sm:w-auto cursor-pointer">
                Jelajahi Katalog Obat
            </a>
        </div>

        {{-- Populated Cart Layout --}}
        <div x-show="items.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-7 items-start">
            {{-- Item Cards List --}}
            <div class="lg:col-span-8 space-y-3 sm:space-y-4 min-w-0">
                <template x-for="item in items" :key="item.id_obat">
                    <article class="bg-white rounded-3xl p-3.5 sm:p-5 border border-slate-200/90 shadow-xs hover:border-emerald-200 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5 sm:gap-4 min-w-0 flex-1">
                            {{-- Product Cover Image --}}
                            <a
                                :href="obatUrlPrefix + encodeURIComponent(item.slug || item.id_obat)"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-50 border border-slate-200/80 p-2 shrink-0 flex items-center justify-center overflow-hidden group focus-visible:outline-2 focus-visible:outline-emerald-700"
                                :aria-label="'Lihat detail ' + item.nama_obat"
                            >
                                <img
                                    :src="item.url_gambar || '/images/placeholder-product.svg'"
                                    :alt="item.nama_obat"
                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='/images/placeholder-product.svg';"
                                />
                            </a>

                            {{-- Product Metadata --}}
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/70" x-text="item.kategori || 'Obat & Kesehatan'"></span>
                                    <span
                                        x-show="typeof item.stok_tersedia === 'number'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border"
                                        :class="item.stok_tersedia >= item.jumlah ? 'bg-slate-50 text-slate-600 border-slate-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                        x-text="'Stok cabang: ' + item.stok_tersedia"
                                    ></span>
                                </div>

                                <h4 class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug break-words">
                                    <a :href="obatUrlPrefix + encodeURIComponent(item.slug || item.id_obat)" class="hover:text-emerald-800 hover:underline" x-text="item.nama_obat"></a>
                                </h4>

                                <p class="text-xs text-slate-500 font-medium">
                                    <span x-text="formatRp(item.harga)"></span>
                                    <span> / </span>
                                    <span x-text="item.satuan || 'Unit'"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Controls & Subtotal --}}
                        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3 sm:gap-5 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0">
                            {{-- Quantity Selector --}}
                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50 shrink-0">
                                <button
                                    type="button"
                                    @click="updateQty(item.id_obat, item.jumlah - 1)"
                                    class="min-w-[40px] min-h-[40px] w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-200 active:bg-slate-300 transition cursor-pointer"
                                    aria-label="Kurangi kuantitas"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-10 text-center text-sm font-extrabold text-slate-900 select-none" x-text="item.jumlah"></span>
                                <button
                                    type="button"
                                    @click="updateQty(item.id_obat, item.jumlah + 1)"
                                    :disabled="item.stok_tersedia && item.jumlah >= item.stok_tersedia"
                                    class="min-w-[40px] min-h-[40px] w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-200 active:bg-slate-300 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="Tambah kuantitas"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            {{-- Subtotal & Remove --}}
                            <div class="text-right shrink-0 flex flex-col items-end min-w-[100px]">
                                <div class="text-[11px] text-slate-400 sm:hidden">Subtotal</div>
                                <div class="text-sm sm:text-base font-black text-slate-900 whitespace-nowrap" x-text="formatRp(item.subtotal)"></div>
                                <button
                                    type="button"
                                    @click="removeItem(item.id_obat)"
                                    class="mt-1 text-xs font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2 py-1 rounded-lg transition min-h-[34px] inline-flex items-center justify-end gap-1 cursor-pointer"
                                    aria-label="Hapus obat dari keranjang"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            {{-- Order Summary Sidebar --}}
            <aside class="lg:col-span-4 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs lg:sticky lg:top-24 min-w-0 space-y-5">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Ringkasan Belanja</h3>

                <div class="space-y-3 text-xs sm:text-sm border-b border-slate-100 pb-4">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Item</span>
                        <span class="font-bold text-slate-900" x-text="totalQty + ' item (' + items.length + ' jenis)'"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Obat</span>
                        <span class="font-bold text-slate-900" x-text="formatRp(total)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Layanan Apotek</span>
                        <span class="font-bold text-emerald-700">GRATIS</span>
                    </div>
                </div>

                <div class="flex justify-between items-baseline text-base font-black text-slate-900">
                    <span>Total Pembayaran</span>
                    <span class="text-lg sm:text-xl text-emerald-700" x-text="formatRp(total)"></span>
                </div>

                <div class="space-y-2.5">
                    <a
                        href="{{ $checkoutUrl }}"
                        class="w-full min-h-[46px] py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-center text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-xs cursor-pointer"
                    >
                        <span>Lanjut ke Checkout</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full min-h-[42px] py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-center text-xs transition flex items-center justify-center border border-slate-200"
                    >
                        + Tambah Obat Lain dari Katalog
                    </a>
                </div>

                <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl text-xs text-emerald-900 flex items-start gap-2 font-medium leading-relaxed">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Stok di cabang aktif dikunci otomatis saat Anda menekan tombol bayar pada halaman checkout.</span>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.customer>

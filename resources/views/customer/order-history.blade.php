<x-layouts.customer :title="'Riwayat Pesanan - OmniPharma'" :fullWidth="true">
    <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition">Katalog Apotek</a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold">Riwayat Pesanan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Riwayat Pesanan &amp; Invoice</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau status transaksi, akses halaman pembayaran QRIS, atau cetak invoice pesanan Anda.
                </p>
            </div>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold transition shadow-xs self-start sm:self-auto"
            >
                <span>Belanja Obat Lagi</span>
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/90 shadow-xs text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h2 class="text-lg font-extrabold text-slate-900">Belum Ada Riwayat Pesanan</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Anda belum memiliki transaksi pesanan pada akun atau sesi ini. Silakan pilih obat atau suplemen di katalog utama.
                </p>
                <a href="{{ route('dashboard') }}" class="mt-5 inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs sm:text-sm hover:bg-emerald-700 transition">
                    Buka Katalog Obat
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $cleanNo = ltrim((string) $order->nomor_pesanan, '#');
                        $tanggal = $order->dibuat_pada ? \Carbon\Carbon::parse($order->dibuat_pada)->format('d M Y, H:i') : '-';
                        $branchName = $order->client?->nama_client ?? 'Cabang Apotek';
                        $isUnpaid = $order->status === 'menunggu_pembayaran';
                        $payLink = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pembayaran.show')
                            ? route('customer.order.payment', ['client' => $client, 'id' => $order->id_pesanan])
                            : route('pembayaran.show', ['id' => $order->id_pesanan]);
                        $invoiceLink = request()->is('klien/*') || ! \Illuminate\Support\Facades\Route::has('pesanan.track')
                            ? route('customer.order.track', ['client' => $client, 'id' => $order->id_pesanan])
                            : route('pesanan.track', ['id' => $order->id_pesanan]);
                    @endphp
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-black text-slate-900">#{{ $cleanNo }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize border {{ $order->status === 'dibatalkan' ? 'bg-rose-50 text-rose-800 border-rose-200' : ($isUnpaid ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200') }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                                @if($order->nomor_antrean)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">Antrean {{ $order->nomor_antrean }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ $tanggal }} WIB &bull; <span class="font-semibold text-emerald-800">{{ $branchName }}</span> &bull; {{ $order->items->count() }} item
                            </p>
                            <div class="text-xs text-slate-600 truncate max-w-xl">
                                {{ $order->items->pluck('nama_obat')->take(3)->implode(', ') }}@if($order->items->count() > 3), ...@endif
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                            <div class="text-left sm:text-right mr-1">
                                <span class="block text-[10px] font-bold uppercase text-slate-400">Total Pembayaran</span>
                                <span class="text-base font-black text-emerald-700">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($isUnpaid)
                                    <a href="{{ $payLink }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-2xs">
                                        Bayar QRIS
                                    </a>
                                @endif
                                <a href="{{ $invoiceLink }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs">
                                    Lihat Invoice &amp; Lacak
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.customer>

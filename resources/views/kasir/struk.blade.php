<x-layouts.kasir :title="'Struk Pembayaran ' . ($pesanan->nomor_pesanan ?? '') . ' - OmniPharma'" :branch-name="'Kasir ' . ($client->nama_client ?? 'Cabang')">
    <div class="max-w-xl mx-auto space-y-5">
        <div class="flex items-center justify-between gap-3 print:hidden">
            <a href="{{ route('kasir.dashboard') }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                &larr; Kembali ke Kasir
            </a>
            <button type="button" onclick="window.print()" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs cursor-pointer transition">
                <span>Cetak Struk</span>
            </button>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <div class="text-center border-b border-dashed border-slate-300 pb-4">
                <h1 class="text-lg font-black text-slate-900">OmniPharma Network</h1>
                <p class="text-xs font-bold text-emerald-700 mt-0.5">{{ $client->nama_client ?? 'Cabang Apotek' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $client->alamat ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs border-b border-dashed border-slate-300 pb-4">
                <div>
                    <span class="text-slate-500 block">Nomor Pesanan</span>
                    <strong class="font-mono text-slate-900">{{ $pesanan->nomor_pesanan ?? '-' }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Waktu Bayar</span>
                    <strong class="text-slate-900">{{ $pembayaran->dibuat_pada ? $pembayaran->dibuat_pada->format('d/m/Y H:i') : '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Pasien / Pembeli</span>
                    <strong class="text-slate-900">{{ $pesanan->nama_pembeli ?? 'Pelanggan' }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Kasir / Verifikator</span>
                    <strong class="text-slate-900">{{ $pembayaran->verifikator->nama_lengkap ?? auth()->user()->name ?? 'Kasir Cabang' }}</strong>
                </div>
            </div>

            <div class="space-y-2 border-b border-dashed border-slate-300 pb-4">
                @foreach($pesanan->items ?? [] as $item)
                    <div class="flex items-start justify-between gap-2 text-xs">
                        <div>
                            <p class="font-bold text-slate-900">{{ $item->nama_obat }}</p>
                            <p class="text-[11px] text-slate-500">{{ $item->kuantitas }} x Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</p>
                        </div>
                        <span class="font-bold text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                @if(($pesanan->ongkos_kirim ?? 0) > 0)
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                        <span class="text-slate-600">Ongkos Kirim Kurir</span>
                        <span class="font-semibold text-slate-900">Rp {{ number_format((float) $pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-600">Metode Pembayaran</span>
                    <span class="font-extrabold uppercase text-slate-900">{{ $pembayaran->metode_pembayaran }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-600">Status</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold uppercase">{{ $pembayaran->status }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-base">
                    <span class="font-black text-slate-900">Total Dibayar</span>
                    <span class="font-black text-emerald-700">Rp {{ number_format((float) $pembayaran->jumlah, 0, ',', '.') }}</span>
                </div>
            </div>

            <p class="text-center text-[11px] text-slate-400 pt-2">
                Ref: {{ $pembayaran->id_pembayaran }}<br>
                Terima kasih atas kepercayaan Anda pada layanan OmniPharma.
            </p>
        </div>
    </div>
</x-layouts.kasir>

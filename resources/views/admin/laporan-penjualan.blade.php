@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header Dashboard --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analitik Penjualan & Rekonsiliasi</h1>
            <p class="text-sm text-gray-500">Pantau omzet cabang, rekonsiliasi kas, dan penutupan buku periode transaksi[cite: 1, 2].</p>
        </div>

        {{-- Form Filter Periode --}}
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
            <input type="date" name="periode_mulai" value="{{ $periodeMulai }}"
                   class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="text-gray-400">s/d</span>
            <input type="date" name="periode_selesai" value="{{ $periodeSelesai }}"
                   class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                Terapkan
            </button>
        </form>
    </div>

    {{-- Pesan Status / Notifikasi --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Kartu Ringkasan Metrik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pendapatan</span>
            <p class="text-2xl font-black text-gray-900 mt-2">Rp {{ number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.') }}</p>
            <span class="text-xs text-emerald-600 font-medium">Periode aktif terpilih</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Transaksi Lunas</span>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ $summary['total_pesanan'] ?? 0 }} Pesanan</p>
            <span class="text-xs text-gray-500 font-medium">Pesanan diproses & selesai</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-rata Keranjang (AOV)</span>
            <p class="text-2xl font-black text-gray-900 mt-2">Rp {{ number_format($summary['rata_rata_transaksi'] ?? 0, 0, ',', '.') }}</p>
            <span class="text-xs text-blue-600 font-medium">Nilai rata-rata per transaksi</span>
        </div>
    </div>

    {{-- Bar Aksi Rekonsiliasi & Ekspor --}}
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            @if(isset($ringkasanTerkunci) && $ringkasanTerkunci)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    STATUS: PERIODE TERKUNCI (IMMUTABLE)
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    STATUS: PERIODE BERJALAN (TERBUKA)
                </span>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Tombol Ekspor CSV --}}
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-lg text-sm font-semibold transition">
                Unduh Spreadsheet (CSV)
            </a>

            {{-- Tombol Aksi Penutupan Periode --}}
            @if(!isset($ringkasanTerkunci) || !$ringkasanTerkunci)
                <form method="POST" action="{{ route('tenant.laporan.tutup-periode', ['client' => $client]) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin mengunci periode buku ini? Data yang telah terkunci tidak dapat diubah lagi (ADR-0008).')">
                    @csrf
                    <input type="hidden" name="periode_mulai" value="{{ $periodeMulai }}">
                    <input type="hidden" name="periode_selesai" value="{{ $periodeSelesai }}">
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        Tutup Buku Periode Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Tabel Rincian Pesanan --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Rincian Transaksi Pesanan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-3">Nomor Pesanan</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Metode Layanan</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Total Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summary['daftar_pesanan'] as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $order->nomor_pesanan }}</td>
                            <td class="px-6 py-4">{{ $order->dibuat_pada ? $order->dibuat_pada->format('d M Y H:i') : '-' }}</td>
                            <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $order->metode_layanan ?? '-') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 uppercase">
                                    {{ $order->status ?? $order->status_pesanan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                Rp {{ number_format($order->total_harga ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                Tidak ada transaksi yang tercatat pada rentang periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
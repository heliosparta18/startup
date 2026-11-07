<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekonsiliasi Penjualan</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1f2937; margin: 24px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #111827; padding-bottom: 12px; }
        .header h1 { font-size: 18px; margin: 0 0 4px 0; color: #111827; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #4b5563; font-size: 11px; }
        .meta-table { width: 100%; margin-bottom: 16px; border: none; }
        .meta-table td { padding: 4px 0; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #d1d5db; padding: 7px 9px; }
        .table th { background-color: #f3f4f6; color: #111827; font-weight: bold; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 9px; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .footer-summary { margin-top: 20px; float: right; width: 40%; }
        .footer-summary table { width: 100%; border-collapse: collapse; }
        .footer-summary td { padding: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>OmniPharma Network</h1>
        <p>Laporan Rekonsiliasi Penjualan & Penutupan Kas Cabang Apotek</p>
        <p><strong>Periode Transaksi:</strong> {{ $periodeMulai }} s/d {{ $periodeSelesai }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Nomor Pesanan</th>
                <th>Waktu Transaksi</th>
                <th>Layanan</th>
                <th>Status</th>
                <th class="text-right">Total Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $item->nomor_pesanan }}</strong></td>
                <td>{{ $item->dibuat_pada ? $item->dibuat_pada->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $item->metode_layanan ?? 'Click & Collect')) }}</td>
                <td>
                    <span class="badge badge-success">{{ strtoupper($item->status ?? $item->status_pesanan) }}</span>
                </td>
                <td class="text-right">Rp {{ number_format($item->total_harga ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 16px; color: #6b7280;">Tidak ada catatan pesanan lunas pada rentang tanggal ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9fafb; font-weight: bold;">
                <td colspan="5" class="text-right">Total Akumulasi Pendapatan:</td>
                <td class="text-right">Rp {{ number_format($totalPendapatan ?? $totalOmzet ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 32px; font-size: 10px; color: #6b7280;">
        <p>Dokumen ini disahkan secara digital oleh Sistem Rekonsiliasi OmniPharma Network (ADR-0008: Snapshot Immutability)[cite: 1, 2]. Dicetak pada: {{ now()->format('d M Y H:i:s') }} WIB.</p>
    </div>
</body>
</html>
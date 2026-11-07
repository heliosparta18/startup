<?php

namespace App\Modules\Reporting\Services;

use App\Models\Pesanan;

class SalesReportService
{
    /**
     * Menghitung ringkasan pendapatan dan pesanan cabang.
     */
    public function getSummary(string $clientId, string $periodeMulai, string $periodeSelesai): array
    {
        $orders = Pesanan::withoutGlobalScopes()
            ->where('id_client', $clientId)
            ->where(function ($query) {
                $query->whereIn('status', ['diproses', 'siap_diambil', 'selesai'])
                      ->orWhereIn('status_pesanan', ['diproses', 'siap_diambil', 'selesai']);
            })
            ->whereBetween('dibuat_pada', [$periodeMulai . ' 00:00:00', $periodeSelesai . ' 23:59:59'])
            ->get();

        $totalPendapatan = (float) $orders->sum('total_harga');
        $totalPesanan = $orders->count();

        return [
            'total_pendapatan' => $totalPendapatan,
            'total_omzet' => $totalPendapatan,
            'total_pesanan' => $totalPesanan,
            'total_transaksi' => $totalPesanan,
            'rata_rata_transaksi' => $totalPesanan > 0 ? (float) ($totalPendapatan / $totalPesanan) : 0,
            'daftar_pesanan' => $orders,
        ];
    }
}
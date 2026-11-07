<?php

namespace App\Modules\Reporting\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Menghitung ringkasan pendapatan dan pesanan cabang langsung dari tabel item_pesanan.
     */
    public function getSummary(string $clientId, string $periodeMulai, string $periodeSelesai): array
    {
        $orders = Pesanan::withoutGlobalScopes()
            ->where('id_client', $clientId)
            ->whereIn('status', ['diproses', 'siap_diambil', 'selesai'])
            ->whereBetween('dibuat_pada', [$periodeMulai . ' 00:00:00', $periodeSelesai . ' 23:59:59'])
            ->get();

        $orderIds = $orders->pluck('id_pesanan');

        // Ambil data subtotal dan kuantitas dari tabel item_pesanan
        $itemsByOrder = DB::table('item_pesanan')
            ->whereIn('id_pesanan', $orderIds)
            ->get()
            ->groupBy('id_pesanan');

        $totalPendapatan = 0;
        $totalObatTerjual = 0;

        foreach ($orders as $order) {
            $orderItems = $itemsByOrder->get($order->id_pesanan, collect());
            $subtotal = (float) $orderItems->sum('subtotal');
            $kuantitas = (int) $orderItems->sum('kuantitas');

            // Tetapkan total_harga agar blade view dan cetak PDF tetap berfungsi normal
            $order->setAttribute('total_harga', $subtotal);

            $totalPendapatan += $subtotal;
            $totalObatTerjual += $kuantitas;
        }

        $totalPesanan = $orders->count();

        return [
            'total_pendapatan' => (float) $totalPendapatan,
            'total_omzet' => (float) $totalPendapatan,
            'total_pesanan' => $totalPesanan,
            'total_transaksi' => $totalPesanan,
            'total_obat_terjual' => $totalObatTerjual,
            'rata_rata_transaksi' => $totalPesanan > 0 ? (float) ($totalPendapatan / $totalPesanan) : 0,
            'daftar_pesanan' => $orders,
        ];
    }
}
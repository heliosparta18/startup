<?php

namespace App\Modules\Reporting\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportExportService
{
    /**
     * Menghasilkan file CSV ter-stream aman dari serangan Formula Injection.
     */
    public function exportCsv(string $clientId, string $mulai, string $selesai): StreamedResponse
    {
        $fileName = "laporan-penjualan-{$clientId}-{$mulai}-{$selesai}.csv";

        return new StreamedResponse(function () use ($clientId, $mulai, $selesai) {
            $handle = fopen('php://output', 'w');

            if (! is_resource($handle)) {
                throw new \RuntimeException('Gagal membuka stream output CSV.');
            }

            fputcsv($handle, ['Nomor Pesanan', 'Tanggal', 'Metode Layanan', 'Status', 'Total Harga (Rp)']);

            $orders = Pesanan::withoutGlobalScopes()
                ->where('id_client', $clientId)
                ->whereBetween('dibuat_pada', [$mulai.' 00:00:00', $selesai.' 23:59:59'])
                ->get();

            $orderIds = $orders->pluck('id_pesanan');

            $subtotals = DB::table('item_pesanan')
                ->whereIn('id_pesanan', $orderIds)
                ->groupBy('id_pesanan')
                ->selectRaw('id_pesanan, SUM(subtotal) as total')
                ->pluck('total', 'id_pesanan');

            foreach ($orders as $order) {
                $nomorPesanan = $this->sanitizeForCsv((string) $order->nomor_pesanan);
                $tanggal = $order->dibuat_pada ? $order->dibuat_pada->format('Y-m-d H:i') : '-';
                $metode = (string) ($order->metode_layanan ?? '-');
                $status = (string) ($order->status ?? '-');
                $total = (float) ($subtotals->get($order->id_pesanan) ?? 0);

                fputcsv($handle, [
                    $nomorPesanan,
                    $tanggal,
                    $metode,
                    $status,
                    $total,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Sanitasi karakter berbahaya pemicu eksekusi formula Excel / CSV Injection.
     */
    public function sanitizeForCsv(string $value): string
    {
        if (in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
            return "'".$value;
        }

        return $value;
    }
}

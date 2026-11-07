<?php

namespace App\Modules\Reporting\Actions;

use App\Models\Client;
use App\Models\Pesanan;
use App\Models\RingkasanPeriodePenjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CloseSalesPeriodAction
{
    public function execute(string $clientId, string $periodeMulai, string $periodeSelesai): RingkasanPeriodePenjualan
    {
        return DB::transaction(function () use ($clientId, $periodeMulai, $periodeSelesai) {
            $existing = RingkasanPeriodePenjualan::where('id_client', $clientId)
                ->where('periode_mulai', $periodeMulai)
                ->where('periode_selesai', $periodeSelesai)
                ->first();

            if ($existing && $existing->status_terkunci) {
                throw new RuntimeException('Periode penjualan ini sudah pernah ditutup dan berstatus terkunci.');
            }

            $client = Client::withoutGlobalScopes()->where('id_client', $clientId)->first();
            $namaClient = $client ? $client->nama_client : 'Cabang Apotek';

            $orders = Pesanan::withoutGlobalScopes()
                ->where('id_client', $clientId)
                ->whereIn('status', ['diproses', 'siap_diambil', 'selesai'])
                ->whereBetween('dibuat_pada', [$periodeMulai . ' 00:00:00', $periodeSelesai . ' 23:59:59'])
                ->get();

            $orderIds = $orders->pluck('id_pesanan');

            $items = DB::table('item_pesanan')
                ->whereIn('id_pesanan', $orderIds)
                ->selectRaw('COALESCE(SUM(subtotal), 0) as total_pendapatan, COALESCE(SUM(kuantitas), 0) as total_obat_terjual')
                ->first();

            $totalPendapatan = (float) ($items->total_pendapatan ?? 0);
            $totalObatTerjual = (int) ($items->total_obat_terjual ?? 0);
            $totalPesanan = $orders->count();

            return RingkasanPeriodePenjualan::updateOrCreate(
                [
                    'id_client' => $clientId,
                    'periode_mulai' => $periodeMulai,
                    'periode_selesai' => $periodeSelesai,
                ],
                [
                    'id_ringkasan' => (string) Str::uuid(),
                    'nama_client' => $namaClient,
                    'total_obat_terjual' => $totalObatTerjual,
                    'total_pesanan' => $totalPesanan,
                    'total_pendapatan' => $totalPendapatan,
                    'total_retur' => 0,
                    'status_terkunci' => true,
                    'dibuat_pada' => now(),
                ]
            );
        });
    }
}
<?php

namespace App\Modules\Ordering\Actions;

use App\Models\Pesanan;
use App\Models\StokClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelOrderAction
{
    public function execute(Pesanan $pesanan, string $alasan = 'Dibatalkan oleh sistem / kedaluwarsa'): void
    {
        DB::transaction(function () use ($pesanan) {
            /** @var Pesanan|null $lockedPesanan */
            $lockedPesanan = Pesanan::withoutTenancy()
                ->where('id_pesanan', $pesanan->id_pesanan)
                ->lockForUpdate()
                ->first();

            if (! $lockedPesanan) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }

            // Hanya batalkan jika status masih menunggu pembayaran
            if ($lockedPesanan->status !== 'menunggu_pembayaran') {
                return;
            }

            // Kembalikan stok fisik obat ke cabang secara atomik
            foreach ($lockedPesanan->items as $item) {
                StokClient::withoutTenancy()
                    ->where('id_client', $lockedPesanan->id_client)
                    ->where('id_obat', $item->id_obat)
                    ->increment('jumlah_stok', $item->kuantitas);
            }

            $lockedPesanan->update([
                'status' => 'dibatalkan',
            ]);
        });
    }
}

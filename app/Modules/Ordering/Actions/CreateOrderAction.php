<?php

namespace App\Modules\Ordering\Actions;

use App\Models\ItemPesanan;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CreateOrderAction
{
    /**
     * @param  array<int, array{id_obat: string, nama_obat: string, harga: float|int, jumlah: int}>  $items
     */
    public function execute(string $clientId, ?string $userId, array $items, string $metodeLayanan = 'click_collect', ?string $sessionToken = null): Pesanan
    {
        if (empty($items)) {
            throw new RuntimeException('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($clientId, $userId, $items, $metodeLayanan, $sessionToken) {
            $resolvedUserId = $userId;
            if (! $resolvedUserId) {
                $guestUser = Pengguna::where('peran', 'pelanggan')->first();
                if (! $guestUser) {
                    $guestUser = Pengguna::create([
                        'id_pengguna' => (string) Str::uuid(),
                        'nama_lengkap' => 'Pelanggan Tamu',
                        'peran' => 'pelanggan',
                        'telepon_email' => 'tamu-'.Str::random(8).'@omnipharma.test',
                        'aktif' => true,
                    ]);
                }
                $resolvedUserId = $guestUser->id_pengguna;
            }

            // 1. Validasi & Lock Stok Secara Pesimistik (Pessimistic Locking)
            $stokRecords = [];
            foreach ($items as $item) {
                $stok = StokClient::withoutTenancy()
                    ->where('id_client', $clientId)
                    ->where('id_obat', $item['id_obat'])
                    ->lockForUpdate()
                    ->first();

                if (! $stok || $stok->jumlah_stok < $item['jumlah']) {
                    throw new RuntimeException("Stok obat {$item['nama_obat']} tidak mencukupi di cabang ini.");
                }

                $stokRecords[] = [$stok, $item['jumlah']];
            }

            // 2. Terbitkan Nomor Pesanan Standar (#ORD-YYYYMMDD-XXXX)
            $orderNumber = '#ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $pesanan = Pesanan::withoutTenancy()->create([
                'id_pesanan' => (string) Str::uuid(),
                'id_pengguna' => $resolvedUserId,
                'id_client' => $clientId,
                'nomor_pesanan' => $orderNumber,
                'status' => 'menunggu_pembayaran',
                'metode_layanan' => $metodeLayanan,
                'token_sesi' => $sessionToken,
            ]);

            // 3. Simpan Item dengan Snapshot Harga (ADR-0008) & Kurangi Stok
            foreach ($items as $item) {
                $harga = (float) $item['harga'];
                $jumlah = (int) $item['jumlah'];

                ItemPesanan::create([
                    'id_item' => (string) Str::uuid(),
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_obat' => $item['id_obat'],
                    'kuantitas' => $jumlah,
                    'nama_obat' => $item['nama_obat'],
                    'harga_satuan' => $harga,
                    'subtotal' => $harga * $jumlah,
                    'dibuat_pada' => now(),
                ]);
            }

            foreach ($stokRecords as [$stokRecord, $qtyToDeduct]) {
                $stokRecord->decrement('jumlah_stok', $qtyToDeduct);
            }

            return $pesanan;
        });
    }
}

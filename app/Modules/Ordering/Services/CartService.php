<?php

namespace App\Modules\Ordering\Services;

use App\Models\Obat;
use App\Models\StokClient;
use Illuminate\Support\Facades\Redis;

class CartService
{
    public function getCartKey(string $sessionId, string $clientId): string
    {
        return "cart:{$sessionId}:{$clientId}";
    }

    public function addItem(string $sessionId, string $clientId, string $obatId, int $qty = 1): void
    {
        if ($qty <= 0) {
            return;
        }

        $key = $this->getCartKey($sessionId, $clientId);
        Redis::hincrby($key, $obatId, $qty);
        Redis::expire($key, 7 * 24 * 3600); // TTL 7 hari
    }

    public function updateItem(string $sessionId, string $clientId, string $obatId, int $qty): void
    {
        $key = $this->getCartKey($sessionId, $clientId);

        if ($qty <= 0) {
            Redis::hdel($key, $obatId);

            return;
        }

        Redis::hset($key, $obatId, $qty);
        Redis::expire($key, 7 * 24 * 3600);
    }

    public function removeItem(string $sessionId, string $clientId, string $obatId): void
    {
        $key = $this->getCartKey($sessionId, $clientId);
        Redis::hdel($key, $obatId);
    }

    /**
     * @return array<int, array{id_obat: string, nama_obat: string, harga: float, jumlah: int, subtotal: float, stok_tersedia: int, sedia: bool}>
     */
    public function getItems(string $sessionId, string $clientId): array
    {
        $key = $this->getCartKey($sessionId, $clientId);
        /** @var array<string, int|string> $rawItems */
        $rawItems = Redis::hgetall($key);

        if (empty($rawItems)) {
            return [];
        }

        $obatIds = array_keys($rawItems);
        $obats = Obat::with('kategori')->whereIn('id_obat', $obatIds)->get()->keyBy('id_obat');
        $stoks = StokClient::withoutTenancy()
            ->where('id_client', $clientId)
            ->whereIn('id_obat', $obatIds)
            ->get()
            ->keyBy('id_obat');

        $result = [];
        foreach ($rawItems as $obatId => $qty) {
            $qtyInt = (int) $qty;
            if ($qtyInt <= 0 || ! isset($obats[$obatId])) {
                continue;
            }

            $obat = $obats[$obatId];
            $harga = (float) $obat->harga;
            $stokTersedia = isset($stoks[$obatId]) ? (int) $stoks[$obatId]->jumlah_stok : 0;

            $result[] = [
                'id_obat' => (string) $obatId,
                'nama_obat' => (string) $obat->nama_obat,
                'harga' => $harga,
                'jumlah' => $qtyInt,
                'subtotal' => $harga * $qtyInt,
                'stok_tersedia' => $stokTersedia,
                'sedia' => $stokTersedia >= $qtyInt,
                'url_gambar' => (string) ($obat->url_gambar ?: asset('images/placeholder-product.svg')),
                'slug' => (string) ($obat->slug ?: $obat->id_obat),
                'kategori' => (string) ($obat->kategori->nama_kategori ?? 'Obat & Kesehatan'),
                'satuan' => (string) ($obat->satuan_kemasan ?: ($obat->jenis_sediaan ?: 'Unit')),
                'badge_status' => (string) ($obat->badge_status ?? ''),
            ];
        }

        return $result;
    }

    public function getTotal(string $sessionId, string $clientId): float
    {
        $items = $this->getItems($sessionId, $clientId);

        return (float) array_sum(array_column($items, 'subtotal'));
    }

    public function clear(string $sessionId, string $clientId): void
    {
        Redis::del($this->getCartKey($sessionId, $clientId));
    }

    public function getCount(string $sessionId, string $clientId): int
    {
        try {
            $key = $this->getCartKey($sessionId, $clientId);
            /** @var array<string, int|string> $rawItems */
            $rawItems = Redis::hgetall($key);
            if (empty($rawItems)) {
                return 0;
            }

            $count = 0;
            foreach ($rawItems as $qty) {
                $qtyInt = (int) $qty;
                if ($qtyInt > 0) {
                    $count += $qtyInt;
                }
            }

            return $count;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

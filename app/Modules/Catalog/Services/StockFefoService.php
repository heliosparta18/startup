<?php

namespace App\Modules\Catalog\Services;

use App\Models\Client;
use App\Models\StokClient;
use Illuminate\Database\Eloquent\Collection;

class StockFefoService
{
    /**
     * @return Collection<int, StokClient>
     */
    public function getBatchesByFefo(string $clientId, ?string $obatId = null): Collection
    {
        $resolvedClient = Client::findByIdentifier($clientId);
        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        $query = StokClient::query()
            ->with('obat')
            ->select('stok_client.*')
            ->join('obat', 'stok_client.id_obat', '=', 'obat.id_obat')
            ->where('stok_client.id_client', $actualClientId)
            ->where('stok_client.jumlah_stok', '>', 0);

        if ($obatId) {
            $query->where('stok_client.id_obat', $obatId);
        }

        return $query->orderBy('obat.expired', 'asc') // FEFO Sort
            ->get()
            ->each(function (StokClient $batch): void {
                $expiredDate = $batch->obat->expired ?? $batch->tanggal_kadaluarsa;
                if ($expiredDate) {
                    $sisaHari = (int) now()->diffInDays($expiredDate, false);
                    $batch->sisa_hari_ed = $sisaHari;
                    $batch->is_near_ed = $sisaHari <= 30; // Indikator H-30
                    $batch->is_critical_ed = $sisaHari <= 7; // Indikator H-7
                }
            });
    }

    /**
     * @return Collection<int, StokClient>
     */
    public function getNearExpiryBatches(string $clientId, int $daysThreshold = 30): Collection
    {
        return $this->getBatchesByFefo($clientId)->filter(function (StokClient $batch) use ($daysThreshold) {
            return $batch->sisa_hari_ed !== null && $batch->sisa_hari_ed <= $daysThreshold;
        })->values();
    }
}

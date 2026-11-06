<?php

namespace App\Modules\Catalog\Services;

use App\Models\StokClient;
use Illuminate\Database\Eloquent\Collection;

class StockFefoService
{
    /**
     * @return Collection<int, StokClient>
     */
    public function getBatchesByFefo(string $clientId, string $obatId): Collection
    {
        return StokClient::where('id_client', $clientId)
            ->where('id_obat', $obatId)
            ->where('jumlah_stok', '>', 0)
            ->orderBy('tanggal_kadaluarsa', 'asc') // FEFO Sort
            ->get()
            ->each(function (StokClient $batch): void {
                $sisaHari = now()->diffInDays($batch->tanggal_kadaluarsa, false);
                $batch->sisa_hari_ed = (int) $sisaHari;
                $batch->is_near_ed = $sisaHari <= 30; // Indikator H-30
                $batch->is_critical_ed = $sisaHari <= 7; // Indikator H-7
            });
    }
}

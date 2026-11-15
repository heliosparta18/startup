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
    public function getBatchesByFefo(string $clientId, ?string $obatId = null, bool $includeEmpty = false, ?int $limit = null): Collection
    {
        $resolvedClient = Client::findByIdentifier($clientId);
        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        $query = StokClient::query()
            ->with('obat')
            ->select('stok_client.*')
            ->join('obat', 'stok_client.id_obat', '=', 'obat.id_obat')
            ->where('stok_client.id_client', $actualClientId);

        if (! $includeEmpty) {
            $query->where('stok_client.jumlah_stok', '>', 0);
        }

        if ($obatId) {
            $query->where('stok_client.id_obat', $obatId);
        }

        $query->orderBy('obat.expired', 'asc'); // FEFO Sort

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        return $query->get()
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
     * Ringkasan agregat batch kedaluwarsa di level SQL tanpa hidrasi ribuan model Eloquent.
     *
     * @return array{total_batches: int, critical_count: int, near_count: int, low_stock_or_expiring_count: int}
     */
    public function getExpirySummary(string $clientId): array
    {
        $resolvedClient = Client::findByIdentifier($clientId);
        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        $criticalBoundary = now()->addDays(7)->endOfDay()->toDateTimeString();
        $nearBoundary = now()->addDays(30)->endOfDay()->toDateTimeString();

        $summary = StokClient::query()
            ->withoutGlobalScopes()
            ->join('obat', 'stok_client.id_obat', '=', 'obat.id_obat')
            ->where('stok_client.id_client', $actualClientId)
            ->where('stok_client.jumlah_stok', '>', 0)
            ->selectRaw(
                'COUNT(*) as total_batches, '
                .'COALESCE(SUM(CASE WHEN obat.expired IS NOT NULL AND obat.expired <= ? THEN 1 ELSE 0 END), 0) as critical_count, '
                .'COALESCE(SUM(CASE WHEN obat.expired IS NOT NULL AND obat.expired <= ? THEN 1 ELSE 0 END), 0) as near_count, '
                .'COALESCE(SUM(CASE WHEN (obat.expired IS NOT NULL AND obat.expired <= ?) OR stok_client.jumlah_stok <= 10 THEN 1 ELSE 0 END), 0) as low_stock_or_expiring_count',
                [$criticalBoundary, $nearBoundary, $nearBoundary]
            )
            ->first();

        return [
            'total_batches' => (int) ($summary->total_batches ?? 0),
            'critical_count' => (int) ($summary->critical_count ?? 0),
            'near_count' => (int) ($summary->near_count ?? 0),
            'low_stock_or_expiring_count' => (int) ($summary->low_stock_or_expiring_count ?? 0),
        ];
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

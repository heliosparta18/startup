<?php

namespace App\Modules\Catalog\Services;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    /**
     * @return LengthAwarePaginator<int, Obat>
     */
    public function getCatalog(string $clientId, ?string $search = null, ?string $bentukSediaan = null): LengthAwarePaginator
    {
        return Obat::query()
            ->with(['stokClient' => function ($q) use ($clientId) {
                $q->where('id_client', $clientId);
            }])
            ->whereHas('stokClient', function (Builder $q) use ($clientId) {
                $q->where('id_client', $clientId)->where('jumlah_stok', '>', 0);
            })
            ->when($search, function (Builder $q, $search) {
                $q->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('komposisi', 'like', "%{$search}%");
            })
            ->when($bentukSediaan, function (Builder $q, $sediaan) {
                $q->where('bentuk_sediaan', $sediaan);
            })
            ->orderBy('nama_obat', 'asc')
            ->paginate(12);
    }
}

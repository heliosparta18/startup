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
    public function getCatalog(string $clientId, ?string $search = null, ?string $bentukSediaan = null, ?string $kategori = null): LengthAwarePaginator
    {
        return Obat::query()
            ->with(['stokClient' => function ($q) use ($clientId) {
                $q->where('id_client', $clientId);
            }])
            ->whereHas('stokClient', function (Builder $q) use ($clientId) {
                $q->where('id_client', $clientId)->where('jumlah_stok', '>', 0);
            })
            ->when($search, function (Builder $q, $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('nama_obat', 'like', "%{$search}%")
                        ->orWhere('merek', 'like', "%{$search}%");
                });
            })
            ->when($bentukSediaan, function (Builder $q, $sediaan) {
                $q->where('jenis_sediaan', $sediaan);
            })
            ->when($kategori, function (Builder $q, $kat) {
                $q->where(function (Builder $sub) use ($kat) {
                    $sub->where('id_kategori', $kat)
                        ->orWhereHas('kategori', function (Builder $kq) use ($kat) {
                            $kq->where('nama_kategori', $kat);
                        });
                });
            })
            ->orderBy('nama_obat', 'asc')
            ->paginate(12);
    }

    /**
     * @return array<int, string>
     */
    public function getDaftarSediaan(): array
    {
        return Obat::query()
            ->distinct()
            ->whereNotNull('jenis_sediaan')
            ->pluck('jenis_sediaan')
            ->toArray();
    }
}

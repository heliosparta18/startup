<?php

namespace App\Modules\Catalog\Services;

use App\Models\Client;
use App\Models\Obat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    /**
     * @return LengthAwarePaginator<int, Obat>
     */
    public function getCatalog(string $clientId, ?string $search = null, ?string $jenisSediaan = null, ?string $kategoriId = null): LengthAwarePaginator
    {
        $resolvedClient = Client::findByIdentifier($clientId);
        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        return Obat::query()
            ->with(['kategori', 'stokClient' => function ($q) use ($actualClientId) {
                $q->where('id_client', $actualClientId);
            }])
            ->whereHas('stokClient', function (Builder $q) use ($actualClientId) {
                $q->where('id_client', $actualClientId)->where('jumlah_stok', '>', 0);
            })
            ->when($search, function (Builder $q, $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('nama_obat', 'like', "%{$search}%")
                        ->orWhere('merek', 'like', "%{$search}%");
                });
            })
            ->when($jenisSediaan, function (Builder $q, $sediaan) {
                $q->where('jenis_sediaan', $sediaan);
            })
            ->when($kategoriId, function (Builder $q, $cat) {
                $q->where('id_kategori', $cat);
            })
            ->orderBy('nama_obat', 'asc')
            ->paginate(12);
    }

    /**
     * @return array<int, string>
     */
    public function getDaftarSediaan(): array
    {
        return ['tablet', 'sirup', 'kapsul', 'salep', 'injeksi'];
    }
}

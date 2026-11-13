<?php

namespace App\Modules\Catalog\Services;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\SubkategoriObat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    /**
     * Mengambil daftar produk katalog berpaginasi untuk cabang klien tertentu.
     *
     * @return LengthAwarePaginator<int, Obat>
     */
    public function getCatalog(
        string $clientId,
        ?string $search = null,
        ?string $bentukSediaan = null,
        ?string $kategori = null,
        ?string $subKategori = null,
        int $perPage = 15,
        bool $unified = false
    ): LengthAwarePaginator {
        $resolvedClient = Client::where('id_client', $clientId)
            ->orWhere('slug', $clientId)
            ->orWhere('nama_client', $clientId)
            ->first();

        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        return Obat::query()
            ->with([
                'stokClient' => function ($q) {
                    $q->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->with('client');
                },
                'kategori',
                'subkategori',
            ])
            ->when(! $unified, function (Builder $q) use ($actualClientId) {
                $q->whereHas('stokClient', function (Builder $sq) use ($actualClientId) {
                    $sq->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                        ->where('id_client', $actualClientId)
                        ->where('jumlah_stok', '>', 0);
                });
            }, function (Builder $q) {
                $q->whereHas('stokClient', function (Builder $sq) {
                    $sq->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                        ->where('jumlah_stok', '>', 0);
                });
            })
            ->when($search, function (Builder $q, $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('nama_obat', 'like', "%{$search}%")
                        ->orWhere('merek', 'like', "%{$search}%")
                        ->orWhere('komposisi', 'like', "%{$search}%")
                        ->orWhere('indikasi', 'like', "%{$search}%");
                });
            })
            ->when($bentukSediaan, function (Builder $q, $sediaan) {
                $q->where('jenis_sediaan', $sediaan);
            })
            ->when($kategori, function (Builder $q, $kat) {
                if (strtolower($kat) === 'obat') {
                    $q->where(function (Builder $sub) {
                        $sub->whereHas('kategori', function (Builder $kq) {
                            $kq->where('nama_kategori', 'like', '%Obat%')
                                ->orWhere('slug', 'obat')
                                ->orWhereIn('nama_kategori', ['Obat', 'Wajib Resep (Keras)', 'Obat Bebas', 'Bebas Terbatas']);
                        });
                    });
                } else {
                    $q->where(function (Builder $sub) use ($kat) {
                        $sub->where('id_kategori', $kat)
                            ->orWhereHas('kategori', function (Builder $kq) use ($kat) {
                                $kq->where('nama_kategori', $kat)
                                    ->orWhere('slug', $kat);
                            });
                    });
                }
            })
            ->when($subKategori, function (Builder $q, $subKat) {
                $q->where(function (Builder $sub) use ($subKat) {
                    $sub->where('id_subkategori', $subKat)
                        ->orWhereHas('subkategori', function (Builder $sq) use ($subKat) {
                            $sq->where('nama_subkategori', $subKat)
                                ->orWhere('slug', $subKat);
                        });
                });
            })
            ->orderBy('nama_obat', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mendapatkan daftar kategori utama katalog obat & kesehatan.
     *
     * @return Collection<int, KategoriObat>
     */
    public function getDaftarKategori(): Collection
    {
        return KategoriObat::query()
            ->whereIn('nama_kategori', [
                'Obat',
                'Suplemen',
                'Nutrisi',
                'Herbal',
                'Ibu & Anak',
                'Alat Kesehatan',
                'Perawatan Diri',
            ])
            ->orWhereNotNull('slug')
            ->orderByRaw("CASE 
                WHEN nama_kategori = 'Obat' THEN 1 
                WHEN nama_kategori = 'Suplemen' THEN 2 
                WHEN nama_kategori = 'Nutrisi' THEN 3 
                WHEN nama_kategori = 'Herbal' THEN 4 
                WHEN nama_kategori = 'Ibu & Anak' THEN 5 
                WHEN nama_kategori = 'Alat Kesehatan' THEN 6 
                WHEN nama_kategori = 'Perawatan Diri' THEN 7 
                ELSE 8 END")
            ->orderBy('nama_kategori')
            ->get();
    }

    /**
     * Mendapatkan daftar sub-kategori berdasarkan kategori induk aktif.
     *
     * @return Collection<int, SubkategoriObat>
     */
    public function getDaftarSubkategori(?string $kategori = null, ?string $clientId = null): Collection
    {
        $resolvedClientId = null;
        if ($clientId) {
            $client = Client::where('id_client', $clientId)
                ->orWhere('slug', $clientId)
                ->orWhere('nama_client', $clientId)
                ->first();
            $resolvedClientId = $client ? $client->id_client : $clientId;
        }

        $query = SubkategoriObat::query()->with('kategori');

        if ($resolvedClientId) {
            $query->withCount(['obat' => function (Builder $oq) {
                $oq->whereHas('stokClient', function (Builder $sq) {
                    $sq->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('jumlah_stok', '>', 0);
                });
            }]);
        }

        if ($kategori && strtolower($kategori) !== 'semua') {
            $query->whereHas('kategori', function (Builder $q) use ($kategori) {
                if (strtolower($kategori) === 'obat') {
                    $q->where('nama_kategori', 'like', '%Obat%')
                        ->orWhere('slug', 'obat');
                } else {
                    $q->where('nama_kategori', $kategori)
                        ->orWhere('slug', $kategori)
                        ->orWhere('id_kategori', $kategori);
                }
            });
        }

        return $query->orderBy('urutan')->orderBy('nama_subkategori')->get();
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

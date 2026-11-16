<?php

namespace App\Modules\Catalog\Services;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Scopes\TenantScope;
use App\Models\SubkategoriObat;
use Database\Seeders\GoApotikFullCatalogSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CatalogService
{
    private static bool $catalogSyncedThisProcess = false;

    /**
     * Sinkronisasi lazy satu kali per versi file scraped_goapotik_lengkap.json
     * agar server yang auto-update hanya via git pull tetap mendapat ribuan produk terbaru.
     */
    public function ensureCatalogSynchronized(): void
    {
        if (self::$catalogSyncedThisProcess || app()->runningUnitTests() || app()->environment('testing')) {
            return;
        }

        $jsonPath = database_path('data/scraped_goapotik_lengkap.json');
        if (! is_file($jsonPath)) {
            self::$catalogSyncedThisProcess = true;

            return;
        }

        $mtime = @filemtime($jsonPath) ?: 0;
        $size = @filesize($jsonPath) ?: 0;
        $cacheDir = storage_path('framework/cache');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $markerPath = "{$cacheDir}/goapotik_catalog_{$mtime}_{$size}.synced";

        if (is_file($markerPath)) {
            self::$catalogSyncedThisProcess = true;

            return;
        }

        $lockPath = "{$cacheDir}/goapotik_catalog.lock";
        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle === false) {
            return;
        }

        try {
            if (! @flock($lockHandle, LOCK_EX | LOCK_NB)) {
                return;
            }

            if (is_file($markerPath)) {
                self::$catalogSyncedThisProcess = true;

                return;
            }

            if (! Schema::hasTable('obat') || ! Schema::hasTable('subkategori_obat')) {
                return;
            }

            $alreadySynced = Obat::where('slug', 'postinor-0-75-mg-2-tablet')->exists()
                && Obat::count() >= 2400;

            if (! $alreadySynced) {
                Artisan::call('db:seed', [
                    '--class' => GoApotikFullCatalogSeeder::class,
                    '--force' => true,
                ]);
                $this->clearMetadataCache();
            }

            @file_put_contents($markerPath, now()->toIso8601String());
            self::$catalogSyncedThisProcess = true;
        } catch (Throwable $e) {
            Log::warning('Sinkronisasi otomatis katalog GoApotik gagal sementara.', [
                'error' => $e->getMessage(),
            ]);
        } finally {
            @flock($lockHandle, LOCK_UN);
            @fclose($lockHandle);
        }
    }

    /**
     * Versi cache metadata katalog agar invalidasi saat CRUD admin tetap O(1).
     */
    private function metaCacheVersion(): int
    {
        try {
            $cached = Cache::get('catalog:meta_version');
            if (is_int($cached) || is_numeric($cached)) {
                return max(1, (int) $cached);
            }

            Cache::forever('catalog:meta_version', 1);

            return 1;
        } catch (Throwable) {
            return 1;
        }
    }

    public function clearMetadataCache(): void
    {
        try {
            $next = $this->metaCacheVersion() + 1;
            Cache::forever('catalog:meta_version', $next);
        } catch (Throwable) {
            // Biarkan katalog tetap berjalan tanpa gangguan meski cache store sementara offline.
        }
    }

    /**
     * Bungkus pembacaan cache metadata agar gangguan Redis tidak menyebabkan HTTP 500.
     *
     * @template TCacheValue
     *
     * @param  \Closure(): TCacheValue  $resolver
     * @return TCacheValue
     */
    private function rememberMetadata(string $key, \Closure $resolver): mixed
    {
        try {
            return Cache::remember($key, now()->addMinutes(5), $resolver);
        } catch (Throwable) {
            return $resolver();
        }
    }

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
        $this->ensureCatalogSynchronized();

        $resolvedClient = Client::where('id_client', $clientId)
            ->orWhere('slug', $clientId)
            ->orWhere('nama_client', $clientId)
            ->first();

        $actualClientId = $resolvedClient ? $resolvedClient->id_client : $clientId;

        return Obat::query()
            ->with([
                'stokClient' => function ($q) {
                    $q->withoutGlobalScope(TenantScope::class)->with('client');
                },
                'kategori',
                'subkategori',
            ])
            ->when(! $unified, function (Builder $q) use ($actualClientId) {
                $q->whereHas('stokClient', function (Builder $sq) use ($actualClientId) {
                    $sq->withoutGlobalScope(TenantScope::class)
                        ->where('id_client', $actualClientId)
                        ->where('jumlah_stok', '>', 0);
                });
            }, function (Builder $q) {
                $q->whereHas('stokClient', function (Builder $sq) {
                    $sq->withoutGlobalScope(TenantScope::class)
                        ->where('jumlah_stok', '>', 0);
                });
            })
            ->when($search, function (Builder $q, $search) {
                $trimmed = trim((string) $search);
                $tokens = array_values(array_filter(preg_split('/\s+/', $trimmed) ?: []));

                $q->where(function (Builder $outer) use ($trimmed, $tokens): void {
                    $outer->where('nama_obat', 'like', "%{$trimmed}%")
                        ->orWhere('merek', 'like', "%{$trimmed}%")
                        ->orWhere('komposisi', 'like', "%{$trimmed}%")
                        ->orWhere('indikasi', 'like', "%{$trimmed}%");

                    if (count($tokens) > 1) {
                        $outer->orWhere(function (Builder $tokenGroup) use ($tokens): void {
                            foreach ($tokens as $token) {
                                $tokenGroup->where(function (Builder $sub) use ($token): void {
                                    $sub->where('nama_obat', 'like', "%{$token}%")
                                        ->orWhere('merek', 'like', "%{$token}%")
                                        ->orWhere('komposisi', 'like', "%{$token}%")
                                        ->orWhere('indikasi', 'like', "%{$token}%");
                                });
                            }
                        });
                    }
                });
            })
            ->when($bentukSediaan, function (Builder $q, string $sediaan): void {
                $keywords = match ($sediaan) {
                    'Tetes (Drops)' => ['Tetes (Drops)', 'tetes', 'drop'],
                    'Salep/Krim' => ['Salep/Krim', 'salep', 'krim', 'cream', 'gel', 'ointment'],
                    'Serbuk/Sachet' => ['Serbuk/Sachet', 'serbuk', 'sachet', 'powder'],
                    'Larutan/Cairan' => ['Larutan/Cairan', 'larutan', 'cairan', 'solution'],
                    'Alat Medis' => ['Alat Medis', 'alat'],
                    'Plester/Strip' => ['Plester/Strip', 'plester', 'patch'],
                    default => [$sediaan],
                };

                $q->where(function (Builder $sq) use ($sediaan, $keywords): void {
                    $sq->where('jenis_sediaan', $sediaan);
                    foreach ($keywords as $kw) {
                        $sq->orWhere('jenis_sediaan', 'like', "%{$kw}%");
                    }
                });
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
     * Disimpan sebagai array primitif di cache karena config/cache.php menetapkan
     * serializable_classes => false (objek model ter-unserialize jadi __PHP_Incomplete_Class).
     *
     * @return Collection<int, KategoriObat>
     */
    public function getDaftarKategori(): Collection
    {
        $this->ensureCatalogSynchronized();

        $ver = $this->metaCacheVersion();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->rememberMetadata("catalog:v{$ver}:kategori:arr", function (): array {
            return KategoriObat::query()
                ->withCount(['obat' => function (Builder $oq): void {
                    $oq->whereHas('stokClient', function (Builder $sq): void {
                        $sq->withoutGlobalScope(TenantScope::class)->where('jumlah_stok', '>', 0);
                    });
                }])
                ->where(function (Builder $q): void {
                    $q->whereIn('nama_kategori', [
                        'Obat',
                        'Suplemen',
                        'Nutrisi',
                        'Herbal',
                        'Ibu & Anak',
                        'Alat Kesehatan',
                        'Perawatan Diri',
                        'Mata',
                    ])->orWhereNotNull('slug');
                })
                ->orderByRaw("CASE 
                    WHEN nama_kategori = 'Obat' THEN 1 
                    WHEN nama_kategori = 'Suplemen' THEN 2 
                    WHEN nama_kategori = 'Nutrisi' THEN 3 
                    WHEN nama_kategori = 'Herbal' THEN 4 
                    WHEN nama_kategori = 'Ibu & Anak' THEN 5 
                    WHEN nama_kategori = 'Alat Kesehatan' THEN 6 
                    WHEN nama_kategori = 'Perawatan Diri' THEN 7 
                    WHEN nama_kategori = 'Mata' THEN 8 
                    ELSE 9 END")
                ->orderBy('nama_kategori')
                ->get()
                ->map(static fn (KategoriObat $kat): array => $kat->getAttributes())
                ->all();
        });

        return KategoriObat::hydrate($rows);
    }

    /**
     * Mendapatkan daftar sub-kategori berdasarkan kategori induk aktif.
     *
     * @return Collection<int, SubkategoriObat>
     */
    public function getDaftarSubkategori(?string $kategori = null, ?string $clientId = null): Collection
    {
        $ver = $this->metaCacheVersion();
        $katKey = md5((string) ($kategori ?? 'all').'|'.(string) ($clientId ?? 'global'));

        /** @var array<int, array{attributes: array<string, mixed>, kategori: array<string, mixed>|null}> $rows */
        $rows = $this->rememberMetadata("catalog:v{$ver}:subkategori:{$katKey}:arr", function () use ($kategori, $clientId): array {
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
                        $sq->withoutGlobalScope(TenantScope::class)->where('jumlah_stok', '>', 0);
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

            $subkategoris = $query->orderBy('urutan')->orderBy('nama_subkategori')->get();

            $canonicalOrder = [
                'Darah' => 1,
                'Hormon' => 2,
                'Hormon & Kontrasepsi' => 3,
                'Kepala' => 4,
                'Kulit' => 5,
                'Liver/Hati, Pankreas & Empedu' => 6,
                'Otot, Sendi & Tulang' => 7,
                'Rambut' => 8,
                'Rongga Mulut & Gigi' => 9,
                'Obat Saluran Pencernaan' => 10,
                'Saluran Kemih, Ginjal & Prostat' => 11,
                'Telinga, Hidung & Tenggorokan' => 12,
                'Tubuh' => 13,
                'Obat Lainnya' => 14,
            ];

            return $subkategoris->sortBy(function (SubkategoriObat $sub) use ($canonicalOrder): array {
                $prio = $canonicalOrder[$sub->nama_subkategori] ?? 100;

                return [$prio, (int) ($sub->urutan ?? 0), strtolower($sub->nama_subkategori)];
            })
                ->values()
                ->map(static fn (SubkategoriObat $sub): array => [
                    'attributes' => $sub->getAttributes(),
                    'kategori' => $sub->kategori instanceof KategoriObat ? $sub->kategori->getAttributes() : null,
                ])
                ->all();
        });

        $hydrated = SubkategoriObat::hydrate(array_column($rows, 'attributes'));
        foreach ($hydrated as $index => $sub) {
            $katAttrs = $rows[$index]['kategori'] ?? null;
            $sub->setRelation(
                'kategori',
                is_array($katAttrs) ? (new KategoriObat)->newFromBuilder($katAttrs) : null
            );
        }

        return $hydrated;
    }

    /**
     * @return array<int, string>
     */
    public function getDaftarSediaan(): array
    {
        $ver = $this->metaCacheVersion();

        return $this->rememberMetadata("catalog:v{$ver}:sediaan", function (): array {
            $rawList = Obat::query()
                ->distinct()
                ->whereNotNull('jenis_sediaan')
                ->pluck('jenis_sediaan')
                ->toArray();

            $standardMap = [
                'tablet' => 'Tablet',
                'kaplet' => 'Kaplet',
                'kapsul' => 'Kapsul',
                'sirup' => 'Sirup',
                'suspensi' => 'Suspensi',
                'tetes' => 'Tetes (Drops)',
                'drop' => 'Tetes (Drops)',
                'salep' => 'Salep/Krim',
                'krim' => 'Salep/Krim',
                'serbuk' => 'Serbuk/Sachet',
                'sachet' => 'Serbuk/Sachet',
                'larutan' => 'Larutan/Cairan',
                'cairan' => 'Larutan/Cairan',
                'alat' => 'Alat Medis',
                'plester' => 'Plester/Strip',
            ];

            $cleaned = [];
            foreach ($rawList as $s) {
                $matched = false;
                foreach ($standardMap as $key => $std) {
                    if (str_contains(strtolower($s), $key)) {
                        $cleaned[$std] = true;
                        $matched = true;
                        break;
                    }
                }
                if (! $matched && strlen($s) <= 20) {
                    $cleaned[ucwords(strtolower($s))] = true;
                }
            }

            $order = ['Tablet', 'Kaplet', 'Kapsul', 'Sirup', 'Suspensi', 'Tetes (Drops)', 'Salep/Krim', 'Serbuk/Sachet', 'Larutan/Cairan', 'Alat Medis', 'Plester/Strip'];
            $sorted = [];
            foreach ($order as $item) {
                if (isset($cleaned[$item])) {
                    $sorted[] = $item;
                    unset($cleaned[$item]);
                }
            }

            return array_merge($sorted, array_keys($cleaned));
        });
    }
}

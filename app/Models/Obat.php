<?php

namespace App\Models;

use Database\Factories\ObatFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    /** @use HasFactory<ObatFactory> */
    use HasFactory, HasUuids;

    protected $table = 'obat';

    protected $primaryKey = 'id_obat';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_obat',
        'nama_obat',
        'slug',
        'merek',
        'jenis_sediaan',
        'satuan_kemasan',
        'badge_status',
        'gambar_url',
        'nomor_ijin_edar',
        'indikasi',
        'deskripsi',
        'dosis',
        'komposisi',
        'aturan_pakai',
        'jumlah_obat',
        'id_kategori',
        'id_subkategori',
        'harga',
        'expired',
        'batch_number',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'expired' => 'date',
            'jumlah_obat' => 'integer',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function getUrlGambarAttribute(): string
    {
        return $this->gambar_url ?: asset('images/placeholder-product.svg');
    }

    /**
     * @return BelongsTo<KategoriObat, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, 'id_kategori', 'id_kategori');
    }

    /**
     * @return BelongsTo<SubkategoriObat, $this>
     */
    public function subkategori(): BelongsTo
    {
        return $this->belongsTo(SubkategoriObat::class, 'id_subkategori', 'id_subkategori');
    }

    /**
     * @return HasMany<StokClient, $this>
     */
    public function stokClient(): HasMany
    {
        return $this->hasMany(StokClient::class, 'id_obat', 'id_obat');
    }

    /**
     * Relasi multi-cabang tanpa tenant scope untuk ketersediaan multi-cabang.
     *
     * @return HasMany<StokClient, $this>
     */
    public function allStokClient(): HasMany
    {
        return $this->hasMany(StokClient::class, 'id_obat', 'id_obat')
            ->withoutGlobalScope(\App\Models\Scopes\TenantScope::class);
    }

    /**
     * Mendapatkan daftar cabang yang memiliki stok aktif (>0) untuk obat ini.
     *
     * @return \Illuminate\Support\Collection<int, Client>
     */
    public function getAvailableBranches(): \Illuminate\Support\Collection
    {
        $stoks = $this->relationLoaded('allStokClient')
            ? $this->allStokClient
            : ($this->relationLoaded('stokClient')
                ? $this->stokClient
                : $this->allStokClient()->with('client')->get());

        return $stoks
            ->filter(fn ($stok) => (int) $stok->jumlah_stok > 0 && ($stok->client?->aktif ?? true))
            ->map(fn ($stok) => $stok->client)
            ->filter()
            ->unique('id_client')
            ->values();
    }

    /**
     * Indikator teks ketersediaan multi-cabang (Antislop R-02 Compliant, zero em dash).
     */
    public function getBranchAvailabilityTextAttribute(): string
    {
        $available = $this->getAvailableBranches();
        if ($available->isEmpty()) {
            return 'Stok Habis di Semua Cabang';
        }

        $names = $available->map(function ($client) {
            return preg_replace('/^Cabang\s+/i', '', $client->nama_client);
        })->values();

        return 'Tersedia di: ' . $names->implode(' & ');
    }

    /**
     * Mengecek apakah obat tersedia (stok > 0) di cabang tertentu (berdasarkan ID atau slug).
     */
    public function isAvailableInBranch(string $branchIdentifier): bool
    {
        $stoks = $this->relationLoaded('allStokClient')
            ? $this->allStokClient
            : ($this->relationLoaded('stokClient')
                ? $this->stokClient
                : $this->allStokClient()->with('client')->get());

        return $stoks->contains(function ($stok) use ($branchIdentifier) {
            $matches = ($stok->id_client === $branchIdentifier)
                || ($stok->client?->slug === $branchIdentifier)
                || ($stok->client?->id_client === $branchIdentifier);

            return $matches && (int) $stok->jumlah_stok > 0;
        });
    }

    /**
     * Mengambil cabang alternatif yang memiliki stok aktif ketika cabang saat ini kosong.
     */
    public function getAlternativeAvailableBranch(string $currentBranchIdentifier): ?Client
    {
        $stoks = $this->relationLoaded('allStokClient')
            ? $this->allStokClient
            : ($this->relationLoaded('stokClient')
                ? $this->stokClient
                : $this->allStokClient()->with('client')->get());

        $altStok = $stoks->first(function ($stok) use ($currentBranchIdentifier) {
            $isCurrent = ($stok->id_client === $currentBranchIdentifier)
                || ($stok->client?->slug === $currentBranchIdentifier)
                || ($stok->client?->id_client === $currentBranchIdentifier);

            return ! $isCurrent && (int) $stok->jumlah_stok > 0 && ($stok->client?->aktif ?? true);
        });

        return $altStok?->client;
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'id_obat', 'id_obat');
    }
}

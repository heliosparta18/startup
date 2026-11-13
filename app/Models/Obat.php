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
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'id_obat', 'id_obat');
    }
}

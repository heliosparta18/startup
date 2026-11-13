<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubkategoriObat extends Model
{
    use HasUuids;

    protected $table = 'subkategori_obat';

    protected $primaryKey = 'id_subkategori';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = null;

    protected $fillable = [
        'id_subkategori',
        'id_kategori',
        'nama_subkategori',
        'slug',
        'icon_url',
        'urutan',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<KategoriObat, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, 'id_kategori', 'id_kategori');
    }

    /**
     * @return HasMany<Obat, $this>
     */
    public function obat(): HasMany
    {
        return $this->hasMany(Obat::class, 'id_subkategori', 'id_subkategori');
    }
}

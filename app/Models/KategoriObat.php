<?php

namespace App\Models;

use Database\Factories\KategoriObatFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriObat extends Model
{
    /** @use HasFactory<KategoriObatFactory> */
    use HasFactory, HasUuids;

    protected $table = 'kategori_obat';

    protected $primaryKey = 'id_kategori';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'nama_kategori',
        'wajib_resep',
        'deskripsi',
        'id_client',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'wajib_resep' => 'boolean',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Obat, $this>
     */
    public function obat(): HasMany
    {
        return $this->hasMany(Obat::class, 'id_kategori', 'id_kategori');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }
}

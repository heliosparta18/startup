<?php

namespace App\Models;

use Database\Factories\StokClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokClient extends Model
{
    /** @use HasFactory<StokClientFactory> */
    use HasFactory, HasUuids;

    protected $table = 'stok_client';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_obat',
        'id_client',
        'jumlah_stok',
        'diperbarui_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_stok' => 'integer',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Obat, $this>
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'id_obat', 'id_obat');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }
}

<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasUuids;

    protected $table = 'client';

    protected $primaryKey = 'id_client';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_client',
        'nama_client',
        'alamat',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * @return HasMany<StokClient, $this>
     */
    public function stok(): HasMany
    {
        return $this->hasMany(StokClient::class, 'id_client', 'id_client');
    }

    /**
     * @return HasMany<Pesanan, $this>
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_client', 'id_client');
    }

    /**
     * @return HasMany<RingkasanPeriodePenjualan, $this>
     */
    public function ringkasanPeriode(): HasMany
    {
        return $this->hasMany(RingkasanPeriodePenjualan::class, 'id_client', 'id_client');
    }

    /**
     * @return HasMany<AdminClient, $this>
     */
    public function adminClient(): HasMany
    {
        return $this->hasMany(AdminClient::class, 'id_client', 'id_client');
    }
}

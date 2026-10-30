<?php

namespace App\Models;

use Database\Factories\PesananFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    /** @use HasFactory<PesananFactory> */
    use HasFactory, HasUuids;

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pesanan',
        'id_pengguna',
        'id_client',
        'nomor_pesanan',
        'status',
        'metode_layanan',
    ];

    protected function casts(): array
    {
        return [
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }

    /**
     * @return HasOne<Resep, $this>
     */
    public function resep(): HasOne
    {
        return $this->hasOne(Resep::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasOne<Pembayaran, $this>
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasMany<PermintaanRetur, $this>
     */
    public function retur(): HasMany
    {
        return $this->hasMany(PermintaanRetur::class, 'id_pesanan', 'id_pesanan');
    }
}

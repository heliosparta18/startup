<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Database\Factories\PesananFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id_pesanan
 * @property string $id_pengguna
 * @property string $id_client
 * @property string $nomor_pesanan
 * @property string $status
 * @property string $status_pesanan
 * @property string|null $token_sesi
 * @property string $metode_layanan
 * @property Carbon|null $dibuat_pada
 * @property Carbon|null $diperbarui_pada
 */
class Pesanan extends Model
{
    /** @use HasFactory<PesananFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

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
        'token_sesi',
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

    public function getTotalHargaAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    public function isLunas(): bool
    {
        return $this->pembayaran()->where('status', 'lunas')->exists()
            || $this->status === 'diproses'
            || $this->status === 'selesai';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeMenungguPembayaran($query)
    {
        return $query->where('status', 'menunggu_pembayaran');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeDiproses($query)
    {
        return $query->where('status', 'diproses');
    }
}

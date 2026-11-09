<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'nama_klien',
        'slug',
        'alamat',
        'aktif',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if (empty($client->nama_client) && ! empty($client->getAttribute('nama_klien'))) {
                $client->nama_client = $client->getAttribute('nama_klien');
            }

            if (empty($client->slug) && ! empty($client->nama_client)) {
                $client->slug = Str::slug(str_replace('Cabang ', '', $client->nama_client));
            }

            if (empty($client->alamat)) {
                $client->alamat = '-';
            }
        });
    }

    public function isAktif(): bool
    {
        return (bool) $this->aktif;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id_client', $value)
            ->orWhere('slug', $value)
            ->orWhere('nama_client', $value)
            ->first();
    }

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

    public static function findByIdentifier(string $identifier): ?self
    {
        $client = static::where('id_client', $identifier)->first();
        if ($client) {
            return $client;
        }

        $clean = str_replace(['-', '_'], ' ', strtolower($identifier));

        return static::whereRaw('LOWER(nama_client) LIKE ?', ["%{$clean}%"])->first();
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

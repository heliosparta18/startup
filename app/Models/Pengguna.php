<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'pengguna';

    protected $primaryKey = 'id_pengguna';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna',
        'nama_lengkap',
        'peran',
        'telepon_email',
        'password',
        'terdaftar_pada',
        'aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'terdaftar_pada' => 'date',
            'password' => 'hashed',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function hasRole(string $role): bool
    {
        return ($this->peran === $role
            || ($role === 'tenant' && $this->peran === 'apoteker')
            || ($role === 'admin' && $this->peran === 'super_admin')
            || ($role === 'customer' && $this->peran === 'pelanggan')
            || ($role === 'apoteker' && $this->peran === 'tenant')
            || ($role === 'super_admin' && $this->peran === 'admin')
            || ($role === 'pelanggan' && $this->peran === 'customer'))
            && $this->isActive();
    }

    public function isActive(): bool
    {
        return (bool) $this->aktif;
    }

    /**
     * @return HasOne<SuperAdmin, $this>
     */
    public function superAdmin(): HasOne
    {
        return $this->hasOne(SuperAdmin::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * @return HasOne<AdminClient, $this>
     */
    public function adminClient(): HasOne
    {
        return $this->hasOne(AdminClient::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * @return HasMany<Pesanan, $this>
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * @return HasMany<PeranClientPengguna, $this>
     */
    public function peranClient(): HasMany
    {
        return $this->hasMany(PeranClientPengguna::class, 'id_pengguna', 'id_pengguna');
    }
}

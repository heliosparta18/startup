<?php

namespace App\Models;

use Database\Factories\KonsultasiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id_konsultasi
 * @property string $id_client
 * @property string|null $id_pengguna
 * @property string|null $token_sesi
 * @property string $nama_pasien
 * @property string $status
 * @property string|null $pesan_terakhir
 * @property Carbon|null $pesan_terakhir_pada
 * @property Carbon|null $dibuat_pada
 * @property Carbon|null $diperbarui_pada
 * @property-read Client $client
 * @property-read Pengguna|null $pengguna
 * @property-read Collection<int, PesanKonsultasi> $pesan
 */
class Konsultasi extends Model
{
    /** @use HasFactory<KonsultasiFactory> */
    use HasFactory, HasUuids;

    protected $table = 'konsultasi';

    protected $primaryKey = 'id_konsultasi';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_konsultasi',
        'id_client',
        'id_pengguna',
        'token_sesi',
        'nama_pasien',
        'status',
        'pesan_terakhir',
        'pesan_terakhir_pada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pesan_terakhir_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function pesan(): HasMany
    {
        return $this->hasMany(PesanKonsultasi::class, 'id_konsultasi', 'id_konsultasi')
            ->orderBy('dibuat_pada', 'asc');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeForClient(Builder $query, string $clientId): Builder
    {
        return $query->where('id_client', $clientId);
    }
}

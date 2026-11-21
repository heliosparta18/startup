<?php

namespace App\Models;

use Database\Factories\PesanKonsultasiFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id_pesan
 * @property string $id_konsultasi
 * @property string|null $id_pengguna
 * @property string $pengirim_tipe
 * @property string $pesan
 * @property string|null $lampiran_url
 * @property string|null $lampiran_tipe
 * @property Carbon|null $dibaca_pada
 * @property Carbon|null $dibuat_pada
 * @property-read Konsultasi $konsultasi
 * @property-read Pengguna|null $pengguna
 */
class PesanKonsultasi extends Model
{
    /** @use HasFactory<PesanKonsultasiFactory> */
    use HasFactory, HasUuids;

    protected $table = 'pesan_konsultasi';

    protected $primaryKey = 'id_pesan';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = null;

    protected $fillable = [
        'id_pesan',
        'id_konsultasi',
        'id_pengguna',
        'pengirim_tipe',
        'pesan',
        'lampiran_url',
        'lampiran_tipe',
        'dibaca_pada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dibaca_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Konsultasi, $this>
     */
    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'id_konsultasi', 'id_konsultasi');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}

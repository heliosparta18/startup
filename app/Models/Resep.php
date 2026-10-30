<?php

namespace App\Models;

use Database\Factories\ResepFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resep extends Model
{
    /** @use HasFactory<ResepFactory> */
    use HasFactory, HasUuids;

    protected $table = 'resep';

    protected $primaryKey = 'id_resep';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_resep',
        'id_pesanan',
        'url_gambar',
        'status_verifikasi',
        'nama_dokter',
        'sip_dokter',
        'nama_pasien',
        'sip_valid',
        'dosis_valid',
        'catatan_apoteker',
        'diverifikasi_oleh',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'sip_valid' => 'boolean',
            'dosis_valid' => 'boolean',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh', 'id_pengguna');
    }
}

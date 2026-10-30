<?php

namespace App\Models;

use Database\Factories\PermintaanReturFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanRetur extends Model
{
    /** @use HasFactory<PermintaanReturFactory> */
    use HasFactory, HasUuids;

    protected $table = 'permintaan_retur';

    protected $primaryKey = 'id_retur';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_retur',
        'id_pesanan',
        'alasan',
        'jumlah_diajukan',
        'jumlah_disetujui',
        'status',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_diajukan' => 'decimal:2',
            'jumlah_disetujui' => 'decimal:2',
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
}

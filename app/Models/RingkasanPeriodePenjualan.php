<?php

namespace App\Models;

use Database\Factories\RingkasanPeriodePenjualanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingkasanPeriodePenjualan extends Model
{
    /** @use HasFactory<RingkasanPeriodePenjualanFactory> */
    use HasFactory, HasUuids;

    protected $table = 'ringkasan_periode_penjualan';

    protected $primaryKey = 'id_ringkasan';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_ringkasan',
        'id_client',
        'nama_client',
        'total_obat_terjual',
        'periode_mulai',
        'periode_selesai',
        'total_pesanan',
        'total_pendapatan',
        'total_retur',
        'status_terkunci',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'total_pesanan' => 'integer',
            'total_pendapatan' => 'decimal:2',
            'total_retur' => 'integer',
            'status_terkunci' => 'boolean',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }
}

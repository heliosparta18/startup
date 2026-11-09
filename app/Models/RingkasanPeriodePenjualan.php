<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Database\Factories\RingkasanPeriodePenjualanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id_ringkasan
 * @property string $id_client
 * @property string $nama_client
 * @property string|int $total_obat_terjual
 * @property string $periode_mulai
 * @property string $periode_selesai
 * @property int $total_pesanan
 * @property float $total_pendapatan
 * @property int $total_retur
 * @property bool $status_terkunci
 */
class RingkasanPeriodePenjualan extends Model
{
    /** @use HasFactory<RingkasanPeriodePenjualanFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

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
            'total_obat_terjual' => 'integer',
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

    /**
     * Scope untuk memfilter laporan periode yang berstatus terkunci permanen.
     */
    /**
     * @param  Builder<RingkasanPeriodePenjualan>  $query
     * @return Builder<RingkasanPeriodePenjualan>
     */
    public function scopeTerkunci(Builder $query): Builder
    {
        return $query->where('status_terkunci', true);
    }
}

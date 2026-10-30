<?php

namespace App\Models;

use Database\Factories\ItemPesananFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPesanan extends Model
{
    /** @use HasFactory<ItemPesananFactory> */
    use HasFactory, HasUuids;

    protected $table = 'item_pesanan';

    protected $primaryKey = 'id_item';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_item',
        'id_pesanan',
        'id_obat',
        'kuantitas',
        'nama_obat',
        'harga_satuan',
        'subtotal',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'kuantitas' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
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
     * @return BelongsTo<Obat, $this>
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'id_obat', 'id_obat');
    }
}

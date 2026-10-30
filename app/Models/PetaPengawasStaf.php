<?php

namespace App\Models;

use Database\Factories\PetaPengawasStafFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetaPengawasStaf extends Model
{
    /** @use HasFactory<PetaPengawasStafFactory> */
    use HasFactory, HasUuids;

    protected $table = 'peta_pengawas_staf';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_staf',
        'id_pengawas',
        'id_client',
        'berlaku_dari',
        'berlaku_sampai',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'berlaku_dari' => 'date',
            'berlaku_sampai' => 'date',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function staf(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_staf', 'id_pengguna');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengawas(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengawas', 'id_pengguna');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }
}

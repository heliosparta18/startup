<?php

namespace App\Models;

use Database\Factories\PeranClientPenggunaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeranClientPengguna extends Model
{
    /** @use HasFactory<PeranClientPenggunaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'peran_client_pengguna';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_pengguna',
        'id_client',
        'peran',
        'ditugaskan_dari',
        'ditugaskan_sampai',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'ditugaskan_dari' => 'date',
            'ditugaskan_sampai' => 'date',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }
}

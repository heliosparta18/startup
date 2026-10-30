<?php

namespace App\Models;

use Database\Factories\LogAuditFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAudit extends Model
{
    /** @use HasFactory<LogAuditFactory> */
    use HasFactory, HasUuids;

    protected $table = 'log_audit';

    protected $primaryKey = 'id_audit';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_audit',
        'id_aktor',
        'aksi',
        'nama_entitas',
        'id_entitas',
        'payload',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_aktor', 'id_pengguna');
    }
}

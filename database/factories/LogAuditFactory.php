<?php

namespace Database\Factories;

use App\Models\LogAudit;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LogAudit>
 */
class LogAuditFactory extends Factory
{
    protected $model = LogAudit::class;

    public function definition(): array
    {
        return [
            'id_audit' => (string) Str::uuid(),
            'id_aktor' => Pengguna::factory(),
            'aksi' => 'VERIFIKASI_RESEP',
            'nama_entitas' => 'resep',
            'id_entitas' => (string) Str::uuid(),
        ];
    }
}

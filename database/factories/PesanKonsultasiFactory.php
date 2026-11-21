<?php

namespace Database\Factories;

use App\Models\Konsultasi;
use App\Models\PesanKonsultasi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PesanKonsultasi>
 */
class PesanKonsultasiFactory extends Factory
{
    protected $model = PesanKonsultasi::class;

    public function definition(): array
    {
        return [
            'id_pesan' => (string) Str::uuid(),
            'id_konsultasi' => Konsultasi::factory(),
            'id_pengguna' => null,
            'pengirim_tipe' => 'pasien',
            'pesan' => 'Halo, saya butuh bantuan konsultasi obat',
            'lampiran_url' => null,
            'lampiran_tipe' => null,
            'dibaca_pada' => null,
            'dibuat_pada' => now(),
        ];
    }
}

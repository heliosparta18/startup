<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Konsultasi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Konsultasi>
 */
class KonsultasiFactory extends Factory
{
    protected $model = Konsultasi::class;

    public function definition(): array
    {
        return [
            'id_konsultasi' => (string) Str::uuid(),
            'id_client' => Client::factory(),
            'id_pengguna' => null,
            'token_sesi' => Str::random(32),
            'nama_pasien' => fake()->name(),
            'status' => 'aktif',
            'pesan_terakhir' => 'Halo, saya butuh bantuan konsultasi obat',
            'pesan_terakhir_pada' => now(),
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ];
    }
}

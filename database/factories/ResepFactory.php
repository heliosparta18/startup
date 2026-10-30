<?php

namespace Database\Factories;

use App\Models\Pesanan;
use App\Models\Resep;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Resep>
 */
class ResepFactory extends Factory
{
    protected $model = Resep::class;

    public function definition(): array
    {
        return [
            'id_resep' => (string) Str::uuid(),
            'id_pesanan' => Pesanan::factory(),
            'url_gambar' => '/images/resep-sample.jpg',
            'status_verifikasi' => 'menunggu',
            'nama_dokter' => 'dr. '.fake()->name().', Sp.PD',
            'sip_dokter' => fake()->numerify('###/SIP/2024'),
            'nama_pasien' => fake()->name(),
            'sip_valid' => false,
            'dosis_valid' => false,
            'catatan_apoteker' => null,
            'dibuat_pada' => now(),
        ];
    }
}

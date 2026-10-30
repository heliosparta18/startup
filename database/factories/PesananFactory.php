<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    protected $model = Pesanan::class;

    public function definition(): array
    {
        return [
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => Pengguna::factory(),
            'id_client' => Client::factory(),
            'nomor_pesanan' => 'ORD-'.date('Ymd').'-'.fake()->unique()->numerify('####'),
            'status' => 'menunggu_verifikasi',
            'metode_layanan' => 'click_collect',
        ];
    }
}

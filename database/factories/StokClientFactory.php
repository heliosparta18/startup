<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Obat;
use App\Models\StokClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StokClient>
 */
class StokClientFactory extends Factory
{
    protected $model = StokClient::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_obat' => Obat::factory(),
            'id_client' => Client::factory(),
            'jumlah_stok' => fake()->numberBetween(0, 100),
            'diperbarui_pada' => now(),
        ];
    }
}

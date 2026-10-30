<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'id_client' => (string) Str::uuid(),
            'nama_client' => 'Cabang '.fake()->city(),
            'alamat' => fake()->address(),
            'aktif' => true,
        ];
    }
}

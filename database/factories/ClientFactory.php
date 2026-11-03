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
        $nama = 'Cabang '.fake()->city();

        return [
            'id_client' => (string) Str::uuid(),
            'nama_client' => $nama,
            'slug' => Str::slug($nama),
            'alamat' => fake()->address(),
            'aktif' => true,
        ];
    }
}

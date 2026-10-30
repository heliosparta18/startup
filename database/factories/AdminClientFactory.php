<?php

namespace Database\Factories;

use App\Models\AdminClient;
use App\Models\Client;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AdminClient>
 */
class AdminClientFactory extends Factory
{
    protected $model = AdminClient::class;

    public function definition(): array
    {
        return [
            'id_admin' => (string) Str::uuid(),
            'id_pengguna' => Pengguna::factory(),
            'id_client' => Client::factory(),
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}

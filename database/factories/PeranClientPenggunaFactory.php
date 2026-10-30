<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\PeranClientPengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PeranClientPengguna>
 */
class PeranClientPenggunaFactory extends Factory
{
    protected $model = PeranClientPengguna::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_pengguna' => Pengguna::factory(),
            'id_client' => Client::factory(),
            'peran' => 'staf_klien',
            'ditugaskan_dari' => now()->toDateString(),
            'ditugaskan_sampai' => null,
        ];
    }
}

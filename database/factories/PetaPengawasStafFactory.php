<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\PetaPengawasStaf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PetaPengawasStaf>
 */
class PetaPengawasStafFactory extends Factory
{
    protected $model = PetaPengawasStaf::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_staf' => Pengguna::factory(),
            'id_pengawas' => Pengguna::factory(),
            'id_client' => Client::factory(),
            'berlaku_dari' => now()->toDateString(),
            'berlaku_sampai' => null,
        ];
    }
}

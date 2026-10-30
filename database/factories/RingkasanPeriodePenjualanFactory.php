<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\RingkasanPeriodePenjualan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RingkasanPeriodePenjualan>
 */
class RingkasanPeriodePenjualanFactory extends Factory
{
    protected $model = RingkasanPeriodePenjualan::class;

    public function definition(): array
    {
        /** @var Client $client */
        $client = Client::factory()->create();

        return [
            'id_ringkasan' => (string) Str::uuid(),
            'id_client' => (string) $client->id_client,
            'nama_client' => (string) $client->nama_client,
            'total_obat_terjual' => fake()->numberBetween(100, 1000).' item',
            'periode_mulai' => now()->startOfMonth()->toDateString(),
            'periode_selesai' => now()->endOfMonth()->toDateString(),
            'total_pesanan' => fake()->numberBetween(50, 500),
            'total_pendapatan' => fake()->numberBetween(5000000, 50000000),
            'total_retur' => fake()->numberBetween(0, 5),
            'status_terkunci' => false,
            'dibuat_pada' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    protected $model = Pembayaran::class;

    public function definition(): array
    {
        return [
            'id_pembayaran' => (string) Str::uuid(),
            'id_pesanan' => Pesanan::factory(),
            'metode_pembayaran' => fake()->randomElement(['transfer', 'e-wallet', 'cod']),
            'jumlah' => fake()->numberBetween(10000, 200000),
            'url_lampiran' => null,
            'status' => 'menunggu',
            'diverifikasi_oleh' => null,
            'dibuat_pada' => now(),
        ];
    }
}

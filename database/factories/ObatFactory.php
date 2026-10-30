<?php

namespace Database\Factories;

use App\Models\KategoriObat;
use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Obat>
 */
class ObatFactory extends Factory
{
    protected $model = Obat::class;

    public function definition(): array
    {
        return [
            'id_obat' => (string) Str::uuid(),
            'nama_obat' => fake()->word().' '.fake()->randomElement(['500mg', '200mg', '10mg', 'Syrup 60ml']),
            'merek' => fake()->company(),
            'jenis_sediaan' => fake()->randomElement(['Strip 10 tablet', 'Botol', 'Box 5 strip', 'Sirup']),
            'jumlah_obat' => fake()->numberBetween(10, 200),
            'id_kategori' => KategoriObat::factory(),
            'harga' => fake()->numberBetween(5000, 150000),
            'expired' => now()->addMonths(fake()->numberBetween(6, 24))->toDateString(),
            'batch_number' => 'BTC-'.fake()->numerify('####'),
        ];
    }
}

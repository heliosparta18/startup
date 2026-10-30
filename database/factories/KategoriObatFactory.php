<?php

namespace Database\Factories;

use App\Models\KategoriObat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KategoriObat>
 */
class KategoriObatFactory extends Factory
{
    protected $model = KategoriObat::class;

    public function definition(): array
    {
        return [
            'id_kategori' => (string) Str::uuid(),
            'nama_kategori' => fake()->randomElement(['Obat Bebas', 'Bebas Terbatas', 'Wajib Resep (Keras)']),
            'wajib_resep' => false,
            'deskripsi' => fake()->sentence(),
            'id_client' => null,
            'dibuat_pada' => now(),
        ];
    }
}

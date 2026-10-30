<?php

namespace Database\Factories;

use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    public function definition(): array
    {
        return [
            'id_pengguna' => (string) Str::uuid(),
            'nama_lengkap' => fake()->name(),
            'peran' => 'pelanggan',
            'telepon_email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'terdaftar_pada' => now()->toDateString(),
            'aktif' => true,
        ];
    }

    public function apoteker(): static
    {
        return $this->state(fn () => ['peran' => 'apoteker']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['peran' => 'admin']);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['peran' => 'super_admin']);
    }
}

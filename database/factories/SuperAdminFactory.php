<?php

namespace Database\Factories;

use App\Models\Pengguna;
use App\Models\SuperAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SuperAdmin>
 */
class SuperAdminFactory extends Factory
{
    protected $model = SuperAdmin::class;

    public function definition(): array
    {
        return [
            'id_super_admin' => (string) Str::uuid(),
            'id_pengguna' => Pengguna::factory(),
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}

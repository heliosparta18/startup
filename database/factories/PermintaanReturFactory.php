<?php

namespace Database\Factories;

use App\Models\PermintaanRetur;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PermintaanRetur>
 */
class PermintaanReturFactory extends Factory
{
    protected $model = PermintaanRetur::class;

    public function definition(): array
    {
        return [
            'id_retur' => (string) Str::uuid(),
            'id_pesanan' => Pesanan::factory(),
            'alasan' => 'Kemasan obat rusak saat pengantaran kurir.',
            'jumlah_diajukan' => 1,
            'jumlah_disetujui' => 1,
            'status' => 'menunggu',
        ];
    }
}

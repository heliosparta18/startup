<?php

namespace Database\Factories;

use App\Models\ItemPesanan;
use App\Models\Obat;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ItemPesanan>
 */
class ItemPesananFactory extends Factory
{
    protected $model = ItemPesanan::class;

    public function definition(): array
    {
        /** @var Obat $obat */
        $obat = Obat::factory()->create();
        $qty = fake()->numberBetween(1, 5);
        $harga = (float) $obat->harga;

        return [
            'id_item' => (string) Str::uuid(),
            'id_pesanan' => Pesanan::factory(),
            'id_obat' => (string) $obat->id_obat,
            'kuantitas' => $qty,
            'nama_obat' => (string) $obat->nama_obat,
            'harga_satuan' => $harga,
            'subtotal' => $harga * $qty,
            'dibuat_pada' => now(),
        ];
    }
}

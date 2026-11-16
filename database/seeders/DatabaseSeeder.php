<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for OmniPharma Apotek Multi-Klien.
     */
    public function run(): void
    {
        $seeders = [
            DemoApotekSeeder::class,
            GoApotikCatalogSeeder::class,
        ];

        // Muat dataset katalog lengkap GoApotik (ribuan produk + Postinor) di lingkungan lokal/produksi
        if (! app()->runningUnitTests()) {
            $seeders[] = GoApotikFullCatalogSeeder::class;
        }

        $this->call($seeders);
    }
}

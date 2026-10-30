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
        $this->call([
            DemoApotekSeeder::class,
        ]);
    }
}

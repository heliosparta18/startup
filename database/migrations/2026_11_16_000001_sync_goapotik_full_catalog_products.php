<?php

use App\Modules\Catalog\Services\CatalogService;
use Database\Seeders\GoApotikFullCatalogSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Sinkronkan katalog lengkap GoApotik (2.424 produk + Postinor) secara idempoten saat deploy menjalankan migrate.
     */
    public function up(): void
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => GoApotikFullCatalogSeeder::class,
            '--force' => true,
        ]);

        app(CatalogService::class)->clearMetadataCache();
    }

    public function down(): void
    {
        // Sinkronisasi katalog bersifat idempoten dan tidak menghapus data produk yang ada.
    }
};

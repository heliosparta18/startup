<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\StokClient;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Catalog\Services\CustomerSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class GuestSessionAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_session_generates_32_character_opaque_token_cookie(): void
    {
        $service = app(CustomerSessionService::class);
        $request = Request::create('/');

        $token = $service->resolveSession($request);

        $this->assertIsString($token);
        $this->assertSame(32, strlen($token));
    }

    public function test_catalog_filters_medicines_by_search_keyword(): void
    {
        $client = Client::factory()->create(['nama_client' => 'Cabang Banyuwangi Kota']);
        $kategori = KategoriObat::factory()->create();

        $obatParacetamol = Obat::factory()->create([
            'nama_obat' => 'Paracetamol 500mg',
            'merek' => 'Sanmol',
            'id_kategori' => $kategori->id_kategori,
        ]);

        $obatAmoxicillin = Obat::factory()->create([
            'nama_obat' => 'Amoxicillin 500mg',
            'merek' => 'Amoxsan',
            'id_kategori' => $kategori->id_kategori,
        ]);

        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatParacetamol->id_obat,
            'jumlah_stok' => 20,
        ]);

        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatAmoxicillin->id_obat,
            'jumlah_stok' => 15,
        ]);

        $service = app(CatalogService::class);
        $results = $service->getCatalog($client->id_client, 'Paracetamol');

        $this->assertSame(1, $results->total());
        $this->assertSame('Paracetamol 500mg', $results->items()[0]->nama_obat);
    }

    public function test_catalog_filters_medicines_by_jenis_sediaan(): void
    {
        $client = Client::factory()->create(['nama_client' => 'Cabang Rogojampi']);
        $kategori = KategoriObat::factory()->create();

        $obatTablet = Obat::factory()->create([
            'nama_obat' => 'Antasida Tablet',
            'jenis_sediaan' => 'tablet',
            'id_kategori' => $kategori->id_kategori,
        ]);

        $obatSirup = Obat::factory()->create([
            'nama_obat' => 'OBH Sirup 100ml',
            'jenis_sediaan' => 'sirup',
            'id_kategori' => $kategori->id_kategori,
        ]);

        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatTablet->id_obat,
            'jumlah_stok' => 10,
        ]);

        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatSirup->id_obat,
            'jumlah_stok' => 8,
        ]);

        $service = app(CatalogService::class);
        $results = $service->getCatalog($client->id_client, null, 'tablet');

        $this->assertSame(1, $results->total());
        $this->assertSame('tablet', $results->items()[0]->jenis_sediaan);
    }

    public function test_catalog_isolates_stock_per_branch(): void
    {
        $clientA = Client::factory()->create(['nama_client' => 'Cabang A']);
        $clientB = Client::factory()->create(['nama_client' => 'Cabang B']);
        $kategori = KategoriObat::factory()->create();

        $obat = Obat::factory()->create([
            'nama_obat' => 'Amodiaquine 200mg',
            'id_kategori' => $kategori->id_kategori,
        ]);

        StokClient::factory()->create([
            'id_client' => $clientA->id_client,
            'id_obat' => $obat->id_obat,
            'jumlah_stok' => 12,
        ]);

        StokClient::factory()->create([
            'id_client' => $clientB->id_client,
            'id_obat' => $obat->id_obat,
            'jumlah_stok' => 0,
        ]);

        $service = app(CatalogService::class);

        $resultsA = $service->getCatalog($clientA->id_client);
        $this->assertSame(1, $resultsA->total());

        $resultsB = $service->getCatalog($clientB->id_client);
        $this->assertSame(0, $resultsB->total());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\StokClient;
use App\Models\SubkategoriObat;
use App\Modules\Catalog\Livewire\PublicCatalog;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\GoApotikCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GoApotikCatalogIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_catalog_database_has_30_to_50_representative_products(): void
    {
        $totalObat = Obat::count();
        $this->assertGreaterThanOrEqual(30, $totalObat);
        $this->assertLessThanOrEqual(50, $totalObat);

        // Pastikan 7 kategori utama produk kesehatan terdaftar
        $kategoriUtama = ['Obat', 'Suplemen', 'Nutrisi', 'Herbal', 'Ibu & Anak', 'Alat Kesehatan', 'Perawatan Diri'];
        foreach ($kategoriUtama as $kat) {
            $this->assertTrue(
                KategoriObat::where('nama_kategori', $kat)->exists(),
                "Kategori utama [{$kat}] wajib terdaftar di database."
            );
        }

        // Pastikan subkategori terdistribusi dengan baik
        $totalSubkategori = SubkategoriObat::count();
        $this->assertGreaterThanOrEqual(20, $totalSubkategori);

        // Pastikan setiap produk GoApotik memiliki atribut lengkap
        $sampleProduk = Obat::whereNotNull('slug')->first();
        $this->assertNotNull($sampleProduk);
        $this->assertNotEmpty($sampleProduk->nama_obat);
        $this->assertNotEmpty($sampleProduk->satuan_kemasan);
        $this->assertNotEmpty($sampleProduk->gambar_url);
        $this->assertGreaterThan(0, (float) $sampleProduk->harga);
    }

    public function test_seeder_is_completely_idempotent_when_executed_multiple_times(): void
    {
        $initialObatCount = Obat::count();
        $initialKategoriCount = KategoriObat::count();
        $initialSubkategoriCount = SubkategoriObat::count();
        $initialStokCount = StokClient::count();

        // Eksekusi seeder untuk kedua kalinya
        $this->seed(GoApotikCatalogSeeder::class);

        $this->assertSame($initialObatCount, Obat::count(), 'Jumlah obat berubah setelah re-seeding.');
        $this->assertSame($initialKategoriCount, KategoriObat::count(), 'Jumlah kategori berubah setelah re-seeding.');
        $this->assertSame($initialSubkategoriCount, SubkategoriObat::count(), 'Jumlah subkategori berubah setelah re-seeding.');
        $this->assertSame($initialStokCount, StokClient::count(), 'Jumlah stok_client berubah setelah re-seeding.');

        // Eksekusi seeder untuk ketiga kalinya
        $this->seed(GoApotikCatalogSeeder::class);

        $this->assertSame($initialObatCount, Obat::count(), 'Jumlah obat berubah pada eksekusi ketiga.');
        $this->assertSame($initialKategoriCount, KategoriObat::count(), 'Jumlah kategori berubah pada eksekusi ketiga.');
        $this->assertSame($initialSubkategoriCount, SubkategoriObat::count(), 'Jumlah subkategori berubah pada eksekusi ketiga.');
        $this->assertSame($initialStokCount, StokClient::count(), 'Jumlah stok_client berubah pada eksekusi ketiga.');
    }

    public function test_livewire_catalog_filters_by_parent_category(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        // 1. Kategori default Obat
        Livewire::test(PublicCatalog::class, ['client' => $client->slug])
            ->assertSet('kategori', 'Obat')
            ->assertSee('PARACETAMOL')
            ->assertDontSee('CETAPHIL')
            ->assertDontSee('ENTRASOL')
            // 2. Beralih ke Suplemen
            ->call('selectCategory', 'Suplemen')
            ->assertSet('kategori', 'Suplemen')
            ->assertSee('ENERVON C')
            ->assertDontSee('AMOXICILLIN')
            // 3. Beralih ke Herbal
            ->call('selectCategory', 'Herbal')
            ->assertSet('kategori', 'Herbal')
            ->assertSee('TOLAK ANGIN')
            ->assertDontSee('PARACETAMOL');
    }

    public function test_livewire_catalog_filters_by_subcategory(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        Livewire::test(PublicCatalog::class, ['client' => $client->slug])
            ->call('selectCategory', 'Obat')
            ->call('selectSubCategory', 'Pereda Demam & Nyeri')
            ->assertSet('subKategori', 'Pereda Demam & Nyeri')
            ->assertSee('PARACETAMOL BERLICO')
            ->assertDontSee('AMOXICILLIN TRIHYDRATE')
            ->call('selectSubCategory', 'Antibiotik')
            ->assertSet('subKategori', 'Antibiotik')
            ->assertSee('AMOXICILLIN')
            ->assertDontSee('PARACETAMOL BERLICO');
    }

    public function test_livewire_catalog_searches_products_by_keyword_reactively(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        Livewire::test(PublicCatalog::class, ['client' => $client->slug])
            ->set('search', 'Promag')
            ->assertSee('PROMAG SUSPENSI')
            ->assertDontSee('CETAPHIL')
            ->set('search', 'ProdukYangPastiTidakAda123XYZ')
            ->assertSee('Produk Tidak Ditemukan')
            ->assertSee('Reset Semua Filter')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertDontSee('Produk Tidak Ditemukan');
    }

    public function test_catalog_card_renders_bpom_regulatory_badges_and_pricing(): void
    {
        $response = $this->get('/klien/banyuwangi-kota/katalog');

        $response->assertOk();
        $response->assertSee('Katalog');
        $response->assertSee('Rp ');
        $response->assertSee('Cari di Kategori');
        $response->assertSee('Obat Keras (Wajib Resep)');
        $response->assertSee('placeholder-product.svg');

        // Antislop R-02 Compliance: Dilarang menggunakan em dash (—) di teks antarmuka
        $response->assertDontSee('—');
    }

    public function test_catalog_isolates_tenant_inventory_for_active_branches(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogojampi = Client::where('slug', 'rogojampi')->firstOrFail();

        $produkKhusus = Obat::factory()->create([
            'nama_obat' => 'Obat Eksklusif Banyuwangi Kota',
            'harga' => 50000,
        ]);

        StokClient::factory()->create([
            'id_client' => $clientBwi->id_client,
            'id_obat' => $produkKhusus->id_obat,
            'jumlah_stok' => 25,
        ]);

        StokClient::factory()->create([
            'id_client' => $clientRogojampi->id_client,
            'id_obat' => $produkKhusus->id_obat,
            'jumlah_stok' => 0,
        ]);

        // Cabang Banyuwangi Kota melihat produk tersebut
        Livewire::test(PublicCatalog::class, ['client' => $clientBwi->slug])
            ->set('search', 'Obat Eksklusif')
            ->assertSee('Obat Eksklusif Banyuwangi Kota');

        // Cabang Rogojampi tidak melihat produk dengan stok 0
        Livewire::test(PublicCatalog::class, ['client' => $clientRogojampi->slug])
            ->set('search', 'Obat Eksklusif')
            ->assertDontSee('Obat Eksklusif Banyuwangi Kota');
    }
}

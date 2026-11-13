<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Catalog\Livewire\PublicCatalog;
use App\Modules\Catalog\Services\CustomerSessionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedCatalogAndMultiBranchAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Acceptance Criteria R1:
     * Database seeder berhasil mengalokasikan stok aktif untuk 20-30 produk obat pada masing-masing cabang.
     */
    public function test_seeder_allocates_between_20_and_30_active_products_per_branch(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogo = Client::where('slug', 'rogojampi')->firstOrFail();

        $bwiActiveCount = StokClient::withoutGlobalScopes()
            ->where('id_client', $clientBwi->id_client)
            ->where('jumlah_stok', '>', 0)
            ->count();

        $rogoActiveCount = StokClient::withoutGlobalScopes()
            ->where('id_client', $clientRogo->id_client)
            ->where('jumlah_stok', '>', 0)
            ->count();

        $this->assertGreaterThanOrEqual(20, $bwiActiveCount, 'Stok aktif Banyuwangi Kota minimal 20 produk.');
        $this->assertLessThanOrEqual(30, $bwiActiveCount, 'Stok aktif Banyuwangi Kota maksimal 30 produk.');

        $this->assertGreaterThanOrEqual(20, $rogoActiveCount, 'Stok aktif Rogojampi minimal 20 produk.');
        $this->assertLessThanOrEqual(30, $rogoActiveCount, 'Stok aktif Rogojampi maksimal 30 produk.');
    }

    /**
     * Acceptance Criteria R1:
     * Terdapat variasi ketersediaan produk:
     * - Produk berstok di kedua cabang
     * - Produk hanya berstok di Banyuwangi Kota
     * - Produk hanya berstok di Rogojampi
     */
    public function test_stock_distribution_has_clear_multi_branch_variation(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogo = Client::where('slug', 'rogojampi')->firstOrFail();

        $bwiActiveIds = StokClient::withoutGlobalScopes()
            ->where('id_client', $clientBwi->id_client)
            ->where('jumlah_stok', '>', 0)
            ->pluck('id_obat')
            ->toArray();

        $rogoActiveIds = StokClient::withoutGlobalScopes()
            ->where('id_client', $clientRogo->id_client)
            ->where('jumlah_stok', '>', 0)
            ->pluck('id_obat')
            ->toArray();

        $bothBranches = array_intersect($bwiActiveIds, $rogoActiveIds);
        $bwiOnly = array_diff($bwiActiveIds, $rogoActiveIds);
        $rogoOnly = array_diff($rogoActiveIds, $bwiActiveIds);

        $this->assertNotEmpty($bothBranches, 'Harus ada produk yang tersedia di kedua cabang.');
        $this->assertGreaterThanOrEqual(10, count($bothBranches), 'Produk di kedua cabang minimal 10.');

        $this->assertNotEmpty($bwiOnly, 'Harus ada produk yang hanya tersedia di Banyuwangi Kota.');
        $this->assertGreaterThanOrEqual(5, count($bwiOnly), 'Produk eksklusif Banyuwangi Kota minimal 5.');

        $this->assertNotEmpty($rogoOnly, 'Harus ada produk yang hanya tersedia di Rogojampi.');
        $this->assertGreaterThanOrEqual(5, count($rogoOnly), 'Produk eksklusif Rogojampi minimal 5.');
    }

    /**
     * Acceptance Criteria R2 & R3:
     * Indikator teks ketersediaan multi-cabang pada model Obat dan helper methods.
     */
    public function test_obat_model_multi_branch_availability_helpers(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogo = Client::where('slug', 'rogojampi')->firstOrFail();

        // 1. Produk di kedua cabang
        $obatBoth = Obat::query()
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientBwi->id_client)->where('jumlah_stok', '>', 0))
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientRogo->id_client)->where('jumlah_stok', '>', 0))
            ->firstOrFail();

        $this->assertTrue($obatBoth->isAvailableInBranch('banyuwangi-kota'));
        $this->assertTrue($obatBoth->isAvailableInBranch('rogojampi'));
        $this->assertStringContainsString('Banyuwangi Kota', $obatBoth->branch_availability_text);
        $this->assertStringContainsString('Rogojampi', $obatBoth->branch_availability_text);
        $this->assertStringContainsString('&', $obatBoth->branch_availability_text);

        // 2. Produk hanya di Banyuwangi Kota
        $obatBwiOnly = Obat::query()
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientBwi->id_client)->where('jumlah_stok', '>', 0))
            ->whereDoesntHave('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientRogo->id_client)->where('jumlah_stok', '>', 0))
            ->firstOrFail();

        $this->assertTrue($obatBwiOnly->isAvailableInBranch('banyuwangi-kota'));
        $this->assertFalse($obatBwiOnly->isAvailableInBranch('rogojampi'));
        $this->assertStringContainsString('Banyuwangi Kota', $obatBwiOnly->branch_availability_text);
        $this->assertStringNotContainsString('Rogojampi', $obatBwiOnly->branch_availability_text);

        // Di cabang Rogojampi, alternatifnya harus Banyuwangi Kota
        $altFromRogo = $obatBwiOnly->getAlternativeAvailableBranch('rogojampi');
        $this->assertNotNull($altFromRogo);
        $this->assertSame($clientBwi->id_client, $altFromRogo->id_client);

        // 3. Produk hanya di Rogojampi
        $obatRogoOnly = Obat::query()
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientRogo->id_client)->where('jumlah_stok', '>', 0))
            ->whereDoesntHave('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientBwi->id_client)->where('jumlah_stok', '>', 0))
            ->firstOrFail();

        $this->assertFalse($obatRogoOnly->isAvailableInBranch('banyuwangi-kota'));
        $this->assertTrue($obatRogoOnly->isAvailableInBranch('rogojampi'));
        $this->assertStringContainsString('Rogojampi', $obatRogoOnly->branch_availability_text);
        $this->assertStringNotContainsString('Banyuwangi Kota', $obatRogoOnly->branch_availability_text);

        // Di cabang Banyuwangi Kota, alternatifnya harus Rogojampi
        $altFromBwi = $obatRogoOnly->getAlternativeAvailableBranch('banyuwangi-kota');
        $this->assertNotNull($altFromBwi);
        $this->assertSame($clientRogo->id_client, $altFromBwi->id_client);
    }

    /**
     * Acceptance Criteria R2 & R3:
     * Kartu produk katalog menampilkan badge ketersediaan dan tombol beralih cabang saat stok kosong di cabang aktif.
     */
    public function test_catalog_livewire_renders_multi_branch_availability_and_switch_button(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogo = Client::where('slug', 'rogojampi')->firstOrFail();

        // Cari produk yang hanya ada di Rogojampi
        $obatRogoOnly = Obat::query()
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientRogo->id_client)->where('jumlah_stok', '>', 0))
            ->whereDoesntHave('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientBwi->id_client)->where('jumlah_stok', '>', 0))
            ->firstOrFail();

        // Saat dikunjungi dari katalog Banyuwangi Kota
        Livewire::test(PublicCatalog::class, ['client' => 'banyuwangi-kota'])
            ->set('kategori', $obatRogoOnly->kategori->nama_kategori)
            ->set('search', $obatRogoOnly->nama_obat)
            ->assertSee($obatRogoOnly->nama_obat)
            ->assertSee('Tersedia di: Rogojampi')
            ->assertSee('Beralih ke Rogojampi');

        // Uji eksekusi aksi switchBranch
        Livewire::test(PublicCatalog::class, ['client' => 'banyuwangi-kota'])
            ->call('switchBranch', 'rogojampi')
            ->assertRedirect(route('customer.catalog', ['client' => 'rogojampi']));

        $this->assertSame('rogojampi', session('active_branch'));
    }

    /**
     * Acceptance Criteria R3:
     * Halaman detail obat menampilkan indikator ketersediaan cabang dan tombol beralih cabang.
     */
    public function test_obat_detail_page_renders_branch_availability_indicators(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientRogo = Client::where('slug', 'rogojampi')->firstOrFail();

        // Cari produk yang hanya ada di Rogojampi
        $obatRogoOnly = Obat::query()
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientRogo->id_client)->where('jumlah_stok', '>', 0))
            ->whereDoesntHave('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $clientBwi->id_client)->where('jumlah_stok', '>', 0))
            ->firstOrFail();

        // Akses detail produk dari cabang Banyuwangi Kota (tempat stok kosong)
        $response = $this->get(route('customer.obat.detail', [
            'client' => 'banyuwangi-kota',
            'id' => $obatRogoOnly->id_obat,
        ]));

        $response->assertOk();
        $response->assertSee('Ketersediaan Stok Cabang:');
        $response->assertSee('Kosong di cabang aktif: Tersedia di: Rogojampi');
        $response->assertSee('Beralih ke Rogojampi');

        // Antislop R-02 Compliance: Bebas karakter em dash (—)
        $response->assertDontSee('—');
    }

    /**
     * Acceptance Criteria R2:
     * Tombol "Mulai Belanja" pada Dashboard mengarah ke rute Unified Catalog.
     */
    public function test_dashboard_and_navigation_align_to_unified_catalog(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Mulai Belanja Obat');
        $response->assertSee(route('customer.catalog', ['client' => 'banyuwangi-kota']));

        // Shortcut /katalog me-redirect ke katalog cabang aktif
        $catalogRedirect = $this->get(route('katalog'));
        $catalogRedirect->assertRedirect(route('customer.catalog', ['client' => 'banyuwangi-kota']));
    }
}

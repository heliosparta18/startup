<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Modules\Catalog\Livewire\PublicCatalog;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Ordering\Services\CartService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceAndCatalogAnimationVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * R3.1: Verifikasi ketersediaan dan fungsionalitas layanan Redis lokal.
     */
    public function test_redis_service_is_active_and_accessible(): void
    {
        // 1. Verifikasi ping
        $ping = Redis::ping();
        $this->assertTrue(
            $ping === true || $ping === 'PONG' || $ping === '+PONG' || $ping === 1 || $ping === '1',
            'Layanan Redis lokal wajib merespons perintah PING.'
        );

        // 2. Verifikasi operasi read/write/delete dasar
        $testKey = 'omnidata:verification:test_'.uniqid();
        $testValue = 'omnivalue_'.time();

        Redis::set($testKey, $testValue);
        $retrieved = Redis::get($testKey);
        $this->assertSame($testValue, $retrieved, 'Redis wajib menyimpan dan mengembalikan data secara presisi.');

        Redis::del($testKey);
        $this->assertNull(Redis::get($testKey), 'Redis del wajib menghapus key.');
    }

    /**
     * R3.1: Verifikasi CartService beroperasi normal di atas Redis tanpa exception koneksi.
     */
    public function test_cart_service_operates_cleanly_with_redis(): void
    {
        $cartService = app(CartService::class);
        $obat = Obat::first();
        $this->assertNotNull($obat, 'Membutuhkan minimal 1 obat di database untuk verifikasi keranjang.');
        $client = Client::first();
        $this->assertNotNull($client, 'Membutuhkan minimal 1 client di database.');

        $testSession = 'session_verify_'.uniqid();
        $testClientId = $client->id_client;

        $cartService->addItem($testSession, $testClientId, (string) $obat->id_obat, 3);
        $items = $cartService->getItems($testSession, $testClientId);

        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertSame((string) $obat->id_obat, (string) $items[0]['id_obat']);
        $this->assertSame(3, (int) $items[0]['jumlah']);

        $cartService->clear($testSession, $testClientId);
        $cleared = $cartService->getItems($testSession, $testClientId);
        $this->assertEmpty($cleared);
    }

    /**
     * R1.1, R1.2, R1.3: Verifikasi urutan checkpoint kategori dan pembatasan terminal pada Mata.
     */
    public function test_category_bar_checkpoint_order_and_mata_terminal_boundary(): void
    {
        $expectedCheckpoints = [
            'Obat',
            'Suplemen',
            'Nutrisi',
            'Herbal',
            'Ibu & Anak',
            'Alat Kesehatan',
            'Perawatan Diri',
            'Mata',
        ];

        // 1. Verifikasi konstanta urutan checkpoint
        $this->assertSame($expectedCheckpoints, PublicCatalog::CATEGORY_CHECKPOINTS);

        // 2. Verifikasi initial state pada Livewire PublicCatalog
        $component = Livewire::test(PublicCatalog::class, ['client' => 'banyuwangi-kota'])
            ->assertSet('kategori', 'Obat')
            ->assertSet('subKategori', '');

        // 3. Verifikasi pergerakan maju adjacent (Obat -> Suplemen -> Nutrisi)
        $component->call('nextCategory')
            ->assertSet('kategori', 'Suplemen');

        $component->call('nextCategory')
            ->assertSet('kategori', 'Nutrisi');

        // 4. Verifikasi lompatan langsung non-adjacent ke Mata
        $component->call('selectCategory', 'Mata')
            ->assertSet('kategori', 'Mata');

        // 5. Verifikasi Mata sebagai terminal checkpoint: tidak boleh maju melebihi Mata
        $component->call('nextCategory')
            ->assertSet('kategori', 'Mata');

        // 6. Verifikasi navigasi mundur diperbolehkan dari Mata ke Perawatan Diri
        $component->call('prevCategory')
            ->assertSet('kategori', 'Perawatan Diri');

        $component->call('prevCategory')
            ->assertSet('kategori', 'Alat Kesehatan');

        // 7. Verifikasi batas bawah checkpoint: dari Obat tidak boleh mundur melebihi Obat
        $component->call('selectCategory', 'Obat')
            ->call('prevCategory')
            ->assertSet('kategori', 'Obat');
    }

    /**
     * R1.1, R1.2, R1.3: Verifikasi elemen Blade tab kategori memuat sliding indicator dan batas Mata.
     */
    public function test_category_tabs_blade_renders_sliding_indicators_and_terminal_meta(): void
    {
        $catalogService = app(CatalogService::class);
        $view = $this->view('customer.partials.catalog-category-tabs', [
            'kategoriList' => $catalogService->getDaftarKategori(),
            'kategoriAktif' => 'Suplemen',
            'inDashboard' => false,
        ]);

        $rendered = (string) $view;

        // Verifikasi container tablist dan x-ref
        $this->assertStringContainsString('x-ref="tabContainer"', $rendered);

        // Verifikasi sliding circular indicator (z-5)
        $this->assertStringContainsString('x-ref="activeCircleIndicator"', $rendered);
        $this->assertStringContainsString('ring-emerald-600/40', $rendered);
        $this->assertStringContainsString('z-5', $rendered);

        // Verifikasi sliding bottom bar indicator (z-10)
        $this->assertStringContainsString('x-ref="activeBottomBar"', $rendered);
        $this->assertStringContainsString('bg-emerald-700', $rendered);

        // Verifikasi atribut checkpoint data dan terminal checkpoint 'Mata'
        $this->assertStringContainsString('data-category="Mata"', $rendered);
        $this->assertStringContainsString('data-terminal="true"', $rendered);
        $this->assertStringContainsString('data-category="Obat"', $rendered);
        $this->assertStringContainsString('data-terminal="false"', $rendered);
    }

    /**
     * R2.1: Verifikasi elemen subkategori memuat sliding active indicator pill.
     */
    public function test_subcategory_sidebar_blade_renders_sliding_indicator_pill(): void
    {
        $catalogService = app(CatalogService::class);
        $obats = $catalogService->getCatalog('banyuwangi-kota', null, null, 'Obat', null, 15, true);
        $subkategoriList = $catalogService->getDaftarSubkategori('Obat', 'banyuwangi-kota');

        $view = $this->view('customer.partials.catalog-subcategory-sidebar', [
            'subkategoriList' => $subkategoriList,
            'kategoriAktif' => 'Obat',
            'subKategoriAktif' => '',
            'obats' => $obats,
        ]);

        $rendered = (string) $view;

        $this->assertStringContainsString('x-ref="subcatNav"', $rendered);
        $this->assertStringContainsString('x-ref="activeSubcatIndicator"', $rendered);
        $this->assertStringContainsString('data-subcat=""', $rendered);
    }

    /**
     * R2.2: Verifikasi penghapusan disruptive loading flash & penambahan transisi halus.
     */
    public function test_catalog_view_eliminates_disruptive_loading_and_stabilizes_grid(): void
    {
        $response = $this->get('/klien/banyuwangi-kota/katalog');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Grid produk tidak boleh memiliki class hidden saat wire:loading
        $this->assertStringNotContainsString('wire:loading.delay.class="hidden"', $content);
        $this->assertStringNotContainsString('wire:loading.delay.class.remove="hidden"', $content);

        // 2. Grid produk menggunakan transisi opacity crossfade dan min-h stabil
        $this->assertStringContainsString('wire:loading.class="opacity-60 pointer-events-none"', $content);
        $this->assertStringContainsString('min-h-[460px]', $content);
        $this->assertStringContainsString('transition-opacity duration-200', $content);

        // 3. Verifikasi ketersediaan top loading progress line
        $this->assertStringContainsString('wire:loading', $content);
        $this->assertStringContainsString('bg-emerald-600 animate-pulse', $content);
    }
}

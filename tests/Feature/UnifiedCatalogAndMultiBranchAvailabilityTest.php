<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Catalog\Livewire\PublicCatalog;
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

        // Verifikasi kuantitatif eksak alokasi produk deterministik
        $this->assertSame(27, $bwiActiveCount, 'Stok aktif Banyuwangi Kota harus tepat 27 produk.');
        $this->assertSame(26, $rogoActiveCount, 'Stok aktif Rogojampi harus tepat 26 produk.');
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
            ->assertDontSee('Tersedia di:')
            ->assertSee('Beralih ke Rogojampi');

        // Uji eksekusi aksi switchBranch dua arah
        Livewire::test(PublicCatalog::class, ['client' => 'banyuwangi-kota'])
            ->call('switchBranch', 'rogojampi')
            ->assertRedirect(route('customer.catalog', ['client' => 'rogojampi']));

        $this->assertSame('rogojampi', session('active_branch'));

        Livewire::test(PublicCatalog::class, ['client' => 'rogojampi'])
            ->call('switchBranch', 'banyuwangi-kota')
            ->assertRedirect(route('customer.catalog', ['client' => 'banyuwangi-kota']));

        $this->assertSame('banyuwangi-kota', session('active_branch'));
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

        // 1. Cabang default tanpa session terpasang (fallback ke Banyuwangi Kota) dan katalog menyatu di /dashboard
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Mulai Belanja Obat');
        $response->assertSee('Cari di Kategori');
        $response->assertSeeLivewire(\App\Modules\Catalog\Livewire\PublicCatalog::class);
        $response->assertSee(route('customer.cart', ['client' => 'banyuwangi-kota']));
        $response->assertDontSee(route('customer.catalog', ['client' => 'banyuwangi-kota']));

        // Pengguna login yang mengakses /katalog atau /klien/.../katalog?search=... diarahkan ke /dashboard tanpa keluar
        $authKatalogRedirect = $this->actingAs($user)->get(route('katalog'));
        $authKatalogRedirect->assertRedirect(route('dashboard'));

        $authClientCatalogRedirect = $this->actingAs($user)->get(route('customer.catalog', [
            'client' => 'banyuwangi-kota',
            'search' => 'posti',
        ]));
        $authClientCatalogRedirect->assertRedirect(route('dashboard', ['search' => 'posti']));

        // Pencarian via query ?search= di /dashboard langsung merender obat berstok yang cocok
        $sampleObat = \App\Models\Obat::query()
            ->whereNotNull('nama_obat')
            ->whereHas('stokClient', fn ($q) => $q->withoutGlobalScopes()->where('jumlah_stok', '>', 0))
            ->orderBy('nama_obat')
            ->first();
        if ($sampleObat) {
            $keyword = explode(' ', trim($sampleObat->nama_obat))[0];
            $searchResponse = $this->actingAs($user)->get(route('dashboard', ['search' => $keyword]));
            $searchResponse->assertOk();
            $searchResponse->assertSee($sampleObat->nama_obat);
        }

        // Pengunjung tamu /katalog tetap me-redirect ke katalog cabang aktif
        auth()->logout();
        $catalogRedirect = $this->get(route('katalog'));
        $catalogRedirect->assertRedirect(route('customer.catalog', ['client' => 'banyuwangi-kota']));

        // 2. Cabang aktif berubah ke Rogojampi via session
        $responseRogo = $this->actingAs($user)
            ->withSession(['active_branch' => 'rogojampi'])
            ->get(route('dashboard'));
        $responseRogo->assertOk();
        $responseRogo->assertSee(route('customer.cart', ['client' => 'rogojampi']));
        $responseRogo->assertDontSee(route('customer.catalog', ['client' => 'rogojampi']));

        auth()->logout();
        $catalogRogoRedirect = $this->withSession(['active_branch' => 'rogojampi'])->get(route('katalog'));
        $catalogRogoRedirect->assertRedirect(route('customer.catalog', ['client' => 'rogojampi']));
    }

    public function test_clean_short_endpoints_and_role_shortcuts(): void
    {
        $bwiClient = \App\Models\Client::where('slug', 'banyuwangi-kota')->first();
        if (! $bwiClient) {
            $this->seed(\Database\Seeders\DemoApotekSeeder::class);
            $bwiClient = \App\Models\Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        }

        $sampleObat = \App\Models\Obat::query()->whereNotNull('nama_obat')->firstOrFail();
        $identifier = $sampleObat->slug ?: $sampleObat->id_obat;

        \App\Models\StokClient::withoutTenancy()->updateOrCreate(
            [
                'id_client' => $bwiClient->id_client,
                'id_obat' => $sampleObat->id_obat,
            ],
            [
                'jumlah_stok' => 50,
            ]
        );

        $customer = \App\Models\User::factory()->create([
            'role' => 'pelanggan',
            'status' => 'active',
        ]);
        $sessionToken = 'clean-endpoint-test-'.\Illuminate\Support\Str::random(10);

        // 1. Detail produk & produk alias redirect
        $detailResponse = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->get(route('obat.show', ['id' => $identifier]));
        $detailResponse->assertOk();
        $detailResponse->assertSee($sampleObat->nama_obat);
        $detailResponse->assertDontSee('—');

        $produkRedirect = $this->actingAs($customer)
            ->get(route('produk.show', ['id' => $identifier]));
        $produkRedirect->assertRedirect(route('obat.show', ['id' => $identifier]));

        // 2. Keranjang & /cart redirect
        $emptyCartResponse = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->get(route('keranjang'));
        $emptyCartResponse->assertOk();
        $emptyCartResponse->assertDontSee('—');

        $cartRedirect = $this->actingAs($customer)->get(route('cart.short'));
        $cartRedirect->assertRedirect(route('keranjang'));

        // 3. Unggah resep & search API URL menggunakan endpoint bersih
        $resepFormResponse = $this->actingAs($customer)->get(route('resep.form'));
        $resepFormResponse->assertRedirect(route('dashboard').'#unggah-resep');

        $searchQuery = mb_substr(trim((string) $sampleObat->nama_obat), 0, 18);
        $searchJson = $this->actingAs($customer)
            ->getJson('/api/search-obat?q='.urlencode($searchQuery));
        $searchJson->assertOk();
        $expectedUrl = route('obat.show', ['id' => $identifier]);
        $matchedUrl = collect($searchJson->json())->contains(
            fn (array $item): bool => ($item['url'] ?? null) === $expectedUrl
        );
        $this->assertTrue($matchedUrl, "Expected /api/search-obat to include {$expectedUrl}");

        // 4. Alur penuh tambah keranjang -> checkout -> pembayaran -> lacak -> batal via endpoint bersih
        $addResponse = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->post(route('keranjang.add'), [
                'id_obat' => $sampleObat->id_obat,
                'jumlah' => 2,
            ]);
        $addResponse->assertRedirect(route('keranjang'));

        $checkoutPage = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->get(route('checkout.index'));
        $checkoutPage->assertOk();
        $checkoutPage->assertSee($sampleObat->nama_obat);
        $checkoutPage->assertDontSee('—');

        $checkoutSubmit = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->post(route('checkout.store'), [
                'metode_layanan' => 'click_collect',
                'slot_waktu' => '15 Menit Lagi (Segera)',
                'catatan' => 'Pengujian endpoint bersih',
            ]);

        $pesanan = \App\Models\Pesanan::withoutTenancy()
            ->where('token_sesi', $sessionToken)
            ->latest('dibuat_pada')
            ->firstOrFail();

        $checkoutSubmit->assertRedirect(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));

        $paymentPage = $this->actingAs($customer)
            ->get(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $paymentPage->assertOk();
        $paymentPage->assertSee($pesanan->nomor_pesanan);
        $paymentPage->assertDontSee('—');

        $trackPage = $this->actingAs($customer)
            ->get(route('pesanan.track', ['id' => $pesanan->id_pesanan]));
        $trackPage->assertOk();
        $trackPage->assertSee($pesanan->nomor_pesanan);
        $trackPage->assertDontSee('—');

        $cancelResponse = $this->actingAs($customer)
            ->post(route('pesanan.cancel', ['id' => $pesanan->id_pesanan]));
        $cancelResponse->assertRedirect(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $this->assertSame('dibatalkan', $pesanan->fresh()->status);

        // 5. Role shortcuts terautentikasi (/apoteker, /antrean, /verifikasi-resep, /stok-fefo, /admin)
        $apoteker = \App\Models\User::factory()->create([
            'role' => 'apoteker',
            'status' => 'active',
        ]);
        $slug = $apoteker->getClientSlug();

        $this->actingAs($apoteker)->get('/apoteker')
            ->assertRedirect(route('tenant.dashboard', ['client' => $slug]));
        $this->actingAs($apoteker)->get('/antrean')
            ->assertRedirect(route('tenant.antrean.index', ['client' => $slug]));
        $this->actingAs($apoteker)->get('/verifikasi-resep')
            ->assertRedirect(route('tenant.resep.antrean', ['client' => $slug]));
        $this->actingAs($apoteker)->get('/stok-fefo')
            ->assertRedirect(route('tenant.stok.fefo', ['client' => $slug]));

        $superAdmin = \App\Models\User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $this->actingAs($superAdmin)->get('/admin')
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Acceptance Criteria:
     * 1. Obat keras (logo K) dapat dibeli langsung tanpa blokir tebus resep via ikon keranjang.
     * 2. Menambahkan produk ke keranjang mengembalikan cart_count dan memperbarui badge navbar.
     * 3. Navbar menampilkan keranjang mode ikon, badge notif keranjang, dan tombol Chat (Coming Soon) menggantikan Dashboard Utama.
     */
    public function test_hard_medicine_can_be_purchased_directly_and_navbar_displays_cart_badge_and_chat_coming_soon(): void
    {
        $clientBwi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $hardObat = Obat::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where('badge_status', 'like', '%Keras%')
                    ->orWhereHas('kategori', fn ($k) => $k->where('wajib_resep', true));
            })
            ->whereHas('stokClient', function ($q) use ($clientBwi) {
                $q->withoutGlobalScopes()
                    ->where('id_client', $clientBwi->id_client)
                    ->where('jumlah_stok', '>', 0);
            })
            ->first();

        if (! $hardObat) {
            $hardObat = Obat::withoutGlobalScopes()->firstOrFail();
            $hardObat->update(['badge_status' => 'Obat Keras']);
            StokClient::withoutGlobalScopes()->updateOrCreate(
                ['id_client' => $clientBwi->id_client, 'id_obat' => $hardObat->id_obat],
                ['jumlah_stok' => 10]
            );
        }

        // 1. Kartu katalog untuk obat keras harus menampilkan ikon keranjang & tidak memblokir dengan Tebus Resep
        Livewire::test(PublicCatalog::class, ['client' => 'banyuwangi-kota'])
            ->set('search', $hardObat->nama_obat)
            ->assertSee($hardObat->nama_obat)
            ->assertDontSee('Tebus Resep')
            ->assertSeeHtml('add-to-cart');

        $customer = User::factory()->create([
            'role' => 'pelanggan',
            'status' => 'active',
        ]);

        // 2. Halaman detail obat keras menampilkan kontrol pembelian langsung (Tambah ke Keranjang) & navbar baru
        $detailResponse = $this->actingAs($customer)->get(route('obat.show', [
            'id' => $hardObat->slug ?: $hardObat->id_obat,
        ]));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Obat Keras (Wajib Resep)');
        $detailResponse->assertSee('Tambah ke Keranjang');
        $detailResponse->assertDontSee('Unggah Foto Resep Dokter di Cabang Ini');
        $detailResponse->assertSee('Konsultasi Apoteker (Coming Soon)');
        $detailResponse->assertSee('<span class="sr-only">Keranjang</span>', false);
        $detailResponse->assertDontSee('aria-label="Kembali ke Dashboard Utama"', false);

        // 3. Tambah obat keras ke keranjang via JSON API mengembalikan cart_count akurat
        $cartService = app(\App\Modules\Ordering\Services\CartService::class);
        $cartService->clear('test-cart-badge-session', (string) $clientBwi->id_client);
        $sessionToken = 'test-cart-badge-'.\Illuminate\Support\Str::random(10);

        $addFirst = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->postJson(route('keranjang.add'), [
                'id_obat' => $hardObat->id_obat,
                'jumlah' => 2,
            ]);
        $addFirst->assertOk()->assertJson([
            'success' => true,
            'cart_count' => 2,
        ]);

        $addSecond = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->postJson(route('keranjang.add'), [
                'id_obat' => $hardObat->id_obat,
                'jumlah' => 1,
            ]);
        $addSecond->assertOk()->assertJson([
            'success' => true,
            'cart_count' => 3,
        ]);

        // 4. Dashboard menampilkan ikon keranjang dengan count=3, Chat icon (Coming Soon), dan tanpa tombol Dashboard Utama di navbar
        $dashboardResponse = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionToken)
            ->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Konsultasi Apoteker (Coming Soon)');
        $dashboardResponse->assertSee('<span class="sr-only">Keranjang</span>', false);
        $dashboardResponse->assertDontSee('aria-label="Dashboard Utama"', false);
        $dashboardResponse->assertSeeHtml('cartCount: 3');
        $dashboardResponse->assertDontSee('—');

        $cartService->clear($sessionToken, (string) $clientBwi->id_client);
    }
}

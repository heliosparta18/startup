<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Payments\Services\QrisEmvcoBuilder;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CartCheckoutPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_cart_service_stores_and_isolates_by_client(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();
        $obat = Obat::firstOrFail();

        $cartService = app(CartService::class);
        $sessionId = 'test-session-'.Str::random(8);

        // Tambah ke cabang A
        $cartService->addItem($sessionId, $clientA->id_client, $obat->id_obat, 2);

        $itemsA = $cartService->getItems($sessionId, $clientA->id_client);
        $itemsB = $cartService->getItems($sessionId, $clientB->id_client);

        $this->assertCount(1, $itemsA);
        $this->assertEquals($obat->id_obat, $itemsA[0]['id_obat']);
        $this->assertEquals(2, $itemsA[0]['jumlah']);
        $this->assertEquals((float) $obat->harga * 2, $itemsA[0]['subtotal']);

        // Cabang B harus kosong (Isolasi multi-tenant)
        $this->assertEmpty($itemsB);

        // Update kuantitas
        $cartService->updateItem($sessionId, $clientA->id_client, $obat->id_obat, 5);
        $updatedItems = $cartService->getItems($sessionId, $clientA->id_client);
        $this->assertEquals(5, $updatedItems[0]['jumlah']);

        // Clear cart
        $cartService->clear($sessionId, $clientA->id_client);
        $this->assertEmpty($cartService->getItems($sessionId, $clientA->id_client));
    }

    public function test_atomic_checkout_locks_stock_and_creates_order_snapshot(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        // Siapkan stok fisik awal 20
        $stokRecord = StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 20]
        );

        $createOrderAction = app(CreateOrderAction::class);

        $items = [
            [
                'id_obat' => $obat->id_obat,
                'nama_obat' => $obat->nama_obat,
                'harga' => (float) $obat->harga,
                'jumlah' => 3,
            ],
        ];

        $pesanan = $createOrderAction->execute($client->id_client, $pelanggan->id_pengguna, $items, 'click_collect', 'test-session-123');

        $this->assertNotNull($pesanan);
        $this->assertStringStartsWith('#ORD-', $pesanan->nomor_pesanan);
        $this->assertEquals('menunggu_pembayaran', $pesanan->status);
        $this->assertEquals('click_collect', $pesanan->metode_layanan);

        $this->assertSame('test-session-123', $pesanan->token_sesi);
        // Cek snapshot harga pada item_pesanan (ADR-0008)
        $this->assertDatabaseHas('item_pesanan', [
            'id_pesanan' => $pesanan->id_pesanan,
            'id_obat' => $obat->id_obat,
            'nama_obat' => $obat->nama_obat,
            'kuantitas' => 3,
            'harga_satuan' => (float) $obat->harga,
            'subtotal' => (float) $obat->harga * 3,
        ]);

        // Cek pengurangan stok fisik (20 - 3 = 17)
        $stokAkhir = StokClient::withoutTenancy()->find($stokRecord->id);
        $this->assertNotNull($stokAkhir);
        $this->assertEquals(17, $stokAkhir->jumlah_stok);
    }

    public function test_checkout_fails_atomically_if_stock_insufficient(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        // Atur stok hanya sisa 2
        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 2]
        );

        $createOrderAction = app(CreateOrderAction::class);

        $items = [
            [
                'id_obat' => $obat->id_obat,
                'nama_obat' => $obat->nama_obat,
                'harga' => (float) $obat->harga,
                'jumlah' => 5, // Melebihi stok
            ],
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/tidak mencukupi/i');

        $createOrderAction->execute($client->id_client, $pelanggan->id_pengguna, $items);

        // Pastikan stok tidak berkurang
        $stokRecord = StokClient::withoutTenancy()
            ->where('id_obat', $obat->id_obat)
            ->where('id_client', $client->id_client)
            ->first();
        $this->assertEquals(2, $stokRecord?->jumlah_stok);
    }

    public function test_qris_emvco_builder_produces_standard_emvco_string(): void
    {
        $orderNumber = '#ORD-20261105-ABCD';
        $amount = 75000.0;
        $nmid = 'ID1020000012345';

        $qrisString = QrisEmvcoBuilder::build($nmid, $orderNumber, $amount);

        // Standard EMVCo checks
        $this->assertStringStartsWith('000201010212', $qrisString);
        $this->assertStringContainsString('52045912', $qrisString); // MCC 5912: Apotek
        $this->assertStringContainsString('5303360', $qrisString);  // Currency IDR 360
        $this->assertStringContainsString('5802ID', $qrisString);   // Country ID
        $this->assertStringContainsString('6304', $qrisString);     // Tag 63 CRC16 length 04

        // Panjang CRC16 harus 4 hex characters
        $crc = substr($qrisString, -4);
        $this->assertMatchesRegularExpression('/^[0-9A-F]{4}$/', $crc);
    }

    public function test_payment_webhook_with_valid_hmac_signature_settles_order(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 1],
        ]);

        $payloadData = [
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ];
        $payload = (string) json_encode($payloadData);
        $secret = (string) config('services.qris.webhook_secret', 'omnipharma_secret_key');
        $signature = hash_hmac('sha256', $payload, $secret);
        Event::fake([OrderStatusUpdated::class]);

        $response = $this->withHeaders([
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/qris', $payloadData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'diproses']);

        // Pastikan status pesanan berubah menjadi diproses
        $freshPesanan = Pesanan::withoutTenancy()->find($pesanan->id_pesanan);
        $this->assertNotNull($freshPesanan);
        $this->assertEquals('diproses', $freshPesanan->status);
        $this->assertTrue($freshPesanan->isLunas());

        // Pastikan catatan pembayaran tersimpan
        $this->assertDatabaseHas('pembayaran', [
            'id_pesanan' => $pesanan->id_pesanan,
            'metode_pembayaran' => 'QRIS',
            'status' => 'lunas',
        ]);

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($pesanan) {
            return $event->pesanan->nomor_pesanan === $pesanan->nomor_pesanan;
        });
    }

    public function test_payment_webhook_with_invalid_signature_is_rejected(): void
    {
        $payloadData = [
            'order_number' => '#ORD-FAKE-0001',
            'status' => 'PAID',
        ];

        $response = $this->withHeaders([
            'X-Signature' => 'bogus-invalid-signature',
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/qris', $payloadData);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid signature']);
    }

    public function test_payment_webhook_is_idempotent(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 1],
        ]);

        $payloadData = [
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ];
        $payload = (string) json_encode($payloadData);
        $secret = (string) config('services.qris.webhook_secret', 'omnipharma_secret_key');
        $signature = hash_hmac('sha256', $payload, $secret);

        // Panggilan pertama
        $res1 = $this->withHeaders(['X-Signature' => $signature])->postJson('/api/webhooks/qris', $payloadData);
        $res1->assertStatus(200);

        // Panggilan kedua (idempotency check)
        $res2 = $this->withHeaders(['X-Signature' => $signature])->postJson('/api/webhooks/qris', $payloadData);
        $res2->assertStatus(200);
        $res2->assertJson(['message' => 'Pesanan sudah diproses sebelumnya (Idempotent)']);

        // Record pembayaran tetap hanya 1 (tidak duplikat)
        $pembayaranCount = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)->count();
        $this->assertEquals(1, $pembayaranCount);
    }

    public function test_customer_web_routes_render_cart_checkout_and_payment(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $sessionId = 'omni-test-session-1234567890123456';

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        // 1. Render halaman cart
        $cartRes = $this->withCookies(['omni_session' => $sessionId])->get('/klien/'.$client->slug.'/cart');
        $cartRes->assertStatus(200);
        $cartRes->assertSee('Keranjang Belanja');

        // 2. Tambah obat via post
        $addRes = $this->withCookies(['omni_session' => $sessionId])->post('/klien/'.$client->slug.'/cart/add', [
            'id_obat' => $obat->id_obat,
            'jumlah' => 2,
        ]);
        $addRes->assertRedirect('/klien/'.$client->slug.'/cart');

        // 3. Checkout screen
        $checkoutRes = $this->withCookies(['omni_session' => $sessionId])->get('/klien/'.$client->slug.'/checkout');
        $checkoutRes->assertStatus(200);
        $checkoutRes->assertSee('Konfirmasi Pesanan');

        // 4. Submit checkout
        $processRes = $this->withCookies(['omni_session' => $sessionId])->post('/klien/'.$client->slug.'/checkout', [
            'metode_layanan' => 'click_collect',
            'nama_pemesan' => 'Budi Santoso',
            'telepon' => '081234567890',
        ]);

        $pesanan = Pesanan::withoutTenancy()->where('status', 'menunggu_pembayaran')->firstOrFail();
        $processRes->assertRedirect('/klien/'.$client->slug.'/order/'.$pesanan->id_pesanan.'/payment');

        // 5. Payment screen
        $paymentRes = $this->withCookies(['omni_session' => $sessionId])->get('/klien/'.$client->slug.'/order/'.$pesanan->id_pesanan.'/payment');
        $paymentRes->assertStatus(200);
        $paymentRes->assertSee('Pembayaran QRIS Dinamis');
        $paymentRes->assertSee($pesanan->nomor_pesanan);
    }

    public function test_order_cancellation_restores_physical_stock_atomically(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        $createAction = app(CreateOrderAction::class);
        $pesanan = $createAction->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 3],
        ]);

        // Stok berkurang jadi 47
        $stokAfterCheckout = StokClient::withoutTenancy()
            ->where('id_obat', $obat->id_obat)
            ->where('id_client', $client->id_client)
            ->value('jumlah_stok');
        $this->assertEquals(47, $stokAfterCheckout);

        // Batalkan pesanan
        $cancelAction = app(CancelOrderAction::class);
        $cancelAction->execute($pesanan, 'Pelanggan membatalkan pesanan');

        // Status berubah jadi dibatalkan
        $freshPesanan = Pesanan::withoutTenancy()->find($pesanan->id_pesanan);
        $this->assertNotNull($freshPesanan);
        $this->assertEquals('dibatalkan', $freshPesanan->status);

        // Stok fisik kembali jadi 50
        $stokAfterCancel = StokClient::withoutTenancy()
            ->where('id_obat', $obat->id_obat)
            ->where('id_client', $client->id_client)
            ->value('jumlah_stok');
        $this->assertEquals(50, $stokAfterCancel);
    }

    public function test_payment_page_rejects_cross_tenant_access_preventing_idor(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();
        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $clientB->id_client],
            ['jumlah_stok' => 50]
        );

        // Buat pesanan di Cabang B (Rogojampi)
        $createAction = app(CreateOrderAction::class);
        $pesananB = $createAction->execute($clientB->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 1],
        ]);

        // Buka lewat URL Cabang A (Banyuwangi Kota) -> Harus 404 (IDOR dicegah)
        $response = $this->get('/klien/'.$clientA->slug.'/order/'.$pesananB->id_pesanan.'/payment');
        $response->assertStatus(404);
    }

    public function test_cart_items_include_product_cover_and_metadata(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();

        $cartService = app(CartService::class);
        $sessionId = 'cover-session-'.Str::random(8);
        $cartService->addItem($sessionId, $clientA->id_client, $obat->id_obat, 2);

        $items = $cartService->getItems($sessionId, $clientA->id_client);

        $this->assertCount(1, $items);
        $this->assertNotEmpty($items[0]['url_gambar']);
        $this->assertSame($obat->url_gambar, $items[0]['url_gambar']);
        $this->assertSame($obat->slug ?: $obat->id_obat, $items[0]['slug']);
        $this->assertArrayHasKey('kategori', $items[0]);
        $this->assertArrayHasKey('satuan', $items[0]);
    }

    public function test_delivery_checkout_calculates_distance_based_ongkir_and_renders_google_maps_routes(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $customer = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $obat = Obat::firstOrFail();
        $sessionId = 'delivery-session-'.Str::random(10);

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 30]
        );

        // Tambah item ke keranjang sebagai pelanggan dengan cookie sesi terenkripsi konsisten
        $addResponse = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->postJson(route('keranjang.add'), [
                'id_obat' => $obat->id_obat,
                'jumlah' => 2,
            ]);
        $addResponse->assertOk();

        // Verifikasi halaman checkout memiliki isian alamat, paste link Google Maps, Ongkir & cover obat tanpa banner GPS popup
        $checkoutPage = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->get(route('checkout.index'));
        $checkoutPage->assertOk();
        $checkoutPage->assertDontSee("Izinkan Lokasi, atau geser pin langsung di peta.'; } else");
        $checkoutPage->assertDontSee('parseMapsInput() { const raw =');
        $checkoutPage->assertDontSee('Aktifkan Akses GPS Lokasi Anda');
        $checkoutPage->assertSee('Isi Alamat &amp; Link Lokasi Pengiriman', false);
        $checkoutPage->assertSee('Isi Alamat Lengkap Pengiriman');
        $checkoutPage->assertSee('Paste Link Lokasi Google Maps Saat Ini');
        $checkoutPage->assertSee('Buka Google Maps');
        $checkoutPage->assertSee('Tempel Link');
        $checkoutPage->assertSee('Lihat Rute Pengiriman di Google Maps');

        // Proses checkout metode delivery jarak 4 km => 5000 + ceil(4-2)*2500 = 10000
        $processResponse = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->post(route('checkout.store'), [
                'metode_layanan' => 'delivery',
                'jarak_km' => 4,
                'koordinat_tujuan' => '-8.2050,114.3750',
                'alamat_pengantaran' => 'Jl. Ahmad Yani No. 10',
                'nama_pemesan' => 'Guntur Afandy',
                'telepon' => '081234567890',
                'catatan' => 'Tolong hubungi saat tiba',
            ]);
        $processResponse->assertRedirect();
        $redirectPath = (string) parse_url((string) $processResponse->headers->get('Location'), PHP_URL_PATH);
        $this->assertStringContainsString('/pembayaran/', $redirectPath, 'Checkout redirect target: '.$redirectPath);
        $orderId = basename($redirectPath);

        $pesanan = Pesanan::withoutTenancy()->findOrFail($orderId);
        $expectedSubtotal = (float) $obat->harga * 2;
        $expectedRouteUrl = 'https://www.google.com/maps/dir/?api=1&origin=-8.2192,114.3691&destination=-8.205,114.375&travelmode=driving';

        $this->assertSame('delivery', $pesanan->metode_layanan);
        $this->assertSame(10000.0, $pesanan->ongkos_kirim);
        $this->assertSame(4.0, $pesanan->jarak_km);
        $this->assertSame('-8.205,114.375', $pesanan->koordinat_tujuan);
        $this->assertSame('Jl. Ahmad Yani No. 10', $pesanan->alamat_pengantaran);
        $this->assertSame('Tolong hubungi saat tiba', $pesanan->catatan_bersih);
        $this->assertSame($expectedRouteUrl, $pesanan->maps_route_url);
        $this->assertEquals($expectedSubtotal + 10000.0, $pesanan->total_harga);

        // Pembayaran & Lacak Pesanan menampilkan rincian ongkir + rute Google Maps kurir tanpa kebocoran atribut HTML
        $paymentPage = $this->actingAs($customer)->get(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $paymentPage->assertOk();
        $paymentPage->assertSee('window.omniPaymentPageData');
        $paymentPage->assertDontSee('body: `{"order_number":', false);
        $paymentPage->assertSee('Cek Status');
        $paymentPage->assertSee('Batalkan Pesanan');
        $paymentPage->assertDontSee('Simulasi Scan & Bayar Sekarang (Lunas)');
        $paymentPage->assertDontSee('Pantau Status Pesanan & Antrean');
        $paymentPage->assertSee('Rp 10.000');
        $paymentPage->assertSee('Buka Rute Kurir di Google Maps');
        $paymentPage->assertSee($expectedRouteUrl);

        $trackPage = $this->actingAs($customer)->get(route('pesanan.track', ['id' => $pesanan->id_pesanan]));
        $trackPage->assertOk();
        $trackPage->assertSee('Buka Rute Google Maps (Pengirim)');
        $trackPage->assertSee($expectedRouteUrl);
        $trackPage->assertSee('Invoice Resmi Apotek');
        $trackPage->assertSee('Cetak Invoice');
        $trackPage->assertDontSee('##ORD-');
        $trackPage->assertSee('Riwayat Pesanan');
        $trackPage->assertSee('Putih');
        $trackPage->assertSee('Dark');
        $trackPage->assertDontSee('Halaman Sambutan Utama');

        // Panel Antrean Apoteker / Pengirim menampilkan tombol langsung Buka Rute Maps
        $queuePage = $this->actingAs($apoteker)->get(route('tenant.antrean.index', ['client' => $client->slug]));
        $queuePage->assertOk();
        $queuePage->assertSee('Kurir (4 km)');
        $queuePage->assertSee('Buka Rute Maps');
        $queuePage->assertSee($expectedRouteUrl);
    }

    public function test_customer_checkout_accepts_manual_address_and_pasted_google_maps_shortlink(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $customer = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();
        $obat = Obat::firstOrFail();
        $sessionId = 'shortlink-session-'.Str::random(10);

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 25]
        );

        $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->postJson(route('keranjang.add'), [
                'id_obat' => $obat->id_obat,
                'jumlah' => 1,
            ])
            ->assertOk();

        $shortlink = 'https://maps.app.goo.gl/ContohLokasiRumah99';

        $response = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->post(route('checkout.store'), [
                'metode_layanan' => 'delivery',
                'jarak_km' => 3,
                'link_maps' => $shortlink,
                'alamat_pengantaran' => 'Jl. Sudirman No. 45 Banyuwangi',
                'nama_pemesan' => 'Guntur Afandy',
                'telepon' => '081234567890',
            ]);

        $response->assertRedirect();
        $orderId = basename((string) parse_url((string) $response->headers->get('Location'), PHP_URL_PATH));
        $pesanan = Pesanan::withoutTenancy()->findOrFail($orderId);

        $this->assertSame('delivery', $pesanan->metode_layanan);
        $this->assertSame(7500.0, $pesanan->ongkos_kirim);
        $this->assertSame($shortlink, $pesanan->maps_route_url);
        $this->assertSame('Jl. Sudirman No. 45 Banyuwangi', $pesanan->alamat_pengantaran);
        $this->assertNull($pesanan->koordinat_tujuan);
    }

    public function test_midtrans_sandbox_qris_charge_status_check_and_webhook_settlement(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 2],
        ]);

        config([
            'services.midtrans.server_key' => 'Mid-server-test-sandbox-key',
            'services.midtrans.client_key' => 'Mid-client-test-sandbox-key',
            'services.midtrans.merchant_id' => 'G484050115',
            'services.midtrans.is_production' => false,
        ]);

        $midtransOrderId = ltrim((string) $pesanan->nomor_pesanan, '#');
        Cache::forget("midtrans_qris:{$pesanan->id_pesanan}");
        Cache::forget("midtrans_order_id:{$pesanan->id_pesanan}");

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_id' => 'tx-midtrans-sandbox-001',
                'order_id' => $midtransOrderId,
                'merchant_id' => 'G484050115',
                'gross_amount' => (string) (int) round((float) $pesanan->total_harga).'.00',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'acquirer' => 'gopay',
                'qr_string' => '000201010212MOCKMIDTRANSQRSTRING',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => 'https://api.sandbox.midtrans.com/v2/qris/tx-midtrans-sandbox-001/qr-code',
                    ],
                ],
                'expiry_time' => now()->addMinutes(30)->toDateTimeString(),
            ], 200),
            "https://api.sandbox.midtrans.com/v2/{$midtransOrderId}/status" => Http::sequence()
                ->push([
                    'status_code' => '201',
                    'order_id' => $midtransOrderId,
                    'transaction_status' => 'pending',
                ], 200)
                ->push([
                    'status_code' => '200',
                    'order_id' => $midtransOrderId,
                    'transaction_status' => 'settlement',
                    'gross_amount' => (string) (int) round((float) $pesanan->total_harga).'.00',
                ], 200),
        ]);

        // 1. Halaman pembayaran memanggil Midtrans /v2/charge & menampilkan badge Midtrans Sandbox
        $paymentPage = $this->get(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $paymentPage->assertOk();
        $paymentPage->assertSee('MIDTRANS SANDBOX');
        $paymentPage->assertSee('https://api.sandbox.midtrans.com/v2/qris/tx-midtrans-sandbox-001/qr-code');

        // 2. Cek status pertama masih pending
        $pendingCheck = $this->postJson(route('pesanan.cek-status', ['id' => $pesanan->id_pesanan]));
        $pendingCheck->assertOk()->assertJson([
            'paid' => false,
            'midtrans_status' => 'pending',
        ]);
        $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status);

        // 3. Cek status kedua setelah bayar di Simulator -> settlement -> lunas!
        Event::fake([OrderStatusUpdated::class]);
        $settledCheck = $this->postJson(route('pesanan.cek-status', ['id' => $pesanan->id_pesanan]));
        $settledCheck->assertOk()->assertJson([
            'paid' => true,
            'source' => 'midtrans_sandbox',
            'status' => 'diproses',
        ]);
        $this->assertSame('diproses', $pesanan->fresh()->status);
        Event::assertDispatched(OrderStatusUpdated::class);

        // 4. Uji webhook Midtrans SHA-512 signature pada pesanan baru
        $pesanan2 = $action->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 1],
        ]);
        $order2Id = ltrim((string) $pesanan2->nomor_pesanan, '#');
        $gross2 = (string) (int) round((float) $pesanan2->total_harga).'.00';
        $sig2 = hash('sha512', $order2Id.'200'.$gross2.'Mid-server-test-sandbox-key');

        $webhookRes = $this->postJson('/api/webhooks/qris', [
            'order_id' => $order2Id,
            'status_code' => '200',
            'gross_amount' => $gross2,
            'signature_key' => $sig2,
            'transaction_status' => 'settlement',
        ]);
        $webhookRes->assertOk()->assertJson(['success' => true, 'status' => 'diproses']);
        $this->assertSame('diproses', $pesanan2->fresh()->status);
    }

    public function test_order_history_page_renders_customer_orders_and_clean_profile_dropdown(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $customer = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();
        $obat = Obat::firstOrFail();
        $sessionId = 'history-session-'.Str::random(10);

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 25]
        );

        $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->postJson(route('keranjang.add'), [
                'id_obat' => $obat->id_obat,
                'jumlah' => 1,
            ])
            ->assertOk();

        $checkoutRes = $this->actingAs($customer)
            ->withCredentials()
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->post(route('checkout.store'), [
                'metode_layanan' => 'click_collect',
                'nama_pemesan' => 'Guntur Afandy',
                'telepon' => '081234567890',
            ]);
        $checkoutRes->assertRedirect();
        $orderId = basename((string) parse_url((string) $checkoutRes->headers->get('Location'), PHP_URL_PATH));
        $pesanan = Pesanan::withoutTenancy()->findOrFail($orderId);

        $historyRes = $this->actingAs($customer)
            ->withCookie('omnipharma_customer_session', $sessionId)
            ->get(route('pesanan.riwayat'));
        $historyRes->assertOk();
        $historyRes->assertSee('Riwayat Pesanan');
        $historyRes->assertSee(ltrim((string) $pesanan->nomor_pesanan, '#'));
        $historyRes->assertSee('Lihat Invoice');
        $historyRes->assertSee('Profile');
        $historyRes->assertSee('Putih');
        $historyRes->assertSee('Dark');
        $historyRes->assertDontSee('Halaman Sambutan Utama');
    }

    public function test_qris_expiry_persists_across_refresh_and_auto_poll_does_not_auto_settle_pending_order(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute($client->id_client, $pelanggan->id_pengguna, [
            ['id_obat' => $obat->id_obat, 'nama_obat' => $obat->nama_obat, 'harga' => (float) $obat->harga, 'jumlah' => 1],
        ]);

        // Simulasikan pesanan dibuat 10 menit lalu, lalu halaman pembayaran di-refresh
        $pesanan->forceFill(['dibuat_pada' => now()->subMinutes(10)])->save();
        Cache::forget("qris_expiry_ts:{$pesanan->id_pesanan}");
        Cache::forget("midtrans_qris:{$pesanan->id_pesanan}");

        config(['services.midtrans.server_key' => '']);

        $firstLoad = $this->get(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $firstLoad->assertOk();
        $firstHtml = (string) $firstLoad->getContent();
        $this->assertMatchesRegularExpression('/remainingSeconds(?:\\\\u0022|"):\s*(11[89]\d|1200)/', $firstHtml);
        $this->assertMatchesRegularExpression('/>\s*(19:[45]\d|20:00)\s*</', $firstHtml);
        $this->assertDoesNotMatchRegularExpression('/>\s*30:00\s*</', $firstHtml);

        // Refresh ulang tetap mempertahankan sisa waktu ~20 menit, tidak reset ke 30:00
        $secondLoad = $this->get(route('pembayaran.show', ['id' => $pesanan->id_pesanan]));
        $secondLoad->assertOk();
        $this->assertMatchesRegularExpression('/remainingSeconds(?:\\\\u0022|"):\s*(11[89]\d|1200)/', (string) $secondLoad->getContent());

        // Polling otomatis setiap 3 detik (auto_timer=true, timer_expired=false) tidak boleh langsung melunaskan pesanan
        $autoPollLocal = $this->postJson(route('pesanan.cek-status', ['id' => $pesanan->id_pesanan]), [
            'auto_timer' => true,
            'timer_expired' => false,
        ]);
        $autoPollLocal->assertOk()->assertJson(['paid' => false]);
        $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status);

        // Saat Midtrans Sandbox aktif namun endpoint status sementara gagal/null, jangan auto-lunas
        config(['services.midtrans.server_key' => 'Mid-server-test-sandbox-key']);
        Http::fake([
            'https://api.sandbox.midtrans.com/*' => Http::response(['status_code' => '404'], 404),
        ]);
        $midtransNullRes = $this->postJson(route('pesanan.cek-status', ['id' => $pesanan->id_pesanan]), [
            'auto_timer' => true,
        ]);
        $midtransNullRes->assertOk()->assertJson([
            'paid' => false,
            'midtrans_status' => 'pending',
        ]);
        $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Payments\Services\QrisEmvcoBuilder;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $pesanan = $createOrderAction->execute($client->id_client, $pelanggan->id_pengguna, $items, 'click_collect');

        $this->assertNotNull($pesanan);
        $this->assertStringStartsWith('#ORD-', $pesanan->nomor_pesanan);
        $this->assertEquals('menunggu_pembayaran', $pesanan->status);
        $this->assertEquals('click_collect', $pesanan->metode_layanan);

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

        // Pastikan stok obat tersedia di cabang ini agar checkout berhasil
        \App\Models\StokClient::updateOrCreate(
            [
                'id_client' => $client->id_client,
                'id_obat' => $obat->id_obat,
            ],
            [
                'jumlah_stok' => 100,
            ]
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

        // Pastikan stok obat tersedia di Cabang B sebelum membuat pesanan
        \App\Models\StokClient::updateOrCreate(
            [
                'id_client' => $clientB->id_client,
                'id_obat' => $obat->id_obat,
            ],
            [
                'jumlah_stok' => 50,
            ]
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
}

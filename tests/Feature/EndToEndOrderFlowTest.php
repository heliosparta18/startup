<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_complete_end_to_end_order_and_period_closing_flow(): void
    {
        // 1. Data Awal Cabang & Obat
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $obat = Obat::firstOrFail();
        $pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();
        $superAdminUser = User::where('role', 'super_admin')->firstOrFail();

        // Pastikan stok fisik tersedia
        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $obat->id_obat, 'id_client' => $client->id_client],
            ['jumlah_stok' => 50]
        );

        // 2. Pasien Mengunggah Resep Dokter
        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Budi, Sp.PD',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Candi Chier',
            'catatan' => 'Resep untuk pembelian Amodiaquine',
        ]);

        $uploadResponse->assertOk();
        $uploadResponse->assertJson(['success' => true]);

        // 3. Pasien Checkout Atomik
        $sessionId = 'e2e-session-'.Str::random(10);
        $cartService = app(CartService::class);
        $cartService->addItem($sessionId, $client->id_client, $obat->id_obat, 2);

        $createOrderAction = app(CreateOrderAction::class);
        $items = $cartService->getItems($sessionId, $client->id_client);

        $pesanan = $createOrderAction->execute(
            $client->id_client,
            $pelanggan->id_pengguna,
            $items,
            'click_collect',
            $sessionId
        );

        $this->assertNotNull($pesanan);
        $this->assertSame('menunggu_pembayaran', $pesanan->status);
        $this->assertSame($sessionId, $pesanan->token_sesi);

        // 4. Pembayaran via Webhook QRIS & Siaran Reverb
        Event::fake([OrderStatusUpdated::class]);

        $payloadData = [
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'amount' => (float) $pesanan->total_harga,
        ];
        $payload = (string) json_encode($payloadData);
        $secret = (string) config('services.qris.webhook_secret', 'omnipharma_secret_key');
        $signature = hash_hmac('sha256', $payload, $secret);

        $webhookResponse = $this->withHeaders([
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/qris', $payloadData);

        $webhookResponse->assertOk();
        $webhookResponse->assertJson(['success' => true, 'status' => 'diproses']);

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($pesanan) {
            return $event->pesanan->nomor_pesanan === $pesanan->nomor_pesanan;
        });

        // Verifikasi status pesanan lunas
        $freshPesanan = Pesanan::withoutTenancy()->find($pesanan->id_pesanan);
        $this->assertSame('diproses', $freshPesanan->status);

        // 5. Penutupan Buku Kas Admin & Snapshot Immutability (ADR-0008)
        $this->actingAs($superAdminUser);

        $closeAction = app(CloseSalesPeriodAction::class);
        $ringkasan = $closeAction->execute(
            $client->id_client,
            now()->startOfMonth()->toDateString(),
            now()->toDateString()
        );

        $this->assertNotNull($ringkasan);
        $this->assertTrue($ringkasan->status_terkunci);
        $this->assertDatabaseHas('ringkasan_periode_penjualan', [
            'id_client' => $client->id_client,
            'status_terkunci' => true,
        ]);
    }
}

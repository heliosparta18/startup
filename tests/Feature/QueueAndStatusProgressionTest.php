<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QueueAndStatusProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected Obat $obat;

    protected Pengguna $pelanggan;

    protected User $pharmacistUser;

    protected User $superAdminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);

        $this->client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $this->obat = Obat::firstOrFail();
        $this->pelanggan = Pengguna::where('peran', 'pelanggan')->firstOrFail();

        // Pastikan stok fisik tersedia
        StokClient::withoutTenancy()->updateOrCreate(
            ['id_obat' => $this->obat->id_obat, 'id_client' => $this->client->id_client],
            ['jumlah_stok' => 50]
        );

        $this->pharmacistUser = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $this->superAdminUser = User::where('email', 'daffa@omnipharma.test')->firstOrFail();
    }

    public function test_order_creation_assigns_slot_waktu_and_virtual_queue_number(): void
    {
        $action = app(CreateOrderAction::class);

        $items = [
            [
                'id_obat' => $this->obat->id_obat,
                'nama_obat' => $this->obat->nama_obat,
                'harga' => (float) $this->obat->harga,
                'jumlah' => 2,
            ],
        ];

        $pesanan = $action->execute(
            $this->client->id_client,
            $this->pelanggan->id_pengguna,
            $items,
            'click_collect',
            'session-123',
            'Hari ini, 13:00 - 15:00 WIB'
        );

        $this->assertNotNull($pesanan);
        $this->assertSame('Hari ini, 13:00 - 15:00 WIB', $pesanan->slot_waktu);
        $this->assertStringStartsWith('A-', (string) $pesanan->nomor_antrean);
    }

    public function test_pharmacist_can_advance_order_status_and_dispatches_reverb_event(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute(
            $this->client->id_client,
            $this->pelanggan->id_pengguna,
            [
                [
                    'id_obat' => $this->obat->id_obat,
                    'nama_obat' => $this->obat->nama_obat,
                    'harga' => (float) $this->obat->harga,
                    'jumlah' => 1,
                ],
            ],
            'click_collect'
        );

        $this->actingAs($this->pharmacistUser);

        // Majukan status: diproses -> siap_diambil
        $response = $this->postJson(route('tenant.pesanan.progres-status', [
            'client' => $this->client->slug,
            'id' => $pesanan->id_pesanan,
        ]), [
            'status' => 'siap_diambil',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'status' => 'siap_diambil']);

        $freshPesanan = Pesanan::withoutTenancy()->find($pesanan->id_pesanan);
        $this->assertSame('siap_diambil', $freshPesanan->status);

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($pesanan) {
            return $event->pesanan->id_pesanan === $pesanan->id_pesanan;
        });

        // Selesaikan penyerahan obat: siap_diambil -> selesai
        $finishResponse = $this->postJson(route('tenant.pesanan.progres-status', [
            'client' => $this->client->slug,
            'id' => $pesanan->id_pesanan,
        ]), [
            'status' => 'selesai',
        ]);

        $finishResponse->assertOk();
        $this->assertSame('selesai', Pesanan::withoutTenancy()->find($pesanan->id_pesanan)->status);
    }

    public function test_pharmacist_can_update_current_serving_queue_and_add_walkin(): void
    {
        $this->actingAs($this->pharmacistUser);

        // 1. Update nomor antrean berjalan manual
        $response = $this->postJson(route('tenant.antrean.update', ['client' => $this->client->slug]), [
            'action' => 'set_nomor',
            'nomor_antrean' => 'A-010',
        ]);

        $response->assertOk();
        $this->assertSame('A-010', Cache::get("antrean_berjalan:{$this->client->id_client}"));

        // 2. Panggil antrean berikutnya (+1)
        $nextResponse = $this->postJson(route('tenant.antrean.update', ['client' => $this->client->slug]), [
            'action' => 'panggil_berikutnya',
        ]);

        $nextResponse->assertOk();
        $this->assertSame('A-011', Cache::get("antrean_berjalan:{$this->client->id_client}"));

        // 3. Tambah antrean walk-in
        $walkinResponse = $this->post(route('tenant.antrean.walkin', ['client' => $this->client->slug]), [
            'nama_pasien' => 'Pak Ahmad Walkin',
            'catatan' => 'Beli vitamin di apotek',
        ]);

        $walkinResponse->assertRedirect();
        $this->assertDatabaseHas('pesanan', [
            'id_client' => $this->client->id_client,
            'status' => 'diproses',
            'slot_waktu' => 'Walk-In Langsung di Apotek',
        ]);
    }

    public function test_super_admin_can_access_read_only_monitoring_progres(): void
    {
        $this->actingAs($this->superAdminUser);

        $response = $this->get(route('admin.progres.index'));

        $response->assertOk();
        $response->assertSee('Monitoring Progres Obat');
        $response->assertSee('Live Farmasi');
        $response->assertSee('OBAT SEDANG DIRACIK / DIPROSES');
    }

    public function test_customer_can_view_order_tracking_with_virtual_queue(): void
    {
        $action = app(CreateOrderAction::class);
        $pesanan = $action->execute(
            $this->client->id_client,
            $this->pelanggan->id_pengguna,
            [
                [
                    'id_obat' => $this->obat->id_obat,
                    'nama_obat' => $this->obat->nama_obat,
                    'harga' => (float) $this->obat->harga,
                    'jumlah' => 1,
                ],
            ],
            'click_collect',
            'session-guest-99',
            'Hari ini, 16:00 - 18:00 WIB',
            'A-025'
        );

        $response = $this->get(route('customer.order.track', [
            'client' => $this->client->slug,
            'id' => $pesanan->id_pesanan,
        ]));

        $response->assertOk();
        $response->assertSee('A-025');
        $response->assertSee('Hari ini, 16:00 - 18:00 WIB');
        $response->assertSee('Nomor Antrean Anda');
    }

    public function test_dashboard_hides_super_admin_and_live_monitoring_from_pharmacists(): void
    {
        // 1. Apoteker Banyuwangi
        $this->actingAs($this->pharmacistUser);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Telaah Resep Dokter &amp; FEFO', false);
        $response->assertSee('Antrean &amp; Progres Status', false);
        $response->assertDontSee('Super Admin Eksekutif');
        $response->assertDontSee('Live Monitoring Obat Lintas Cabang');

        // 2. Apoteker Rogojampi
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();
        $this->actingAs($apotekerRogojampi);
        $responseRogojampi = $this->get(route('dashboard'));

        $responseRogojampi->assertOk();
        $responseRogojampi->assertSee('Cabang Rogojampi');
        $responseRogojampi->assertDontSee('Super Admin Eksekutif');
        $responseRogojampi->assertDontSee('Live Monitoring Obat Lintas Cabang');

        // 3. Super Admin
        $this->actingAs($this->superAdminUser);
        $responseSuperAdmin = $this->get(route('dashboard'));

        $responseSuperAdmin->assertOk();
        $responseSuperAdmin->assertSee('Super Admin Eksekutif &amp; KPI', false);
        $responseSuperAdmin->assertSee('Live Monitoring Obat Lintas Cabang');
    }
}

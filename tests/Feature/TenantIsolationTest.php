<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Exceptions\TenantAccessDeniedException;
use App\Support\Tenant\TenantContext;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_tenant_context_resolves_active_client_from_route_parameter(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->get('/klien/'.$client->slug);

        $response->assertStatus(200);
        $this->assertEquals($client->id_client, app(TenantManagerInterface::class)->id());
    }

    public function test_tenant_context_resolves_active_client_from_uuid_parameter(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->get('/klien/'.$client->id_client);

        $response->assertStatus(200);
        $this->assertEquals($client->id_client, app(TenantManagerInterface::class)->id());
    }

    public function test_inactive_tenant_returns_403_forbidden(): void
    {
        $client = Client::create([
            'id_client' => (string) Str::uuid(),
            'nama_client' => 'Cabang Nonaktif',
            'slug' => 'cabang-nonaktif',
            'aktif' => false,
        ]);

        $response = $this->get('/klien/'.$client->slug);

        $response->assertStatus(403);
    }

    public function test_non_existent_tenant_returns_404_not_found(): void
    {
        $response = $this->get('/klien/cabang-fiktif-tidak-ada');

        $response->assertStatus(404);
    }

    public function test_tenant_scope_automatically_filters_orders_by_active_client(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        app(TenantManagerInterface::class)->set($clientA);
        $ordersA = Pesanan::all();

        $this->assertNotEmpty($ordersA);
        foreach ($ordersA as $order) {
            $this->assertEquals($clientA->id_client, $order->id_client);
            $this->assertNotEquals($clientB->id_client, $order->id_client);
        }
    }

    public function test_tenant_scope_automatically_assigns_client_id_on_creating(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $pelanggan = Pengguna::first() ?? Pengguna::factory()->create();
        app(TenantManagerInterface::class)->set($client);

        $pesanan = Pesanan::create([
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => $pelanggan->id_pengguna,
            'nomor_pesanan' => '#ORD-TEST-0001',
            'status' => 'menunggu_verifikasi',
            'metode_layanan' => 'ambil_di_tempat',
        ]);

        $this->assertEquals($client->id_client, $pesanan->id_client);
    }

    public function test_tenant_scope_rejects_payload_tampering_with_different_client_id(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();
        $pelanggan = Pengguna::first() ?? Pengguna::factory()->create();

        app(TenantManagerInterface::class)->set($clientA);

        $this->expectException(TenantAccessDeniedException::class);

        // Percobaan injeksi ID cabang B pada konteks aktif cabang A
        Pesanan::create([
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => $pelanggan->id_pengguna,
            'id_client' => $clientB->id_client,
            'nomor_pesanan' => '#ORD-HACK-0001',
            'status' => 'menunggu_verifikasi',
            'metode_layanan' => 'ambil_di_tempat',
        ]);
    }

    public function test_pharmacist_cannot_view_orders_from_another_branch_via_idor(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        $orderB = Pesanan::withoutGlobalScopes()->where('id_client', $clientB->id_client)->first();
        $this->assertNotNull($orderB);

        $apoteker = Pengguna::where('telepon_email', 'candia@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apoteker)
            ->get('/cabang/'.$clientA->slug.'/pesanan/'.$orderB->id_pesanan);

        $response->assertStatus(403);
    }

    public function test_pharmacist_cannot_access_unassigned_branch_portal(): void
    {
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();
        $apotekerBanyuwangi = Pengguna::where('telepon_email', 'candia@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apotekerBanyuwangi)
            ->get('/cabang/'.$clientB->slug.'/dashboard');

        $response->assertStatus(403);
    }

    public function test_customer_in_branch_a_cannot_see_branch_b_stock(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        app(TenantManagerInterface::class)->set($clientA);
        $stokA = StokClient::all();

        $this->assertNotEmpty($stokA);
        foreach ($stokA as $stok) {
            $this->assertEquals($clientA->id_client, $stok->id_client);
            $this->assertNotEquals($clientB->id_client, $stok->id_client);
        }
    }

    public function test_super_admin_can_bypass_tenant_scope_using_without_tenancy(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        app(TenantManagerInterface::class)->set($clientA);

        $allOrders = Pesanan::withoutTenancy()->get();
        $scopedOrders = Pesanan::all();

        $this->assertGreaterThanOrEqual($scopedOrders->count(), $allOrders->count());
    }

    public function test_tenant_context_clear_resets_singleton(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $context = app(TenantManagerInterface::class);

        $context->set($client);
        $this->assertTrue($context->check());

        $context->clear();
        $this->assertFalse($context->check());
    }

    public function test_run_in_context_executes_callback_safely_and_restores_original_context(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        /** @var TenantContext $context */
        $context = app(TenantManagerInterface::class);
        $context->set($clientA);

        $this->assertEquals($clientA->id_client, $context->id());

        $result = $context->runInContext($clientB, function ($switchedClient) use ($clientB) {
            $this->assertEquals($clientB->id_client, app(TenantManagerInterface::class)->id());
            $this->assertEquals($clientB->id_client, $switchedClient->id_client);

            return 'executed_in_rogojampi';
        });

        $this->assertEquals('executed_in_rogojampi', $result);
        // Memastikan konteks awal dikembalikan secara sempurna
        $this->assertEquals($clientA->id_client, $context->id());
    }

    public function test_audit_tenant_isolation_artisan_command_passes(): void
    {
        $this->artisan('tenant:audit')
            ->expectsOutputToContain('Audit SELESAI: Seluruh entitas domain terisolasi sempurna per cabang.')
            ->assertExitCode(0);
    }
}

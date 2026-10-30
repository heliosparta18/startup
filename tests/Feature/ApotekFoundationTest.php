<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\User;
use App\Modules\Admin\AdminServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Ordering\OrderingServiceProvider;
use App\Modules\Payments\PaymentsServiceProvider;
use App\Modules\Prescription\PrescriptionServiceProvider;
use App\Modules\Reporting\ReportingServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class ApotekFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $peran, bool $aktif = true): Pengguna
    {
        return Pengguna::factory()->create([
            'peran' => $peran,
            'aktif' => $aktif,
        ]);
    }

    private function user(string $role, string $status = 'active'): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => $status,
            'email_verified_at' => now(),
        ]);
    }

    public function test_all_six_apotek_module_providers_are_registered(): void
    {
        $providers = array_map('get_class', app()->getProviders(ServiceProvider::class));

        foreach ([
            AdminServiceProvider::class,
            CatalogServiceProvider::class,
            OrderingServiceProvider::class,
            PaymentsServiceProvider::class,
            PrescriptionServiceProvider::class,
            ReportingServiceProvider::class,
        ] as $expectedProvider) {
            $this->assertContains($expectedProvider, $providers, "Provider modul tidak terdaftar: {$expectedProvider}");
        }
    }

    public function test_customer_portal_is_publicly_accessible(): void
    {
        $response = $this->get(route('customer.home', ['client' => 'banyuwangi-kota']));
        $response->assertOk();
        $response->assertSee('OmniPharma');
        $response->assertSee('Amodiaquine 200mg');
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('+ Unggah Resep');
        $response->assertSee('+ Beli Langsung');

        $detailResponse = $this->get(route('customer.obat.detail', ['client' => 'banyuwangi-kota', 'id' => 'sample-id']));
        $detailResponse->assertOk();
    }

    public function test_customer_can_upload_prescription_digitally(): void
    {
        $response = $this->post(route('customer.resep.upload', ['client' => 'banyuwangi-kota']), [
            'namaDokter' => 'dr. Budi, Sp.PD',
            'sipDokter' => '503/SIP/2024',
            'namaPasien' => 'Candi Chier',
            'umurPasien' => '88 th',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_guest_is_redirected_to_login_on_protected_portals(): void
    {
        $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))
            ->assertRedirect(route('login'));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_apoteker_can_access_tenant_portal(): void
    {
        $apoteker = $this->user('apoteker');
        $this->actingAs($apoteker);

        $response = $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
        $response->assertOk();
        $response->assertSee('Portal Apoteker Cabang');
        $response->assertSee('#ORD-20260917-0042');
        $response->assertSee('Peringatan Batch Kedaluwarsa (FEFO)');
        $response->assertSee('#1 FEFO');
    }

    public function test_super_admin_can_access_admin_portal(): void
    {
        $superAdmin = $this->user('super_admin');
        $this->actingAs($superAdmin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Executive Dashboard');
        $response->assertSee('142.850.000');
        $response->assertSee('1.840 Pesanan');
        $response->assertSee('Cabang Banyuwangi Kota');
        $response->assertSee('Cabang Rogojampi');
    }

    public function test_cross_role_access_is_forbidden_with_403(): void
    {
        // Apoteker tidak boleh masuk ke executive admin dashboard
        $this->actingAs($this->user('apoteker'));
        $this->get(route('admin.dashboard'))->assertForbidden();

        // Super Admin tanpa peran apoteker tidak boleh masuk ke portal apoteker cabang
        $this->actingAs($this->user('super_admin'));
        $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();

        // Pelanggan tidak boleh masuk ke tenant maupun admin
        $this->actingAs($this->user('pelanggan'));
        $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_inactive_user_is_forbidden(): void
    {
        $inactiveApoteker = $this->user('apoteker', status: 'suspended');
        $this->actingAs($inactiveApoteker);

        $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();
    }

    public function test_pharmacist_can_verify_prescription_endpoint(): void
    {
        $this->actingAs($this->user('apoteker'));

        $response = $this->post(route('tenant.resep.verifikasi', [
            'client' => 'banyuwangi-kota',
            'id' => 'ORD-20260917-0042',
        ]), [
            'action' => 'disetujui',
            'catatan' => 'Resep sah dan dosis terverifikasi.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status_verifikasi' => 'disetujui',
        ]);
    }

    public function test_admin_can_execute_period_closing_endpoint(): void
    {
        $this->actingAs($this->user('super_admin'));

        $response = $this->post(route('admin.periode.tutup'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status_terkunci' => true,
        ]);
    }

    public function test_admin_can_export_report_endpoint(): void
    {
        $this->actingAs($this->user('super_admin'));

        $response = $this->get(route('admin.laporan.ekspor'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'format' => 'xlsx',
        ]);
    }
}

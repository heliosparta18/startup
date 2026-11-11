<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiRoleDashboardFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    /**
     * 1. Tamu dapat mengakses halaman sambutan dengan logo OmniPharma, informasi cabang,
     * serta panel pilihan login demo 1-klik untuk 4 peran pengguna.
     */
    public function test_guest_can_access_welcome_page_with_demo_login_options(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('OmniPharma');
        $response->assertSee('Apotek Terdaftar');
        $response->assertSee('Banyuwangi &amp; Rogojampi', false);
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');

        // Memastikan tombol demo login 1-klik untuk 4 peran pengguna tersedia pada halaman sambutan
        $response->assertSee('daffa@omnipharma.test');
        $response->assertSee('bambang@omnipharma.test');
        $response->assertSee('dewi@omnipharma.test');
        $response->assertSee('gunturafandy5@gmail.com');
    }

    /**
     * 2. Pelanggan biasa (pasien) dapat login 1-klik dan dialihkan ke dashboard pelanggan
     * dengan melihat profil, status keranjang, tautan belanja, serta kartu portal layanan.
     */
    public function test_pelanggan_can_login_and_access_customer_dashboard(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'gunturafandy5@gmail.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Guntur Afandy');
        $dashboardResponse->assertSee('Dashboard Pelanggan');
        $dashboardResponse->assertSee('Mulai Belanja Obat');
        $dashboardResponse->assertSee('Lihat Keranjang');
        $dashboardResponse->assertSee('Katalog Obat Cabang Bwi');
        $dashboardResponse->assertSee('Katalog Cabang Rogojampi');

        // Memastikan visual guard muncul untuk peran yang membutuhkan hak akses lebih tinggi
        $dashboardResponse->assertSee('Khusus Apoteker');
        $dashboardResponse->assertSee('Khusus Admin');
    }

    /**
     * 3. Apoteker Penanggung Jawab (APJ) dapat login dan dialihkan langsung ke dashboard tenant
     * cabang Banyuwangi Kota untuk telaah resep dan inspeksi FEFO.
     */
    public function test_apoteker_can_login_and_access_tenant_dashboard(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'bambang@omnipharma.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
        $this->assertAuthenticated();

        $tenantResponse = $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
        $tenantResponse->assertOk();
        $tenantResponse->assertSee('Portal Apoteker Cabang');
        $tenantResponse->assertSee('Banyuwangi Kota');
        $tenantResponse->assertSee('FEFO');
    }

    /**
     * 4. Super Admin Eksekutif dapat login dan dialihkan langsung ke dashboard admin
     * untuk melihat agregasi omzet dan kontrol tutup periode penjualan.
     */
    public function test_super_admin_can_login_and_access_admin_dashboard(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'daffa@omnipharma.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $adminResponse = $this->get(route('admin.dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Executive Dashboard');
        $adminResponse->assertSee('Agregasi Cabang');
        $adminResponse->assertSee('M. Daffa Ariby');
    }

    /**
     * 5. Kasir / Staf Administrasi dapat login dan mengakses buku kasir & rekonsiliasi pembayaran.
     */
    public function test_kasir_can_login_and_access_kasir_ledger(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'kasir.bwi@omnipharma.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $kasirResponse = $this->get(route('admin.kasir.ledger'));
        $kasirResponse->assertOk();
        $kasirResponse->assertSee('Rekonsiliasi Kasir &amp; Ledger Pembayaran', false);
        $kasirResponse->assertSee('Buku Kas &amp; Pembayaran Harian', false);
    }

    /**
     * 6. Pengguna yang sudah login tetap dapat membuka halaman sambutan dengan parameter ?welcome=1
     * untuk membaca panduan dan beralih peran atau melakukan logout.
     */
    public function test_authenticated_user_can_view_welcome_page_with_welcome_param(): void
    {
        $user = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();

        // Tanpa parameter ?welcome=1, pengguna login otomatis dialihkan ke dashboard
        $redirectResponse = $this->actingAs($user)->get(route('home'));
        $redirectResponse->assertRedirect(route('dashboard'));

        // Dengan parameter ?welcome=1, halaman sambutan ditampilkan beserta identitas pengguna
        $welcomeResponse = $this->actingAs($user)->get(route('home', ['welcome' => 1]));
        $welcomeResponse->assertOk();
        $welcomeResponse->assertSee('Guntur Afandy');
        $welcomeResponse->assertSee('Masuk Dashboard');
        $welcomeResponse->assertSee('Keluar');
    }

    /**
     * 7. Alur logout via POST /logout membersihkan sesi autentikasi dan mengembalikan ke status tamu.
     */
    public function test_logout_flow_clears_session_and_redirects(): void
    {
        $user = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * 8. Seluruh peran diwajibkan login sebelum dapat mengakses dashboard masing-masing.
     * Tamu yang mencoba mengakses dashboard peran manapun dialihkan langsung ke halaman login.
     */
    public function test_guests_are_redirected_to_login_before_accessing_any_role_dashboard(): void
    {
        // Dashboard Pelanggan
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        // Dashboard Apoteker Cabang
        $this->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertRedirect(route('login'));

        // Dashboard Super Admin Eksekutif
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        // Buku Kasir Ledger
        $this->get(route('admin.kasir.ledger'))->assertRedirect(route('login'));
    }

    /**
     * 9. Sesi login mengabaikan intended URL yang mengarah ke tempat beli resep/katalog,
     * sehingga pengguna diwajibkan mendarat di dashboard masing-masing peran terlebih dahulu.
     */
    public function test_login_ignores_intended_customer_urls_to_prevent_landing_on_prescription_store(): void
    {
        $customerStoreUrl = route('customer.home', ['client' => 'banyuwangi-kota']);

        $response = $this->withSession(['url.intended' => $customerStoreUrl])
            ->post(route('login.store'), [
                'email' => 'gunturafandy5@gmail.com',
                'password' => 'password',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Verifikasi bahwa pengguna tidak terlempar ke tempat beli resep
        $this->assertNotEquals($customerStoreUrl, $response->headers->get('Location'));
    }
}

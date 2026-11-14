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
     * 1. Tamu dapat mengakses halaman sambutan yang bersih dan profesional,
     * sedangkan kredensial akun pengujian terdokumentasi terpusat pada AKUN_LOGIN.md.
     */
    public function test_guest_can_access_clean_welcome_page_and_credentials_are_documented(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('OmniPharma');
        $response->assertSee('Apotek Terdaftar');
        $response->assertSee('Banyuwangi &amp; Rogojampi', false);
        $response->assertSee('Mulai Belanja');
        $response->assertSee('Katalog Obat');
        $response->assertSee('Daftar');
        $response->assertSee('apt. Dinda Ayu Shafira, S.Farm.');

        // Verifikasi tombol Mulai Belanja dan Akses Cepat Katalog mengarah ke rute login bagi pengunjung/tamu
        $contentHtml = $response->getContent();
        $this->assertMatchesRegularExpression('/href="[^"]*\/login"[^>]*>[\s\S]*?Mulai Belanja/i', $contentHtml);
        $this->assertMatchesRegularExpression('/href="[^"]*\/login"[^>]*>[\s\S]*?Katalog Obat/i', $contentHtml);

        // Memastikan halaman sambutan bersih dari data login mentah
        $response->assertDontSee('daffa@omnipharma.test');
        $response->assertDontSee('bambang@omnipharma.test');

        // Memastikan berkas dokumentasi akun pengujian tim tersedia
        $this->assertFileExists(base_path('AKUN_LOGIN.md'));
        $content = (string) file_get_contents(base_path('AKUN_LOGIN.md'));
        $this->assertStringContainsString('daffa@omnipharma.test', $content);
        $this->assertStringContainsString('bambang@omnipharma.test', $content);
        $this->assertStringContainsString('dewi@omnipharma.test', $content);
        $this->assertStringContainsString('apt. Dinda Ayu Shafira, S.Farm.', $content);
        $this->assertStringContainsString('gunturafandy5@gmail.com', $content);
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
        $dashboardResponse->assertSee('Katalog Cabang Banyuwangi Kota');
        $dashboardResponse->assertSee('Katalog Cabang Rogojampi');
        $dashboardResponse->assertSee('Keranjang');
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
        $adminResponse->assertSee('Daffa');
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
        $kasirResponse->assertSee('Ledger Terverifikasi', false);
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

    /**
     * 10. Verifikasi hasil audit alur Sambutan -> Login (Email/Username) -> Register/Profil -> Dashboard:
     *     - Login bisa menggunakan username maupun handle pendek (@omnipharma.test)
     *     - Halaman sambutan hanya menampilkan 2 cabang aktif resmi tanpa tautan 404 /klien/genteng
     *     - Halaman complete-profile merender Google Fonts tanpa directive @fonts mentah
     *     - Dashboard admin menampilkan kartu Rekonsiliasi Kasir & Ledger Pembayaran
     */
    public function test_user_can_login_with_username_and_welcome_branches_switch_cleanly(): void
    {
        // A. Login menggunakan username lengkap ('Guntur Afandy')
        $resUsername = $this->post(route('login.store'), [
            'email' => 'Guntur Afandy',
            'password' => 'password',
        ]);
        $resUsername->assertSessionHasNoErrors();
        $resUsername->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->post(route('logout'));

        // B. Login menggunakan handle pendek ('daffa') -> daffa@omnipharma.test
        $resHandle = $this->post(route('login.store'), [
            'email' => 'daffa',
            'password' => 'password',
        ]);
        $resHandle->assertSessionHasNoErrors();
        $resHandle->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        // Admin dashboard view di /dashboard memuat kartu Rekonsiliasi Kasir
        $adminDash = $this->get(route('dashboard'));
        $adminDash->assertOk();
        $adminDash->assertSee('Rekonsiliasi Kasir &amp; Ledger Pembayaran', false);
        $adminDash->assertSee(route('admin.kasir.ledger'));
        $this->post(route('logout'));

        // C. Halaman sambutan tamu hanya menampilkan 2 cabang resmi & bebas cabang fiktif
        $welcomeGuest = $this->get(route('home'));
        $welcomeGuest->assertOk();
        $welcomeGuest->assertSee('2 Cabang Aktif');
        $welcomeGuest->assertSee('Cabang Banyuwangi Kota');
        $welcomeGuest->assertSee('Cabang Rogojampi');
        $welcomeGuest->assertDontSee('6 Cabang Aktif');
        $welcomeGuest->assertDontSee('/klien/genteng');
        $welcomeGuest->assertDontSee('/klien/muncar');
        $welcomeGuest->assertDontSee('/klien/giri');
        $welcomeGuest->assertDontSee('/klien/cluring');

        // D. Halaman sambutan terautentikasi (?welcome=1) memakai branch.switch menuju /dashboard
        $pelanggan = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();
        $welcomeAuth = $this->actingAs($pelanggan)->get(route('home', ['welcome' => 1]));
        $welcomeAuth->assertOk();
        $welcomeAuth->assertSee(route('branch.switch'));
        $welcomeAuth->assertDontSee('/klien/banyuwangi-kota');

        // E. Halaman complete-profile bebas dari teks @fonts mentah
        $completeProfile = $this->actingAs($pelanggan)->get(route('auth.complete-profile'));
        $completeProfile->assertOk();
        $completeProfile->assertDontSee('@fonts');
        $completeProfile->assertSee('fonts.googleapis.com');
    }
}

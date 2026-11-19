<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ItemPesanan;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\PeranClientPengguna;
use App\Models\Pesanan;
use App\Models\PetaPengawasStaf;
use App\Models\StokClient;
use App\Models\User;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleCrudAndKasirPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_cross_role_matrix_enforces_403_across_all_four_portals(): void
    {
        $kasir = User::where('email', 'kasir.bwi@omnipharma.test')->firstOrFail();
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $superAdmin = User::where('email', 'daffa@omnipharma.test')->firstOrFail();
        $pelanggan = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();

        // Kasir -> OK on /kasir/*, 403 on /admin/* & /cabang/*/dashboard
        $this->actingAs($kasir)->get(route('kasir.dashboard'))->assertOk();
        $this->actingAs($kasir)->get(route('kasir.ledger'))->assertOk();
        $this->actingAs($kasir)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($kasir)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();

        // Apoteker -> OK on /cabang/*/dashboard, 403 on /admin/* & /kasir/*
        $this->actingAs($apoteker)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertOk();
        $this->actingAs($apoteker)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($apoteker)->get(route('kasir.dashboard'))->assertForbidden();

        // Super Admin -> OK on /admin/*, 403 on /kasir/* & /cabang/*/dashboard
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.obat.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('kasir.dashboard'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();

        // Pelanggan -> OK on /dashboard, 403 on internal portals
        $this->actingAs($pelanggan)->get(route('dashboard'))->assertOk();
        $this->actingAs($pelanggan)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($pelanggan)->get(route('kasir.dashboard'))->assertForbidden();
        $this->actingAs($pelanggan)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']))->assertForbidden();
    }

    public function test_kasir_can_create_walkin_order_settle_unpaid_order_print_struk_and_cancel(): void
    {
        $kasir = User::where('email', 'kasir.bwi@omnipharma.test')->firstOrFail();
        $bwiClient = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $stok = StokClient::withoutTenancy()
            ->with('obat')
            ->where('id_client', $bwiClient->id_client)
            ->where('jumlah_stok', '>=', 5)
            ->firstOrFail();

        $initialStock = (int) $stok->jumlah_stok;

        // 1. Transaksi Walk-in Tunai langsung selesai
        $walkinRes = $this->actingAs($kasir)->post(route('kasir.walkin.store'), [
            'id_obat' => $stok->id_obat,
            'jumlah' => 2,
            'nama_pasien' => 'Pasien Walk-In Uji',
            'metode_pembayaran' => 'tunai',
            'langsung_selesai' => 1,
        ]);

        $walkinRes->assertRedirect();
        $walkinRes->assertSessionHas('success');
        $walkinRes->assertSessionHas('struk_id');

        $strukId = (string) session('struk_id');
        $this->assertSame($initialStock - 2, (int) $stok->fresh()->jumlah_stok);

        // Cetak struk resmi Kasir
        $strukRes = $this->actingAs($kasir)->get(route('kasir.struk', ['id' => $strukId]));
        $strukRes->assertOk();
        $strukRes->assertSee('Pasien Walk-In Uji');

        // 2. Pesanan menunggu_pembayaran -> dilunasi kasir
        $pelangganPengguna = Pengguna::where('telepon_email', 'gunturafandy5@gmail.com')->firstOrFail();
        $orderForSettle = Pesanan::create([
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => $pelangganPengguna->id_pengguna,
            'id_client' => $bwiClient->id_client,
            'nomor_pesanan' => 'ORD-TEST-KASIR-01',
            'status' => 'menunggu_pembayaran',
            'metode_layanan' => 'ambil_di_tempat',
            'dibuat_pada' => now(),
        ]);
        ItemPesanan::create([
            'id_item' => (string) Str::uuid(),
            'id_pesanan' => $orderForSettle->id_pesanan,
            'id_obat' => $stok->id_obat,
            'nama_obat' => $stok->obat->nama_obat,
            'kuantitas' => 1,
            'harga_satuan' => (float) $stok->obat->harga,
            'subtotal' => (float) $stok->obat->harga,
        ]);

        $lunasiRes = $this->actingAs($kasir)->post(route('kasir.pesanan.lunasi', ['id' => $orderForSettle->id_pesanan]), [
            'metode_pembayaran' => 'qris',
        ]);
        $lunasiRes->assertRedirect();
        $lunasiRes->assertSessionHas('success');
        $this->assertSame('diproses', $orderForSettle->fresh()->status);
        $this->assertTrue(Pembayaran::where('id_pesanan', $orderForSettle->id_pesanan)->where('status', 'lunas')->exists());

        // 3. Batalkan pesanan menunggu_pembayaran & pulihkan stok
        $orderForCancel = Pesanan::create([
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => $pelangganPengguna->id_pengguna,
            'id_client' => $bwiClient->id_client,
            'nomor_pesanan' => 'ORD-TEST-KASIR-02',
            'status' => 'menunggu_pembayaran',
            'metode_layanan' => 'ambil_di_tempat',
            'dibuat_pada' => now(),
        ]);
        ItemPesanan::create([
            'id_item' => (string) Str::uuid(),
            'id_pesanan' => $orderForCancel->id_pesanan,
            'id_obat' => $stok->id_obat,
            'nama_obat' => $stok->obat->nama_obat,
            'kuantitas' => 2,
            'harga_satuan' => (float) $stok->obat->harga,
            'subtotal' => (float) $stok->obat->harga * 2,
        ]);
        $stok->update(['jumlah_stok' => $initialStock - 4]);

        $cancelRes = $this->actingAs($kasir)->post(route('kasir.pesanan.batalkan', ['id' => $orderForCancel->id_pesanan]));
        $cancelRes->assertRedirect();
        $this->assertSame('dibatalkan', $orderForCancel->fresh()->status);
        $this->assertSame($initialStock - 2, (int) $stok->fresh()->jumlah_stok);
    }

    public function test_super_admin_can_crud_branch_staff_and_master_obat(): void
    {
        $superAdmin = User::where('email', 'daffa@omnipharma.test')->firstOrFail();

        // A. CRUD Cabang Baru
        $createBranch = $this->actingAs($superAdmin)->post(route('admin.cabang.store'), [
            'nama_client' => 'Cabang Muncar',
            'slug' => 'muncar',
            'alamat' => 'Jl. Pelabuhan Muncar No. 9',
            'radius_km' => 10,
            'jam_buka' => '08:00 - 21:00 WIB',
            'aktif' => '1',
        ]);
        $createBranch->assertRedirect();
        $branch = Client::where('slug', 'muncar')->firstOrFail();

        $updateBranch = $this->actingAs($superAdmin)->post(route('admin.cabang.pengaturan.save', ['client' => 'muncar']), [
            'nama_client' => 'Cabang Muncar Selatan',
            'alamat' => 'Jl. Raya Muncar Km 2',
            'radius_km' => 15,
            'jam_buka' => '07:00 - 22:00 WIB',
            'aktif' => '0',
        ]);
        $updateBranch->assertRedirect();
        $this->assertSame('Cabang Muncar Selatan', $branch->fresh()->nama_client);
        $this->assertSame(15, (int) $branch->fresh()->radius_km);
        $this->assertFalse((bool) $branch->fresh()->aktif);

        // B. CRUD Staff Akun, Toggle Status, Role Assignment & 5NF Supervision + Hapus
        $createStaff = $this->actingAs($superAdmin)->post(route('admin.cabang.staf.akun', ['client' => 'banyuwangi-kota']), [
            'nama_lengkap' => 'Staf Uji Baru',
            'email' => 'staf.uji@omnipharma.test',
            'password' => 'password123',
            'role' => 'kasir',
            'peran_lokal' => 'kasir',
        ]);
        $createStaff->assertRedirect();

        $newUser = User::where('email', 'staf.uji@omnipharma.test')->firstOrFail();
        $newPengguna = Pengguna::where('telepon_email', 'staf.uji@omnipharma.test')->firstOrFail();
        $bwiClient = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('admin.cabang.staf.status', ['client' => 'banyuwangi-kota', 'pengguna' => $newPengguna->id_pengguna]))
            ->assertRedirect();
        $this->assertSame('suspended', $newUser->fresh()->status);

        $roleRow = PeranClientPengguna::where('id_pengguna', $newPengguna->id_pengguna)
            ->where('id_client', $bwiClient->id_client)
            ->firstOrFail();

        $apj = Pengguna::where('telepon_email', 'bambang@omnipharma.test')->firstOrFail();
        $this->actingAs($superAdmin)->post(route('admin.cabang.staf.pengawas', ['client' => 'banyuwangi-kota']), [
            'id_apj' => $apj->id_pengguna,
            'id_staf' => $newPengguna->id_pengguna,
        ])->assertRedirect();

        $supRow = PetaPengawasStaf::where('id_staf', $newPengguna->id_pengguna)->firstOrFail();

        $this->actingAs($superAdmin)
            ->delete(route('admin.cabang.staf.pengawas.destroy', ['client' => 'banyuwangi-kota', 'supervisionId' => $supRow->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('peta_pengawas_staf', ['id' => $supRow->id]);

        $this->actingAs($superAdmin)
            ->delete(route('admin.cabang.staf.role.destroy', ['client' => 'banyuwangi-kota', 'roleId' => $roleRow->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('peran_client_pengguna', ['id' => $roleRow->id]);

        // C. CRUD Master Obat & Multi-Cabang Stok
        $kategori = KategoriObat::firstOrFail();
        $createObat = $this->actingAs($superAdmin)->post(route('admin.obat.store'), [
            'nama_obat' => 'Vitamin C 500mg Uji',
            'merek' => 'OmniTest',
            'id_kategori' => $kategori->id_kategori,
            'jenis_sediaan' => 'Tablet',
            'satuan_kemasan' => 'Strip @ 10',
            'badge_status' => 'Obat Bebas',
            'harga' => 15000,
            'expired' => now()->addYear()->toDateString(),
            'batch_number' => 'VITC-001',
            'stok' => [
                $bwiClient->id_client => 30,
            ],
        ]);
        $createObat->assertRedirect();

        $obat = Obat::where('nama_obat', 'Vitamin C 500mg Uji')->firstOrFail();
        $this->actingAs($superAdmin)->put(route('admin.obat.update', ['id' => $obat->id_obat]), [
            'nama_obat' => 'Vitamin C 500mg Forte Uji',
            'merek' => 'OmniTest',
            'id_kategori' => $kategori->id_kategori,
            'jenis_sediaan' => 'Kaplet',
            'satuan_kemasan' => 'Strip @ 10',
            'badge_status' => 'Obat Bebas',
            'harga' => 18000,
            'expired' => now()->addYear()->toDateString(),
            'batch_number' => 'VITC-002',
            'stok' => [
                $bwiClient->id_client => 45,
            ],
        ])->assertRedirect();

        $this->assertSame('Vitamin C 500mg Forte Uji', $obat->fresh()->nama_obat);
        $this->assertSame(
            45,
            (int) StokClient::withoutTenancy()->where('id_obat', $obat->id_obat)->where('id_client', $bwiClient->id_client)->value('jumlah_stok')
        );

        $this->actingAs($superAdmin)->delete(route('admin.obat.destroy', ['id' => $obat->id_obat]))->assertRedirect();
        $this->assertDatabaseMissing('obat', ['id_obat' => $obat->id_obat]);

        // D. Hapus cabang kosong (Muncar)
        $this->actingAs($superAdmin)->delete(route('admin.cabang.destroy', ['client' => 'muncar']))->assertRedirect();
        $this->assertDatabaseMissing('client', ['slug' => 'muncar']);
    }

    public function test_apoteker_can_manage_fefo_stock_isolated_patients_and_branch_report(): void
    {
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $bwiClient = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $rogoClient = Client::where('slug', 'rogojampi')->firstOrFail();
        $kategori = KategoriObat::firstOrFail();

        $obatBaru = Obat::create([
            'id_obat' => (string) Str::uuid(),
            'nama_obat' => 'Cetirizine 10mg Cabang',
            'slug' => 'cetirizine-10mg-cabang',
            'merek' => 'Kimia Farma',
            'jenis_sediaan' => 'Tablet',
            'badge_status' => 'Obat Bebas Terbatas',
            'id_kategori' => $kategori->id_kategori,
            'harga' => 8000,
            'expired' => now()->addMonths(6)->toDateString(),
            'batch_number' => 'CTZ-01',
            'jumlah_obat' => 0,
        ]);

        // Tambah stok cabang via Portal Apoteker
        $this->actingAs($apoteker)->post(route('tenant.stok.store', ['client' => 'banyuwangi-kota']), [
            'id_obat' => $obatBaru->id_obat,
            'jumlah_stok' => 15,
            'batch_number' => 'CTZ-02',
        ])->assertRedirect();

        $stok = StokClient::withoutTenancy()
            ->where('id_client', $bwiClient->id_client)
            ->where('id_obat', $obatBaru->id_obat)
            ->firstOrFail();
        $this->assertSame(15, (int) $stok->jumlah_stok);

        // Delta +1 & Karantina
        $this->actingAs($apoteker)->post(route('tenant.stok.penyesuaian', [
            'client' => 'banyuwangi-kota',
            'id' => $stok->id,
        ]), ['delta' => 1])->assertRedirect();
        $this->assertSame(16, (int) $stok->fresh()->jumlah_stok);

        $this->actingAs($apoteker)->post(route('tenant.stok.penyesuaian', [
            'client' => 'banyuwangi-kota',
            'id' => $stok->id,
        ]), ['karantina' => 1])->assertRedirect();
        $this->assertSame(0, (int) $stok->fresh()->jumlah_stok);

        // Pasien isolation test: pasien hanya muncul di cabang tempat ia memesan
        $rogoOnlyPatient = Pengguna::create([
            'id_pengguna' => (string) Str::uuid(),
            'nama_lengkap' => 'Pasien Khusus Rogojampi',
            'peran' => 'pelanggan',
            'telepon_email' => 'khusus.rogo@example.test',
            'password' => bcrypt('password'),
            'terdaftar_pada' => now()->toDateString(),
            'aktif' => true,
        ]);
        app(TenantManagerInterface::class)->runInContext(
            $rogoClient,
            function () use ($rogoOnlyPatient, $rogoClient): void {
                Pesanan::create([
                    'id_pesanan' => (string) Str::uuid(),
                    'id_pengguna' => $rogoOnlyPatient->id_pengguna,
                    'id_client' => $rogoClient->id_client,
                    'nomor_pesanan' => 'ORD-ROGO-ONLY-01',
                    'status' => 'selesai',
                    'metode_layanan' => 'ambil_di_tempat',
                    'dibuat_pada' => now(),
                ]);
            }
        );

        $pasienBwiRes = $this->actingAs($apoteker)->get(route('tenant.pasien.index', ['client' => 'banyuwangi-kota']));
        $pasienBwiRes->assertOk();
        $pasienBwiRes->assertDontSee('Pasien Khusus Rogojampi');

        // Laporan penjualan cabang render cleanly (menggunakan layout tenant)
        $reportRes = $this->actingAs($apoteker)->get(route('tenant.laporan.index', ['client' => 'banyuwangi-kota']));
        $reportRes->assertOk();
        $reportRes->assertSee('Analitik Penjualan');
    }

    public function test_dashboard_and_profile_adapt_buttons_for_each_role_and_super_admin_can_audit_apoteker(): void
    {
        $superAdmin = User::where('email', 'daffa@omnipharma.test')->firstOrFail();
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $kasir = User::where('email', 'kasir.bwi@omnipharma.test')->firstOrFail();
        $pelanggan = User::where('email', 'gunturafandy5@gmail.com')->firstOrFail();

        // 1. Apoteker di /dashboard & /settings/profile melihat tombol arah Apoteker
        $this->actingAs($apoteker)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Apoteker')
            ->assertSee('Telaah Resep Dokter');

        $this->actingAs($apoteker)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dashboard Apoteker')
            ->assertSee('Stok &amp; Batch FEFO', false);

        // 2. Super Admin di /dashboard & /settings/profile melihat CRUD lengkap + Cek Apoteker
        $this->actingAs($superAdmin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('CRUD Cabang &amp; Pengaturan', false)
            ->assertSee('CRUD Master Obat &amp; Stok', false)
            ->assertSee('CRUD Staf, Role &amp; Hak Akses', false)
            ->assertSee('Cek Operasional Apoteker');

        $this->actingAs($superAdmin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('CRUD Staf, Role &amp; Hak Akses', false)
            ->assertSee('Cek Operasional Apoteker')
            ->assertSee('Audit Kasir &amp; Ledger Cabang', false);

        // 3. Super Admin dapat membuka /admin/apoteker untuk cek cabang Banyuwangi Kota & Rogojampi
        $this->actingAs($superAdmin)->get(route('admin.apoteker.index'))
            ->assertOk()
            ->assertSee('Cek Operasional Apoteker: Cabang Banyuwangi Kota')
            ->assertSee('apt. Bambang, S.Farm');

        $this->actingAs($superAdmin)->get(route('admin.apoteker.index', ['client' => 'rogojampi']))
            ->assertOk()
            ->assertSee('Cek Operasional Apoteker: Cabang Rogojampi');

        // Role lain tetap ditolak 403 di /admin/apoteker
        $this->actingAs($apoteker)->get(route('admin.apoteker.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.apoteker.index'))->assertForbidden();
        $this->actingAs($pelanggan)->get(route('admin.apoteker.index'))->assertForbidden();

        // 4. Kasir di /dashboard & /settings/profile menyesuaikan ke POS Loket & Ledger
        $this->actingAs($kasir)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Kasir &amp; POS Loket', false)
            ->assertSee('Buku Ledger Kasir');

        $this->actingAs($kasir)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dashboard Kasir &amp; POS Loket', false)
            ->assertSee('Buku Ledger Kasir');

        // 5. Pelanggan biasa tidak melihat tombol internal portal di /settings/profile
        $this->actingAs($pelanggan)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Keranjang Belanja')
            ->assertDontSee('Executive Dashboard Pusat')
            ->assertDontSee('Dashboard Apoteker')
            ->assertDontSee('Dashboard Kasir');
    }
}

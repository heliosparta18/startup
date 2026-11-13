<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\User;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPrescriptionUploadAndQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    /**
     * Pelanggan mengunggah resep dokter dan sistem membuat record Pesanan & Resep baru
     * dengan status menunggu_verifikasi / menunggu.
     */
    public function test_customer_prescription_upload_creates_pesanan_and_resep_records(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Andika Pratama, Sp.A',
            'sip_dokter' => '503/SIP/2026',
            'nama_pasien' => 'Siti Nurhaliza (5 th)',
            'catatan' => 'Resep puyer antibiotik anak mohon segera disiapkan.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'client' => $client->slug,
            'status' => 'menunggu_verifikasi',
        ]);

        $orderNumber = $response->json('nomor_pesanan');
        $idPesanan = $response->json('id_pesanan');
        $idResep = $response->json('id_resep');

        $this->assertNotEmpty($orderNumber);
        $this->assertNotEmpty($idPesanan);
        $this->assertNotEmpty($idResep);

        // Verifikasi Pesanan tersimpan di database
        $this->assertDatabaseHas('pesanan', [
            'id_pesanan' => $idPesanan,
            'id_client' => $client->id_client,
            'nomor_pesanan' => $orderNumber,
            'status' => 'menunggu_verifikasi',
        ]);

        // Verifikasi Resep tersimpan di database sesuai R1
        $this->assertDatabaseHas('resep', [
            'id_resep' => $idResep,
            'id_pesanan' => $idPesanan,
            'nama_dokter' => 'dr. Andika Pratama, Sp.A',
            'sip_dokter' => '503/SIP/2026',
            'nama_pasien' => 'Siti Nurhaliza (5 th)',
            'catatan' => 'Resep puyer antibiotik anak mohon segera disiapkan.',
            'status_verifikasi' => 'menunggu',
        ]);
    }

    /**
     * Pelanggan mengunggah foto resep asli, berkas tersimpan di storage publik.
     */
    public function test_customer_prescription_upload_stores_image_in_public_storage(): void
    {
        Storage::fake('public');

        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $file = UploadedFile::fake()->image('resep-asli-dokter.jpg', 800, 1000)->size(1500);

        $response = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Kartika Sari, Sp.PD',
            'sip_dokter' => '503/SIP/2025',
            'nama_pasien' => 'Ahmad Dahlan (45 th)',
            'catatan' => 'Penebusan resep rutin hipertensi.',
            'foto_resep' => $file,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'menunggu_verifikasi',
            'nama_berkas' => 'resep-asli-dokter.jpg',
        ]);

        $urlGambar = $response->json('url_gambar');
        $this->assertStringStartsWith('/storage/resep/', $urlGambar);

        $storagePath = str_replace('/storage/', '', $urlGambar);
        Storage::disk('public')->assertExists($storagePath);

        $idResep = $response->json('id_resep');
        $this->assertDatabaseHas('resep', [
            'id_resep' => $idResep,
            'url_gambar' => $urlGambar,
            'status_verifikasi' => 'menunggu',
        ]);
    }

    /**
     * Resep baru yang diunggah pelanggan langsung tampil di dashboard apoteker cabang.
     */
    public function test_uploaded_prescription_appears_in_tenant_pharmacist_dashboard(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Budi Setiawan, Sp.PD',
            'sip_dokter' => '503/SIP/2026',
            'nama_pasien' => 'Farhan Kamil',
            'catatan' => 'Resep antibiotik dosis tinggi',
        ]);

        $uploadResponse->assertOk();
        $nomorPesanan = $uploadResponse->json('nomor_pesanan');

        // Apoteker Banyuwangi Kota login
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $dashboardResponse = $this->actingAs($apoteker)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee($nomorPesanan);
        $dashboardResponse->assertSee('dr. Budi Setiawan, Sp.PD');
        $dashboardResponse->assertSee('Farhan Kamil');
        $dashboardResponse->assertSee('503/SIP/2026');
        $dashboardResponse->assertSee('Resep antibiotik dosis tinggi');
    }

    /**
     * Resep baru yang diunggah pelanggan langsung tampil pada antrean telaah resep digital apoteker.
     */
    public function test_uploaded_prescription_appears_in_tenant_prescription_verification_queue(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Dini Anggraeni, Sp.A',
            'sip_dokter' => '503/SIP/2026',
            'nama_pasien' => 'Aura Kasih',
            'catatan' => 'Alergi penisilin',
        ]);

        $uploadResponse->assertOk();
        $nomorPesanan = $uploadResponse->json('nomor_pesanan');

        // Apoteker Banyuwangi Kota membuka antrean resep
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $queueResponse = $this->actingAs($apoteker)->get(route('tenant.resep.antrean', ['client' => 'banyuwangi-kota']));

        $queueResponse->assertOk();
        $queueResponse->assertSee($nomorPesanan);
        $queueResponse->assertSee('dr. Dini Anggraeni, Sp.A');
        $queueResponse->assertSee('Aura Kasih');
        $queueResponse->assertSee('503/SIP/2026');
        $queueResponse->assertSee('Alergi penisilin');
    }

    /**
     * Isolasi cabang: resep untuk Rogojampi tidak muncul di Banyuwangi Kota, dan sebaliknya.
     */
    public function test_prescriptions_are_strictly_isolated_by_branch_tenant(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $rogojampi->slug]), [
            'nama_dokter' => 'dr. Hasan Rogojampi, Sp.JP',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Pasien Rogojampi Murni',
            'catatan' => 'Resep khusus cabang Rogojampi',
        ]);

        $uploadResponse->assertOk();
        $nomorPesananRogojampi = $uploadResponse->json('nomor_pesanan');

        // Apoteker Banyuwangi Kota tidak boleh melihat resep Rogojampi
        $apotekerBwi = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $bwiResponse = $this->actingAs($apotekerBwi)->get(route('tenant.resep.antrean', ['client' => 'banyuwangi-kota']));
        $bwiResponse->assertOk();
        $bwiResponse->assertDontSee($nomorPesananRogojampi);
        $bwiResponse->assertDontSee('Pasien Rogojampi Murni');

        // Apoteker Rogojampi harus melihat resep miliknya
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();
        $rogojampiResponse = $this->actingAs($apotekerRogojampi)->get(route('tenant.resep.antrean', ['client' => 'rogojampi']));
        $rogojampiResponse->assertOk();
        $rogojampiResponse->assertSee($nomorPesananRogojampi);
        $rogojampiResponse->assertSee('Pasien Rogojampi Murni');
    }

    /**
     * Apoteker dapat menyetujui resep pelanggan dan status pesanan diperbarui menjadi diproses.
     */
    public function test_pharmacist_can_approve_prescription(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Budi, Sp.PD',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Bambang Sudirman',
            'catatan' => 'Mohon diverifikasi segera',
        ]);

        $uploadResponse->assertOk();
        $idResep = $uploadResponse->json('id_resep');
        $idPesanan = $uploadResponse->json('id_pesanan');

        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();

        $verifyResponse = $this->actingAs($apoteker)->post(route('tenant.resep.verifikasi', [
            'client' => 'banyuwangi-kota',
            'id' => $idResep,
        ]), [
            'action' => 'disetujui',
            'nomor_sip' => '503/SIP/2024',
            'catatan' => 'SIP tervalidasi aktif di KKI, dosis rasional.',
        ]);

        $verifyResponse->assertOk();

        // Status resep berubah menjadi disetujui
        $resep = Resep::find($idResep);
        $this->assertSame('disetujui', $resep->status_verifikasi);
        $this->assertTrue((bool) $resep->sip_valid);

        // Status pesanan berubah menjadi diproses
        $pesanan = Pesanan::withoutTenancy()->find($idPesanan);
        $this->assertSame('diproses', $pesanan->status);
    }

    /**
     * Apoteker dapat menolak resep pelanggan dan status pesanan diperbarui menjadi dibatalkan.
     */
    public function test_pharmacist_can_reject_prescription(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Bodong',
            'sip_dokter' => '999/SIP/2020',
            'nama_pasien' => 'Pasien Ditolak',
            'catatan' => 'Resep kadaluwarsa',
        ]);

        $uploadResponse->assertOk();
        $idResep = $uploadResponse->json('id_resep');
        $idPesanan = $uploadResponse->json('id_pesanan');

        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();

        $verifyResponse = $this->actingAs($apoteker)->post(route('tenant.resep.verifikasi', [
            'client' => 'banyuwangi-kota',
            'id' => $idResep,
        ]), [
            'action' => 'ditolak',
            'alasan' => 'Masa berlaku resep dokter telah melebihi batas 30 hari.',
        ]);

        $verifyResponse->assertOk();

        // Status resep berubah menjadi ditolak
        $resep = Resep::find($idResep);
        $this->assertSame('ditolak', $resep->status_verifikasi);
        $this->assertFalse((bool) $resep->sip_valid);

        // Status pesanan berubah menjadi dibatalkan
        $pesanan = Pesanan::withoutTenancy()->find($idPesanan);
        $this->assertSame('dibatalkan', $pesanan->status);
    }

    /**
     * Resep yang diunggah oleh pelanggan terautentikasi terhubung ke akun pengguna tersebut.
     */
    public function test_customer_prescription_upload_with_authenticated_user_links_user_account(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $customerUser = User::factory()->create([
            'email' => 'customer.test@example.com',
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($customerUser)->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Sarah Sp.A',
            'sip_dokter' => '503/SIP/2026',
            'nama_pasien' => 'Ananda Rizky',
            'catatan' => 'Resep anak',
        ]);

        $response->assertOk();
        $idPesanan = $response->json('id_pesanan');
        $pesanan = Pesanan::withoutTenancy()->find($idPesanan);

        $pengguna = Pengguna::where('telepon_email', 'customer.test@example.com')->first();
        $this->assertNotNull($pengguna);
        $this->assertSame($pengguna->id_pengguna, $pesanan->id_pengguna);
    }

    /**
     * Validasi unggah resep menolak berkas berukuran melebihi 5MB.
     */
    public function test_customer_prescription_upload_rejects_oversized_file(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $oversizedFile = UploadedFile::fake()->create('huge-resep.jpg', 6000, 'image/jpeg');

        $response = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'foto_resep' => $oversizedFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['foto_resep']);
    }

    /**
     * Validasi unggah resep menolak format berkas selain gambar (misal PDF).
     */
    public function test_customer_prescription_upload_rejects_invalid_file_type(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'foto_resep' => $pdfFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['foto_resep']);
    }

    /**
     * Upload dengan input string kosong ('') tetap menghasilkan data valid tanpa string hampa.
     */
    public function test_customer_prescription_upload_handles_empty_string_inputs_gracefully(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => '',
            'sip_dokter' => '',
            'nama_pasien' => '',
            'catatan' => '',
        ]);

        $response->assertOk();
        $idResep = $response->json('id_resep');
        $resep = Resep::find($idResep);

        $this->assertNotEmpty($resep->nama_dokter);
        $this->assertSame('dr. Budi, Sp.PD', $resep->nama_dokter);
        $this->assertSame('503/SIP/2024', $resep->sip_dokter);
        $this->assertSame('Pelanggan Tamu', $resep->nama_pasien);
    }

    /**
     * Apoteker tidak dapat memverifikasi resep yang milik cabang lain (IDOR protection).
     */
    public function test_pharmacist_cannot_verify_prescription_from_another_branch(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $rogojampi->slug]), [
            'nama_dokter' => 'dr. Rogojampi',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Pasien Rogojampi',
        ]);
        $uploadResponse->assertOk();
        $idResepRogojampi = $uploadResponse->json('id_resep');

        // Apoteker Banyuwangi mencoba memverifikasi resep Rogojampi di endpoint Banyuwangi
        $apotekerBwi = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $crossVerifyResponse = $this->actingAs($apotekerBwi)->post(route('tenant.resep.verifikasi', [
            'client' => 'banyuwangi-kota',
            'id' => $idResepRogojampi,
        ]), [
            'action' => 'disetujui',
        ]);

        $crossVerifyResponse->assertNotFound();
    }

    /**
     * Submit form telaah resep via browser dengan Accept text/html menghasilkan redirect back dengan flash.
     */
    public function test_pharmacist_verification_via_browser_form_redirects_back(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Browser Form',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Pasien Web Form',
        ]);
        $idResep = $uploadResponse->json('id_resep');

        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apoteker)
            ->from(route('tenant.resep.antrean', ['client' => 'banyuwangi-kota']))
            ->post(route('tenant.resep.verifikasi', [
                'client' => 'banyuwangi-kota',
                'id' => $idResep,
            ]), [
                '_token' => 'dummy-csrf-token',
                'action' => 'disetujui',
                'nomor_sip' => '503/SIP/2024',
                'catatan' => 'Diverifikasi via form browser',
            ], [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]);

        $response->assertRedirect(route('tenant.resep.antrean', ['client' => 'banyuwangi-kota']));
        $response->assertSessionHas('success');
    }

    /**
     * Verifikasi resep memancarkan event OrderStatusUpdated ke Reverb WebSockets.
     */
    public function test_pharmacist_verification_dispatches_order_status_updated_event(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $uploadResponse = $this->postJson(route('customer.resep.upload', ['client' => $client->slug]), [
            'nama_dokter' => 'dr. Reverb Event',
            'sip_dokter' => '503/SIP/2024',
            'nama_pasien' => 'Pasien Reverb',
        ]);
        $idResep = $uploadResponse->json('id_resep');

        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();
        $this->actingAs($apoteker)->post(route('tenant.resep.verifikasi', [
            'client' => 'banyuwangi-kota',
            'id' => $idResep,
        ]), [
            'action' => 'disetujui',
        ]);

        Event::assertDispatched(OrderStatusUpdated::class);
    }
}

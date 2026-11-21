<?php

namespace Tests\Feature;

use App\Livewire\Customer\TanyaApotekerModal;
use App\Livewire\Tenant\ApotekerChatPortal;
use App\Models\Client;
use App\Models\Konsultasi;
use App\Models\PesanKonsultasi;
use App\Models\User;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TanyaApotekerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_customer_ui_renders_tanya_apoteker_button_without_soon_badge(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->get('/klien/'.$client->slug);

        $response->assertOk();
        $response->assertSee('Tanya Apoteker');
        $response->assertDontSee('Konsultasi Apoteker (Coming Soon)');
        $response->assertDontSee('>Soon</span>', false);
        $response->assertDontSee('>Segera</span>', false);
    }

    public function test_dashboard_renders_tanya_apoteker_button_without_soon_badge(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Tanya Apoteker');
        $response->assertDontSee('Konsultasi Apoteker (Coming Soon)');
        $response->assertDontSee('>Soon</span>', false);
        $response->assertDontSee('>Segera</span>', false);
    }

    public function test_customer_verification_modal_validation(): void
    {
        Livewire::test(TanyaApotekerModal::class)
            ->set('nama_pasien', '')
            ->set('selected_client_id', '')
            ->call('mulaiKonsultasi')
            ->assertHasErrors(['nama_pasien', 'selected_client_id']);
    }

    public function test_customer_branch_selection_and_starter_template_message(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();

        Livewire::test(TanyaApotekerModal::class)
            ->set('nama_pasien', 'Ahmad Dahlan')
            ->set('selected_client_id', $rogojampi->id_client)
            ->call('mulaiKonsultasi')
            ->assertHasNoErrors()
            ->assertSet('isVerified', true);

        $konsultasi = Konsultasi::where('nama_pasien', 'Ahmad Dahlan')->first();
        $this->assertNotNull($konsultasi);
        $this->assertEquals($rogojampi->id_client, $konsultasi->id_client);
        $this->assertEquals('aktif', $konsultasi->status);
        $this->assertEquals('Halo, saya butuh bantuan konsultasi obat', $konsultasi->pesan_terakhir);

        $starterMessage = PesanKonsultasi::where('id_konsultasi', $konsultasi->id_konsultasi)->first();
        $this->assertNotNull($starterMessage);
        $this->assertEquals('pasien', $starterMessage->pengirim_tipe);
        $this->assertEquals('Halo, saya butuh bantuan konsultasi obat', $starterMessage->pesan);
    }

    public function test_customer_can_send_text_message_and_image_attachment(): void
    {
        Storage::fake('public');
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $konsultasi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'token_sesi' => session()->getId(),
            'nama_pasien' => 'Dewi Lestari',
            'status' => 'aktif',
            'pesan_terakhir' => 'Halo, saya butuh bantuan konsultasi obat',
            'pesan_terakhir_pada' => now(),
        ]);

        $fakePhoto = UploadedFile::fake()->image('resep-obat.jpg', 600, 400)->size(1024);

        Livewire::test(TanyaApotekerModal::class)
            ->set('konsultasiId', $konsultasi->id_konsultasi)
            ->set('isVerified', true)
            ->set('pesanBaru', 'Apakah obat ini harus diminum sebelum makan?')
            ->set('lampiran', $fakePhoto)
            ->call('kirimPesan')
            ->assertHasNoErrors()
            ->assertSet('pesanBaru', '')
            ->assertSet('lampiran', null);

        $sentMessage = PesanKonsultasi::where('id_konsultasi', $konsultasi->id_konsultasi)
            ->where('pesan', 'Apakah obat ini harus diminum sebelum makan?')
            ->first();

        $this->assertNotNull($sentMessage);
        $this->assertEquals('pasien', $sentMessage->pengirim_tipe);
        $this->assertNotNull($sentMessage->lampiran_url);
        $this->assertStringStartsWith('/storage/chat-attachments/', $sentMessage->lampiran_url);

        $storedPath = str_replace('/storage/', '', $sentMessage->lampiran_url);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_customer_image_upload_rejects_oversized_file(): void
    {
        Storage::fake('public');
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $konsultasi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'token_sesi' => session()->getId(),
            'nama_pasien' => 'Dewi Lestari',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pesan awal',
            'pesan_terakhir_pada' => now(),
        ]);

        $oversizedPhoto = UploadedFile::fake()->image('resep-besar.jpg')->size(6000);

        Livewire::test(TanyaApotekerModal::class)
            ->set('konsultasiId', $konsultasi->id_konsultasi)
            ->set('isVerified', true)
            ->set('pesanBaru', 'Pertanyaan')
            ->set('lampiran', $oversizedPhoto)
            ->call('kirimPesan')
            ->assertHasErrors(['lampiran']);
    }

    public function test_apoteker_portal_renders_chat_button_for_apoteker_role(): void
    {
        $apoteker = User::where('email', 'bambang@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apoteker)->get('/cabang/banyuwangi-kota/dashboard');

        $response->assertOk();
        $response->assertSee('id="btn-apoteker-chat"', false);
    }

    public function test_apoteker_multi_patient_interface_and_branch_isolation(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();

        $konsulBwi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'nama_pasien' => 'Pasien Banyuwangi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pertanyaan Banyuwangi',
            'pesan_terakhir_pada' => now(),
        ]);

        $konsulRogojampi = Konsultasi::create([
            'id_client' => $rogojampi->id_client,
            'nama_pasien' => 'Pasien Rogojampi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pertanyaan Rogojampi',
            'pesan_terakhir_pada' => now(),
        ]);

        // Login sebagai Apoteker Rogojampi (apt. Dinda / dewi@omnipharma.test)
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $component = Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('openChat');

        // Harus melihat Pasien Rogojampi dan TIDAK melihat Pasien Banyuwangi
        $component->assertSee('Pasien Rogojampi');
        $component->assertDontSee('Pasien Banyuwangi');
    }

    public function test_apoteker_can_view_conversation_and_send_reply(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsultasi = Konsultasi::create([
            'id_client' => $rogojampi->id_client,
            'nama_pasien' => 'Pasien Siti',
            'status' => 'aktif',
            'pesan_terakhir' => 'Berapa dosis paracetamol anak 5 tahun?',
            'pesan_terakhir_pada' => now(),
        ]);

        PesanKonsultasi::create([
            'id_konsultasi' => $konsultasi->id_konsultasi,
            'pengirim_tipe' => 'pasien',
            'pesan' => 'Berapa dosis paracetamol anak 5 tahun?',
            'dibuat_pada' => now()->subMinute(),
        ]);

        $replyText = 'Dosis sirup paracetamol anak 5 tahun adalah 120-250mg per kali konsumsi, maksimal 4 kali sehari.';

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('openChat')
            ->call('selectKonsultasi', $konsultasi->id_konsultasi)
            ->set('balasanTeks', $replyText)
            ->call('kirimBalasan')
            ->assertHasNoErrors()
            ->assertSet('balasanTeks', '');

        $reply = PesanKonsultasi::where('id_konsultasi', $konsultasi->id_konsultasi)
            ->where('pengirim_tipe', 'apoteker')
            ->first();

        $this->assertNotNull($reply);
        $this->assertEquals($replyText, $reply->pesan);

        $konsultasi->refresh();
        $this->assertEquals($replyText, $konsultasi->pesan_terakhir);
    }

    public function test_apoteker_can_mark_session_as_completed(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsultasi = Konsultasi::create([
            'id_client' => $rogojampi->id_client,
            'nama_pasien' => 'Pasien Budi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Terima kasih informasinya',
            'pesan_terakhir_pada' => now(),
        ]);

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('selesaiSesi', $konsultasi->id_konsultasi);

        $konsultasi->refresh();
        $this->assertEquals('selesai', $konsultasi->status);
    }

    public function test_unauthenticated_user_cannot_access_apoteker_chat_portal(): void
    {
        Livewire::test(ApotekerChatPortal::class)
            ->assertForbidden();
    }

    public function test_customer_user_cannot_access_apoteker_chat_portal(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Livewire::actingAs($customer)
            ->test(ApotekerChatPortal::class)
            ->assertForbidden();
    }

    public function test_apoteker_cannot_select_cross_branch_consultation_idor(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsulBwi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'nama_pasien' => 'Pasien Rahasia Banyuwangi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pesan rahasia',
            'pesan_terakhir_pada' => now(),
        ]);

        $pesanBwi = PesanKonsultasi::create([
            'id_konsultasi' => $konsulBwi->id_konsultasi,
            'pengirim_tipe' => 'pasien',
            'pesan' => 'Saya menderita penyakit kronis rahasia di Banyuwangi',
            'dibaca_pada' => null,
            'dibuat_pada' => now(),
        ]);

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('selectKonsultasi', $konsulBwi->id_konsultasi)
            ->assertSet('activeKonsultasiId', null);

        $pesanBwi->refresh();
        $this->assertNull($pesanBwi->dibaca_pada);
    }

    public function test_apoteker_cannot_reply_to_cross_branch_consultation_idor(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsulBwi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'nama_pasien' => 'Pasien Rahasia Banyuwangi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pesan rahasia',
            'pesan_terakhir_pada' => now(),
        ]);

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->set('activeKonsultasiId', $konsulBwi->id_konsultasi)
            ->set('balasanTeks', 'Infiltrasi balasan dari Apoteker Rogojampi')
            ->call('kirimBalasan')
            ->assertHasErrors(['balasanTeks']);

        $infiltratedReply = PesanKonsultasi::where('id_konsultasi', $konsulBwi->id_konsultasi)
            ->where('pengirim_tipe', 'apoteker')
            ->first();

        $this->assertNull($infiltratedReply);
    }

    public function test_apoteker_cannot_terminate_cross_branch_consultation_idor(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsulBwi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'nama_pasien' => 'Pasien Banyuwangi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pesan',
            'pesan_terakhir_pada' => now(),
        ]);

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('selesaiSesi', $konsulBwi->id_konsultasi);

        $konsulBwi->refresh();
        $this->assertEquals('aktif', $konsulBwi->status);
    }

    public function test_customer_cannot_select_inactive_branch(): void
    {
        $inactiveBranch = Client::create([
            'id_client' => (string) Str::uuid(),
            'slug' => 'cabang-tutup',
            'nama_client' => 'Apotek Omni Cabang Nonaktif',
            'aktif' => false,
        ]);

        Livewire::test(TanyaApotekerModal::class)
            ->set('nama_pasien', 'Pasien Budi')
            ->set('selected_client_id', $inactiveBranch->id_client)
            ->call('mulaiKonsultasi')
            ->assertHasErrors(['selected_client_id']);

        $created = Konsultasi::where('id_client', $inactiveBranch->id_client)->first();
        $this->assertNull($created);
    }

    public function test_customer_cannot_view_or_send_to_other_customer_session_idor(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $konsulPasienA = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'token_sesi' => 'token-pasien-a',
            'nama_pasien' => 'Pasien A',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pesan rahasia Pasien A',
            'pesan_terakhir_pada' => now(),
        ]);

        PesanKonsultasi::create([
            'id_konsultasi' => $konsulPasienA->id_konsultasi,
            'pengirim_tipe' => 'pasien',
            'pesan' => 'Riwayat penyakit kronis pribadi Pasien A',
            'dibuat_pada' => now(),
        ]);

        // Pasien B (dengan cookie sesi berbeda) mencoba mengakses konsultasi Pasien A
        $component = Livewire::withCookie('omnipharma_customer_session', 'token-pasien-b')
            ->test(TanyaApotekerModal::class)
            ->set('konsultasiId', $konsulPasienA->id_konsultasi)
            ->set('isVerified', true);

        $component->assertDontSee('Riwayat penyakit kronis pribadi Pasien A');
        $component->assertSet('konsultasiId', null);
        $component->assertSet('isVerified', false);

        // Pasien B mencoba mengirim pesan ke sesi Pasien A
        Livewire::withCookie('omnipharma_customer_session', 'token-pasien-b')
            ->test(TanyaApotekerModal::class)
            ->set('konsultasiId', $konsulPasienA->id_konsultasi)
            ->set('isVerified', true)
            ->set('pesanBaru', 'Pesan palsu dari Pasien B')
            ->call('kirimPesan')
            ->assertHasErrors(['pesanBaru']);

        $infiltratedMessage = PesanKonsultasi::where('id_konsultasi', $konsulPasienA->id_konsultasi)
            ->where('pesan', 'Pesan palsu dari Pasien B')
            ->first();

        $this->assertNull($infiltratedMessage);
    }

    public function test_non_previewable_file_upload_does_not_crash_customer_modal(): void
    {
        $banyuwangi = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $token = session()->getId();

        $konsultasi = Konsultasi::create([
            'id_client' => $banyuwangi->id_client,
            'token_sesi' => $token,
            'nama_pasien' => 'Dewi Lestari',
            'status' => 'aktif',
            'pesan_terakhir' => 'Halo',
            'pesan_terakhir_pada' => now(),
        ]);

        $pdfFile = UploadedFile::fake()->create('resep_dokter.pdf', 200, 'application/pdf');

        Livewire::test(TanyaApotekerModal::class)
            ->set('konsultasiId', $konsultasi->id_konsultasi)
            ->set('isVerified', true)
            ->set('lampiran', $pdfFile)
            ->assertSee('resep_dokter.pdf');
    }

    public function test_non_previewable_file_upload_does_not_crash_apoteker_portal(): void
    {
        $rogojampi = Client::where('slug', 'rogojampi')->firstOrFail();
        $apotekerRogojampi = User::where('email', 'dewi@omnipharma.test')->firstOrFail();

        $konsultasi = Konsultasi::create([
            'id_client' => $rogojampi->id_client,
            'nama_pasien' => 'Pasien Rogojampi',
            'status' => 'aktif',
            'pesan_terakhir' => 'Pertanyaan',
            'pesan_terakhir_pada' => now(),
        ]);

        $pdfFile = UploadedFile::fake()->create('dokumen_medis.pdf', 300, 'application/pdf');

        Livewire::actingAs($apotekerRogojampi)
            ->test(ApotekerChatPortal::class)
            ->call('selectKonsultasi', $konsultasi->id_konsultasi)
            ->set('lampiranBalasan', $pdfFile)
            ->assertSee('dokumen_medis.pdf');
    }

    public function test_invalid_uuid_or_deleted_consultation_handled_gracefully(): void
    {
        $randomUuid = (string) Str::uuid();

        // Customer
        Livewire::test(TanyaApotekerModal::class)
            ->set('konsultasiId', $randomUuid)
            ->set('isVerified', true)
            ->set('pesanBaru', 'Pesan ID Fiktif')
            ->call('kirimPesan')
            ->assertHasErrors(['pesanBaru']);

        // Apoteker
        $apoteker = User::where('email', 'dewi@omnipharma.test')->firstOrFail();
        Livewire::actingAs($apoteker)
            ->test(ApotekerChatPortal::class)
            ->set('activeKonsultasiId', $randomUuid)
            ->set('balasanTeks', 'Balasan ID Fiktif')
            ->call('kirimBalasan')
            ->assertHasErrors(['balasanTeks']);
    }

    public function test_realtime_upload_hooks_validate_mime_and_size(): void
    {
        // Customer
        Livewire::test(TanyaApotekerModal::class)
            ->set('lampiran', UploadedFile::fake()->create('dokumen.txt', 10, 'text/plain'))
            ->assertHasErrors(['lampiran']);

        // Apoteker
        $apoteker = User::where('email', 'dewi@omnipharma.test')->firstOrFail();
        Livewire::actingAs($apoteker)
            ->test(ApotekerChatPortal::class)
            ->set('lampiranBalasan', UploadedFile::fake()->create('skrip.php', 10, 'application/x-php'))
            ->assertHasErrors(['lampiranBalasan']);
    }
}

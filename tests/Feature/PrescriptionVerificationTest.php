<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Modules\Prescription\Actions\VerifyPrescriptionAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrescriptionVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacist_can_approve_prescription_with_valid_sip(): void
    {
        $apoteker = Pengguna::factory()->create([
            'peran' => 'apoteker',
            'aktif' => true,
        ]);

        $pesanan = Pesanan::factory()->create([
            'status' => 'menunggu_verifikasi',
        ]);

        $resep = Resep::factory()->create([
            'id_pesanan' => $pesanan->id_pesanan,
            'status_verifikasi' => 'menunggu',
        ]);

        $action = app(VerifyPrescriptionAction::class);
        $action->execute($pesanan, $apoteker->id_pengguna, 'disetujui', '503/SIP/2024');

        $this->assertSame('disetujui', $resep->fresh()->status_verifikasi);
        $this->assertSame('503/SIP/2024', $resep->fresh()->sip_dokter);
        $this->assertTrue((bool) $resep->fresh()->sip_valid);
        $this->assertSame($apoteker->id_pengguna, $resep->fresh()->diverifikasi_oleh);
        $this->assertSame('diproses', $pesanan->fresh()->status);

        $this->assertDatabaseHas('log_audit', [
            'id_aktor' => $apoteker->id_pengguna,
            'aksi' => 'TELAAH_RESEP',
            'id_entitas' => $resep->id_resep,
        ]);
    }

    public function test_approving_prescription_with_invalid_sip_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $apoteker = Pengguna::factory()->create([
            'peran' => 'apoteker',
            'aktif' => true,
        ]);

        $pesanan = Pesanan::factory()->create([
            'status' => 'menunggu_verifikasi',
        ]);

        Resep::factory()->create([
            'id_pesanan' => $pesanan->id_pesanan,
            'status_verifikasi' => 'menunggu',
        ]);

        $action = app(VerifyPrescriptionAction::class);
        $action->execute($pesanan, $apoteker->id_pengguna, 'disetujui', 'SIP-TIDAK-VALID');
    }

    public function test_pharmacist_can_reject_prescription_with_mandatory_reason(): void
    {
        $apoteker = Pengguna::factory()->create([
            'peran' => 'apoteker',
            'aktif' => true,
        ]);

        $pesanan = Pesanan::factory()->create([
            'status' => 'menunggu_verifikasi',
        ]);

        $resep = Resep::factory()->create([
            'id_pesanan' => $pesanan->id_pesanan,
            'status_verifikasi' => 'menunggu',
        ]);

        $alasanPenolakan = 'Dosis sediaan antibiotik melampaui rentang terapi pediatri.';
        $action = app(VerifyPrescriptionAction::class);
        $action->execute($pesanan, $apoteker->id_pengguna, 'ditolak', null, $alasanPenolakan);

        $this->assertSame('ditolak', $resep->fresh()->status_verifikasi);
        $this->assertSame($alasanPenolakan, $resep->fresh()->catatan_apoteker);
        $this->assertFalse((bool) $resep->fresh()->sip_valid);
        $this->assertSame('dibatalkan', $pesanan->fresh()->status);
    }

    public function test_rejecting_prescription_without_reason_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $apoteker = Pengguna::factory()->create([
            'peran' => 'apoteker',
            'aktif' => true,
        ]);

        $pesanan = Pesanan::factory()->create([
            'status' => 'menunggu_verifikasi',
        ]);

        Resep::factory()->create([
            'id_pesanan' => $pesanan->id_pesanan,
            'status_verifikasi' => 'menunggu',
        ]);

        $action = app(VerifyPrescriptionAction::class);
        $action->execute($pesanan, $apoteker->id_pengguna, 'ditolak', null, '');
    }
}

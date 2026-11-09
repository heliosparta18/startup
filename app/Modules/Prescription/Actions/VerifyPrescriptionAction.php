<?php

namespace App\Modules\Prescription\Actions;

use App\Models\LogAudit;
use App\Models\Pesanan;
use App\Models\Resep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyPrescriptionAction
{
    public function execute(Pesanan $pesanan, string $apotekerId, string $keputusan, ?string $nomorSip = null, ?string $alasan = null): void
    {
        DB::transaction(function () use ($pesanan, $apotekerId, $keputusan, $nomorSip, $alasan) {
            $resep = Resep::where('id_pesanan', $pesanan->id_pesanan)->firstOrFail();

            if ($keputusan === 'disetujui') {
                if (empty($nomorSip) || ! preg_match('/^[0-9]{3}\/SIP\/[0-9]{4}$/', $nomorSip)) {
                    throw ValidationException::withMessages([
                        'nomor_sip' => 'Nomor SIP dokter tidak valid. Format resmi: 503/SIP/2024',
                    ]);
                }

                $resep->update([
                    'status_verifikasi' => 'disetujui',
                    'sip_dokter' => $nomorSip,
                    'sip_valid' => true,
                    'diverifikasi_oleh' => $apotekerId,
                ]);

                $pesanan->update(['status' => 'diproses']);
            } else {
                if (empty($alasan)) {
                    throw ValidationException::withMessages([
                        'alasan' => 'Wajib menyertakan alasan penolakan resep.',
                    ]);
                }

                $resep->update([
                    'status_verifikasi' => 'ditolak',
                    'catatan_apoteker' => $alasan,
                    'sip_valid' => false,
                    'diverifikasi_oleh' => $apotekerId,
                ]);

                $pesanan->update(['status' => 'dibatalkan']);
            }

            LogAudit::create([
                'id_audit' => (string) Str::uuid(),
                'id_aktor' => $apotekerId,
                'aksi' => 'TELAAH_RESEP',
                'nama_entitas' => 'resep',
                'id_entitas' => $resep->id_resep,
                'payload' => [
                    'keputusan' => $keputusan,
                    'nomor_sip' => $nomorSip,
                    'alasan' => $alasan,
                ],
                'dibuat_pada' => now(),
            ]);
        });
    }
}

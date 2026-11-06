<?php

namespace App\Modules\Prescription\Actions;

use App\Models\LogAudit;
use App\Models\Pesanan;
use App\Models\ResepDokter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyPrescriptionAction
{
    public function execute(Pesanan $pesanan, string $apotekerId, string $keputusan, ?string $nomorSip = null, ?string $alasan = null): void
    {
        DB::transaction(function () use ($pesanan, $apotekerId, $keputusan, $nomorSip, $alasan) {
            $resep = ResepDokter::where('id_pesanan', $pesanan->id_pesanan)->firstOrFail();

            if ($keputusan === 'disetujui') {
                if (empty($nomorSip) || ! preg_match('/^[0-9]{3}\/SIP\/[0-9]{4}$/', $nomorSip)) {
                    throw ValidationException::withMessages([
                        'nomor_sip' => 'Nomor SIP dokter tidak valid. Format resmi: 503/SIP/2024',
                    ]);
                }

                $resep->update([
                    'status_telaah' => 'disetujui',
                    'nomor_sip_dokter' => $nomorSip,
                    'id_apoteker_penelaah' => $apotekerId,
                    'ditelaah_pada' => now(),
                ]);

                $pesanan->update(['status_pesanan' => 'menunggu_pembayaran']);
            } else {
                if (empty($alasan)) {
                    throw ValidationException::withMessages([
                        'alasan' => 'Wajib menyertakan alasan penolakan resep.',
                    ]);
                }

                $resep->update([
                    'status_telaah' => 'ditolak',
                    'catatan_telaah' => $alasan,
                    'id_apoteker_penelaah' => $apotekerId,
                    'ditelaah_pada' => now(),
                ]);

                $pesanan->update(['status_pesanan' => 'dibatalkan']);
            }

            LogAudit::create([
                'id_log' => (string) Str::uuid(),
                'id_pengguna' => $apotekerId,
                'aksi' => 'TELAAH_RESEP',
                'tabel_target' => 'resep_dokter',
                'id_target' => $resep->id_resep,
                'detail_perubahan' => json_encode(['keputusan' => $keputusan, 'nomor_sip' => $nomorSip]),
                'waktu' => now(),
            ]);
        });
    }
}

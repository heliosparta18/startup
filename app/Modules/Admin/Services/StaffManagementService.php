<?php

namespace App\Modules\Admin\Services;

use App\Models\PeranClientPengguna;
use App\Models\PetaPengawasStaf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffManagementService
{
    public function tetapkanPengawas(string $idClient, string $idApj, string $idStaf, ?string $berlakuDari = null): PetaPengawasStaf
    {
        return DB::transaction(function () use ($idClient, $idApj, $idStaf, $berlakuDari) {
            return PetaPengawasStaf::updateOrCreate(
                [
                    'id_client' => $idClient,
                    'id_staf' => $idStaf,
                    'id_pengawas' => $idApj,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'berlaku_dari' => $berlakuDari ?? Carbon::today()->toDateString(),
                    'dibuat_pada' => Carbon::now(),
                ]
            );
        });
    }

    public function beriPeranCabang(string $idClient, string $idPengguna, string $peran, ?string $ditugaskanDari = null): PeranClientPengguna
    {
        return PeranClientPengguna::updateOrCreate(
            [
                'id_client' => $idClient,
                'id_pengguna' => $idPengguna,
            ],
            [
                'id' => (string) Str::uuid(),
                'peran' => $peran,
                'ditugaskan_dari' => $ditugaskanDari ?? Carbon::today()->toDateString(),
                'dibuat_pada' => Carbon::now(),
            ]
        );
    }

    /**
     * @return Collection<int, PeranClientPengguna>
     */
    public function getStaffByClient(string $idClient): Collection
    {
        return PeranClientPengguna::with('pengguna')
            ->where('id_client', $idClient)
            ->get();
    }

    /**
     * @return Collection<int, PetaPengawasStaf>
     */
    public function getPengawasByClient(string $idClient): Collection
    {
        return PetaPengawasStaf::with(['staf', 'pengawas'])
            ->where('id_client', $idClient)
            ->get();
    }
}

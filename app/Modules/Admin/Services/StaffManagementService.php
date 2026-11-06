<?php

namespace App\Modules\Admin\Services;

use App\Models\PetaPengawasStaf;
use App\Models\PeranClientPengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffManagementService
{
    public function tetapkanPengawas(string $idClient, string $idApj, string $idStaf): PetaPengawasStaf
    {
        return DB::transaction(function () use ($idClient, $idApj, $idStaf) {
            return PetaPengawasStaf::updateOrCreate(
                [
                    'id_client' => $idClient,
                    'id_staf' => $idStaf,
                ],
                [
                    'id_peta' => (string) Str::uuid(),
                    'id_apj' => $idApj,
                    'aktif' => true,
                ]
            );
        });
    }

    public function beriPeranCabang(string $idClient, string $idPengguna, string $peran): PeranClientPengguna
    {
        return PeranClientPengguna::updateOrCreate(
            [
                'id_client' => $idClient,
                'id_pengguna' => $idPengguna,
            ],
            [
                'id_peran_client' => (string) Str::uuid(),
                'peran' => $peran,
                'aktif' => true,
            ]
        );
    }
}
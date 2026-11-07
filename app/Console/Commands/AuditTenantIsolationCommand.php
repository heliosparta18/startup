<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Pesanan;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditTenantIsolationCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tenant:audit';

    /**
     * @var string
     */
    protected $description = 'Audit kepatuhan isolasi multi-tenant dan verifikasi integritas referensial id_client';

    public function handle(): int
    {
        $this->info('Memulai audit integritas isolasi multi-tenant...');

        $clients = Client::all();
        $this->info("Total cabang terdaftar: {$clients->count()}");

        $models = [
            'Pesanan' => Pesanan::class,
            'StokClient' => StokClient::class,
            'RingkasanPeriode' => RingkasanPeriodePenjualan::class,
        ];

        $auditRows = [];
        $hasViolation = false;

        foreach ($models as $name => $modelClass) {
            /** @var Builder<Model> $query */
            $query = $modelClass::withoutTenancy();
            $totalCount = $query->count();

            // Cek apakah ada record dengan id_client kosong
            $orphanNullCount = (clone $query)->whereNull('id_client')->count();

            // Cek apakah ada record dengan id_client tidak terdaftar
            $clientIds = $clients->pluck('id_client')->all();
            $unregisteredCount = (clone $query)->whereNotIn('id_client', $clientIds)->count();

            $status = ($orphanNullCount === 0 && $unregisteredCount === 0)
                ? 'TERISOLASI 100%'
                : 'PELANGGARAN';

            if ($status === 'PELANGGARAN') {
                $hasViolation = true;
            }

            $auditRows[] = [
                $name,
                $totalCount,
                $orphanNullCount,
                $unregisteredCount,
                $status,
            ];
        }

        $this->table(
            ['Entitas Domain', 'Total Baris', 'NULL id_client', 'Unregistered Client', 'Status Audit'],
            $auditRows
        );

        if ($hasViolation) {
            $this->error('Audit GAGAL: Terdeteksi kebocoran data multi-tenant!');

            return Command::FAILURE;
        }

        $this->info('Audit SELESAI: Seluruh entitas domain terisolasi sempurna per cabang.');

        return Command::SUCCESS;
    }
}

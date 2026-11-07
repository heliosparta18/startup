<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pesanan;
use App\Models\RingkasanPeriodePenjualan;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use App\Modules\Reporting\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SalesReportAndPeriodLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_period_locks_record_permanently_and_prevents_duplicate(): void
    {
        $clientId = (string) Str::uuid();

        // 1. Eksekusi penutupan periode kas
        $action = new CloseSalesPeriodAction();
        $record = $action->execute($clientId, '2026-11-01', '2026-11-30');

        // Pastikan tersimpan dan status terkunci aktif
        $this->assertTrue((bool) $record->status_terkunci);
        $this->assertDatabaseHas('ringkasan_periode_penjualan', [
            'id_client' => $clientId,
            'periode_mulai' => '2026-11-01',
            'periode_selesai' => '2026-11-30',
            'status_terkunci' => 1,
        ]);

        // 2. Coba tutup kembali periode yang sama, pastikan sistem menolak (ADR-0008)
        $this->expectException(RuntimeException::class);
        $action->execute($clientId, '2026-11-01', '2026-11-30');
    }

    public function test_sales_report_service_calculates_summary_correctly(): void
    {
        $clientId = (string) Str::uuid();

        // Buat data pesanan untuk pengujian
        Pesanan::factory()->create([
            'id_client' => $clientId,
            'total_harga' => 50000,
            'status' => 'selesai',
            'dibuat_pada' => '2026-11-10 10:00:00',
        ]);

        Pesanan::factory()->create([
            'id_client' => $clientId,
            'total_harga' => 75000,
            'status' => 'diproses',
            'dibuat_pada' => '2026-11-15 14:00:00',
        ]);

        $service = new SalesReportService();
        $summary = $service->getSummary($clientId, '2026-11-01', '2026-11-30');

        $this->assertEquals(125000, $summary['total_pendapatan']);
        $this->assertEquals(2, $summary['total_pesanan']);
        $this->assertEquals(62500, $summary['rata_rata_transaksi']);
    }

    public function test_csv_export_mitigates_formula_injection_characters(): void
    {
        $exportService = new SalesReportExportService();

        // Karakter berbahaya formula Excel wajib diawali apostrof (')
        $this->assertEquals("'=SUM(A1:A10)", $exportService->sanitizeForCsv('=SUM(A1:A10)'));
        $this->assertEquals("'+12345", $exportService->sanitizeForCsv('+12345'));
        $this->assertEquals("'-100", $exportService->sanitizeForCsv('-100'));
        $this->assertEquals("'@payload", $exportService->sanitizeForCsv('@payload'));

        // Karakter teks normal tidak boleh diubah
        $this->assertEquals("ORD-20261107-001", $exportService->sanitizeForCsv('ORD-20261107-001'));
    }
}
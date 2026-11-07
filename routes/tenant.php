<?php

use App\Models\Client;
use App\Models\Obat;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use App\Modules\Reporting\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/**
 * Konteks OPERATOR TENANT / APOTEKER CABANG (internal). Prefix: cabang/{client}, name: tenant.*
 * Melayani verifikasi resep (#ORD-20260917-0042) dan inspeksi stok batch FEFO (Mockup Layar 2).
 */
Route::get('/dashboard', function (string $client) {
    $resepPending = Resep::query()
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->first();

    $fefoBatches = Obat::query()
        ->with('stokClient')
        ->orderBy('expired', 'asc')
        ->get();

    return view('tenant.dashboard', [
        'client' => $client,
        'resepPending' => $resepPending,
        'fefoBatches' => $fefoBatches,
    ]);
})->name('dashboard');

Route::post('/resep/{id}/verifikasi', function (string $client, string $id, Request $request) {
    $action = $request->input('action', 'disetujui');
    $catatan = $request->input('catatan', 'Resep asli dan masih berlaku.');

    $resep = Resep::query()->find($id);
    if ($resep) {
        $resep->update([
            'status_verifikasi' => $action,
            'catatan_apoteker' => $catatan,
            'sip_valid' => true,
            'dosis_valid' => true,
        ]);
        if ($resep->pesanan) {
            $resep->pesanan->update([
                'status' => $action === 'disetujui' ? 'diproses' : 'dibatalkan',
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => "Resep {$id} berhasil diverifikasi ({$action}).",
        'status_verifikasi' => $action,
    ]);
})->name('resep.verifikasi');

Route::post('/stok/{id}/penyesuaian', function (string $client, string $id, Request $request) {
    $stok = (int) $request->input('jumlah_stok', 12);
    $stokRecord = StokClient::query()->find($id);
    if ($stokRecord) {
        $stokRecord->update(['jumlah_stok' => $stok]);
    }

    return response()->json([
        'success' => true,
        'message' => "Stok obat pada cabang {$client} berhasil disesuaikan.",
        'jumlah_stok' => $stok,
    ]);
})->name('stok.penyesuaian');

Route::get('/pesanan/{id}', function (string $client, string $id) {
    $pesanan = Pesanan::withoutGlobalScopes()->find($id);

    if (! $pesanan) {
        abort(404, 'Pesanan tidak ditemukan.');
    }

    Gate::authorize('view', $pesanan);

    return response()->json([
        'success' => true,
        'pesanan' => $pesanan,
    ]);
})->name('pesanan.detail');


Route::get('/laporan', function (string $client, Request $request, SalesReportService $reportService, SalesReportExportService $exportService) {
    // Menemukan UUID client berdasarkan slug cabang atau id_client
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $mulai = $request->query('periode_mulai', now()->startOfMonth()->toDateString());
    $selesai = $request->query('periode_selesai', now()->toDateString());

    // Stream download CSV jika ada query export=csv
    if ($request->query('export') === 'csv') {
        return $exportService->exportCsv((string) $clientId, $mulai, $selesai);
    }

    $summary = $reportService->getSummary((string) $clientId, $mulai, $selesai);
    $terkunci = RingkasanPeriodePenjualan::where('id_client', $clientId)
        ->where('periode_mulai', $mulai)
        ->where('periode_selesai', $selesai)
        ->where('status_terkunci', true)
        ->exists();

    return view('admin.laporan-penjualan', [
        'client' => $client,
        'summary' => $summary,
        'periodeMulai' => $mulai,
        'periodeSelesai' => $selesai,
        'ringkasanTerkunci' => $terkunci,
    ]);
})->name('laporan.index');

Route::post('/laporan/tutup-periode', function (string $client, Request $request, CloseSalesPeriodAction $action) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $mulai = $request->input('periode_mulai');
    $selesai = $request->input('periode_selesai');

    try {
        $action->execute((string) $clientId, $mulai, $selesai);
        return back()->with('success', "Periode kas {$mulai} s/d {$selesai} berhasil ditutup dan berstatus terkunci permanen.");
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
})->name('laporan.tutup-periode');
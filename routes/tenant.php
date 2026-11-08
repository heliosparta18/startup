<?php

use App\Models\Resep;
use App\Models\StokClient;
use App\Modules\Catalog\Services\StockFefoService;
use App\Modules\Prescription\Actions\VerifyPrescriptionAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Konteks OPERATOR TENANT / APOTEKER CABANG (internal). Prefix: cabang/{client}, name: tenant.*
 * Melayani verifikasi resep (#ORD-20260917-0042) dan inspeksi stok batch FEFO (Mockup Layar 2).
 */
Route::get('/dashboard', function (string $client, StockFefoService $fefoService) {
    $resepPending = Resep::query()
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->first();

    $fefoBatches = $fefoService->getBatchesByFefo($client);

    return view('tenant.dashboard', [
        'client' => $client,
        'resepPending' => $resepPending,
        'fefoBatches' => $fefoBatches,
    ]);
})->name('dashboard');

Route::get('/resep', function (string $client) {
    $resepPending = Resep::query()
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    return view('tenant.verifikasi-resep', [
        'client' => $client,
        'resepPending' => $resepPending,
    ]);
})->name('resep.antrean');

Route::post('/resep/{id}/verifikasi', function (string $client, string $id, Request $request, VerifyPrescriptionAction $verifier) {
    $action = $request->input('action', 'disetujui');
    $nomorSip = $request->input('nomor_sip', '503/SIP/2024');
    $catatan = $request->input('alasan', $request->input('catatan', 'Resep asli dan dosis klinis absah.'));

    $resep = Resep::query()->where('id_resep', $id)->first();

    if ($resep && $resep->pesanan) {
        $user = $request->user();
        $apotekerId = $user->id_pengguna ?? ($user->id ?? 'a1b2c3d4-0001-4000-8000-000000000001');

        $verifier->execute($resep->pesanan, $apotekerId, $action, $nomorSip, $catatan);
    }

    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
            'success' => true,
            'message' => "Resep {$id} berhasil diverifikasi ({$action}).",
            'status_verifikasi' => $action,
        ]);
    }

    return back()->with('success', "Resep {$id} berhasil diverifikasi ({$action}).");
})->name('resep.verifikasi');

Route::get('/stok-fefo', function (string $client, StockFefoService $fefoService) {
    $fefoBatches = $fefoService->getBatchesByFefo($client);

    return view('tenant.stok-fefo', [
        'client' => $client,
        'fefoBatches' => $fefoBatches,
    ]);
})->name('stok.fefo');

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

<?php

use App\Models\Obat;
use App\Models\Resep;
use App\Models\StokClient;
use Illuminate\Http\Request;
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

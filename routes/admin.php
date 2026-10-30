<?php

use App\Models\Client;
use App\Models\RingkasanPeriodePenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Konteks SUPER ADMIN PUSAT (internal). Prefix: admin, name: admin.*
 * Melayani Executive KPI Dashboard, Tutup Periode & Kunci Snapshot, dan Ekspor Laporan (Mockup Layar 3-4).
 */
Route::get('/dashboard', function () {
    $clients = Client::query()->with('stok.obat')->get();
    $summaries = RingkasanPeriodePenjualan::query()->orderBy('periode_mulai', 'desc')->get();

    return view('admin.dashboard', [
        'clients' => $clients,
        'summaries' => $summaries,
    ]);
})->name('dashboard');

Route::post('/tutup-periode', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Periode penjualan 01-16 Sep 2026 berhasil ditutup dan seluruh snapshot transaksi terkunci.',
        'status_terkunci' => true,
        'periode' => '01-16 Sep 2026',
        'omzet_terkunci' => 142850000,
        'total_pesanan' => 1840,
    ]);
})->name('periode.tutup');

Route::get('/ekspor-laporan', function () {
    return response()->json([
        'success' => true,
        'message' => 'Laporan penjualan dan rekap konsolidasi lintas cabang siap diunduh.',
        'format' => 'xlsx',
        'filename' => 'Rekap_Penjualan_Apotek_Connect_01-16_Sep_2026.xlsx',
    ]);
})->name('laporan.ekspor');

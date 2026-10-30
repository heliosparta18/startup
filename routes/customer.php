<?php

use App\Models\KategoriObat;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Konteks PELANGGAN (publik, anonim). Prefix: klien/{client}, name: customer.*
 * Melayani katalog OmniPharma, detail obat, pencarian, dan unggah resep digital (Mockup Layar 1).
 */
Route::get('/', function (string $client) {
    $kategori = KategoriObat::query()->orderBy('nama_kategori')->get();
    $obat = Obat::query()->with(['kategori', 'stokClient'])->orderBy('nama_obat')->get();

    return view('customer.home', [
        'client' => $client,
        'kategori' => $kategori,
        'obat' => $obat,
    ]);
})->name('home');

Route::get('/obat/{id}', function (string $client, string $id) {
    $obat = Obat::query()->with(['kategori', 'stokClient'])->find($id);

    return view('customer.obat-detail', [
        'client' => $client,
        'obat' => $obat,
        'id' => $id,
    ]);
})->name('obat.detail');

Route::post('/resep/unggah', function (string $client, Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Resep dokter berhasil diunggah dan masuk antrean verifikasi apoteker.',
        'nomor_pesanan' => '#ORD-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
        'client' => $client,
        'status' => 'menunggu_verifikasi',
    ]);
})->name('resep.upload');

<?php

use App\Http\Requests\Customer\UnggahResepRequest;
use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Modules\Catalog\Livewire\PublicCatalog;
use App\Modules\Ordering\Http\Controllers\CartController;
use App\Modules\Ordering\Http\Controllers\CheckoutController;
use App\Modules\Ordering\Http\Controllers\OrderTrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

/**
 * Konteks PELANGGAN (publik, anonim). Prefix: klien/{client}, name: customer.*
 * Melayani katalog OmniPharma, detail obat, pencarian, unggah resep digital (Mockup Layar 1),
 * serta keranjang belanja Redis, checkout atomik, dan pembayaran QRIS (Modul Guntur M08-M11).
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
Route::get('/katalog', PublicCatalog::class)->name('catalog');

Route::get('/obat/{id}', function (string $client, string $id) {
    $obat = Obat::query()->with(['kategori', 'stokClient'])->find($id);

    return view('customer.obat-detail', [
        'client' => $client,
        'obat' => $obat,
        'id' => $id,
    ]);
})->name('obat.detail');

Route::post('/resep/unggah', function (string $client, UnggahResepRequest $request) {
    $urlGambar = null;
    $namaBerkas = null;

    if ($request->hasFile('foto_resep')) {
        $file = $request->file('foto_resep');
        if ($file->isValid()) {
            $namaBerkas = $file->getClientOriginalName();
            $path = $file->store('resep', 'public');
            $urlGambar = '/storage/'.$path;
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Resep dokter berhasil diunggah dan masuk antrean verifikasi apoteker.',
        'nomor_pesanan' => '#ORD-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
        'client' => $client,
        'status' => 'menunggu_verifikasi',
        'url_gambar' => $urlGambar,
        'nama_berkas' => $namaBerkas,
    ]);
})->name('resep.upload');

$resolveCustomerSession = function (Request $request): string {
    $cookie = $request->cookie('omnipharma_customer_session') ?? $request->cookie('omni_session');
    if (is_string($cookie) && $cookie !== '') {
        if (! $request->hasCookie('omnipharma_customer_session')) {
            Cookie::queue('omnipharma_customer_session', $cookie, 60 * 24 * 30);
        }

        return $cookie;
    }

    $sessionId = $request->session()->getId();
    $token = $sessionId !== '' ? $sessionId : 'guest-'.md5((string) $request->ip());

    Cookie::queue('omnipharma_customer_session', $token, 60 * 24 * 30);

    return $token;
};

// PERTEMUAN 08: Keranjang Belanja Redis Multi-Tenant
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// PERTEMUAN 09: Checkout Atomik dengan Pessimistic Lock & Idempotency
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

// PERTEMUAN 10 & 12: Dynamic QRIS Sandbox Generator & Reverb Order Tracking
Route::get('/order/{id}/payment', [OrderTrackingController::class, 'payment'])->name('order.payment');
Route::post('/order/{id}/cancel', [OrderTrackingController::class, 'cancel'])->name('order.cancel');
Route::get('/order/{id}/track', [OrderTrackingController::class, 'track'])->name('order.track');

<?php

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\Request;
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

$resolveCustomerSession = function (Request $request): string {
    $cookie = $request->cookie('omni_session');
    if (is_string($cookie) && $cookie !== '') {
        return $cookie;
    }

    $sessionId = $request->session()->getId();

    return $sessionId !== '' ? $sessionId : 'guest-'.md5((string) $request->ip());
};

// PERTEMUAN 08: Keranjang Belanja Redis Multi-Tenant
Route::get('/cart', function (string $client, Request $request, CartService $cartService, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $items = $cartService->getItems($sessionId, $clientId);
    $total = $cartService->getTotal($sessionId, $clientId);

    return view('customer.cart', [
        'client' => $client,
        'items' => $items,
        'total' => $total,
    ]);
})->name('cart');

Route::post('/cart/add', function (string $client, Request $request, CartService $cartService, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $obatId = (string) $request->input('id_obat');
    $qty = (int) $request->input('jumlah', 1);

    $cartService->addItem($sessionId, $clientId, $obatId, $qty);

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Obat berhasil ditambahkan ke keranjang.',
        ]);
    }

    return redirect()->route('customer.cart', ['client' => $client]);
})->name('cart.add');

Route::post('/cart/update', function (string $client, Request $request, CartService $cartService, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $obatId = (string) $request->input('id_obat');
    $qty = (int) $request->input('jumlah', 1);

    $cartService->updateItem($sessionId, $clientId, $obatId, $qty);

    return response()->json(['success' => true]);
})->name('cart.update');

Route::post('/cart/remove', function (string $client, Request $request, CartService $cartService, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $obatId = (string) $request->input('id_obat');

    $cartService->removeItem($sessionId, $clientId, $obatId);

    return response()->json(['success' => true]);
})->name('cart.remove');

// PERTEMUAN 09: Checkout Atomik dengan Pessimistic Lock & Snapshot Harga
Route::get('/checkout', function (string $client, Request $request, CartService $cartService, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $items = $cartService->getItems($sessionId, $clientId);

    if (empty($items)) {
        return redirect()->route('customer.cart', ['client' => $client])
            ->with('error', 'Keranjang belanja Anda masih kosong.');
    }

    $total = $cartService->getTotal($sessionId, $clientId);
    $clientModel = $tenantContext->get();

    return view('customer.checkout', [
        'client' => $client,
        'clientModel' => $clientModel,
        'items' => $items,
        'total' => $total,
    ]);
})->name('checkout');

Route::post('/checkout', function (string $client, Request $request, CartService $cartService, CreateOrderAction $createOrderAction, TenantContext $tenantContext) use ($resolveCustomerSession) {
    $clientId = $tenantContext->id();
    $sessionId = $resolveCustomerSession($request);
    $items = $cartService->getItems($sessionId, $clientId);

    if (empty($items)) {
        return redirect()->route('customer.cart', ['client' => $client])
            ->with('error', 'Keranjang belanja Anda kosong.');
    }

    $user = $request->user();
    $userId = $user ? Pengguna::where('telepon_email', $user->email)->value('id_pengguna') : null;
    $metodeLayanan = (string) $request->input('metode_layanan', 'click_collect');

    try {
        $pesanan = $createOrderAction->execute($clientId, $userId, $items, $metodeLayanan);
        $cartService->clear($sessionId, $clientId);

        return redirect()->route('customer.order.payment', [
            'client' => $client,
            'id' => $pesanan->id_pesanan,
        ]);

    } catch (Throwable $e) {
        return redirect()->route('customer.checkout', ['client' => $client])
            ->with('error', $e->getMessage());
    }
})->name('checkout.process');

// PERTEMUAN 10: Dynamic QRIS Sandbox Generator (EMVCo MPM)
Route::get('/order/{id}/payment', function (string $client, string $id, PaymentGatewayInterface $gateway, TenantContext $tenantContext) {
    $pesanan = Pesanan::withoutTenancy()->with(['items', 'pembayaran'])->find($id);

    if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
        abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
    }

    $qris = $gateway->generateQris($pesanan);

    return view('customer.payment', [
        'client' => $client,
        'pesanan' => $pesanan,
        'qris' => $qris,
    ]);
})->name('order.payment');

Route::post('/order/{id}/cancel', function (string $client, string $id, CancelOrderAction $cancelOrderAction, TenantContext $tenantContext) {
    $pesanan = Pesanan::withoutTenancy()->with('items')->find($id);

    if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
        abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
    }

    $cancelOrderAction->execute($pesanan, 'Dibatalkan oleh pelanggan');

    return redirect()->route('customer.order.payment', ['client' => $client, 'id' => $pesanan->id_pesanan])
        ->with('status', 'Pesanan berhasil dibatalkan dan stok telah dikembalikan ke cabang.');
})->name('order.cancel');

Route::get('/order/{id}/track', function (string $client, string $id, TenantContext $tenantContext) {
    $pesanan = Pesanan::withoutTenancy()->with(['items', 'pembayaran'])->find($id);

    if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
        abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
    }

    return view('customer.order-track', [
        'client' => $client,
        'pesanan' => $pesanan,
    ]);
})->name('order.track');

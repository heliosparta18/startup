<?php

use App\Http\Requests\Customer\UnggahResepRequest;
use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\Scopes\TenantScope;
use App\Modules\Catalog\Livewire\PublicCatalog;
use App\Modules\Ordering\Http\Controllers\CartController;
use App\Modules\Ordering\Http\Controllers\CheckoutController;
use App\Modules\Ordering\Http\Controllers\OrderTrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
    $obat = Obat::query()
        ->with([
            'kategori',
            'subkategori',
            'stokClient' => function ($q) {
                $q->withoutGlobalScope(TenantScope::class)->with('client');
            },
        ])
        ->where('id_obat', $id)
        ->orWhere('slug', $id)
        ->first();

    return view('customer.obat-detail', [
        'client' => $client,
        'obat' => $obat,
        'id' => $id,
    ]);
})->name('obat.detail');

Route::post('/resep/unggah', function (string $client, UnggahResepRequest $request) use ($resolveCustomerSession) {
    // 1. Simpan berkas foto resep ke storage publik jika ada
    $urlGambar = null;
    $namaBerkas = null;

    if ($request->hasFile('foto_resep')) {
        $file = $request->file('foto_resep');
        if ($file && $file->isValid()) {
            $namaBerkas = $file->getClientOriginalName();
            $path = $file->store('resep', 'public');
            $urlGambar = '/storage/'.$path;
        }
    }

    if (! $urlGambar) {
        $urlGambar = '/images/resep-sample.jpg';
    }

    // 2. Resolve client / tenant cabang aktif
    $clientModel = Client::where('id_client', $client)
        ->orWhere('slug', $client)
        ->orWhere('nama_client', $client)
        ->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    // 3. Resolve customer account / session
    $sessionToken = $resolveCustomerSession($request);

    $namaPasien = trim((string) ($request->input('nama_pasien') ?: $request->input('namaPasien') ?: 'Pelanggan Tamu'));
    $namaDokter = trim((string) ($request->input('nama_dokter') ?: $request->input('namaDokter') ?: 'dr. Budi, Sp.PD'));
    $sipDokter = trim((string) ($request->input('sip_dokter') ?: $request->input('sipDokter') ?: '503/SIP/2024'));
    $catatan = trim((string) ($request->input('catatan') ?: $request->input('catatan_pasien') ?: '')) ?: null;

    $resolvedUserId = null;
    $user = $request->user();
    if ($user) {
        if (isset($user->id_pengguna) && ! empty($user->id_pengguna)) {
            $resolvedUserId = $user->id_pengguna;
        } else {
            $pengguna = Pengguna::where('telepon_email', $user->email)->first();
            if (! $pengguna) {
                $pengguna = Pengguna::create([
                    'id_pengguna' => (string) Str::uuid(),
                    'nama_lengkap' => $user->name ?: $namaPasien,
                    'peran' => 'pelanggan',
                    'telepon_email' => $user->email,
                    'password' => $user->password ?: bcrypt(Str::random(16)),
                    'aktif' => true,
                ]);
            }
            $resolvedUserId = $pengguna->id_pengguna;
        }
    }

    if (! $resolvedUserId) {
        $guestUser = Pengguna::where('peran', 'pelanggan')->first();
        if (! $guestUser) {
            $guestUser = Pengguna::create([
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => $namaPasien ?: 'Pelanggan Tamu',
                'peran' => 'pelanggan',
                'telepon_email' => 'tamu-'.Str::random(8).'@omnipharma.test',
                'password' => bcrypt(Str::random(16)),
                'aktif' => true,
            ]);
        }
        $resolvedUserId = $guestUser->id_pengguna;
    }

    // 4. Terbitkan nomor pesanan (#ORD-YYYYMMDD-XXXX) dan antrean
    $orderNumber = '#ORD-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $todayCount = Pesanan::withoutTenancy()
        ->where('id_client', $clientId)
        ->whereDate('dibuat_pada', now()->toDateString())
        ->count();
    $nomorAntrean = 'A-'.str_pad((string) ($todayCount + 1), 3, '0', STR_PAD_LEFT);

    // 5. Buat record Pesanan baru dengan status menunggu_verifikasi
    $pesanan = Pesanan::withoutTenancy()->create([
        'id_pesanan' => (string) Str::uuid(),
        'id_pengguna' => $resolvedUserId,
        'id_client' => $clientId,
        'nomor_pesanan' => $orderNumber,
        'status' => 'menunggu_verifikasi',
        'metode_layanan' => 'click_collect',
        'token_sesi' => $sessionToken,
        'slot_waktu' => '15 Menit Lagi (Segera)',
        'nomor_antrean' => $nomorAntrean,
        'catatan_pesanan' => $catatan,
    ]);

    // 6. Buat record Resep baru dengan atribut id_pesanan, url_gambar, nama_dokter, sip_dokter, nama_pasien, catatan, status_verifikasi 'menunggu'
    $resep = Resep::create([
        'id_resep' => (string) Str::uuid(),
        'id_pesanan' => $pesanan->id_pesanan,
        'url_gambar' => $urlGambar,
        'nama_dokter' => $namaDokter,
        'sip_dokter' => $sipDokter,
        'nama_pasien' => $namaPasien,
        'catatan' => $catatan,
        'status_verifikasi' => 'menunggu',
        'sip_valid' => false,
        'dosis_valid' => false,
        'dibuat_pada' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Resep dokter berhasil diunggah dan masuk antrean verifikasi apoteker.',
        'nomor_pesanan' => $pesanan->nomor_pesanan,
        'id_pesanan' => $pesanan->id_pesanan,
        'id_resep' => $resep->id_resep,
        'client' => $client,
        'status' => 'menunggu_verifikasi',
        'url_gambar' => $urlGambar,
        'nama_berkas' => $namaBerkas,
    ]);
})->name('resep.upload');

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
Route::post('/order/{id}/check-status', [OrderTrackingController::class, 'checkStatus'])->name('order.check-status');
Route::post('/order/{id}/cancel', [OrderTrackingController::class, 'cancel'])->name('order.cancel');
Route::get('/order/{id}/track', [OrderTrackingController::class, 'track'])->name('order.track');
Route::get('/riwayat-pesanan', [OrderTrackingController::class, 'history'])->name('order.history');

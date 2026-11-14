<?php

use App\Http\Controllers\Auth\EmailOtpAuthController;
use App\Http\Controllers\Auth\FirebaseAuthSessionController;
use App\Models\Obat;
use App\Models\Scopes\TenantScope;
use App\Modules\Catalog\Services\CustomerSessionService;
use App\Modules\Ordering\Http\Controllers\CartController;
use App\Modules\Ordering\Http\Controllers\CheckoutController;
use App\Modules\Ordering\Http\Controllers\OrderTrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (auth()->check()) {
        if ($request->has('welcome') || $request->has('sambutan')) {
            return view('welcome');
        }

        $user = auth()->user();
        if ($user->hasRole('super_admin') || $user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('apoteker') || $user->role === 'apoteker') {
            $branchSlug = method_exists($user, 'getClientSlug') ? $user->getClientSlug() : 'banyuwangi-kota';

            return redirect()->route('tenant.dashboard', ['client' => $branchSlug]);
        }

        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/katalog', function (Request $request, CustomerSessionService $sessionService) {
    if (auth()->check()) {
        return redirect()->route('dashboard', $request->query());
    }

    $branch = session('active_branch') ?? session('active_client');
    if (! $branch) {
        $client = $sessionService->getActiveBranch($request);
        $branch = $client->slug ?? 'banyuwangi-kota';
    }

    return redirect()->route('customer.catalog', ['client' => $branch]);
})->name('katalog');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    $resolveBranchSlug = static function (Request $request): string {
        $user = $request->user();

        return $user && method_exists($user, 'getClientSlug')
            ? ($user->getClientSlug() ?: 'banyuwangi-kota')
            : 'banyuwangi-kota';
    };

    Route::get('/apoteker', fn (Request $request) => redirect()->route('tenant.dashboard', [
        'client' => $resolveBranchSlug($request),
    ]));
    Route::get('/antrean', fn (Request $request) => redirect()->route('tenant.antrean.index', [
        'client' => $resolveBranchSlug($request),
    ]));
    Route::get('/verifikasi-resep', fn (Request $request) => redirect()->route('tenant.resep.antrean', [
        'client' => $resolveBranchSlug($request),
    ]));
    Route::get('/stok-fefo', fn (Request $request) => redirect()->route('tenant.stok.fefo', [
        'client' => $resolveBranchSlug($request),
    ]));
    Route::get('/admin', fn () => redirect()->route('admin.dashboard'));
});
Route::post('/cabang/switch', function (Request $request) {
    $branch = $request->input('branch', 'banyuwangi-kota');
    session([
        'active_branch' => $branch,
        'active_client' => $branch,
    ]);

    return back();
})->name('branch.switch');

Route::get('/api/search-obat', function (Request $request) {
    $q = trim((string) $request->input('q', ''));
    $clientSlug = $request->input('client') ?: (session('active_branch') ?? 'banyuwangi-kota');

    if (strlen($q) < 2) {
        return response()->json([]);
    }

    $obats = Obat::query()
        ->with(['kategori', 'subkategori', 'stokClient' => fn ($sq) => $sq->withoutGlobalScopes()])
        ->where(function ($sub) use ($q) {
            $sub->where('nama_obat', 'like', "%{$q}%")
                ->orWhere('merek', 'like', "%{$q}%")
                ->orWhere('indikasi', 'like', "%{$q}%")
                ->orWhere('komposisi', 'like', "%{$q}%");
        })
        ->take(8)
        ->get()
        ->map(function ($obat) use ($clientSlug) {
            return [
                'id' => $obat->id_obat,
                'nama' => $obat->nama_obat,
                'merek' => $obat->merek,
                'kategori' => $obat->kategori->nama_kategori ?? 'Kesehatan',
                'sediaan' => $obat->satuan_kemasan ?: $obat->jenis_sediaan,
                'harga' => (int) $obat->harga,
                'harga_formatted' => 'Rp '.number_format($obat->harga, 0, ',', '.'),
                'gambar' => $obat->url_gambar,
                'badge' => $obat->badge_status,
                'tersedia' => $obat->isAvailableInBranch($clientSlug),
                'url' => route('obat.show', ['id' => $obat->slug ?: $obat->id_obat]),
            ];
        });

    return response()->json($obats);
})->name('api.search.obat');

Route::middleware(['tenant'])->group(function () {
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
    })->name('obat.show');

    Route::get('/produk/{id}', fn (string $client, string $id) => redirect()->route('obat.show', ['id' => $id]))
        ->name('produk.show');

    Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang');
    Route::get('/cart', fn () => redirect()->route('keranjang'))->name('cart.short');
    Route::post('/keranjang/tambah', [CartController::class, 'add'])->name('keranjang.add');
    Route::post('/keranjang/ubah', [CartController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/hapus', [CartController::class, 'remove'])->name('keranjang.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.store');

    Route::get('/pembayaran/{id}', [OrderTrackingController::class, 'payment'])->name('pembayaran.show');
    Route::get('/pesanan/{id}/pembayaran', [OrderTrackingController::class, 'payment'])->name('pesanan.pembayaran');
    Route::post('/pesanan/{id}/batal', [OrderTrackingController::class, 'cancel'])->name('pesanan.cancel');
    Route::get('/lacak-pesanan/{id}', [OrderTrackingController::class, 'track'])->name('pesanan.track');
    Route::get('/pesanan/{id}/lacak', [OrderTrackingController::class, 'track'])->name('pesanan.lacak');

    Route::get('/unggah-resep', fn () => redirect()->to(route('dashboard').'#unggah-resep'))
        ->name('resep.form');
});

Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/auth/send-otp', [EmailOtpAuthController::class, 'sendOtp'])->name('auth.otp.send');
});

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/auth/verify-otp', [EmailOtpAuthController::class, 'verifyOtp'])->name('auth.otp.verify');
    Route::post('/auth/register-otp', [EmailOtpAuthController::class, 'registerWithOtp'])->name('auth.otp.register');
});

Route::post('/auth/firebase-session', [FirebaseAuthSessionController::class, 'store'])->name('auth.firebase.session');
Route::match(['GET', 'POST'], '/auth/firebase-logout', [FirebaseAuthSessionController::class, 'destroy'])->name('auth.firebase.logout');
Route::get('/logout', [FirebaseAuthSessionController::class, 'destroy']);

Route::middleware(['auth'])->group(function () {
    Route::get('/auth/complete-profile', [FirebaseAuthSessionController::class, 'showCompleteProfile'])->name('auth.complete-profile');
    Route::post('/auth/complete-profile', [FirebaseAuthSessionController::class, 'saveCompleteProfile'])->name('auth.complete-profile.save');
});

require __DIR__.'/settings.php';

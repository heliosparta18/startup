<?php

use App\Http\Controllers\Auth\EmailOtpAuthController;
use App\Http\Controllers\Auth\FirebaseAuthSessionController;
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

Route::get('/katalog', function (\Illuminate\Http\Request $request, \App\Modules\Catalog\Services\CustomerSessionService $sessionService) {
    $branch = session('active_branch') ?? session('active_client');
    if (! $branch) {
        $client = $sessionService->getActiveBranch($request);
        $branch = $client?->slug ?? 'banyuwangi-kota';
    }

    return redirect()->route('customer.catalog', ['client' => $branch]);
})->name('katalog');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
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

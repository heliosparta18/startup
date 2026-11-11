<?php

use App\Http\Controllers\Auth\EmailOtpAuthController;
use App\Http\Controllers\Auth\FirebaseAuthSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
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

Route::middleware(['auth', 'verified'])->group(function () {
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
Route::post('/auth/firebase-logout', [FirebaseAuthSessionController::class, 'destroy'])->name('auth.firebase.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/auth/complete-profile', [FirebaseAuthSessionController::class, 'showCompleteProfile'])->name('auth.complete-profile');
    Route::post('/auth/complete-profile', [FirebaseAuthSessionController::class, 'saveCompleteProfile'])->name('auth.complete-profile.save');
});

require __DIR__.'/settings.php';

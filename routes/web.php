<?php

use App\Http\Controllers\Auth\EmailOtpAuthController;
use App\Http\Controllers\Auth\FirebaseAuthSessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::post('/auth/send-otp', [EmailOtpAuthController::class, 'sendOtp'])->name('auth.otp.send');
Route::post('/auth/verify-otp', [EmailOtpAuthController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::post('/auth/register-otp', [EmailOtpAuthController::class, 'registerWithOtp'])->name('auth.otp.register');
Route::post('/auth/firebase-session', [FirebaseAuthSessionController::class, 'store'])->name('auth.firebase.session');
Route::post('/auth/firebase-logout', [FirebaseAuthSessionController::class, 'destroy'])->name('auth.firebase.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/auth/complete-profile', [FirebaseAuthSessionController::class, 'showCompleteProfile'])->name('auth.complete-profile');
    Route::post('/auth/complete-profile', [FirebaseAuthSessionController::class, 'saveCompleteProfile'])->name('auth.complete-profile.save');
});

require __DIR__.'/settings.php';

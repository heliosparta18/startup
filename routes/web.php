<?php

use App\Http\Controllers\Auth\FirebaseAuthSessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::post('/auth/firebase-session', [FirebaseAuthSessionController::class, 'store'])->name('auth.firebase.session');
Route::post('/auth/firebase-logout', [FirebaseAuthSessionController::class, 'destroy'])->name('auth.firebase.logout');

require __DIR__.'/settings.php';

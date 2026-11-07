<?php

use App\Modules\Payments\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/qris', [PaymentWebhookController::class, 'handle'])->name('webhooks.qris');

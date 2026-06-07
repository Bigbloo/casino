<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

Route::middleware(['auth', 'throttle:10,1'])->group(function (): void {
    Route::get('/deposit/{userId}/{amount}', [PaymentController::class, 'checkout'])->name('deposit.checkout');
    Route::get('/deposit/success', [PaymentController::class, 'success'])->name('deposit.success');
    Route::get('/deposit/cancel', [PaymentController::class, 'cancel'])->name('deposit.cancel');
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:30,1')
    ->name('stripe.webhook');

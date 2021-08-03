<?php

use Illuminate\Support\Facades\Route;
use Payments\Http\Controllers\Admin\PaymentTypeController;
use Payments\Http\Controllers\Front\WebhookController;

Route::name('payments.')->group(function () {
    Route::post('payments/webhook',[WebhookController::class,'index'])->name('btc-pay-server-webhook');
});

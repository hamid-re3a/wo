<?php

use Illuminate\Support\Facades\Route;
use Payments\Http\Controllers\Front\WebhookController;

Route::name('payments.')->group(function () {
    Route::post('payments/webhook', [WebhookController::class, 'index'])->name('btc-pay-server-webhook');
    Route::name('currency.')->prefix("payments/currency")->group(function () {
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'store']);
        Route::post('edit',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'update']);
        Route::post('delete',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'delete']);
    });
    Route::name('driver.')->prefix("payments/driver")->group(function () {
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'store']);
        Route::post('edit',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'update']);
        Route::post('delete',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'delete']);
    });
});

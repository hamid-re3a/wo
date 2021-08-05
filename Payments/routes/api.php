<?php

use Illuminate\Support\Facades\Route;
use Payments\Http\Controllers\Front\WebhookController;

Route::name('payments.')->group(function () {
    Route::post('payments/webhook', [WebhookController::class, 'index'])->name('btc-pay-server-webhook');
    Route::name('currency.')->prefix("payments/currency")->group(function () {
        Route::get('',[\Payments\Http\Controllers\Front\PaymentCurrencyController::class,'index'])->name('index');
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'store'])->name('store');
        Route::post('edit',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'update'])->name('update');
        Route::post('delete',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'delete'])->name('delete');
    });
    Route::name('driver.')->prefix("payments/driver")->group(function () {
//        Route::get('',[\Payments\Http\Controllers\Front\PaymentDriverController::class,'index'])->name('index');
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'store'])->name('store');
        Route::post('edit',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'update'])->name('update');
        Route::post('delete',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'delete'])->name('delete');
    });
    Route::name('type.')->prefix("payments/type")->group(function () {
        Route::get('',[\Payments\Http\Controllers\Front\PaymentTypeController::class,'index'])->name('index');
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'store'])->name('store');
        Route::post('edit',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'update'])->name('update');
        Route::post('delete',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'delete'])->name('delete');
    });

    Route::post('test',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'tt'])->name('tt');

});

<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Payments\Http\Controllers\Front\InvoiceController;
use Payments\Http\Controllers\Front\WebhookController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-payment-admin)
 * list of all route admin section
 */
Route::middleware(['auth','role:super-admin|subscriptions-payment-admin'])->name('admin-payment.')->group(function () {

    Route::name('type.')->prefix("type")->group(function () {
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'store'])->name('store');
        Route::patch('edit',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'update'])->name('update');
        Route::delete('delete',[\Payments\Http\Controllers\Admin\PaymentTypeController::class,'delete'])->name('delete');
    });
});

/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::/*middleware(['role:client'])->*/name('customer.')->group(function () {
    Route::name('.invoice')->prefix("invoice")->group(function (){
        Route::post('cancel_invoice',[InvoiceController::class,'cancelInvoice'])->name('cancel_invoice');
    });
});

Route::post('webhook', [WebhookController::class, 'index'])->name('btc-pay-server-webhook');
Route::name('payments.')->middleware(['auth'])->group(function () {
    Route::name('currency.')->prefix("currency")->group(function () {
        Route::get('',[\Payments\Http\Controllers\Front\PaymentCurrencyController::class,'index'])->name('index');
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'store'])->name('store');
        Route::patch('edit',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'update'])->name('update');
        Route::delete('delete',[\Payments\Http\Controllers\Admin\PaymentCurrencyController::class,'delete'])->name('delete');
    });
    Route::name('driver.')->prefix("driver")->group(function () {
//        Route::get('',[\Payments\Http\Controllers\Front\PaymentDriverController::class,'index'])->name('index');
        Route::post('create',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'store'])->name('store');
        Route::patch('edit',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'update'])->name('update');
        Route::delete('delete',[\Payments\Http\Controllers\Admin\PaymentDriverController::class,'delete'])->name('delete');
    });


    Route::name('invoice.')->prefix('invoices')->group(function(){
        Route::get('/check-pending-order-invoice',[\Payments\Http\Controllers\Front\InvoiceController::class,'pendingOrderInvoice'])->name('check');
        Route::get('/',[\Payments\Http\Controllers\Front\InvoiceController::class,'index'])->name('get-list');
        Route::post('/',[\Payments\Http\Controllers\Front\InvoiceController::class,'show'])->name('get-invoice-details');
        Route::post('transactions', [\Payments\Http\Controllers\Front\InvoiceController::class, 'transactions'])->name('transactions');
    });

    Route::name('type.')->prefix("type")->group(function () {
        Route::get('',[\Payments\Http\Controllers\Front\PaymentTypeController::class,'index'])->name('index');
    });
});


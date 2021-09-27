<?php

use Illuminate\Support\Facades\Route;
use Payments\Http\Controllers\Admin\EmailContentController;
use Payments\Http\Controllers\Admin\PaymentCurrencyController as AdminPaymentCurrencyController;
use Payments\Http\Controllers\Admin\PaymentDriverController as AdminPaymentDriverController;
use Payments\Http\Controllers\Admin\PaymentTypeController as AdminPaymentController;
use Payments\Http\Controllers\Front\InvoiceController;
use Payments\Http\Controllers\Front\PaymentCurrencyController;
use Payments\Http\Controllers\Front\PaymentTypeController;
use Payments\Http\Controllers\Front\WebhookController;

Route::middleware(['auth', 'role:super-admin|subscriptions-payment-admin'])->prefix('admin')->name('admin.')->group(function () {


        Route::name('type.')->prefix("type")->group(function () {
            Route::get('', [AdminPaymentController::class, 'index'])->name('index');
            Route::post('create', [AdminPaymentController::class, 'store'])->name('store');
            Route::patch('edit', [AdminPaymentController::class, 'update'])->name('update');
            Route::delete('delete', [AdminPaymentController::class, 'delete'])->name('delete');
        });

        Route::name('currency.')->prefix("currency")->group(function () {
            Route::post('create', [AdminPaymentCurrencyController::class, 'store'])->name('store');
            Route::patch('edit', [AdminPaymentCurrencyController::class, 'update'])->name('update');
            Route::delete('delete', [AdminPaymentCurrencyController::class, 'delete'])->name('delete');
        });

        Route::name('driver.')->prefix("driver")->group(function () {
            Route::post('create', [AdminPaymentDriverController::class, 'store'])->name('store');
            Route::patch('edit', [AdminPaymentDriverController::class, 'update'])->name('update');
            Route::delete('delete', [AdminPaymentDriverController::class, 'delete'])->name('delete');
        });

        Route::name('email-contents.')->prefix('email-contents')->group(function () {
            Route::get('', [EmailContentController::class, 'index'])->name('list');
            Route::patch('update', [EmailContentController::class, 'update'])->name('update');
        });

});

Route::middleware(['role:client'])->group(function () {
    Route::name('invoices.')->prefix("invoices")->group(function () {
        Route::post('cancel_invoice', [InvoiceController::class, 'cancelInvoice'])->name('cancel_invoice');
        Route::get('/check-pending-order-invoice', [InvoiceController::class, 'pendingOrderInvoice'])->name('check');
        Route::get('/check-pending-wallet-invoice', [InvoiceController::class, 'pendingWalletInvoice'])->name('check');
        Route::get('/', [InvoiceController::class, 'index'])->name('get-list');
        Route::post('/', [InvoiceController::class, 'show'])->name('get-invoice-details');
        Route::post('transactions', [InvoiceController::class, 'transactions'])->name('transactions');
    });
    Route::name('currency.')->prefix("currency")->group(function () {
        Route::get('', [PaymentCurrencyController::class, 'index'])->name('index');
    });

    Route::name('type.')->prefix("type")->group(function () {
        Route::get('', [PaymentTypeController::class, 'index'])->name('index');
    });
});

Route::post('webhook', [WebhookController::class, 'index'])->name('btc-pay-server-webhook');



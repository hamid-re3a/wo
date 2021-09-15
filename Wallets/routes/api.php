<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Admin\EmailContentController;
use Wallets\Http\Controllers\Admin\InvoiceWalletController;
use Wallets\Http\Controllers\Admin\SettingController;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\WalletController;
use Wallets\Http\Controllers\Admin\UserWalletController AS AdminWalletController;
use Wallets\Services\BankService;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-wallet-admin)
 * list of all route admin section
 */
Route::middleware(['role:super-admin|subscriptions-wallet-admin'])->name('admin.')->group(function () {

});

/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});

Route::name('wallets.')->middleware('auth')->group(function () {
    Route::get('', [WalletController::class, 'index'])->name('index');
    Route::post('transactions', [WalletController::class, 'getTransaction'])->name('get-transaction');

    Route::name('deposit.')->prefix('deposit')->group(function () {
        Route::get('',[DepositWalletController::class, 'index'])->name('get-wallet');
        Route::get('transactions', [DepositWalletController::class, 'transactions'])->name('get-transactions');
        Route::get('transfers', [DepositWalletController::class, 'transfers'])->name('get-transfers');
        Route::post('transfer-preview', [DepositWalletController::class, 'transferPreview'])->name('transfer-fund-preview');
        Route::post('transfer-funds', [DepositWalletController::class, 'transferFunds'])->name('transfer-fund');
        Route::post('deposit-funds', [DepositWalletController::class, 'deposit'])->name('deposit-funds');
        Route::post('payment-request', [DepositWalletController::class, 'paymentRequest'])->name('payment-request');
    });

    Route::name('earning.')->prefix('earning')->group(function () {
        Route::get('', [EarningWalletController::class, 'index'])->name('get-wallet');
        Route::get('counts', [EarningWalletController::class, 'counts'])->name('counts');
        Route::get('transactions', [EarningWalletController::class, 'transactions'])->name('transactions');
        Route::get('transfers', [EarningWalletController::class, 'transfers'])->name('transfers');
        Route::get('transfers/income', [EarningWalletController::class, 'incomeTransfers'])->name('income-transfers');
    });


    //Admin Routes
    Route::name('admin.')->prefix('admin')->group(function () {
        Route::name('users.')->prefix('users')->group(function () {
            Route::post('wallets-list', [AdminWalletController::class, 'getWalletsList'])->name('wallets-list');
            Route::post('wallet-transactions', [AdminWalletController::class, 'getWalletTransactions'])->name('wallet-transactions');
            Route::post('wallet-transfers', [AdminWalletController::class, 'getWalletTransfers'])->name('wallet-transfers');
            Route::post('wallet-balance', [AdminWalletController::class, 'getWalletBalance'])->name('wallet-balance');
        });;

        Route::name('settings.')->prefix('settings')->group(function () {
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::patch('update', [SettingController::class, 'update'])->name('update');
        });

        Route::name('email-contents.')->prefix('email-contents')->group(function () {
            Route::get('', [EmailContentController::class, 'index'])->name('list');
            Route::patch('update', [EmailContentController::class, 'update'])->name('update');
        });
    });
});


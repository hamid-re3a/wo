<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Admin\EmailContentController;
use Wallets\Http\Controllers\Admin\InvoiceWalletController;
use Wallets\Http\Controllers\Admin\SettingController;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\WalletController;
use Wallets\Http\Controllers\Admin\UserWalletController AS AdminWalletController;

Route::name('wallets.')->middleware('auth_user_wallet')->group(function () {
    Route::get('', [WalletController::class, 'index'])->name('index');
    Route::post('transactions', [WalletController::class, 'getTransaction'])->name('get-transaction');
    Route::get('invoiceWallet', [InvoiceWalletController::class, 'invoiceWallet'])->name('get-transaction');

    Route::name('deposit.')->prefix('deposit')->group(function () {
        Route::get('',[DepositWalletController::class, 'index'])->name('get-wallet');
        Route::get('transactions', [DepositWalletController::class, 'transactions'])->name('transactions');
        Route::post('transfer-preview', [DepositWalletController::class, 'transferPreview'])->name('transfer-fund-preview');
        Route::post('transfer', [DepositWalletController::class, 'transfer'])->name('transfer-fund');
        Route::get('transfers', [DepositWalletController::class, 'transfers'])->name('transfers');
        Route::post('add-funds', [DepositWalletController::class, 'deposit'])->name('deposit');
    });

    Route::name('earning.')->prefix('earning')->group(function () {
        Route::get('', [EarningWalletController::class, 'index'])->name('get-wallet');
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

        Route::name('settings.')->prefix('settings')->group(function(){
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::patch('update', [SettingController::class, 'update'])->name('update');
        });

        Route::name('email-contents.')->prefix('email-contents')->group(function(){
            Route::get('', [EmailContentController::class, 'index'])->name('list');
            Route::patch('update', [EmailContentController::class, 'update'])->name('update');
        });
    });
});

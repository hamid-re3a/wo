<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\WalletController;
use Wallets\Http\Controllers\Admin\UserWalletController AS AdminWalletController;
use Wallets\Http\Middlewares\WalletAuthMiddleware;

Route::name('wallets.')->middleware(WalletAuthMiddleware::class)->group(function () {
    Route::get('', [WalletController::class,'index'])->name('test');
    Route::post('transactions', [WalletController::class, 'getTransaction'])->name('get-transaction');

    Route::name('deposit.')->prefix('deposit')->group(function(){
       Route::get('transactions', [DepositWalletController::class, 'transactions'])->name('transactions');
       Route::get('transfers', [DepositWalletController::class, 'transfers'])->name('transfers');
       Route::get('transfers/income', [DepositWalletController::class, 'incomeTransfers'])->name('income-transfers');
    });

    Route::name('earning.')->prefix('earning')->group(function(){
        Route::get('transactions', [EarningWalletController::class, 'transactions'])->name('transactions');
        Route::get('transfers', [EarningWalletController::class, 'transfers'])->name('transfers');
        Route::get('transfers/income', [EarningWalletController::class, 'incomeTransfers'])->name('income-transfers');
    });


    //Admin Routes
    Route::name('admin.')->prefix('admin')->group(function(){
        Route::name('users.')->prefix('users')->group(function () {
            Route::post('wallets-list', [AdminWalletController::class, 'getWalletsList'])->name('wallets-list');
            Route::post('wallet-deposit', [AdminWalletController::class, 'depositUser'])->name('wallet-deposit');
            Route::post('wallet-transactions', [AdminWalletController::class, 'getWalletTransactions'])->name('wallet-transactions');
            Route::post('wallet-transfers', [AdminWalletController::class, 'getWalletTransfers'])->name('wallet-transfers');
            Route::post('wallet-balance', [AdminWalletController::class, 'getWalletBalance'])->name('wallet-balance');
       });;
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\WalletController;

Route::name('wallets.')->group(function () {
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
});

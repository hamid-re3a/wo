<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Admin\EmailContentController;
use Wallets\Http\Controllers\Admin\SettingController;
use Wallets\Http\Controllers\Admin\WithdrawRequestController as AdminWithdrawRequestController;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\WalletController;
use Wallets\Http\Controllers\Admin\UserWalletController AS AdminWalletController;
use Wallets\Http\Controllers\Front\WithdrawRequestController as UserWithdrawRequestController;

Route::middleware('auth')->name('wallets.')->group(function(){
    Route::middleware(['role:' . USER_ROLE_SUPER_ADMIN . '|' . USER_ROLE_ADMIN_SUBSCRIPTIONS_WALLET])->name('wallets.')->prefix('admin')->group(function () {
            Route::name('users.')->prefix('users')->group(function () {
                Route::post('all-transactions', [AdminWalletController::class, 'getAllTransactions'])->name('get-all-transactions');
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

//            Route::name('withdraw-requests.')->prefix('withdraw-requests')->group(function(){
//                Route::get('',[AdminWithdrawRequestController::class,'index'])->name('index');
//                Route::patch('',[AdminWithdrawRequestController::class,'update'])->name('update');
//            });
    });

    Route::middleware(['role:client'])->name('customer.')->group(function () {
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
            Route::get('earned-commissions', [EarningWalletController::class, 'earned_commissions'])->name('earned-commissions');
            Route::get('transactions', [EarningWalletController::class, 'transactions'])->name('transactions');
            Route::get('transfers', [EarningWalletController::class, 'transfers'])->name('transfers');
            Route::post('transfer-funds-preview', [EarningWalletController::class, 'transfer_to_deposit_wallet_preview'])->name('transfer-funds-preview');
            Route::post('transfer-funds', [EarningWalletController::class, 'transfer_to_deposit_wallet'])->name('transfer-funds');
        });

//        Route::name('withdrawRequests.')->prefix('withdraw-requests')->group(function(){
//            Route::get('withdraw-requests', [UserWithdrawRequestController::class, 'withdraw_requests'])->name('withdraw-requests');
//            Route::post('preview', [UserWithdrawRequestController::class, 'create_withdraw_request_preview'])->name('create-withdraw-request-preview');
//            Route::post('', [UserWithdrawRequestController::class, 'create_withdraw_request'])->name('create-withdraw-request');
//        });
    });
});



<?php

use Illuminate\Support\Facades\Route;
use Wallets\Http\Controllers\Admin\CharityWalletController;
use Wallets\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Wallets\Http\Controllers\Admin\EmailContentController;
use Wallets\Http\Controllers\Admin\SettingController;
use Wallets\Http\Controllers\Front\SettingController as UserSettingController;
use Wallets\Http\Controllers\Admin\WithdrawRequestController as AdminWithdrawRequestController;
use Wallets\Http\Controllers\Front\DepositWalletController;
use Wallets\Http\Controllers\Front\EarningWalletController;
use Wallets\Http\Controllers\Front\JanexWalletController;
use Wallets\Http\Controllers\Front\WalletController;
use Wallets\Http\Controllers\Admin\UserWalletController AS AdminWalletController;
use Wallets\Http\Controllers\Front\WithdrawRequestController as UserWithdrawRequestController;

Route::middleware('auth')->name('wallets.')->group(function(){
    Route::middleware(['role:' . USER_ROLE_SUPER_ADMIN . '|' . USER_ROLE_ADMIN_SUBSCRIPTIONS_WALLET])->name('admin.')->prefix('admin')->group(function () {
            Route::name('users.')->prefix('users')->group(function () {
                Route::post('all-transactions', [AdminWalletController::class, 'getAllTransactions'])->name('get-all-transactions');
                Route::post('wallets-list', [AdminWalletController::class, 'getWalletsList'])->name('wallets-list');
                Route::post('wallet-transactions', [AdminWalletController::class, 'getWalletTransactions'])->name('wallet-transactions');
                Route::post('wallet-transfers', [AdminWalletController::class, 'getWalletTransfers'])->name('wallet-transfers');
                Route::post('wallet-balance', [AdminWalletController::class, 'getWalletBalance'])->name('wallet-balance');

                Route::get('sum_deposit_wallets',[AdminDashboardController::class,'sum_deposit_wallets'])->name('dashboard-sum-deposit-wallets');
                Route::get('sum_earning_wallets',[AdminDashboardController::class,'sum_earning_wallets'])->name('dashboard-sum-earning-wallets');
                Route::get('commissions-sum',[AdminDashboardController::class,'commissionsSum'])->name('commissions-sum');
                Route::prefix('charts')->name('charts')->group(function(){
                    Route::post('overall-balance',[AdminDashboardController::class,'overallBalanceChart'])->name('overall-balance');
                    Route::post('investments',[AdminDashboardController::class,'investmentsChart'])->name('investments');
                    Route::post('commissions',[AdminDashboardController::class,'commissionsChart'])->name('commissions');

                });
            });

            Route::name('settings.')->prefix('settings')->group(function () {
                Route::get('', [SettingController::class, 'index'])->name('list');
                Route::patch('update', [SettingController::class, 'update'])->name('update');
            });
            Route::name('email-contents.')->prefix('email-contents')->group(function () {
                Route::get('', [EmailContentController::class, 'index'])->name('list');
                Route::patch('update', [EmailContentController::class, 'update'])->name('update');
            });

            Route::name('withdraw-requests.')->prefix('withdraw-requests')->group(function(){
                Route::get('wallets-balance',[AdminWithdrawRequestController::class,'walletsBalance'])->name('wallets-balance');
                Route::get('counts',[AdminWithdrawRequestController::class,'counts'])->name('counts');
                Route::post('',[AdminWithdrawRequestController::class,'index'])->name('index');
                Route::patch('',[AdminWithdrawRequestController::class,'update'])->name('update');

                Route::prefix('charts')->name('charts')->group(function(){
                    Route::post('pending-amount-vs-time',[AdminWithdrawRequestController::class,'pendingAmountVsTimeChart'])->name('pending-amount-vs-time');
                    Route::post('paid-amount-vs-time',[AdminWithdrawRequestController::class,'paidAmountVsTimeChart'])->name('paid-amount-vs-time');
                });
            });

        Route::name('charity-wallet.')->prefix('charity-wallet')->group(function(){
            Route::get('',[CharityWalletController::class,'index'])->name('index');
            Route::post('transactions',[CharityWalletController::class,'transactions'])->name('transactions');

            Route::prefix('charts')->name('charts')->group(function(){
                Route::post('overall-balance',[CharityWalletController::class,'overallBalanceChart'])->name('overall-balance');
            });
        });
    });

    Route::middleware(['role:' . USER_ROLE_CLIENT])->name('customer.')->group(function () {
        Route::get('settings',[UserSettingController::class,'index'])->name('settings-list');
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

            Route::prefix('charts')->name('charts')->group(function(){
                Route::post('overall-balance',[DepositWalletController::class,'overallBalanceChart'])->name('overall-balance');
                Route::post('investments',[DepositWalletController::class,'investmentsChart'])->name('investments');
            });
        });

        Route::name('earning.')->prefix('earning')->group(function () {
            Route::get('', [EarningWalletController::class, 'index'])->name('get-wallet');
            Route::get('earned-commissions', [EarningWalletController::class, 'earned_commissions'])->name('earned-commissions');
            Route::get('transactions', [EarningWalletController::class, 'transactions'])->name('get-transactions');
            Route::get('transfers', [EarningWalletController::class, 'transfers'])->name('get-transfers');
            Route::post('transfer-funds-preview', [EarningWalletController::class, 'transfer_to_deposit_wallet_preview'])->name('transfer-funds-preview');
            Route::post('transfer-funds', [EarningWalletController::class, 'transfer_to_deposit_wallet'])->name('transfer-funds');

            Route::prefix('charts')->name('charts')->group(function(){
                Route::post('overall-balance',[EarningWalletController::class,'overallBalanceChart'])->name('overall-balance');
                Route::post('commissions-chart',[EarningWalletController::class,'commissionsChart'])->name('commissions');
            });

        });

        Route::name('janex.')->prefix('janex')->group(function () {
            Route::get('', [JanexWalletController::class, 'index'])->name('get-wallet');
            Route::get('earned-commissions', [JanexWalletController::class, 'earned_commissions'])->name('earned-commissions');
            Route::get('transactions', [JanexWalletController::class, 'transactions'])->name('get-transactions');
            Route::get('transfers', [JanexWalletController::class, 'transfers'])->name('get-transfers');

            Route::prefix('charts')->name('charts')->group(function(){
                Route::post('overall-balance',[JanexWalletController::class,'overallBalanceChart'])->name('overall-balance');
                Route::post('commissions-chart',[JanexWalletController::class,'commissionsChart'])->name('commissions');
            });

        });

        Route::name('withdrawRequests.')->prefix('withdraw-requests')->group(function(){
            Route::get('counts', [UserWithdrawRequestController::class, 'counts'])->name('counts');
            Route::get('', [UserWithdrawRequestController::class, 'index'])->name('index');
            Route::post('preview', [UserWithdrawRequestController::class, 'create_withdraw_request_preview'])->name('preview');
            Route::post('', [UserWithdrawRequestController::class, 'create_withdraw_request'])->name('create');
        });
    });
});



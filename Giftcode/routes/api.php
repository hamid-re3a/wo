<?php

use Giftcode\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Giftcode\Http\Controllers\Front\DashboardController as UserDashboardController;
use Giftcode\Http\Controllers\Admin\EmailContentController;
use Giftcode\Http\Controllers\Admin\SettingController;
use Giftcode\Http\Controllers\Front\GiftcodeController;
use Illuminate\Support\Facades\Route;


Route::name('giftcodes.')->middleware('auth')->group(function () {

    //Admin Routes
    Route::middleware(['role:'.USER_ROLE_SUPER_ADMIN.'|' . USER_ROLE_ADMIN_SUBSCRIPTIONS_GIFTCODE])->prefix('admin')->name('admin.')->group(function () { //TODO admin role
        Route::prefix('dashboard')->name('dashboard')->group(function(){
            Route::get('counts',[AdminDashboardController::class,'counts'])->name('counts');
            Route::get('',[AdminDashboardController::class,'index'])->name('index');
            Route::prefix('charts')->name('charts')->group(function(){
                Route::post('giftcodes-vs-time',[AdminDashboardController::class,'giftcodesVsTimeChart'])->name('giftcode-vs-time');
                Route::post('package-vs-time',[AdminDashboardController::class,'packageVsTimeChart'])->name('package-vs-time');
            });
        });
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::patch('update', [SettingController::class, 'update']);
        });

        Route::name('email-contents.')->prefix('email-contents')->group(function () {
            Route::get('', [EmailContentController::class, 'index'])->name('list');
            Route::patch('update', [EmailContentController::class, 'update'])->name('update');
        });

    });

    Route::middleware(['auth','role:' . USER_ROLE_CLIENT])->name('customer.')->group(function () {
        Route::get('', [GiftcodeController::class, 'index'])->name('data');
        Route::get('counts', [GiftcodeController::class, 'counts'])->name('counts');
        Route::post('create', [GiftcodeController::class, 'store'])->name('create');
        Route::get('show/{uuid}', [GiftcodeController::class, 'show'])->name('show');
        Route::post('cancel', [GiftcodeController::class, 'cancel'])->name('cancel');
        Route::prefix('charts')->name('charts')->group(function(){
            Route::post('giftcodes-vs-time',[UserDashboardController::class,'giftcodesVsTimeChart'])->name('giftcode-vs-time');
            Route::post('package-vs-time',[UserDashboardController::class,'packageVsTimeChart'])->name('package-vs-time');
        });
    });
});



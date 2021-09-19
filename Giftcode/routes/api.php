<?php

use Giftcode\Http\Controllers\Admin\SettingController;
use Giftcode\Http\Controllers\User\GiftcodeController;
use Giftcode\Http\Controllers\TestController;
use Giftcode\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-giftcode-admin)
 * list of all route admin section
 */
Route::middleware(['role:super-admin|subscriptions-giftcode-admin'])->name('admin.')->group(function () {

});

Route::middleware(['auth', 'role:client'])->name('customer.')->group(function () {
    Route::name('giftcodes.')->middleware('auth')->group(function () {
        Route::get('', [GiftcodeController::class, 'index'])->name('data');
        Route::get('counts', [GiftcodeController::class, 'counts'])->name('counts');
        Route::post('create', [GiftcodeController::class, 'store'])->name('create');
        Route::get('show/{uuid}', [GiftcodeController::class, 'show'])->name('show');
        Route::post('cancel', [GiftcodeController::class, 'cancel'])->name('cancel');
        Route::post('redeem', [GiftcodeController::class, 'redeem'])->name('redeem');

        //Admin Routes
        Route::middleware(['role:super-admin|subscriptions-admin'])->prefix('admin')->name('admin.')->group(function () { //TODO admin role
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('', [SettingController::class, 'index'])->name('list');
                Route::patch('update', [SettingController::class, 'update']);
            });
        });
    });
});



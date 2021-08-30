<?php

use Giftcode\Http\Controllers\Admin\SettingController;
use Giftcode\Http\Controllers\User\GiftcodeController;
use Giftcode\Http\Controllers\TestController;
use Giftcode\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->middleware('auth_user_gift_code')->group(function () {
    Route::get('', [GiftcodeController::class,'index'])->name('list');
    Route::post('create',[GiftcodeController::class,'store'])->name('create');
    Route::get('show/{uuid}', [GiftcodeController::class,'show'])->name('show');
    Route::post('cancel',[GiftcodeController::class,'cancel'])->name('cancel');
    Route::post('redeem',[GiftcodeController::class,'redeem'])->name('redeem');

    //Admin Routes
    Route::prefix('admin')->name('admin.')->group(function(){ //TODO admin role
        Route::prefix('settings')->name('settings.')->group(function(){
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::post('update', [SettingController::class, 'update']);
        });
    });
});

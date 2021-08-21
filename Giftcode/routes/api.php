<?php

use Giftcode\Http\Controllers\Admin\SettingController;
use Giftcode\Http\Controllers\GiftcodeController;
use Giftcode\Http\Controllers\TestController;
use Giftcode\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->middleware('auth_user_gift_code')->group(function () {
    Route::get('test',[TestController::class, 'test']);
    Route::get('', [GiftcodeController::class,'index'])->name('list');
    Route::post('',[GiftcodeController::class,'store'])->name('create');
    Route::get('/show/{id}', [GiftcodeController::class,'show'])->name('show');
    Route::patch('',[GiftcodeController::class,'cancel'])->name('cancel');

    //Admin Routes
    Route::prefix('admin')->name('admin.')->group(function(){ //TODO admin role
        Route::prefix('settings')->name('settings.')->group(function(){
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::post('update', [SettingController::class, 'update']);
        });
    });
});

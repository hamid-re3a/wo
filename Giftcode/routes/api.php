<?php

use Giftcode\Http\Controllers\Admin\SettingController;
use Giftcode\Http\Controllers\TestController;
use Giftcode\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->middleware(GiftcodeAuthMiddleware::class)->group(function () {
    Route::get('test',[TestController::class, 'test']);

    //Admin Routes
    Route::prefix('admin')->name('admin.')->group(function(){ //TODO admin role
        Route::prefix('settings')->name('settings.')->group(function(){
            Route::get('', [SettingController::class, 'index'])->name('list');
            Route::post('update', [SettingController::class, 'update']);
        });
    });
});

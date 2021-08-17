<?php

use Giftcode_\Http\Controllers\TestController;
use Giftcode_\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->middleware(GiftcodeAuthMiddleware::class)->group(function () {
    Route::get('test',[TestController::class, 'test']);

    //Admin Routes
    Route::name('admin.')->prefix('admin')->group(function(){

    });
});

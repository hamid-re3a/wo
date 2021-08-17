<?php

use Giftcode\Http\Middlewares\GiftcodeAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->middleware(GiftcodeAuthMiddleware::class)->group(function () {


    //Admin Routes
    Route::name('admin.')->prefix('admin')->group(function(){

    });
});

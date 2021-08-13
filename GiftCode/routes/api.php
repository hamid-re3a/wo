<?php

use Illuminate\Support\Facades\Route;

Route::name('giftcodes.')->group(function () {


    //Admin Routes
    Route::name('admin.')->prefix('admin')->group(function(){

    });
});

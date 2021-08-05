<?php

use Illuminate\Support\Facades\Route;

Route::name('orders.')/*->middleware('auth_user_order')*/->group(function () {
    Route::post('store',[\Orders\Http\Controllers\Front\OrderController::class,'newOrder'])->name('store');
});

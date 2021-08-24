<?php

use Illuminate\Support\Facades\Route;
use Orders\Http\Controllers\Front\OrderController;
use Orders\Http\Controllers\Front\PackageController;

Route::name('orders.')->middleware('auth_user_order')->group(function () {
    Route::post('', [OrderController::class, 'index'])->name('list');
    Route::post('show', [OrderController::class, 'showOrder'])->name('show');
    Route::post('store',[OrderController::class,'newOrder'])->name('store');
});

Route::name('packages.')->prefix('packages')->middleware('auth_user_order')->group(function(){
    Route::get('available-packages',[PackageController::class, 'paidPackages'])->name('paid-packages');
    Route::get('has-valid-package',[PackageController::class, 'hasValidPackage'])->name('has-valid-package');
});


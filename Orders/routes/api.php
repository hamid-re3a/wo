<?php

use Illuminate\Support\Facades\Route;
use Orders\Http\Controllers\Front\OrderController;
use Orders\Http\Controllers\Admin\OrderController as OrderAdminController;
use Orders\Http\Controllers\Front\PackageController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-order-admin)
 * list of all route admin section
 */
Route::/*middleware(['role:super-admin|subscriptions-order-admin'])->*/prefix('admin')->name('admin.')->group(function () {

    Route::name('order')->group(function (){
        Route::get('/subscription_count',[OrderAdminController::class,"getCountSubscriptions"]);
        Route::get('/active_package_count',[OrderAdminController::class,'activePackageCount']);
        Route::get('/deactivate_package_count',[OrderAdminController::class,'deactivatePackageCount']);
        Route::post('/package_count_overview',[OrderAdminController::class,'packageOverviewCount']);
    });

});

/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});

Route::name('orders.')->middleware('auth')->group(function () {
    Route::post('', [OrderController::class, 'index'])->name('list');
    Route::post('show', [OrderController::class, 'showOrder'])->name('show');
    Route::post('store',[OrderController::class,'newOrder'])->name('store');
});

Route::name('packages.')->prefix('packages')->middleware('auth')->group(function(){
    Route::get('available-packages',[PackageController::class, 'paidPackages'])->name('paid-packages');
    Route::get('has-valid-package',[PackageController::class, 'hasValidPackage'])->name('has-valid-package');
});

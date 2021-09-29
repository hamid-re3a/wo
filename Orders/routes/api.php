<?php

use Illuminate\Support\Facades\Route;
use Orders\Http\Controllers\Front\DashboardController as UserDashboardController;
use Orders\Http\Controllers\Front\OrderController;
use Orders\Http\Controllers\Admin\OrderController as OrderAdminController;
use Orders\Http\Controllers\Front\PackageController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-order-admin)
 * list of all route admin section
 */
Route::middleware(['role:'.USER_ROLE_SUPER_ADMIN.'|'.USER_ROLE_ADMIN_SUBSCRIPTIONS_ORDER])->prefix('admin')->name('admin.')->group(function () {

    Route::name('order')->group(function (){
        Route::get('/subscription_count',[OrderAdminController::class,"getCountSubscriptions"])->name('getCountSubscriptions');
        Route::get('/active_package_count',[OrderAdminController::class,'activePackageCount'])->name('activePackageCount');
        Route::get('/deactivate_package_count',[OrderAdminController::class,'deactivatePackageCount'])->name('deactivatePackageCount');
        Route::post('/package_count_overview',[OrderAdminController::class,'packageOverviewCount'])->name('packageOverviewCount');
        Route::post('/create_new_order',[OrderAdminController::class,'newOrder'])->name('createNewOrder');
    });

});

/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});

Route::name('orders.')->middleware('auth')->group(function () {
    Route::get('counts', [UserDashboardController::class, 'counts'])->name('counts');
    Route::post('', [UserDashboardController::class, 'index'])->name('list');
    Route::post('show', [UserDashboardController::class, 'showOrder'])->name('show');
    Route::post('store',[OrderController::class,'newOrder'])->name('store');
});

Route::name('packages.')->prefix('packages')->middleware('auth')->group(function(){
    Route::get('available-packages',[PackageController::class, 'paidPackages'])->name('paid-packages');
    Route::get('has-valid-package',[PackageController::class, 'hasValidPackage'])->name('has-valid-package');
});

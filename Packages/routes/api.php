<?php

use Illuminate\Support\Facades\Route;
use Packages\Http\Controllers\Front\PackageController;
use Packages\Http\Controllers\Admin\PackageController as PackageAdminController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-package-admin)
 * list of all route admin section
 */
Route::/*middleware(['role:super-admin|subscriptions-package-admin'])->*/prefix('admin')->name('admin.')->group(function () {

    Route::name('package')->group(function (){
        Route::post('/package_count',[PackageAdminController::class,'getCountPackageByDate']);
    });


});


/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});


Route::name('packages.')->middleware('auth_user')->group(function () {
    Route::get('/',[PackageController::class,'index'])->name('index');
    Route::post('/edit',[PackageAdminController::class,'edit'])->name('edit');
});

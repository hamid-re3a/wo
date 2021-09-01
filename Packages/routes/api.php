<?php

use Illuminate\Support\Facades\Route;
use Packages\Http\Controllers\Front\PackageController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-package-admin)
 * list of all route admin section
 */
Route::middleware(['role:super-admin|subscriptions-package-admin'])->name('admin.')->group(function () {

});


/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});


Route::name('packages.')->middleware('auth_user_package')->group(function () {
    Route::get('/',[PackageController::class,'index'])->name('index');
    Route::post('/edit',[\Packages\Http\Controllers\Admin\PackageController::class,'edit'])->name('edit');
});

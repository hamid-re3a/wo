<?php

use Illuminate\Support\Facades\Route;
use Packages\Http\Controllers\Admin\CategoryController;
use Packages\Http\Controllers\Admin\PackageIndirectCommissionController;
use Packages\Http\Controllers\Front\PackageController;
use Packages\Http\Controllers\Admin\PackageController as PackageAdminController;

/**
 * @todo before lunch project we must migrate all route to this (admin-super subscriptions-package-admin)
 * list of all route admin section
 */
Route::middleware(['role:'.USER_ROLE_SUPER_ADMIN.'|'.USER_ROLE_ADMIN_SUBSCRIPTIONS_PACKAGE])->prefix('admin')->name('admin.')->group(function () {

    Route::name('package')->group(function (){
        Route::post('/package_count',[PackageAdminController::class,'getCountPackageByDate']);
    });

    Route::name('packages.')->group(function () {
        Route::post('/edit',[PackageAdminController::class,'edit'])->name('edit');
        Route::post('/create',[PackageAdminController::class,'create'])->name('create');
        Route::delete('/delete',[PackageAdminController::class,'delete'])->name('delete');
        Route::post('/indirect_commissions/create_or_edit',[PackageIndirectCommissionController::class,'edit'])->name('create-or-edit-package-commission');
        Route::delete('/indirect_commissions/delete',[PackageIndirectCommissionController::class,'delete'])->name('delete_package_commission');
    });

    Route::name('categories.')->prefix('categories')->group(function () {
        Route::put('/edit',[CategoryController::class,'edit'])->name('edit');
        Route::get('/',[CategoryController::class,'index'])->name('index');
        Route::post('/create',[CategoryController::class,'create'])->name('create');
        Route::delete('/create',[CategoryController::class,'delete'])->name('delete');
        Route::post('/indirect_commissions/create_or_edit',[CategoryController::class,'editCommission'])->name('editCommission');
        Route::delete('/indirect_commissions/delete',[CategoryController::class,'deleteCommission'])->name('deleteCommission');
    });
});


/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

    Route::name('packages.')->middleware('auth')->group(function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
    });
});



<?php

use Illuminate\Support\Facades\Route;
use Orders\Http\Controllers\Front\DashboardController as UserDashboardController;
use Orders\Http\Controllers\Front\OrderController;
use Orders\Http\Controllers\Admin\OrderController as AdminOrderController;
use Orders\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Orders\Http\Controllers\Front\PackageController;

Route::middleware('auth')->group(function () {
    //Admin routes
    Route::middleware(['role:' . USER_ROLE_SUPER_ADMIN . '|' . USER_ROLE_ADMIN_SUBSCRIPTIONS_ORDER])->prefix('admin')->name('admin.')->group(function () {

        Route::name('orders.')->prefix('orders')->group(function () {
            Route::name('dashboard.')->prefix('dashboard')->group(function () {
                Route::get('counts', [AdminDashboardController::class, 'counts'])->name('counts');
                Route::post('package_count_overview', [AdminDashboardController::class, 'packageOverviewCount'])->name('package-overview');
                Route::post('package_type_count', [AdminDashboardController::class, 'packageTypeCount'])->name('package-type');
                Route::post('package_type_percentage_count', [AdminDashboardController::class, 'packageTypePercentCount'])->name('package-percentage-type');
            });


            Route::get('/', [AdminOrderController::class, "index"])->name('index');
            Route::post('/create_new_order', [AdminOrderController::class, 'newOrder'])->name('createNewOrder');
        });

    });

    //Client routes
    Route::middleware(['role:' . USER_ROLE_CLIENT])->name('customer.')->group(function () {
        Route::name('orders.')->group(function () {
            Route::post('package_overview_count', [UserDashboardController::class, 'packageOverviewCount'])->name('package_overview_count');
            Route::post('package_type_count', [UserDashboardController::class, 'packageTypeCount'])->name('package_type_count');
            Route::get('counts', [UserDashboardController::class, 'counts'])->name('counts');
            Route::post('', [UserDashboardController::class, 'index'])->name('list');
            Route::post('show', [UserDashboardController::class, 'showOrder'])->name('show');
            Route::post('store', [OrderController::class, 'newOrder'])->name('store');

            Route::name('packages.')->prefix('packages')->middleware('auth')->group(function () {
                Route::get('available-packages', [PackageController::class, 'paidPackages'])->name('paid-packages');
                Route::get('has-valid-package', [PackageController::class, 'hasValidPackage'])->name('has-valid-package');
            });
        });

    });
});




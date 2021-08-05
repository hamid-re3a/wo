<?php

use Illuminate\Support\Facades\Route;
use Packages\Http\Controllers\Front\PackageController;

Route::name('packages.')->group(function () {
    Route::get('/',[PackageController::class,'index'])->name('index');
    Route::post('/edit',[\Packages\Http\Controllers\Admin\PackageController::class,'edit'])->name('edit');
});

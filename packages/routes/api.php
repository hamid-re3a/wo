<?php

use Illuminate\Support\Facades\Route;
use Packages\Http\Controllers\Front\PackageController;

Route::name('packages.')->group(function () {
    Route::get('/packages',[PackageController::class,'index'])->name('index');
});

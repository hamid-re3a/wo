<?php

use Illuminate\Support\Facades\Route;

Route::name('packages.')->group(function () {
    Route::get('packages',[\Packages\Http\Controllers\Front\PackageController::class,'index'])->name('index');
});

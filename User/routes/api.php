<?php

use Illuminate\Support\Facades\Route;


/**
 * @todo before lunch project we must migrate all route to this (all api public and customer side)
 * list of all route admin section
 */
Route::middleware(['role:client'])->name('customer.')->group(function () {

});

Route::name('users.')->group(function () {

});

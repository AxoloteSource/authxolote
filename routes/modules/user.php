<?php

use App\Http\Controllers\V1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    Route::get('/', 'index')->middleware('isAllow:users.index');
    Route::post('/', 'store')->middleware('isAllow:users.store');
});

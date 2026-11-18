<?php

use App\Http\Controllers\V1\UserType\UserTypeController;
use Illuminate\Support\Facades\Route;

Route::controller(UserTypeController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware('isAllow:users.index');
});

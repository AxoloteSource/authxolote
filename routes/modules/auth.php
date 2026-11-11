<?php

use App\Http\Controllers\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('refresh-token', 'refreshToken')->middleware('throttle:10,1');
    Route::post('recovery-password', 'recoveryPassword');
    Route::post('reset-password', 'resetPassword');
});

Route::controller(AuthController::class)->middleware('auth:api')->group(function () {
    Route::post('register', 'register');
    Route::post('validate-credentials', 'validateCredentials')
        ->middleware(['throttle:10,1', 'isAllow:auth.validate-credentials']);
    Route::post('logout', 'logout');
    Route::post('is-allowed', 'isAllowed');
    Route::post('me', 'me');
    Route::post('change-password', 'changePassword')->middleware('isAllow:auth.change-password');
});

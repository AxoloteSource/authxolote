<?php

use App\Http\Controllers\V1\Otp\OtpChallengeController;
use Illuminate\Support\Facades\Route;

Route::controller(OtpChallengeController::class)->group(function () {
    Route::post('challenges', 'store');
    Route::post('challenges/{challenge}/verify', 'verify');
});

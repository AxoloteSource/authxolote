<?php

use App\Http\Controllers\V1\PersonalAccessToken\PersonalAccessTokenController;
use Illuminate\Support\Facades\Route;

Route::controller(PersonalAccessTokenController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware('isAllow:auth.personal-access-tokens.index');

    Route::post('/', 'store')
        ->middleware('isAllow:auth.personal-access-tokens.store');

    Route::post('{id}/revoke', 'revoke')
        ->middleware('isAllow:auth.personal-access-tokens.revoke');

    Route::delete('{id}', 'destroy')
        ->middleware('isAllow:auth.personal-access-tokens.destroy');
});

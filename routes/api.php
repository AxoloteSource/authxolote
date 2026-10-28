<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->group(base_path('/routes/modules/auth.php'));

Route::prefix('otp')->group(base_path('/routes/modules/otp.php'));

Route::middleware('auth:api')->group(function () {
    Route::prefix('users')->group(base_path('routes/modules/user.php'));
    Route::prefix('roles')->group(base_path('routes/modules/roles.php'));
    Route::prefix('user-lists')->group(base_path('routes/modules/user-lists.php'));
    Route::prefix('applications')->group(base_path('routes/modules/applications.php'));
    Route::prefix('menus')->group(base_path('routes/modules/menus.php'));
    Route::prefix('menu-items')->group(base_path('routes/modules/menu-items.php'));
    Route::prefix('')->group(base_path('routes/modules/flow.php'));
});

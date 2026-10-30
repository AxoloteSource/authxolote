<?php

use App\Http\Controllers\V1\MenuItem\MenuItemController;
use Illuminate\Support\Facades\Route;

Route::controller(MenuItemController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware('isAllow:auth.menu_items.index');

    Route::post('/', 'store')
        ->middleware('isAllow:auth.menu_items.store');

    Route::get('{id}', 'show')
        ->middleware('isAllow:auth.menu_items.show');

    Route::put('reorder', 'reorder')
        ->middleware('isAllow:auth.menu_items.reorder');

    Route::put('{id}', 'update')
        ->middleware('isAllow:auth.menu_items.update');

    Route::put('{id}/roles/{roleId}', 'updateRole')
        ->middleware('isAllow:auth.menu_items.roles.update');

    Route::delete('{id}', 'destroy')
        ->middleware('isAllow:auth.menu_items.destroy');
});

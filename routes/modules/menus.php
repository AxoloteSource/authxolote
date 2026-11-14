<?php

use App\Http\Controllers\V1\Menu\MenuController;
use Illuminate\Support\Facades\Route;

Route::controller(MenuController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware('isAllow:auth.menus.index');

    Route::post('/', 'store')
        ->middleware('isAllow:auth.menus.store');

    Route::post('/setup', 'setup')
        ->middleware([
            'isAllow:auth.menus.store',
            'isAllow:auth.menu_items.store',
        ]);

    Route::get('{slug}', 'show')
        ->middleware('isAllow:auth.menus.show');

    Route::get('{slug}/all-items', 'showAll')
        ->middleware('isAllow:auth.menus.show_all');

    Route::put('{id}', 'update')
        ->middleware('isAllow:auth.menus.update');

    Route::delete('{id}', 'destroy')
        ->middleware('isAllow:auth.menus.destroy');
});

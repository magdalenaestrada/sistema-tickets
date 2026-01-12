<?php

use App\Http\Controllers\UserController;

Route::prefix('usuarios')
    ->name('usuarios.')
    ->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->name('index');

        Route::get('/datatable', [UserController::class, 'datatable'])
            ->name('datatable');

        Route::get('/{user}', [UserController::class, 'mostrar'])
            ->name('mostrar');

        Route::put('/{user}', [UserController::class, 'actualizar'])
            ->name('actualizar');
    });

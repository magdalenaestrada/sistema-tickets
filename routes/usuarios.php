<?php

use App\Http\Controllers\UserController;

Route::middleware(['auth', 'can:gestionar usuarios'])->prefix('usuarios')
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

        Route::put('/{user}/activar', [UserController::class, 'activar'])
            ->name('activar');

        Route::put('/{user}/desactivar', [UserController::class, 'desactivar'])
            ->name('desactivar');
    });

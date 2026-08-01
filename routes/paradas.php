<?php

use App\Http\Controllers\PueblitoController;

Route::middleware(['auth'])
    ->prefix('paradas')
    ->name('paradas.')
    ->group(function () {

        Route::get('/', [PueblitoController::class, 'index'])->name('index');

        Route::post('/', [PueblitoController::class, 'store'])->name('store');

        Route::put('/{pueblito}', [PueblitoController::class, 'update'])->name('update');

        Route::delete('/{pueblito}', [PueblitoController::class, 'destroy'])->name('destroy');
    });

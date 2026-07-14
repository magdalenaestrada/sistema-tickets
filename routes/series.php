<?php

use App\Http\Controllers\SerieSucursalController;

Route::get('/series-sucursal', [SerieSucursalController::class, 'index'])
    ->name('series-sucursal.index');

Route::post('/series-sucursal', [SerieSucursalController::class, 'store'])
    ->name('series-sucursal.store');

Route::put('/series-sucursal/{serieSucursal}', [SerieSucursalController::class, 'update'])
    ->name('series-sucursal.update');

Route::delete('/series-sucursal/{serieSucursal}', [SerieSucursalController::class, 'destroy'])
    ->name('series-sucursal.destroy');
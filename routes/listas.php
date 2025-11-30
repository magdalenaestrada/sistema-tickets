<?php

use App\Http\Controllers\ListaController;

Route::middleware(['auth'])->prefix('listas')->name('listas.')->group(function () {
    Route::get('/', [ListaController::class, 'obtenerListas'])->name('all');
    Route::get('/api/sucursales/{distrito}', [ListaController::class, 'listarJson']);
    Route::get('/vehiculos/tipos', [ListaController::class, 'listarTipos'])->name('vehiculos.tipos');
});

<?php

use App\Http\Controllers\PasajeController;

Route::middleware(['auth'])->prefix('pasajes')->name('pasajes.')->group(function () {

    Route::get('/', [PasajeController::class, 'index'])->name('index');
    Route::get('/asientos/{salida}', [PasajeController::class, 'asientos'])->name('asientos');
    Route::get('/buscar', [PasajeController::class, 'buscarReservado'])->name('buscar');
    Route::post('/verificar-promocion', [PasajeController::class, 'verificarPromocion'])->name('verificar_promocion');
    Route::post('/', [PasajeController::class, 'store'])->name('store');
});

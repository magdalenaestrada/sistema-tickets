<?php

use App\Http\Controllers\PasajeController;

Route::middleware(['auth'])->prefix('pasajes')->name('pasajes.')->group(function () {
    Route::get('/', [PasajeController::class, 'index'])->name('index');
    Route::post('/guardar', [PasajeController::class, 'guardar'])->name('guardar');
    Route::get('/horario/{horario}/asientos', [PasajeController::class, 'asientosHorario'])
        ->name('horario.asientos');
    Route::get('/vender', [PasajeController::class, 'vender'])->name('vender');
    Route::post('/reservar', [PasajeController::class, 'reservar'])->name('reservar');
    Route::get('/buscar', [PasajeController::class, 'buscarPasaje'])->name('buscar');
    Route::get('/{pasaje}/editar', [PasajeController::class, 'editar'])
        ->name('editar');
});

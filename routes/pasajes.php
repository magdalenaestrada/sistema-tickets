<?php

use App\Http\Controllers\PasajeController;

Route::middleware(['auth'])->prefix('pasajes')->name('pasajes.')->group(function () {
    Route::get('/', [PasajeController::class, 'index'])->name('index');
    Route::get('/vendidos', [PasajeController::class, 'listarVendidos'])->name('vendidos');
    Route::get('/tabla', [PasajeController::class, 'index_busqueda'])->name('index-busqueda');
    Route::post('/guardar', [PasajeController::class, 'guardar'])->name('guardar');
    Route::get('/horario/{horario}/asientos', [PasajeController::class, 'asientosHorario'])
        ->name('horario.asientos');
    Route::get('/vender', [PasajeController::class, 'vender'])->name('vender');
    Route::post('/reservar', [PasajeController::class, 'reservar'])->name('reservar');
    Route::get('/buscar', [PasajeController::class, 'buscarPasaje'])->name('buscar');
    Route::get('/{pasaje}', [PasajeController::class, 'show'])->name('show');
    Route::post('/{pasaje}/abordo', [PasajeController::class, 'abordo'])->name('abordar');
    Route::post('/{pasaje}/no-abordo', [PasajeController::class, 'noAbordo'])->name('noAbordo');
    Route::get('/{pasaje}/editar', [PasajeController::class, 'editar'])->name('editar');
    Route::get('/filtrar', [PasajeController::class, 'filtrarHorarios'])->name('filtrar');
});

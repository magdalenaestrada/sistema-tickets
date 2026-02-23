<?php

use App\Http\Controllers\HorarioController;

Route::middleware(['auth'])->prefix('horarios')->name('horarios.')->group(function () {

    Route::middleware('can:ver horarios')->group(function () {
        Route::get('/', [HorarioController::class, 'index'])->name('index');
        Route::get('/datatable', [HorarioController::class, 'datatable'])->name('datatable');
        Route::get('/calendario', [HorarioController::class, 'calendario'])->name('calendario');
        Route::get('/calendario/eventos', [HorarioController::class, 'getEventos'])->name('calendario.eventos');
        Route::get('/filtrar', [HorarioController::class, 'filtrar'])->name('filtrar');
        Route::get('/{horario}', [HorarioController::class, 'mostrar'])->name('mostrar');
    });

    Route::middleware('can:gestionar horarios')->group(function () {
        Route::post('/', [HorarioController::class, 'guardar'])->name('guardar');
        Route::put('/{horario}', [HorarioController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{horario}', [HorarioController::class, 'eliminar'])->name('eliminar');
    });
});

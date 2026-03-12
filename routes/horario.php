<?php

use App\Http\Controllers\HorarioPuntoController;

Route::middleware(['auth'])->prefix('horarios')->group(function () {

    Route::get('{horario}/puntos', [HorarioPuntoController::class, 'index'])->name('horario.puntos.index');
    Route::get('{horario}/puntos/{punto}', [HorarioPuntoController::class, 'show'])->name('horario.puntos.show');
    Route::post('/puntos-lote', [HorarioPuntoController::class, 'lote'])
        ->name('puntos.lote');
    Route::middleware('can:gestionar horarios')->group(function () {
        Route::post('{horario}/puntos', [HorarioPuntoController::class, 'store'])->name('horario.puntos.store');
        Route::put('{horario}/puntos/{punto}', [HorarioPuntoController::class, 'update'])->name('horario.puntos.update');
        Route::delete('{horario}/puntos/{punto}', [HorarioPuntoController::class, 'destroy'])->name('horario.puntos.destroy');
    });
});

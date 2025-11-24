<?php

use App\Http\Controllers\HorarioPuntoController;

Route::prefix('horarios')->group(function () {
    Route::get('{horario}/puntos', [HorarioPuntoController::class, 'index'])->name('horario.puntos.index');
    Route::post('{horario}/puntos', [HorarioPuntoController::class, 'store'])->name('horario.puntos.store');
    Route::put('{horario}/puntos/{punto}', [HorarioPuntoController::class, 'update'])->name('horario.puntos.update');
    Route::delete('{horario}/puntos/{punto}', [HorarioPuntoController::class, 'destroy'])->name('horario.puntos.destroy');
    Route::get('{horario}/puntos/{punto}', [HorarioPuntoController::class, "show"])->name('horario.puntos.show');
});

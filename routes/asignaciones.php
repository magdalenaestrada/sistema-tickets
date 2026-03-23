<?php

use App\Http\Controllers\AsignarHorarioController;

Route::prefix('asignaciones')->group(function () {
    Route::get('/', [AsignarHorarioController::class, 'index'])->name('asignaciones.index');
    Route::get('/list', [AsignarHorarioController::class, 'datatable'])->name('asignaciones.datatable');
    Route::post('/', [AsignarHorarioController::class, 'store'])->name('asignaciones.store');
    Route::get('/{asignacion}', [AsignarHorarioController::class, 'show'])->name('asignaciones.show');
    Route::put('/{asignacion}', [AsignarHorarioController::class, 'update'])->name('asignaciones.update');
    Route::delete('/{asignacion}', [AsignarHorarioController::class, 'destroy'])->name('asignaciones.destroy');
});

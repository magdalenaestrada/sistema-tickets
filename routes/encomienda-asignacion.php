<?php

use App\Http\Controllers\AsignacionEncomiendaController;

Route::middleware(['auth', 'can:gestionar asignaciones encomiendas'])->prefix('encomiendas-asignacion')->name('encomiendas-asignacion.')->group(function () {
    Route::post('/guardar', [AsignacionEncomiendaController::class, 'store'])->name('guardar');
});

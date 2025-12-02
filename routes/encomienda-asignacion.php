<?php

use App\Http\Controllers\AsignacionEncomiendaController;

Route::middleware(['auth'])->prefix('encomiendas-asignacion')->name('encomiendas-asignacion.')->group(function () {
    Route::post('/guardar', [AsignacionEncomiendaController::class, 'store'])->name('guardar');
});

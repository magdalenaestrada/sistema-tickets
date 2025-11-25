<?php

use App\Http\Controllers\AsignacionEncomiendaController;

Route::middleware(['auth'])
    ->prefix('asignar-encomiendas')
    ->name('asignar.encomiendas.')
    ->group(function () {

        Route::get('/{asignacion}', [AsignacionEncomiendaController::class, 'create'])
            ->name('create');

        Route::post('/', [AsignacionEncomiendaController::class, 'store'])
            ->name('store');

        Route::get('/datatable/no-asignadas', [AsignacionEncomiendaController::class, 'datatableNoAsignadas'])
            ->name('datatable.noasignadas');
    });

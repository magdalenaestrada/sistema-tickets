<?php

use App\Http\Controllers\EncomiendaController;

Route::middleware(['auth'])->prefix('encomiendas')->name('encomiendas.')->group(function () {
    Route::get('/index-no-asignadas', [EncomiendaController::class, 'index_no_asignadas'])->name('index-no-asignadas');
    Route::get('/datatable/no-asignadas', [EncomiendaController::class, 'datatable_no_asignadas'])->name('datatable.no-asignadas');
    Route::post('/guardar', [EncomiendaController::class, 'guardar'])->name('guardar');
    Route::get('/mostrar/{id}', [EncomiendaController::class, 'mostrar'])->name('mostrar');
    Route::post('/anular/{id}', [EncomiendaController::class, 'anular'])->name('anular');
    Route::get('/crear-encomienda', [EncomiendaController::class, 'formulario'])->name('crear-encomienda');
    Route::get('/ticket/{id}', [EncomiendaController::class, 'ticket'])->name('ticket');

});

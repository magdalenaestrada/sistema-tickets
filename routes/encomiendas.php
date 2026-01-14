<?php

use App\Http\Controllers\EncomiendaController;

Route::middleware(['auth'])->prefix('encomiendas')->name('encomiendas.')->group(function () {
    Route::get('/index-no-asignadas', [EncomiendaController::class, 'index_no_asignadas'])->name('index-no-asignadas');
    Route::get('/index-asignadas', [EncomiendaController::class, 'index_asignadas'])->name('index-asignadas');
    Route::get('/datatable/asignadas', [EncomiendaController::class, 'datatable_asignadas'])->name('datatable.asignadas');
    Route::get('/datatable/no-asignadas', [EncomiendaController::class, 'datatable_no_asignadas'])->name('datatable.no-asignadas');
    Route::post('/guardar', [EncomiendaController::class, 'guardar'])->name('guardar');
    Route::get('/mostrar/{id}', [EncomiendaController::class, 'mostrar'])->name('mostrar');
    Route::post('/anular/{id}', [EncomiendaController::class, 'anular'])->name('anular');
    Route::get('/crear-encomienda', [EncomiendaController::class, 'formulario'])->name('crear-encomienda');
    Route::get('/ticket/{id}', [EncomiendaController::class, 'ticket'])->name('ticket');
    Route::get('/editar/{id}', [EncomiendaController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{id}', [EncomiendaController::class, 'actualizar'])->name('actualizar');
});

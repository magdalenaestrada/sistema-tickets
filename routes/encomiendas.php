<?php

use App\Http\Controllers\EncomiendaController;

Route::prefix('encomiendas')->name('encomiendas.')->group(function () {
    Route::get('/', [EncomiendaController::class, 'index'])->name('index');
    Route::get('/datatable', [EncomiendaController::class, 'datatable'])->name('datatable');
    Route::post('/guardar', [EncomiendaController::class, 'guardar'])->name('guardar');
    Route::get('/mostrar/{id}', [EncomiendaController::class, 'mostrar'])->name('mostrar');
    Route::post('/anular/{id}', [EncomiendaController::class, 'anular'])->name('anular');
    Route::get('/crear-encomienda', [EncomiendaController::class, 'formulario'])->name('crear-encomienda');
});

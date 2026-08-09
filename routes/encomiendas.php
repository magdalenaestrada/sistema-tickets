<?php

use App\Http\Controllers\EncomiendaConsultaController;
use App\Http\Controllers\EncomiendaController;

Route::middleware(['auth'])->prefix('encomiendas')->name('encomiendas.')->group(function () {

    Route::middleware('can:gestionar encomiendas')->group(function () {
        Route::post('/guardar', [EncomiendaController::class, 'guardar'])->name('guardar');
        Route::get('/consulta', [EncomiendaConsultaController::class, 'index'])->name('consulta.index');
    Route::get('/consulta/buscar', [EncomiendaConsultaController::class, 'buscar'])->name('consulta.buscar');
        Route::get('/mostrar/{id}', [EncomiendaController::class, 'mostrar'])->name('mostrar');
        Route::get('/crear-encomienda', [EncomiendaController::class, 'formulario'])->name('crear-encomienda');
        Route::get('/{encomienda}/ticket', [EncomiendaController::class, 'ticket'])->name('ticket');
        Route::get('/editar/{id}', [EncomiendaController::class, 'editar'])->name('editar');
        Route::put('/actualizar/{encomienda}', [EncomiendaController::class, 'actualizar'])->name('actualizar');
        Route::get('/no-asignadas', [EncomiendaController::class, 'index_no_asignadas'])->name('index-no-asignadas');
        Route::get('/asignadas', [EncomiendaController::class, 'index_asignadas'])->name('index-asignadas');
        Route::get('/datatable-no-asignadas', [EncomiendaController::class, 'datatable_no_asignadas'])->name('datatable-no-asignadas');
        Route::get('/datatable-asignadas', [EncomiendaController::class, 'datatable_asignadas'])->name('datatable-asignadas');
        Route::get('/salidas-disponibles', [EncomiendaController::class, 'salidasDisponibles'])->name('salidas-disponibles');
        Route::post('/asignar-salida', [EncomiendaController::class, 'asignarSalida'])->name('asignar-salida');
        Route::post('/entregar-masivo', [EncomiendaController::class, 'entregarMasivo'])->name('entregar-masivo');
        Route::post('/{id}/entregar', [EncomiendaController::class, 'entregar'])->name('entregar');
        Route::post('/{id}/agencia', [EncomiendaController::class, 'enAgencia'])->name('agencia');

        Route::get(
    '{encomienda}/ticket-entrega',
    [EncomiendaController::class, 'ticketEntrega']
)->name('ticket-entrega');
    });

    Route::get(
        '/sobreequipaje/{pasaje}/crear',
        [EncomiendaController::class, 'formularioSobrequipaje']
    )->name('sobreequipaje.formulario');

    Route::middleware('can:eliminar encomiendas')->group(function () {
        Route::post('/anular/{id}', [EncomiendaController::class, 'anular'])->name('anular');
    });
});

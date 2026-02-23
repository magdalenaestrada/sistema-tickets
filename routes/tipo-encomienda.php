<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoEncomiendaController;

Route::middleware(['auth', 'can:gestionar tipo encomiendas'])->prefix('tipo-encomienda')->group(function () {

    Route::get('/datatable', [TipoEncomiendaController::class, 'datatable'])
        ->name('tipo-encomienda.datatable');

    Route::get('/', [TipoEncomiendaController::class, 'index'])
        ->name('tipo-encomienda.index');

    Route::get('/create', [TipoEncomiendaController::class, 'create'])
        ->name('tipo-encomienda.create');

    Route::post('/', [TipoEncomiendaController::class, 'store'])
        ->name('tipo-encomienda.store');

    Route::get('/{id}/edit', [TipoEncomiendaController::class, 'edit'])
        ->name('tipo-encomienda.edit');

    Route::put('/{id}', [TipoEncomiendaController::class, 'update'])
        ->name('tipo-encomienda.update');

    Route::delete('/{id}', [TipoEncomiendaController::class, 'destroy'])
        ->name('tipo-encomienda.destroy');

    Route::get('/listar-todos', [TipoEncomiendaController::class, 'listarTodos'])
        ->name('tipo-encomienda.listar-todos');
});

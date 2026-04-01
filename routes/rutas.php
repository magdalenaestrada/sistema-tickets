<?php

use App\Http\Controllers\RutaController;
use App\Models\Ruta;

Route::middleware(['auth'])->prefix('rutas')->name('rutas.')->group(function () {
    Route::get('/', [RutaController::class, 'index'])->name('index');
    Route::get('/datatable', [RutaController::class, 'datatable'])->name('datatable');
    Route::get('/crear', [RutaController::class, 'create'])->name('create');
    Route::post('', [RutaController::class, 'store'])->name('store');
    Route::get('/{id}/editar', [RutaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RutaController::class, 'update'])->name('update');
    Route::get('/{id}', [RutaController::class, 'show'])->name('show');
    Route::delete('/{id}', [RutaController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/activar', [RutaController::class, 'activar'])->name('activar');
    Route::post('/{id}/desactivar', [RutaController::class, 'desactivar'])->name('desactivar');
});

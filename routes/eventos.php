<?php

use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('eventos')->name('eventos.')->group(function () {
    Route::get('/', [EventoController::class, 'index'])->name('index');
    Route::post('/', [EventoController::class, 'guardar'])->name('guardar');
    Route::put('/{evento}', [EventoController::class, 'actualizar'])->name('actualizar');
    Route::delete('/{evento}', [EventoController::class, 'eliminar'])->name('eliminar');

});

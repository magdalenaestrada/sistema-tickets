<?php

use App\Http\Controllers\EmpleadoController;

Route::prefix('empleados')->name('empleados.')->group(function () {
    Route::get('/', [EmpleadoController::class, 'index'])->name('index');
    Route::get('/datatable', [EmpleadoController::class, 'datatable'])->name('datatable');
    Route::get('/{id}', [EmpleadoController::class, 'mostrar'])->name('mostrar');
    Route::post('/guardar', [EmpleadoController::class, 'guardar'])->name('guardar');
    Route::delete('/{id}', [EmpleadoController::class, 'eliminar'])->name('eliminar');
});

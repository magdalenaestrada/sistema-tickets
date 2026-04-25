<?php

use App\Http\Controllers\DescuentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('descuentos')->name('descuentos.')->group(function () {
    Route::middleware('can:ver descuentos')->group(function () {
        Route::get('/', [DescuentoController::class, 'index'])->name('index');
        Route::get('/datatable', [DescuentoController::class, 'datatable'])->name('datatable');
        Route::get('/buscar', [DescuentoController::class, 'buscar'])->name('buscar');
        Route::get('/persona', [DescuentoController::class, 'cuponesPersona'])
            ->name('persona');
        Route::get('/{descuento}', [DescuentoController::class, 'mostrar'])->name('mostrar');
    });
    Route::middleware('can:gestionar descuentos')->group(function () {
        Route::post('/', [DescuentoController::class, 'guardar'])->name('guardar');
        Route::delete('/{descuento}', [DescuentoController::class, 'eliminar'])->name('eliminar');
        Route::post('/{descuento}/activar', [DescuentoController::class, 'activar'])->name('activar');
        Route::post('/{descuento}/desactivar', [DescuentoController::class, 'desactivar'])->name('desactivar');
    });
});

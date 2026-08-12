<?php

use App\Http\Controllers\SalidaController;

Route::middleware(['auth'])->prefix('salidas')->name('salidas.')->group(function () {
    Route::get('/', [SalidaController::class, 'index'])->name('index');
    Route::get('/index-vendedor', [SalidaController::class, 'index_vendedor'])->name('index-vendedor');
    Route::get('/datatable', [SalidaController::class, 'datatable'])->name('datatable');
    Route::post('/', [SalidaController::class, 'store'])->name('store');
    Route::post('/generar', [SalidaController::class, 'generar'])->name('generar');
    Route::post(
        '/directa',
        [SalidaController::class, 'storeDirecta']
    )->name('store.directa');

    Route::get('{salida}/sucursales-ruta', [SalidaController::class, 'sucursalesRuta'])
        ->name('sucursales_ruta');

    Route::get('{salida}/manifiesto-pasajeros/todos', [SalidaController::class, 'manifiestoPasajerosTodos'])
        ->name('manifiesto_pasajeros.todos');

    Route::delete('/bulk', [SalidaController::class, 'destroyBulk'])
        ->name('destroy.bulk');
    Route::get('/{id}', [SalidaController::class, 'show'])->name('show');
    Route::put('/{id}', [SalidaController::class, 'update'])->name('update');
    Route::delete('/{id}', [SalidaController::class, 'destroy'])->name('destroy');
    Route::get('/{salida}/manifiesto-pasajeros', [SalidaController::class, 'manifiestoPasajeros'])->name('manifiesto_pasajeros');
    Route::get('/{salida}/manifiesto-pasajeros-real', [SalidaController::class, 'manifiestoPasajerosReal'])->name('manifiesto_pasajeros_real');
    Route::get('/{salida}/manifiesto-encomiendas', [SalidaController::class, 'manifiestoEncomiendas'])->name('manifiesto_encomiendas');
    Route::get('/{salida}/manifiesto-bodega', [SalidaController::class, 'manifiestoBodega'])->name('manifiesto_bodega');
    Route::get('/{salida}/manifiesto-conductores', [SalidaController::class, 'manifiestoConductores'])->name('manifiesto_conductores');
    Route::get('/{salida}/recursos-disponibles', [SalidaController::class, 'recursosDisponibles'])
    ->name('recursos_disponibles');
});

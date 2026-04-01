<?php

use App\Http\Controllers\VentaSunatController;
use App\Models\Ruta;
use App\Models\TipoViaje;
use App\Models\TipoVehiculo;

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/rutas/lista', function () {
        return Ruta::select('id', 'nombre')->orderBy('nombre')->get();
    })->name('rutas.lista');

    Route::get('/tipos-viaje/lista', function () {
        return TipoViaje::select('id', 'descripcion')->orderBy('descripcion')->get();
    })->name('tipos_viaje.lista');

    Route::get('/tipos-vehiculo/lista', function () {
        return TipoVehiculo::select('id', 'descripcion')->orderBy('descripcion')->get();
    })->name('tipos_vehiculo.lista');

    Route::get('/ventas/{id}/emitir-sunat', [VentaSunatController::class, 'emitir']);
});

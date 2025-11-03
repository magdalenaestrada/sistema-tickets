<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UbigeoController;

// Rutas reutilizables para cualquier módulo
Route::prefix('ubigeos')->group(function () {
    Route::get('/departamentos', [UbigeoController::class, 'getDepartamentos']);
    Route::get('/provincias/{departamento_id}', [UbigeoController::class, 'getProvincias']);
    Route::get('/distritos/{provincia_id}', [UbigeoController::class, 'getDistritos']);
    Route::get('/ubigeos-con-sucursales', [UbigeoController::class, 'getUbigeosConSucursales']);
    Route::get('/sucursales/{distrito_id}', [UbigeoController::class, 'getSucursalesPorDistrito']);
});

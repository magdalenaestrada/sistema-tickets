<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UbigeoController;

Route::middleware(['auth'])->prefix('ubigeos')->group(function () {
    Route::get('/todo', [UbigeoController::class, 'todo'])
        ->name('ubigeos.todo');
    Route::get('/departamentos', [UbigeoController::class, 'getDepartamentos'])
        ->name('ubigeos.departamentos');
    Route::get('/provincias/{departamento_id}', [UbigeoController::class, 'getProvincias'])
        ->name('ubigeos.provincias');
    Route::get('/distritos/{provincia_id}', [UbigeoController::class, 'getDistritos'])
        ->name('ubigeos.distritos');
    Route::get('/ubigeos-con-sucursales', [UbigeoController::class, 'getUbigeosConSucursales'])
        ->name('ubigeos.conSucursales');
    Route::get('/sucursales/{distrito_id}', [UbigeoController::class, 'getSucursalesPorDistrito'])
        ->name('ubigeos.sucursalesPorDistrito');

    Route::get('/distrito/{id}', [UbigeoController::class, 'byDistrito'])
        ->name('ubigeos.byDistrito');
});

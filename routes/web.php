<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/empresas.php';
require __DIR__ . '/buscar.php';
require __DIR__ . '/sucursales.php';
require __DIR__ . '/areas.php';
require __DIR__ . '/cargos.php';
require __DIR__ . '/empleados.php';
require __DIR__ . '/listas.php';
require __DIR__ . '/ubigeos.php';
require __DIR__ . '/vehiculos.php';
require __DIR__ . '/horarios.php';
require __DIR__ . '/encomiendas.php';
require __DIR__ . '/eventos.php';
require __DIR__ . '/tipo-encomienda.php';

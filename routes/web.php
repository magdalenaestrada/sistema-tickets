<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Caja;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/caja/verificar', function () {
    $user = Auth::user();

    $tieneCaja = Caja::where('sucursal_id', $user->sucursal_id)
        ->where('estado', 'A')
        ->exists();

    return response()->json(['abierta' => $tieneCaja]);
})->name('caja.verificar');



require __DIR__ . '/areas.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/buscar.php';
require __DIR__ . '/cargos.php';
require __DIR__ . '/console.php';
require __DIR__ . '/empleados.php';
require __DIR__ . '/empresas.php';
require __DIR__ . '/encomiendas.php';
require __DIR__ . '/eventos.php';
require __DIR__ . '/horarios.php';
require __DIR__ . '/listas.php';
require __DIR__ . '/sucursales.php';
require __DIR__ . '/tipo-encomienda.php';
require __DIR__ . '/ubigeos.php';
require __DIR__ . '/vehiculos.php';
require __DIR__ . '/caja.php';
require __DIR__ . '/horario.php';
require __DIR__ . '/asignaciones.php';
require __DIR__ . '/pasajes.php';
require __DIR__ . '/permisos.php';
require __DIR__ . '/descuentos.php';
require __DIR__ . '/roles.php';
require __DIR__ . '/reportes.php';
require __DIR__ . '/clientes.php';
require __DIR__ . '/usuarios.php';
require __DIR__ . '/encomienda-asignacion.php';

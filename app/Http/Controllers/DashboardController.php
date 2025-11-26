<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Encomienda;
use App\Models\Horario;
use App\Models\Caja;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $sucursalId = $usuario->sucursal_id;
        $hoy = now()->toDateString();
        $diaSemana = now()->dayOfWeek;
        $mapDias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            0 => 'domingo',
        ];

        $columnaDia = $mapDias[$diaSemana];
        $ventasHoy = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('fecha_emision', $hoy)
            ->count();
        $encomiendasHoy = Encomienda::where('origen', $sucursalId)
            ->whereDate('fecha_creacion', $hoy)
            ->count();
        $caja = Caja::where('usuario_id', $usuario->id)
            ->whereNull('fecha_cierre')
            ->latest('fecha_creacion')
            ->first();

        $montoActual = $caja ? $caja->monto_actual : 0;

        $horariosHoy = Horario::where('punto_origen_id', $sucursalId)
            ->where(function ($query) use ($hoy, $columnaDia) {
                $query->whereDate('fecha_salida', $hoy)
                    ->orWhere($columnaDia, 1);
            })
            ->with(['punto_origen', 'punto_destino', 'tipo_vehiculo', 'tipo_viaje'])
            ->get();

        return view('dashboard', compact(
            'ventasHoy',
            'encomiendasHoy',
            'montoActual',
            'horariosHoy',
            'usuario'
        ));
    }
}

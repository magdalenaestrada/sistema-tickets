<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function __invoke(Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->desde)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->hasta)->endOfDay()
            : now()->endOfDay();

        $sucursalId = $request->sucursal_id;

        $sucursales = Sucursal::orderBy('nombre_comercial')->get();

        // 1. ESTADO DE SALIDAS (GRÁFICA)
        $salidasPorEstado = Salida::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->whereBetween('fecha_salida', [$desde, $hasta])
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->whereHas('horario.ruta.puntos', function ($subQuery) use ($sucursalId) {
                    $subQuery->where('sucursal_id', $sucursalId)
                        ->where('orden', 1);
                });
            })
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $estadoSalidasChart = [
            'labels' => $salidasPorEstado->keys()->map(fn($e) => ucfirst($e))->values(),
            'data'   => $salidasPorEstado->values()->map(fn($n) => (int) $n)->values(),
        ];

        // 2. VENTAS BASE Y KPIS
        $ventasBase = Venta::query()
            ->with(['sucursal', 'usuario'])
            ->whereBetween('fecha_emision', [$desde, $hasta]);

        if ($sucursalId) {
            $ventasBase->where('sucursal_id', $sucursalId);
        }

        $ventasHoy = Venta::query()
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereDate('fecha_emision', now()->toDateString())
            ->sum('total');

        $ticketsHoy = Venta::query()
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereDate('fecha_emision', now()->toDateString())
            ->count();

        $ventasMes = (clone $ventasBase)->sum('total');
        $ticketsMes = (clone $ventasBase)->count();

        // 3. RANKING DE VENDEDORES
        $rankingVendedores = Venta::query()
            ->selectRaw('users.id, users.username, sucursales.nombre_comercial as sucursal, COUNT(ventas.id) as tickets, SUM(ventas.total) as ventas')
            ->join('users', 'users.id', '=', 'ventas.usuario_id')
            ->join('sucursales', 'sucursales.id', '=', 'ventas.sucursal_id')
            ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
            ->when($sucursalId, fn($q) => $q->where('ventas.sucursal_id', $sucursalId))
            ->groupBy('users.id', 'users.username', 'sucursales.nombre_comercial')
            ->orderByDesc('ventas')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'nombre' => $row->username,
                'sucursal' => $row->sucursal,
                'tickets' => (int) $row->tickets,
                'ventas' => (float) $row->ventas,
            ])
            ->values()
            ->all();

        // 4. DETALLE ANALÍTICO DE SALIDAS
        $detalleSalidas = Salida::query()
            ->with([
                'horario.ruta.puntos.sucursal',
                'horario.ruta.puntos.pueblito',
                'vehiculo',
                'conductorPrincipal',
                'conductorSecundario',
                'pasajes',
            ])
            ->whereBetween('fecha_salida', [$desde, $hasta])
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->whereHas('horario.ruta.puntos', function ($subQuery) use ($sucursalId) {
                    $subQuery->where('sucursal_id', $sucursalId)
                        ->where('orden', 1);
                });
            })
            ->orderBy('fecha_salida', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($salida) {

                $puntos = $salida->horario?->ruta?->puntos
                    ?->sortBy('orden')
                    ->values() ?? collect();

                $puntoInicio = $puntos->first();
                $puntoFinal = $puntos->last();

                /*
         * Nombre de ruta.
         * Como no necesariamente tienes una columna "nombre" en rutas,
         * la construimos usando origen y destino.
         */
                $origen = $puntoInicio?->pueblito?->descripcion ?? '-';
                $destino = $puntoFinal?->pueblito?->descripcion ?? '-';

                $ruta = "{$origen} - {$destino}";

                /*
         * Sucursal de origen.
         */
                $sucursal = $puntoInicio?->sucursal?->nombre_comercial
                    ?? $puntoInicio?->pueblito?->sucursal?->nombre_comercial
                    ?? '-';

                /*
         * Pasajes válidos de la salida.
         * Aquí puedes ajustar los estados según tu sistema.
         */
                $pasajes = $salida->pasajes
                    ->whereIn('estado', ['V', 'F']);

                $embarcados = $pasajes->count();

                /*
         * Cambia "capacidad" si en tu tabla vehículos
         * el campo tiene otro nombre.
         */
                $capacidad = (int) (
                    $salida->vehiculo?->capacidad
                    ?? $salida->vehiculo?->cantidad_asientos
                    ?? 0
                );

                $ocupacion = $capacidad > 0
                    ? round(($embarcados / $capacidad) * 100, 1)
                    : 0;

                $ingresos = (float) $pasajes->sum('precio_pasaje');

                return [
                    'id' => $salida->id,

                    'fecha' => Carbon::parse($salida->fecha_salida)
                        ->format('d/m/Y'),

                    'hora' => $salida->horario?->hora_salida
                        ? Carbon::parse($salida->horario->hora_salida)->format('H:i')
                        : '-',

                    'sucursal' => $sucursal,

                    'ruta' => $ruta,

                    'vehiculo' => $salida->vehiculo?->placa ?? '-',

                    'capacidad' => $capacidad,

                    'embarcados' => $embarcados,

                    'ocupacion' => $ocupacion,

                    'ingresos' => $ingresos,

                    'estado' => $salida->estado,
                ];
            })
            ->values()
            ->all();
        // Conteo previo de salidas por sucursal de origen para adjuntarlo al resumen
        $salidasPorSucursal = Salida::query()
            ->join('horarios', 'salidas.horario_id', '=', 'horarios.id')
            ->join('rutas', 'horarios.ruta_id', '=', 'rutas.id')
            ->join('ruta_puntos', function ($join) {
                $join->on('rutas.id', '=', 'ruta_puntos.ruta_id')
                    ->where('ruta_puntos.orden', '=', 1);
            })
            ->selectRaw('ruta_puntos.sucursal_id, COUNT(salidas.id) as total')
            ->whereBetween('salidas.fecha_salida', [$desde, $hasta])
            ->whereNotNull('ruta_puntos.sucursal_id')
            ->groupBy('ruta_puntos.sucursal_id')
            ->pluck('total', 'sucursal_id');

        $resumenSucursales = Venta::query()
            ->selectRaw('sucursales.id, sucursales.nombre_comercial, COUNT(ventas.id) as tickets, SUM(ventas.total) as ventas')
            ->join('sucursales', 'sucursales.id', '=', 'ventas.sucursal_id')
            ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
            ->when($sucursalId, fn($q) => $q->where('ventas.sucursal_id', $sucursalId))
            ->groupBy('sucursales.id', 'sucursales.nombre_comercial')
            ->orderByDesc('ventas')
            ->get()
            ->map(function ($row) use ($salidasPorSucursal) {
                return [
                    'nombre_comercial' => $row->nombre_comercial,
                    'salidas'          => (int) ($salidasPorSucursal[$row->id] ?? 0), // <--- SOLUCIÓN AL ERROR EN BLADE
                    'tickets'          => (int) $row->tickets,
                    'ventas'           => (float) $row->ventas,
                ];
            })
            ->values()
            ->all();

        // 5. VENTAS POR DÍA
        $ventasPorDiaRows = Venta::query()
            ->selectRaw('DATE(fecha_emision) as fecha, SUM(total) as total')
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->groupByRaw('DATE(fecha_emision)')
            ->orderBy('fecha')
            ->get();

        $ventasPorDiaChart = [
            'labels' => $ventasPorDiaRows->pluck('fecha')->values(),
            'data'   => $ventasPorDiaRows->pluck('total')->map(fn($n) => (float) $n)->values(),
        ];

        $ventasPorSucursalChart = [
            'labels' => collect($resumenSucursales)->pluck('nombre_comercial')->values(),
            'data'   => collect($resumenSucursales)->pluck('ventas')->values(),
        ];

        $kpis = [
            'ventas_hoy'         => $ventasHoy,
            'ventas_mes'         => $ventasMes,
            'tickets_hoy'        => $ticketsHoy,
            'tickets_mes'        => $ticketsMes,
            'ocupacion_promedio' => 0,
            'anulaciones'        => 0,
        ];

        return view('dashboard.admin', [
            'sucursales'             => $sucursales,
            'rutas'                  => collect(),
            'kpis'                   => $kpis,
            'topRutas'               => collect(),
            'rankingVendedores'      => $rankingVendedores,
            'resumenSucursales'      => $resumenSucursales,
            'detalleSalidas'         => $detalleSalidas,
            'alertas'                => [],
            'ventasPorDiaChart'      => $ventasPorDiaChart,
            'estadoSalidasChart'     => $estadoSalidasChart,
            'ventasPorSucursalChart' => $ventasPorSucursalChart,
        ]);
    }
}

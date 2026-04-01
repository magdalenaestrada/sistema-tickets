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

        $resumenSucursales = Venta::query()
            ->selectRaw('sucursales.id, sucursales.nombre_comercial, COUNT(ventas.id) as tickets, SUM(ventas.total) as ventas')
            ->join('sucursales', 'sucursales.id', '=', 'ventas.sucursal_id')
            ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
            ->when($sucursalId, fn($q) => $q->where('ventas.sucursal_id', $sucursalId))
            ->groupBy('sucursales.id', 'sucursales.nombre_comercial')
            ->orderByDesc('ventas')
            ->get()
            ->map(function ($row) use ($desde, $hasta) {
                return [
                    'nombre' => $row->nombre,
                    'salidas' => 0,
                    'tickets' => (int) $row->tickets,
                    'ventas' => (float) $row->ventas,
                ];
            })
            ->values()
            ->all();

        $ventasPorDiaRows = Venta::query()
            ->selectRaw('DATE(fecha_emision) as fecha, SUM(total) as total')
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->groupByRaw('DATE(fecha_emision)')
            ->orderBy('fecha')
            ->get();

        $ventasPorDiaChart = [
            'labels' => $ventasPorDiaRows->pluck('fecha')->values(),
            'data' => $ventasPorDiaRows->pluck('total')->map(fn($n) => (float) $n)->values(),
        ];

        $ventasPorSucursalChart = [
            'labels' => collect($resumenSucursales)->pluck('nombre_comercial')->values(),
            'data' => collect($resumenSucursales)->pluck('ventas')->values(),
        ];

        $kpis = [
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'tickets_hoy' => $ticketsHoy,
            'tickets_mes' => $ticketsMes,
            'ocupacion_promedio' => 0,
            'anulaciones' => 0,
        ];

        return view('dashboard.admin', [
            'sucursales' => $sucursales,
            'rutas' => collect(),
            'kpis' => $kpis,
            'topRutas' => collect(),
            'rankingVendedores' => $rankingVendedores,
            'resumenSucursales' => $resumenSucursales,
            'detalleSalidas' => collect(),
            'alertas' => [],
            'ventasPorDiaChart' => $ventasPorDiaChart,
            'estadoSalidasChart' => ['labels' => [], 'data' => []],
            'ventasPorSucursalChart' => $ventasPorSucursalChart,
        ]);
    }
}

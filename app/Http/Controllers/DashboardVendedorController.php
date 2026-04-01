<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardVendedorController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->sucursal_id, 403, 'El usuario no tiene sucursal asignada.');

        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->toDateString()
            : now()->toDateString();

        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = Carbon::parse($fecha)->endOfDay();
        $sucursalId = $user->sucursal_id;

        $vendedores = User::query()
            ->where('sucursal_id', $sucursalId)
            ->orderBy('username')
            ->get();

        $ventasBase = Venta::query()
            ->where('sucursal_id', $sucursalId)
            ->whereBetween('fecha_emision', [$inicioDia, $finDia]);

        if ($request->filled('vendedor_id')) {
            $ventasBase->where('usuario_id', $request->vendedor_id);
        }

        $ticketsHoy = (clone $ventasBase)->count();
        $ventasHoy = (clone $ventasBase)->sum('total');

        $misVentasHoy = Venta::query()
            ->where('sucursal_id', $sucursalId)
            ->where('usuario_id', $user->id)
            ->whereBetween('fecha_emision', [$inicioDia, $finDia])
            ->sum('total');

        $rankingVendedores = Venta::query()
            ->selectRaw('users.id, users.username, COUNT(ventas.id) as tickets, SUM(ventas.total) as importe')
            ->join('users', 'users.id', '=', 'ventas.usuario_id')
            ->where('ventas.sucursal_id', $sucursalId)
            ->whereBetween('ventas.fecha_emision', [$inicioDia, $finDia])
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('importe')
            ->get()
            ->map(function ($row) {
                return [
                    'nombre' => $row->username,
                    'tickets' => (int) $row->tickets,
                    'importe' => (float) $row->importe,
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | SALIDAS DE LA SUCURSAL
        |--------------------------------------------------------------------------
        | Como Salida no tiene sucursal_id directo, filtramos por rutas que pasen
        | por la sucursal del usuario.
        |
        | Esto asume:
        | salida -> horario -> ruta -> puntos()
        | y cada punto tiene sucursal_id
        |--------------------------------------------------------------------------
        */

        $salidasCollection = Salida::query()
            ->with([
                'horario.ruta.puntos.sucursal',
                'horario.tipo_vehiculo',
                'vehiculo',
                'pasajes',
            ])
            ->whereDate('fecha_salida', $fecha)
            ->whereHas('horario.ruta.puntos', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            })
            ->get();

        $salidas = $salidasCollection
            ->map(function ($salida) use ($sucursalId) {
                $capacidad = (int) (
                    $salida->vehiculo->capacidad
                    ?? $salida->horario?->tipo_vehiculo?->capacidad
                    ?? $salida->horario?->tipo_vehiculo?->asientos
                    ?? 0
                );

                $vendidos = $salida->pasajes
                    ->whereIn('estado', ['V', 'R'])
                    ->count();

                $libres = max($capacidad - $vendidos, 0);
                $ocupacion = $capacidad > 0 ? round(($vendidos / $capacidad) * 100) : 0;

                $ocupacionColor = match (true) {
                    $ocupacion < 40 => 'danger',
                    $ocupacion < 70 => 'warning',
                    default => 'success',
                };

                $ruta = $salida->horario?->ruta;
                $puntos = $ruta?->puntos?->sortBy('orden')->values();

                $origen = optional($puntos?->first())->sucursal->nombre ?? '-';
                $destino = optional($puntos?->last())->sucursal->nombre ?? '-';

                return [
                    'hora' => $salida->horario?->hora_formateada ?? '-',
                    'ruta' => $origen . ' - ' . $destino,
                    'vehiculo' => trim(
                        ($salida->vehiculo->marca ?? 'Vehículo') . ' / ' .
                            ($salida->vehiculo->numero_placa ?? '-')
                    ),
                    'capacidad' => $capacidad,
                    'vendidos' => $vendidos,
                    'libres' => $libres,
                    'ocupacion' => $ocupacion,
                    'ocupacion_color' => $ocupacionColor,
                    'estado' => $salida->estado ?? 'Programada',
                    'url_detalle' => route('salidas.show', $salida->id),
                ];
            })
            ->sortBy('hora')
            ->values()
            ->all();

        $salidasHoy = count($salidas);

        $ocupacionPromedio = count($salidas)
            ? round(collect($salidas)->avg('ocupacion'))
            : 0;

        $alertas = collect($salidas)
            ->filter(fn($item) => $item['ocupacion'] < 30)
            ->map(fn($item) => "La salida {$item['hora']} ({$item['ruta']}) tiene baja ocupación: {$item['ocupacion']}%.")
            ->take(5)
            ->values()
            ->all();

        $resumenHorarios = Venta::query()
            ->selectRaw("DATE_FORMAT(fecha_emision, '%H:00') as horario, COUNT(id) as tickets, SUM(total) as ventas")
            ->where('sucursal_id', $sucursalId)
            ->whereBetween('fecha_emision', [$inicioDia, $finDia])
            ->groupByRaw("DATE_FORMAT(fecha_emision, '%H:00')")
            ->orderBy('horario')
            ->get()
            ->map(function ($row) {
                $horaInicio = Carbon::createFromFormat('H:i', $row->horario);
                return [
                    'horario' => $horaInicio->format('H:i') . ' - ' . $horaInicio->copy()->addHour()->format('H:i'),
                    'tickets' => (int) $row->tickets,
                    'ventas' => (float) $row->ventas,
                ];
            })
            ->values()
            ->all();

        $ventasPorHoraChart = [
            'labels' => collect($resumenHorarios)->pluck('horario')->values(),
            'data' => collect($resumenHorarios)->pluck('ventas')->values(),
        ];

        $kpis = [
            'tickets_hoy' => $ticketsHoy,
            'ventas_hoy' => $ventasHoy,
            'salidas_hoy' => $salidasHoy,
            'ocupacion_promedio' => $ocupacionPromedio,
            'mis_ventas_hoy' => $misVentasHoy,
        ];

        return view('dashboard.vendedor', compact(
            'vendedores',
            'kpis',
            'rankingVendedores',
            'salidas',
            'alertas',
            'resumenHorarios',
            'ventasPorHoraChart'
        ));
    }
}

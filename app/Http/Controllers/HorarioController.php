<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\HorarioFecha;
use App\Models\HorarioPunto;
use App\Models\HorarioTramo;
use App\Models\Sucursal;
use App\Models\TipoVehiculo;
use App\Models\TipoViaje;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HorarioController extends Controller
{
    public function index()
    {
        $tiposViaje = TipoViaje::all();
        $tipo_vehiculos   = TipoVehiculo::all();
        $sucursales  = Sucursal::where('estado', 'A')->get();
        $horarios = Horario::with(['tipo_viaje', 'punto_origen', 'punto_destino', 'tipo_vehiculo'])->select('horarios.*');
        $hoy = Carbon::now("America/Lima")->format('Y-m-d');
        return view('horarios.index', compact('hoy', 'tiposViaje', 'tipo_vehiculos', 'sucursales', 'horarios'));
    }

    public function datatable()
    {
        $horarios = Horario::with(['fechas', 'tipo_viaje', 'punto_origen', 'punto_destino', 'tipo_vehiculo']);

        return DataTables::of($horarios)
            ->addColumn('tipo_viaje', fn($h) => $h->tipo_viaje->descripcion)
            ->addColumn('tipo_vehiculo', fn($h) => $h->tipo_vehiculo->descripcion)
            ->addColumn('origen', fn($h) => optional($h->punto_origen)->nombre_comercial)
            ->addColumn('fecha_salida', function ($h) {
                return optional($h->fechas->first())->fecha_salida
                    ? Carbon::parse($h->fechas->first()->fecha_salida)->format('d-m-Y')
                    : '';
            })
            ->addColumn('destino', fn($h) => optional($h->punto_destino)->nombre_comercial)
            ->addColumn('acciones', function ($h) {
                return '
        <button class="btn btn-warning btn-xs editar" data-id="' . $h->id . '">
            <i data-lucide="pen"></i>
        </button>
        <button class="btn btn-danger btn-xs eliminar" data-id="' . $h->id . '">
            <i data-lucide="trash-2"></i>
        </button>
    ';
            })

            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'tipo_viaje_id' => 'required|exists:tipos_viajes,id',
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
            'punto_origen_id' => 'required|exists:sucursales,id',
            'hora_salida' => 'required',
            'fecha_salida' => 'required|date',
            'repetir_hasta' => 'nullable|date|after_or_equal:fecha_salida',
        ]);

        if ($request->tipo_viaje_id == 2) {
            $request->validate([
                'puntos' => 'required|array|min:1',
                'puntos.*.sucursal_id' => 'required|exists:sucursales,id',
                'puntos.*.costo' => 'required|numeric|min:0',
                'puntos.*.duracion' => 'required|integer|min:1',
            ]);
        }

        $inicio = Carbon::parse($request->fecha_salida);
        $fin = $request->repetir_hasta ? Carbon::parse($request->repetir_hasta) : $inicio;
        $fecha = $inicio->copy();

        while ($fecha->lte($fin)) {

            $horario = Horario::create([
                'tipo_viaje_id'    => $request->tipo_viaje_id,
                'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
                'punto_origen_id'  => $request->punto_origen_id,
                'punto_destino_id' => $request->tipo_viaje_id != 2 ? $request->punto_destino_id : null,
                'hora_salida'      => $request->hora_salida,
                'costo_base'       => $request->costo_pasaje,
            ]);

            if ($request->tipo_viaje_id == 2 && $request->filled('puntos')) {
                $puntosArray = $request->input('puntos');
                $ultimoPunto = end($puntosArray);

                $horario->update([
                    'punto_destino_id' => $ultimoPunto['sucursal_id'],
                ]);

                $orden = 1;
                $puntoIds = [];

                $puntoOrigen = HorarioPunto::create([
                    'horario_id' => $horario->id,
                    'sucursal_id' => $request->punto_origen_id,
                    'orden' => $orden++,
                ]);
                $puntoIds[$request->punto_origen_id] = $puntoOrigen->id;

                for ($i = 0; $i < count($puntosArray); $i++) {
                    $punto = HorarioPunto::create([
                        'horario_id' => $horario->id,
                        'sucursal_id' => $puntosArray[$i]['sucursal_id'],
                        'orden' => $orden++,
                    ]);
                    $puntoIds[$puntosArray[$i]['sucursal_id']] = $punto->id;
                }

                $horaActual = Carbon::parse($horario->hora_salida);
                for ($i = 0; $i < count($puntosArray); $i++) {
                    $origenSucursalId = $i === 0 ? $request->punto_origen_id : $puntosArray[$i - 1]['sucursal_id'];
                    $destinoSucursalId = $puntosArray[$i]['sucursal_id'];
                    $duracion = (int) $puntosArray[$i]['duracion'];
                    $horaActual->addMinutes($duracion);

                    HorarioTramo::create([
                        'horario_id'       => $horario->id,
                        'punto_origen_id'  => $puntoIds[$origenSucursalId],
                        'punto_destino_id' => $puntoIds[$destinoSucursalId],
                        'duracion_minutos' => $duracion,
                        'costo_tramo'      => $puntosArray[$i]['costo'],
                        'hora_llegada'     => $horaActual->format('H:i'),
                    ]);
                }
            }

            HorarioFecha::create([
                'horario_id' => $horario->id,
                'fecha_salida' => $fecha->format('Y-m-d'),
                'lunes'     => $fecha->isMonday(),
                'martes'    => $fecha->isTuesday(),
                'miercoles' => $fecha->isWednesday(),
                'jueves'    => $fecha->isThursday(),
                'viernes'   => $fecha->isFriday(),
                'sabado'    => $fecha->isSaturday(),
                'domingo'   => $fecha->isSunday(),
            ]);

            $fecha->addDay();
        }

        return response()->json(['success' => true]);
    }


    public function mostrar($id)
    {
        $horario = Horario::with([
            'tipo_viaje',
            'punto_origen',
            'punto_destino',
            'fechas',
            'puntos',
            'tramos.origen.sucursal',
            'tramos.destino.sucursal',
        ])->findOrFail($id);

        $horario->fecha_salida = optional($horario->fechas->first())->fecha_salida;
        $horario->fecha_fin = optional($horario->fechas->last())->fecha_salida;

        return response()->json($horario);
    }



    public function actualizar(Request $request, Horario $horario)
    {
        $request->validate([
            'tipo_viaje_id' => 'required|exists:tipos_viajes,id',
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
            'punto_origen_id' => 'required|exists:sucursales,id',
            'punto_destino_id' => 'required|exists:sucursales,id',
            'costo_pasaje' => 'required|numeric',
            'hora_salida' => 'required',
            'fecha_salida' => 'required|date',
        ]);

        $horario->update([
            'tipo_viaje_id'    => $request->tipo_viaje_id,
            'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
            'punto_origen_id'  => $request->punto_origen_id,
            'punto_destino_id' => $request->punto_destino_id,
            'hora_salida'      => $request->hora_salida,
            'costo_base'       => $request->costo_pasaje,
        ]);
        return response()->json(['success' => true]);
    }

    public function eliminar(Horario $horario)
    {
        try {
            $horario->delete();
            return response()->json(['success' => true, 'message' => 'Horario eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function calendario()
    {
        $sucursales = Sucursal::where('estado', 'A')->orderBy('nombre_comercial')->get();
        $tiposViaje = TipoViaje::all();

        $fechas = HorarioFecha::with([
            'horario.tipo_viaje',
            'horario.punto_destino',
            'horario.punto_origen',
            'horario.tipo_vehiculo'
        ])
            ->whereDate('fecha_salida', '>=', now()->startOfYear())
            ->whereDate('fecha_salida', '<=', now()->endOfYear())
            ->get();

        $eventos = $fechas->map(fn($f) => [
            'title' => optional($f->horario->punto_origen)->nombre_comercial . ' -> ' . optional($f->horario->punto_destino)->nombre_comercial ?? 'Sin destino',
            'start' => Carbon::parse($f->fecha_salida)->format('Y-m-d') . 'T' . $f->horario->hora_salida,
            'color' => $f->horario->tipo_viaje_id == 1 ? '#007bff' : '#ff006f', // Directo=azul, Tramos=naranja
            'extendedProps' => [
                'id'          => $f->horario->id,
                'origen' => $f->horario->punto_origen->nombre_comercial ?? 'Sin origen',
                'destino' => $f->horario->punto_destino->nombre_comercial ?? 'Sin destino',
                'hora'        => $f->horario->hora_salida,
                'tipo_viaje'  => $f->horario->tipo_viaje->descripcion ?? '',
                'tipo_viaje_id' => $f->horario->tipo_viaje_id,
                'vehiculo'    => $f->horario->tipo_vehiculo->descripcion ?? '',
                'costo'       => $f->horario->costo_base,
            ]
        ]);

        return view('horarios.calendario', compact('eventos', 'sucursales', 'tiposViaje'));
    }

    public function getEventos(Request $request)
    {
        $inicio = $request->start;
        $fin = $request->end;

        $fechas = HorarioFecha::with([
            'horario.tipo_viaje',
            'horario.punto_destino',
            'horario.tipo_vehiculo'
        ])
            ->whereBetween('fecha_salida', [$inicio, $fin])
            ->get();

        $eventos = [];

        foreach ($fechas as $fecha) {

            $horario = $fecha->horario;

            $eventos[] = [
                'title' => optional($horario->punto_destino)->nombre_comercial ?? 'Sin destino',
                'start' => $fecha->fecha_salida . 'T' . $horario->hora_salida,
                'extendedProps' => [
                    'id' => $horario->id,
                    'tipo_viaje' => $horario->tipo_viaje->descripcion ?? '',
                    'tipo_vehiculo' => $horario->tipo_vehiculo->descripcion ?? '',
                    'costo' => $horario->costo_base,
                ],
            ];
        }

        return response()->json($eventos);
    }

    public function filtrar(Request $request)
    {
        $query = Horario::query()
            ->with([
                'tipo_viaje',
                'punto_origen',
                'punto_destino',
                'tipo_vehiculo'
            ])
            ->withCount('pasajes');

        if ($request->origen) {
            $query->where('punto_origen_id', $request->origen);
        }

        if ($request->destino) {
            $query->where('punto_destino_id', $request->destino);
        }

        if ($request->tipo_viaje) {
            $query->where('tipo_viaje_id', $request->tipo_viaje);
        }

        if ($request->tipo_vehiculo) {
            $query->where('tipo_vehiculo_id', $request->tipo_vehiculo);
        }

        if ($request->fecha) {
            $query->whereHas('fechas', function ($q) use ($request) {
                $q->whereDate('fecha_salida', $request->fecha);
            });
        }

        $horarios = $query->get();

        return response()->json(
            $horarios->map(function ($h) {
                return [
                    'id' => $h->id,
                    'hora_salida' => $h->hora_salida,
                    'fecha_salida' => $h->fecha_salida_formateada,
                    'pasajes_count' => $h->pasajes_count,
                    'tipo_vehiculo' => [
                        'id' => $h->tipo_vehiculo->id ?? null,
                        'descripcion' => $h->tipo_vehiculo->descripcion ?? ''
                    ],
                    'tipo_viaje' => [
                        'id' => $h->tipo_viaje->id ?? null,
                        'descripcion' => $h->tipo_viaje->descripcion ?? ''
                    ],
                    'punto_origen' => [
                        'id' => $h->punto_origen->id ?? null,
                        'nombre_comercial' => $h->punto_origen->nombre_comercial ?? ''
                    ],
                    'punto_destino' => [
                        'id' => $h->punto_destino->id ?? null,
                        'nombre_comercial' => $h->punto_destino->nombre_comercial ?? ''
                    ],
                ];
            })
        );
    }
}

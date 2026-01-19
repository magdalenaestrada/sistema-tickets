<?php

namespace App\Http\Controllers;

use App\Models\Horario;
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

        return view('horarios.index', compact('tiposViaje', 'tipo_vehiculos', 'sucursales', 'horarios'));
    }

    public function datatable()
    {
        $horarios = Horario::with(['tipo_viaje', 'punto_origen', 'punto_destino', 'tipo_vehiculo'])->select('horarios.*');

        return DataTables::of($horarios)
            ->addColumn('tipo_viaje', fn($h) => $h->tipo_viaje->descripcion)
            ->addColumn('tipo_vehiculo', fn($h) => $h->tipo_vehiculo->descripcion)
            ->addColumn('origen', fn($h) => $h->punto_origen->nombre_comercial)
            ->addColumn('fecha_salida', fn($h) => $h->fecha_salida->format('d-m-Y'))
            ->addColumn('destino', fn($h) => $h->punto_destino->nombre_comercial)
            ->addColumn('acciones', function ($h) {

                $btnPuntos = '';

                if ($h->tipo_viaje_id == 2) {
                    $btnPuntos = '
           <button 
    class="btn btn-primary btn-xs ver-puntos"
    data-id="' . $h->id . '"
    data-origen="' . e($h->punto_origen->nombre_comercial) . '"
>
                <i class="link-icon" data-lucide="map-pin-house"></i>
            </button>';
                }

                return '
        <button class="btn btn-secondary btn-xs ver" data-id="' . $h->id . '">
            <i data-lucide="info"></i>
        </button>
        <button class="btn btn-warning btn-xs editar" data-id="' . $h->id . '">
            <i data-lucide="pen"></i>
        </button>
        <button class="btn btn-danger btn-xs eliminar" data-id="' . $h->id . '">
            <i data-lucide="trash-2"></i>
        </button>
        ' . $btnPuntos . '
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
            'punto_destino_id' => 'required|exists:sucursales,id',
            'costo_pasaje' => 'required|numeric',
            'hora_embarque' => 'required',
            'fecha_salida' => 'required|date',
            'repetir_hasta' => 'nullable|date',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_salida);
        $fechaFin = $request->repetir_hasta
            ? Carbon::parse($request->repetir_hasta)
            : $fechaInicio->copy()->addMonths(6);

        $diasSeleccionados = [
            'lunes'     => $request->input('lunes') == 1,
            'martes'    => $request->input('martes') == 1,
            'miercoles' => $request->input('miercoles') == 1,
            'jueves'    => $request->input('jueves') == 1,
            'viernes'   => $request->input('viernes') == 1,
            'sabado'    => $request->input('sabado') == 1,
            'domingo'   => $request->input('domingo') == 1,
        ];


        $generarRepetidos = collect($diasSeleccionados)->contains(true);

        $fechas = [];

        if ($generarRepetidos) {

            $carbonMap = [
                1 => 'lunes',
                2 => 'martes',
                3 => 'miercoles',
                4 => 'jueves',
                5 => 'viernes',
                6 => 'sabado',
                7 => 'domingo',
            ];

            $fecha = $fechaInicio->copy();

            while ($fecha->lte($fechaFin)) {

                $nombreDia = $carbonMap[$fecha->dayOfWeekIso];

                if ($diasSeleccionados[$nombreDia]) {
                    $fechas[] = $fecha->copy();
                }

                $fecha->addDay();
            }
        } else {
            $fechas[] = $fechaInicio;
        }

        foreach ($fechas as $f) {
            Horario::create([
                'tipo_viaje_id'    => $request->tipo_viaje_id,
                'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
                'punto_origen_id'  => $request->punto_origen_id,
                'punto_destino_id' => $request->punto_destino_id,
                'costo_pasaje'     => $request->costo_pasaje,
                'hora_embarque'    => $request->hora_embarque,
                'fecha_salida'     => $f->format('Y-m-d'),

                'lunes'     => $diasSeleccionados['lunes'],
                'martes'    => $diasSeleccionados['martes'],
                'miercoles' => $diasSeleccionados['miercoles'],
                'jueves'    => $diasSeleccionados['jueves'],
                'viernes'   => $diasSeleccionados['viernes'],
                'sabado'    => $diasSeleccionados['sabado'],
                'domingo'   => $diasSeleccionados['domingo'],
            ]);
        }

        return response()->json(['success' => true]);
    }


    public function mostrar($id)
    {
        $horario = Horario::with(['tipo_viaje', 'punto_origen', 'punto_destino'])->findOrFail($id);
        return response()->json($horario);
    }

    public function actualizar(Request $request, Horario $horario)
    {
        $request->validate([
            'tipo_viaje_id' => 'required|exists:tipos_viajes,id',
            'tipo_vehiculo_id' => 'required|exists:tipos_vehiculos,id',
            'punto_origen_id' => 'required|exists:sucursales,id',
            'punto_destino_id' => 'required|exists:sucursales,id',
            'costo_pasaje' => 'required|numeric',
            'hora_embarque' => 'required',
            'fecha_salida' => 'required|date',
        ]);

        $horario->update($request->all());

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
        return view('horarios.calendario');
    }
    public function getEventos()
    {
        $horarios = Horario::with(['punto_destino', 'tipo_viaje', 'tipo_vehiculo'])->get();
        $eventos = [];

        foreach ($horarios as $h) {
            $dias = [
                'lunes' => 1,
                'martes' => 2,
                'miercoles' => 3,
                'jueves' => 4,
                'viernes' => 5,
                'sabado' => 6,
                'domingo' => 7,
            ];

            $fechaBase = Carbon::parse($h->fecha_salida);
            $tieneRepeticion = false;

            for ($semana = 0; $semana < 4; $semana++) {
                foreach ($dias as $nombre => $numeroDia) {
                    if ($h->$nombre) {
                        $tieneRepeticion = true;

                        $fechaEvento = $fechaBase->copy()
                            ->startOfWeek()
                            ->addDays($numeroDia - 1)
                            ->addWeeks($semana);

                        $eventos[] = [
                            'title' => $h->punto_destino->nombre_comercial,
                            'start' => $fechaEvento->format('Y-m-d') . 'T' . $h->hora_embarque,
                            'extendedProps' => [
                                'id' => $h->id,
                                'tipo_viaje' => $h->tipo_viaje->descripcion,
                                'tipo_vehiculo' => $h->tipo_vehiculo->descripcion ?? '',
                                'costo' => $h->costo_pasaje,
                                'hora' => $h->hora_embarque,
                            ],
                        ];
                    }
                }
            }

            if (!$tieneRepeticion) {
                $eventos[] = [
                    'title' => $h->punto_destino->nombre_comercial,
                    'start' => $h->fecha_salida . 'T' . $h->hora_embarque,
                    'extendedProps' => [
                        'id' => $h->id,
                        'tipo_viaje' => $h->tipo_viaje->descripcion,
                        'tipo_vehiculo' => $h->tipo_vehiculo->descripcion ?? '',
                        'costo' => $h->costo_pasaje,
                        'hora' => $h->hora_embarque,
                    ],
                ];
            }
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
            $query->whereDate('fecha_salida', $request->fecha);
        }

        $horarios = $query->get();

        $horarios = $horarios->map(function ($h) {
            $h->fecha_salida_formateada = Carbon::parse($h->fecha_salida)->format('d-m-Y');
            return $h;
        });

        return response()->json(
            $query->get()->map(function ($h) {
                return [
                    'id' => $h->id,
                    'hora_embarque' => $h->hora_embarque,
                    'fecha_salida' => $h->fecha_salida->format('d-m-Y'),
                    'pasajes_count' => $h->pasajes_count,
                    'tipo_vehiculo' => $h->tipo_vehiculo,
                    'punto_origen' => $h->punto_origen,
                    'punto_destino' => $h->punto_destino,
                ];
            })
        );
    }
}

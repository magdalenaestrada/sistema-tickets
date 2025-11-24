<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Sucursal;
use App\Models\TipoVehiculo;
use App\Models\TipoViaje;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HorarioController extends Controller
{
    public function index()
    {
        $tiposViaje = TipoViaje::all();
        $tipo_vehiculos   = TipoVehiculo::all();
        $sucursales  = Sucursal::all();

        return view('horarios.index', compact('tiposViaje', 'tipo_vehiculos', 'sucursales'));
    }

    public function datatable()
    {
        $horarios = Horario::with(['tipo_viaje', 'punto_origen', 'punto_destino', 'tipo_vehiculo'])->select('horarios.*');

        return DataTables::of($horarios)
            ->addColumn('tipo_viaje', fn($h) => $h->tipo_viaje->descripcion)
            ->addColumn('origen', fn($h) => $h->punto_origen->nombre_comercial)
            ->addColumn('destino', fn($h) => $h->punto_destino->nombre_comercial)
            ->addColumn('acciones', function ($h) {
                return '
                    <button class="btn btn-secondary btn-xs ver" data-id="' . $h->id . '">
                        <i class="link-icon" data-lucide="eye"></i>
                    </button>
                    <button class="btn btn-warning btn-xs editar" data-id="' . $h->id . '">
                        <i class="link-icon" data-lucide="pen"></i>
                    </button>
                    <button class="btn btn-danger btn-xs eliminar" data-id="' . $h->id . '">
                        <i class="link-icon" data-lucide="trash-2"></i>
                    </button>
                    <button class="btn btn-primary btn-xs ver-puntos" data-id="' . $h->id . '">
    <i class="link-icon" data-lucide="map-pin-house"></i>
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
            'punto_origen_id' => 'required|exists:sucursales,id',
            'punto_destino_id' => 'required|exists:sucursales,id',
            'costo_pasaje' => 'required|numeric',
            'hora_embarque' => 'required',
            'fecha_salida' => 'required|date',
        ]);

        $horario = Horario::create($request->all());

        return response()->json(['success' => true, 'horario' => $horario]);
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

            $fechaBase = \Carbon\Carbon::parse($h->fecha_salida);
            $tieneRepeticion = false;

            // Generar eventos repetidos (si tiene días marcados)
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

            // ⚠️ Si no marcó días de repetición, mostrar solo la fecha_salida
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
}

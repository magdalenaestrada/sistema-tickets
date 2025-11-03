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
            ->addColumn('tipo_vehiculo', fn($h) => $h->tipo_vehiculo->descripcion)
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
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
            'costo_pasaje' => 'required|numeric',
            'hora_embarque' => 'required',
            'fecha_salida' => 'required|date',
        ]);

        $horario = Horario::create($request->all());

        return response()->json(['success' => true, 'horario' => $horario]);
    }

    public function mostrar($id)
    {
        $horario = Horario::with(['tipo_viaje', 'punto_origen', 'punto_destino', 'tipo_vehiculo'])->findOrFail($id);
        return response()->json($horario);
    }

    public function actualizar(Request $request, Horario $horario)
    {
        $request->validate([
            'tipo_viaje_id' => 'required|exists:tipos_viajes,id',
            'punto_origen_id' => 'required|exists:sucursales,id',
            'punto_destino_id' => 'required|exists:sucursales,id',
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
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
            $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            foreach ($dias as $dia) {
                if ($h->$dia) {
                    // FullCalendar necesita fecha y hora, usamos fecha_salida + hora_embarque
                    $eventos[] = [
                        'title' => $h->punto_destino->nombre_comercial, // Solo mostramos destino
                        'start' => $h->fecha_salida . 'T' . $h->hora_embarque,
                        'extendedProps' => [
                            'id' => $h->id,
                            'tipo_viaje' => $h->tipo_viaje->descripcion,
                            'tipo_vehiculo' => $h->tipo_vehiculo->placa,
                            'costo' => $h->costo_pasaje,
                            'hora' => $h->hora_embarque,
                            'dias' => [
                                'lunes' => $h->lunes,
                                'martes' => $h->martes,
                                'miercoles' => $h->miercoles,
                                'jueves' => $h->jueves,
                                'viernes' => $h->viernes,
                                'sabado' => $h->sabado,
                                'domingo' => $h->domingo
                            ],
                        ],
                    ];
                }
            }
        }

        return response()->json($eventos);
    }
}

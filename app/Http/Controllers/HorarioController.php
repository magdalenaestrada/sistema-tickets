<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Ruta;
use App\Models\TipoViaje;
use App\Models\TipoVehiculo;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HorarioController extends Controller
{
    public function index()
    {
        $rutas = Ruta::where("estado", "A")->select('id', 'nombre')->orderBy('nombre')->get();
        $tiposViaje = TipoViaje::select('id', 'descripcion')->orderBy('descripcion')->get();
        $tiposVehiculo = TipoVehiculo::select('id', 'descripcion')->orderBy('descripcion')->get();

        return view('horarios.index', compact('rutas', 'tiposViaje', 'tiposVehiculo'));
    }

    public function datatable()
    {
        $horarios = Horario::with([
            'ruta.puntos.sucursal',
            'tipo_vehiculo'
        ]);

        return DataTables::of($horarios)
            ->addColumn('ruta', function ($horario) {
                return $horario->ruta?->nombre ?? '-';
            })
            ->addColumn('tipo_vehiculo', function ($horario) {
                return $horario->tipo_vehiculo?->descripcion ?? '-';
            })
            ->addColumn('hora_salida_formateada', function ($horario) {
                return $horario->hora_formateada;
            })
            ->addColumn('hora_llegada_formateada', function ($horario) {
                return $horario->hora_llegada;
            })
            ->addColumn('duracion', function ($horario) {
                return $horario->duracion_total_formateada;
            })
            ->addColumn('acciones', function ($horario) {
                return '
                <button class="btn btn-light btn-xs ver" data-id="' . $horario->id . '">
                    <i class="link-icon" data-lucide="info"></i>
                </button>

                <button class="btn btn-warning btn-xs editar" data-id="' . $horario->id . '">
                    <i class="link-icon" data-lucide="pen"></i>
                </button>

                <button class="btn btn-danger btn-xs eliminar" data-id="' . $horario->id . '">
                    <i class="link-icon" data-lucide="trash"></i>
                </button>
            ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function show($id)
    {
        $horario = Horario::with([
            'ruta.puntos.sucursal',
            'ruta.tramos.origen.sucursal',
            'ruta.tramos.destino.sucursal',
            'tipo_viaje',
            'tipo_vehiculo'
        ])->findOrFail($id);

        return response()->json([
            'id' => $horario->id,
            'ruta_id' => $horario->ruta_id,
            'tipo_viaje_id' => $horario->tipo_viaje_id,
            'tipo_vehiculo_id' => $horario->tipo_vehiculo_id,
            'hora_salida' => $horario->hora_salida,
            'tipo_viaje' => $horario->tipo_viaje?->descripcion,
            'tipo_vehiculo' => $horario->tipo_vehiculo?->descripcion,
            'costo_base' => $horario->costo_base,
            'hora_llegada' => $horario->hora_llegada,
            'duracion_total' => $horario->duracion_total_formateada,
            'ruta' => [
                'id' => $horario->ruta?->id,
                'nombre' => $horario->ruta?->nombre,
                'puntos' => $horario->ruta?->puntos?->sortBy('orden')->values()->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'orden' => $p->orden,
                        'nombre' => $p->sucursal?->nombre_comercial,
                    ];
                }),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'tipo_viaje_id' => 'nullable|exists:tipos_viajes,id',
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
            'hora_salida' => 'required',
            'costo_base' => 'nullable|numeric|min:0',
        ]);

        try {
            $horario = Horario::create([
                'ruta_id' => $request->ruta_id,
                'tipo_viaje_id' => $request->tipo_viaje_id,
                'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
                'hora_salida' => $request->hora_salida,
                'costo_base' => $request->costo_base,
            ]);

            return response()->json([
                'ok' => true,
                'id' => $horario->id,
                'mensaje' => 'Horario creado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'tipo_viaje_id' => 'nullable|exists:tipos_viajes,id',
            'tipo_vehiculo_id' => 'required|exists:tipo_vehiculos,id',
            'hora_salida' => 'required',
            'costo_base' => 'nullable|numeric|min:0',
        ]);

        try {
            $horario = Horario::findOrFail($id);

            $horario->update([
                'ruta_id' => $request->ruta_id,
                'tipo_viaje_id' => $request->tipo_viaje_id,
                'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
                'hora_salida' => $request->hora_salida,
                'costo_base' => $request->costo_base,
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Horario actualizado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $horario = Horario::findOrFail($id);
            $horario->delete();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Horario eliminado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SalidaController extends Controller
{
    public function index()
    {
        return view('salidas.index');
    }

    public function datatable()
    {
        $salidas = Salida::with([
            'horario.ruta',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ]);

        return DataTables::of($salidas)
            ->addColumn('ruta', function ($salida) {
                return $salida->horario?->ruta?->nombre ?? '-';
            })
            ->addColumn('hora_salida', function ($salida) {
                return $salida->horario?->hora_formateada ?? '-';
            })
            ->addColumn('hora_llegada', function ($salida) {
                return $salida->horario?->hora_llegada ?? '-';
            })
            ->addColumn('fecha_formateada', function ($salida) {
                return $salida->fecha_formateada;
            })
            ->addColumn('estado_badge', function ($salida) {
                $clase = match ($salida->estado) {
                    'programado' => 'bg-primary',
                    'en_ruta' => 'bg-warning',
                    'finalizado' => 'bg-success',
                    'cancelado' => 'bg-danger',
                    default => 'bg-secondary',
                };

                return '<span class="badge ' . $clase . '">' . ucfirst($salida->estado) . '</span>';
            })
            ->addColumn('acciones', function ($salida) {
                return '
                    <button class="btn btn-light btn-xs ver" data-id="' . $salida->id . '">
                        <i class="link-icon" data-lucide="info"></i>
                    </button>

                    <button class="btn btn-warning btn-xs editar" data-id="' . $salida->id . '">
                        <i class="link-icon" data-lucide="pen"></i>
                    </button>

                    <button class="btn btn-danger btn-xs eliminar" data-id="' . $salida->id . '">
                        <i class="link-icon" data-lucide="trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['acciones', 'estado_badge'])
            ->make(true);
    }

    public function show($id)
    {
        $salida = Salida::with([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])->findOrFail($id);

        return response()->json([
            'id' => $salida->id,
            'horario_id' => $salida->horario_id,
            'fecha' => $salida->fecha?->format('Y-m-d'),
            'fecha_formateada' => $salida->fecha_formateada,
            'estado' => $salida->estado,
            'hora_salida' => $salida->horario?->hora_formateada,
            'hora_llegada' => $salida->horario?->hora_llegada,
            'tipo_viaje' => $salida->horario?->tipo_viaje?->descripcion,
            'tipo_vehiculo' => $salida->horario?->tipo_vehiculo?->descripcion,
            'ruta' => [
                'nombre' => $salida->horario?->ruta?->nombre,
                'puntos' => $salida->horario?->ruta?->puntos?->sortBy('orden')->values()->map(function ($p) {
                    return [
                        'orden' => $p->orden,
                        'nombre' => $p->sucursal?->nombre_comercial,
                    ];
                }),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:programado,en_ruta,finalizado,cancelado',
        ]);

        try {
            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha', $request->fecha)
                ->exists();

            if ($existe) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya existe una salida para ese horario y fecha.'
                ], 422);
            }

            $salida = Salida::create([
                'horario_id' => $request->horario_id,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'ok' => true,
                'id' => $salida->id,
                'mensaje' => 'Salida creada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generar(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'dias' => 'required|array|min:1',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);
            $dias = collect($request->dias)->map(fn($d) => (int) $d)->toArray();

            $creadas = 0;

            while ($fechaInicio->lte($fechaFin)) {
                $dayOfWeek = $fechaInicio->dayOfWeekIso; // 1 lunes ... 7 domingo

                if (in_array($dayOfWeek, $dias)) {
                    $existe = Salida::where('horario_id', $request->horario_id)
                        ->where('fecha', $fechaInicio->format('Y-m-d'))
                        ->exists();

                    if (!$existe) {
                        Salida::create([
                            'horario_id' => $request->horario_id,
                            'fecha' => $fechaInicio->format('Y-m-d'),
                            'estado' => 'programado',
                        ]);

                        $creadas++;
                    }
                }

                $fechaInicio->addDay();
            }

            return response()->json([
                'ok' => true,
                'mensaje' => "Se generaron {$creadas} salidas correctamente."
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
            'horario_id' => 'required|exists:horarios,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:programado,en_ruta,finalizado,cancelado',
        ]);

        try {
            $salida = Salida::findOrFail($id);

            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha', $request->fecha)
                ->where('id', '!=', $salida->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya existe otra salida para ese horario y fecha.'
                ], 422);
            }

            $salida->update([
                'horario_id' => $request->horario_id,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Salida actualizada correctamente'
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
            $salida = Salida::withCount('pasajes')->findOrFail($id);

            if ($salida->pasajes_count > 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se puede eliminar la salida porque ya tiene pasajes registrados.'
                ], 422);
            }

            $salida->delete();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Salida eliminada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
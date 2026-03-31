<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Salida;
use App\Models\Vehiculo;
use App\Services\PdfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SalidaController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with('tipo_vehiculo')->where('estado', 'A')->get();
        $conductores = Empleado::with('persona')->where('cargo_id', 3)->get();
        return view('salidas.index', compact('vehiculos', 'conductores'));
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
                if ($salida->estado == 'en_ruta') {
                    return '<span class="badge bg-warning">EN RUTA</span>';
                }
                if ($salida->estado == 'programado') {
                    return '<span class="badge bg-primary">PROGRAMADO</span>';
                }
                if ($salida->estado == 'finalizado') {
                    return '<span class="badge bg-success">FINALIZADO</span>';
                }
                if ($salida->estado == 'cancelado') {
                    return '<span class="badge bg-danger">CANCELADO</span>';
                }
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

    public function manifiestoPasajeros(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'pasajes.persona.tipoDocumento',
            'pasajes.origen',
            'pasajes.destino',
            'pasajes.venta',
        ]);

        $empresa = \App\Models\Empresa::first();

        $puntos = $salida->horario->ruta->puntos->sortBy('orden')->values();
        $origenNombre = $puntos->first()?->sucursal?->distrito?->nombre ?? '-';
        $destinoNombre = $puntos->last()?->sucursal?->distrito?->nombre ?? '-';

        $pasajes = $salida->pasajes
            ->whereIn('estado', ['V', 'F'])
            ->sortBy('asiento_numero')
            ->values();

        $capacidad = $salida->horario->tipo_vehiculo->capacidad
            ?? $salida->horario->tipo_vehiculo->asientos
            ?? 46;

        $html = view('salidas.manifiestos.pasajeros', compact(
            'salida',
            'empresa',
            'pasajes',
            'origenNombre',
            'destinoNombre',
            'capacidad'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_pasajeros_{$salida->id}.pdf",
            'P'
        );
    }

    public function manifiestoEncomiendas(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
        ]);

        $encomiendas = collect();

        $html = view('salidas.manifiestos.encomiendas', compact(
            'salida',
            'encomiendas'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_encomiendas_{$salida->id}.pdf",
            'P'
        );
    }

    public function manifiestoConductores(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
        ]);

        $puntos = $salida->horario->ruta->puntos->sortBy('orden')->values();
        $origenNombre = $puntos->first()?->sucursal?->nombre_comercial ?? '-';
        $destinoNombre = $puntos->last()?->sucursal?->nombre_comercial ?? '-';

        $html = view('salidas.manifiestos.conductores', compact(
            'salida',
            'origenNombre',
            'destinoNombre'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_conductores_{$salida->id}.pdf",
            'P'
        );
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
            'vehiculo_id' => $salida->vehiculo_id,
            'conductor_principal_id' => $salida->conductor_principal_id,
            'conductor_secundario_id' => $salida->conductor_secundario_id,
            'fecha_salida' => $salida->fecha_salida?->format('Y-m-d'),
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
            'fecha_salida' => 'required|date',
            'estado' => 'required|in:programado,en_ruta,finalizado,cancelado',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'conductor_principal_id' => 'nullable|exists:personas,id',
            'conductor_secundario_id' => 'nullable|exists:personas,id',
        ]);

        try {
            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha_salida', $request->fecha_salida)
                ->exists();

            if ($existe) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya existe una salida para ese horario y fecha.'
                ], 422);
            }

            if ($request->estado === 'en_ruta') {
                if (!$request->vehiculo_id || !$request->conductor_principal_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para poner la salida en ruta debes asignar vehículo y conductor principal.'
                    ], 422);
                }

                if ($request->conductor_principal_id == $request->conductor_secundario_id && $request->conductor_secundario_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes repetir el mismo conductor en ambos campos.'
                    ], 422);
                }
            }

            $salida = Salida::create([
                'horario_id' => $request->horario_id,
                'fecha_salida' => $request->fecha_salida,
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
                $dayOfWeek = $fechaInicio->dayOfWeekIso;

                if (in_array($dayOfWeek, $dias)) {
                    $existe = Salida::where('horario_id', $request->horario_id)
                        ->where('fecha_salida', $fechaInicio->format('Y-m-d'))
                        ->exists();

                    if (!$existe) {
                        Salida::create([
                            'horario_id' => $request->horario_id,
                            'fecha_salida' => $fechaInicio->format('Y-m-d'),
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
            'fecha_salida' => 'required|date',
            'estado' => 'required|in:programado,en_ruta,finalizado,cancelado',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'conductor_principal_id' => 'nullable|exists:personas,id',
            'conductor_secundario_id' => 'nullable|exists:personas,id',
        ]);

        try {
            $salida = Salida::findOrFail($id);

            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha_salida', $request->fecha_salida)
                ->where('id', '!=', $salida->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya existe otra salida para ese horario y fecha.'
                ], 422);
            }


            if ($request->estado === 'en_ruta') {
                if (!$request->vehiculo_id || !$request->conductor_principal_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para poner la salida en ruta debes asignar vehículo y conductor principal.'
                    ], 422);
                }

                if ($request->conductor_principal_id == $request->conductor_secundario_id && $request->conductor_secundario_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes repetir el mismo conductor en ambos campos.'
                    ], 422);
                }
            }

            $salida->update([
                'horario_id' => $request->horario_id,
                'fecha_salida' => $request->fecha_salida,
                'estado' => $request->estado,
                'vehiculo_id' => $request->vehiculo_id,
                'conductor_principal_id' => $request->conductor_principal_id,
                'conductor_secundario_id' => $request->conductor_secundario_id,
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

<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Salida;
use App\Models\Vehiculo;
use App\Services\PdfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalidaController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with('tipo_vehiculo')->where('estado', 'A')->get();
        $conductores = Empleado::with('persona')->where('cargo_id', 3)->get();
        $horariosSalida = Horario::with(['ruta', 'tipo_vehiculo'])
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'tipo_vehiculo_id' => $h->tipo_vehiculo_id,
                    'nombre' => ($h->ruta?->nombre ?? 'Sin ruta') .
                        ' - ' .
                        ($h->hora_formateada ?? '') .
                        ' - ' .
                        ($h->tipo_vehiculo?->descripcion ?? ''),
                ];
            });
        return view('salidas.index', compact('vehiculos', 'conductores', 'horariosSalida'));
    }

    public function index_vendedor()
    {
        $vehiculos = Vehiculo::with('tipo_vehiculo')->where('estado', 'A')->get();
        $conductores = Empleado::with('persona')->where('cargo_id', 3)->get();
        $horariosSalida = Horario::with(['ruta', 'tipo_vehiculo'])
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'tipo_vehiculo_id' => $h->tipo_vehiculo_id,
                    'nombre' => ($h->ruta?->nombre ?? 'Sin ruta') .
                        ' - ' .
                        ($h->hora_formateada ?? '') .
                        ' - ' .
                        ($h->tipo_vehiculo?->descripcion ?? ''),
                ];
            });
        return view('salidas.index-vendedor', compact('vehiculos', 'conductores', 'horariosSalida'));
    }


    public function datatable()
    {
        $salidas = Salida::with([
            'horario.ruta',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])
            ->whereDate('fecha_salida', '>=', now()->toDateString())
            ->orderBy('fecha_salida', 'asc')
            ->get();

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
                if ($salida->estado == 'reprogramado') {
                    return '<span class="badge bg-info">REPROGRAMADO</span>';
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

        $empresa = Empresa::first();

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

    public function destroyBulk(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'IDs inválidos'], 422);
        }

        Salida::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Eliminadas correctamente']);
    }

    public function manifiestoEncomiendas(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'encomiendas',
        ]);

        $encomiendas = $salida->encomiendas()
            ->wherePivot('estado', 'A')
            ->get();

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
            'horario.ruta.puntos.pueblito',
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

            'fecha_cambio_estado' => $salida->fecha_cambio_estado?->format('Y-m-d'),
            'hora_cambio_estado' => $salida->hora_cambio_estado?->format('H:i'),
            'motivo_cambio_estado' => $salida->motivo_cambio_estado,

            'hora_salida' => $salida->horario?->hora_formateada,
            'hora_llegada' => $salida->horario?->hora_llegada,
            'tipo_viaje' => $salida->horario?->tipo_viaje?->descripcion,
            'tipo_vehiculo' => $salida->horario?->tipo_vehiculo?->descripcion,
            'ruta' => [
                'nombre' => $salida->horario?->ruta?->nombre,
                'puntos' => $salida->horario?->ruta?->puntos
                    ?->sortBy('orden')
                    ->values()
                    ->map(function ($p) use ($salida) {
                        return [
                            'orden' => $p->orden,
                            'nombre' => $p->pueblito?->descripcion,
                            'hora' => $salida->horario->horaEnPunto($p->id),
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
            'estado' => 'required|in:programado,reprogramado,en_ruta,finalizado,cancelado',
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
                'hora_salida' => $request->hora_salida,
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
        $user = Auth::id();
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha_cambio_estado' => 'nullable|date|after_or_equal:today',
            'fecha_salida' => 'required|date',
            'estado' => 'required|in:programado,reprogramado,en_ruta,finalizado,cancelado',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'conductor_principal_id' => 'nullable|exists:personas,id',
            'conductor_secundario_id' => 'nullable|exists:personas,id',
            'hora_cambio_estado' => 'nullable|date_format:H:i',
            'motivo_cambio_estado' => 'nullable|string|max:500',
            'aplicar_a_pasajes' => 'nullable|boolean',
        ]);

        if (in_array($request->estado, ['reprogramado', 'cancelado'])) {
            if (!$request->fecha_cambio_estado || !$request->hora_cambio_estado || !$request->motivo_cambio_estado) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Debe registrar fecha, hora y motivo.'
                ], 422);
            }
        }

        try {
            $salida = Salida::with('pasajes')->findOrFail($id);

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
                'usuario_cambio_estado_id' => $user,
                'vehiculo_id' => $request->vehiculo_id,
                'hora_salida' => $request->hora_salida,
                'conductor_principal_id' => $request->conductor_principal_id,
                'conductor_secundario_id' => $request->conductor_secundario_id,
                'fecha_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->fecha_cambio_estado : null,
                'hora_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->hora_cambio_estado : null,
                'motivo_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->motivo_cambio_estado : null,
            ]);

            if (in_array($request->estado, ['reprogramado', 'cancelado'])) {
                foreach ($salida->pasajes as $pasaje) {
                    $pasaje->update([
                        'estado' => $request->estado,
                        'fecha_cambio_estado' => $request->fecha_cambio_estado,
                        'hora_cambio_estado' => $request->hora_cambio_estado,
                        'motivo_cambio_estado' => $request->motivo_cambio_estado,
                    ]);
                }
            }
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

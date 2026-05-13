<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Ruta;
use App\Models\RutaPunto;
use App\Models\RutaTramo;
use App\Models\Sucursal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class RutaController extends Controller
{

    public function index()
    {
        $rutas = Ruta::with('puntos.distrito', 'puntos.pueblito', 'puntos.sucursal')->get();
        return view('rutas.index', compact('rutas'));
    }

    public function datatable()
    {
        $rutas = Ruta::with('puntos.distrito', 'puntos.pueblito', 'puntos.sucursal');

        return DataTables::of($rutas)

            ->addColumn('puntos', function ($ruta) {
                return $ruta->puntos->count();
            })
            ->addColumn('estado', function ($ruta) {
                if ($ruta->estado == "A") {
                    return '<span class="badge bagde-pill bg-success">ACTIVO </span>';
                } else {
                    return '<span class="badge bagde-pill bg-danger">INACTIVO </span>';
                }
            })
            ->addColumn('acciones', function ($ruta) {
                if ($ruta->estado == "A") {
                    return '

                 <button class="btn btn-light btn-xs ver" data-id="' . $ruta->id . '">
                    <i class="link-icon" data-lucide="info"></i>
                </button>

                <button class="btn btn-warning btn-xs editar" data-id="' . $ruta->id . '">
                    <i class="link-icon" data-lucide="pen"></i>
                </button>

                <button class="btn btn-danger btn-xs desactivar" data-id="' . $ruta->id . '">
                    <i class="link-icon" data-lucide="eye-closed"></i>
                </button>';
                } else {
                    return '
                <button class="btn btn-success btn-xs activar" data-id="' . $ruta->id . '">
                    <i class="link-icon" data-lucide="eye"></i>
                </button>';
                }
            })
            ->rawColumns(['acciones', 'estado'])
            ->make(true);
    }

    public function create()
    {
        $distritos = Distrito::all();
        $sucursales = Sucursal::all();
        return view('rutas.create', compact('sucursales', 'distritos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'puntos.*.distrito_id' => 'required|exists:distritos,id',
            'puntos.*.pueblito_id' => 'required|exists:pueblitos,id',
            'puntos.*.sucursal_id' => 'nullable|exists:sucursales,id',
            'puntos' => 'required|array|min:2',
        ]);

        DB::beginTransaction();

        try {

            $ruta = Ruta::create([
                'nombre' => $request->nombre
            ]);

            $puntosData = [];

            foreach ($request->puntos as $index => $punto) {
                $puntosData[] = [
                    'ruta_id' => $ruta->id,
                    'distrito_id' => $punto['distrito_id'],
                    'pueblito_id' => $punto['pueblito_id'],
                    'sucursal_id' => $punto['sucursal_id'] ?? null,
                    'orden' => $index + 1
                ];
            }

            RutaPunto::insert($puntosData);

            $puntos = RutaPunto::where('ruta_id', $ruta->id)
                ->orderBy('orden')
                ->get();

            foreach ($puntos as $i => $punto) {
                if (!isset($puntos[$i + 1])) continue;

                RutaTramo::create([
                    'ruta_id' => $ruta->id,
                    'punto_origen_id' => $punto->id,
                    'punto_destino_id' => $puntos[$i + 1]->id,
                    'duracion_minutos' => $request->duracion[$i] ?? 0,
                    'costo_tramo' => $request->costo[$i] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('rutas.index')
                ->with('success', 'Ruta creada correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        $ruta = Ruta::with(
            'puntos.distrito',
            'puntos.pueblito',
            'puntos.sucursal'
        )->findOrFail($id);
        $sucursales = Sucursal::where("estado", "A")->get();
        $distritos = Distrito::all();

        return view('rutas.edit', compact('ruta', 'sucursales', 'distritos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'puntos' => 'required|array|min:2',
            'puntos.*.distrito_id' => 'required|exists:distritos,id',
            'puntos.*.pueblito_id' => 'required|exists:pueblitos,id',
            'puntos.*.sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $ruta = Ruta::findOrFail($id);

        $ruta->update([
            'nombre' => $request->nombre
        ]);

        RutaTramo::where('ruta_id', $ruta->id)->delete();

        RutaPunto::where('ruta_id', $ruta->id)->delete();

        $nuevosPuntos = [];

        foreach ($request->puntos as $index => $punto) {

            $nuevoPunto = RutaPunto::create([
                'ruta_id' => $ruta->id,
                'orden' => $index + 1,
                'distrito_id' => $punto['distrito_id'],
                'pueblito_id' => $punto['pueblito_id'],
                'sucursal_id' => $punto['sucursal_id'] ?? null,
            ]);

            $nuevosPuntos[] = $nuevoPunto;
        }

        foreach ($request->duracion as $i => $duracion) {

            $origen = $nuevosPuntos[$i] ?? null;
            $destino = $nuevosPuntos[$i + 1] ?? null;

            if (!$origen || !$destino) {
                continue;
            }

            RutaTramo::create([
                'ruta_id' => $ruta->id,
                'punto_origen_id' => $origen->id,
                'punto_destino_id' => $destino->id,
                'duracion_minutos' => $duracion,
                'costo_tramo' => $request->costo[$i] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }
    public function destroy($id)
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        return back()->with('success', 'Ruta eliminada');
    }

    public function show($id)
    {
        $ruta = Ruta::with([
            'puntos.distrito',
            'puntos.pueblito',
            'puntos.sucursal',
            'tramos.origen.sucursal',
            'tramos.destino.sucursal',
            'tramos.origen.distrito',
            'tramos.destino.distrito'
        ])->findOrFail($id);

        return response()->json([
            'id' => $ruta->id,

            'nombre' => $ruta->nombre,

            'puntos' => $ruta->puntos
                ->sortBy('orden')
                ->values()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'sucursal_id' => $p->sucursal_id,
                        'distrito_id' => $p->distrito_id,
                        'pueblito_id' => $p->pueblito_id,
                        'distrito' => $p->distrito->nombre,
                        'pueblito' => $p->pueblito?->nombre,
                        'sucursal' => $p->sucursal?->nombre_comercial,
                    ];
                }),

            'tramos' => $ruta->tramos
                ->sortBy('punto_origen_id')
                ->values()
                ->map(function ($t) {
                    return [
                        'origen_id' => $t->punto_origen_id,
                        'destino_id' => $t->punto_destino_id,
                        'duracion' => $t->duracion_minutos,
                        'costo' => $t->costo_tramo,
                    ];
                }),
        ]);
    }

    public function guardarTramos(Request $request, $rutaId)
    {
        $ruta = Ruta::findOrFail($rutaId);

        DB::beginTransaction();

        try {
            $ruta->tramos()->delete();

            foreach ($request->tramos as $tramo) {
                RutaTramo::create([
                    'ruta_id' => $ruta->id,
                    'punto_origen_id' => $tramo['origen'],
                    'punto_destino_id' => $tramo['destino'],
                    'duracion_minutos' => $tramo['duracion'],
                    'costo_tramo' => $tramo['costo'],
                ]);
            }

            DB::commit();

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function desactivar($id)
    {
        $ruta = Ruta::findOrFail($id);

        $ruta->update([
            "estado" => "I"
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Ruta desactivada'
        ]);
    }

    public function activar($id)
    {
        $ruta = Ruta::findOrFail($id);

        $ruta->update([
            "estado" => "A"
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Ruta activada'
        ]);
    }
}

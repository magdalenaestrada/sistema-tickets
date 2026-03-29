<?php

namespace App\Http\Controllers;

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
        $rutas = Ruta::with('puntos.sucursal')->get();
        return view('rutas.index', compact('rutas'));
    }

    public function datatable()
    {
        $rutas = Ruta::with('puntos.sucursal');

        return DataTables::of($rutas)

            ->addColumn('puntos', function ($ruta) {
                return $ruta->puntos->count();
            })

            ->addColumn('acciones', function ($ruta) {
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
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create()
    {
        $sucursales = Sucursal::all();
        return view('rutas.create', compact('sucursales'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'puntos' => 'required|array|min:2'
        ]);

        DB::beginTransaction();

        try {

            $ruta = Ruta::create([
                'nombre' => $request->nombre
            ]);

            $puntosData = [];

            foreach ($request->puntos as $index => $sucursalId) {
                $puntosData[] = [
                    'ruta_id' => $ruta->id,
                    'sucursal_id' => $sucursalId,
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
        $ruta = Ruta::with('puntos')->findOrFail($id);
        $sucursales = Sucursal::all();

        return view('rutas.edit', compact('ruta', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        
        $ruta = Ruta::findOrFail($id);

        DB::beginTransaction();

        try {

            $ruta->update([
                'nombre' => $request->nombre
            ]);
            $ruta->tramos()->delete();

            $ruta->puntos()->delete();

            foreach ($request->puntos as $index => $sucursalId) {
                RutaPunto::create([
                    'ruta_id' => $ruta->id,
                    'sucursal_id' => $sucursalId,
                    'orden' => $index + 1
                ]);
            }

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

            return response()->json([
                'ok' => true,
                'mensaje' => 'Ruta actualizada'
            ]);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
            'puntos.sucursal',
            'tramos.origen.sucursal',
            'tramos.destino.sucursal'
        ])->findOrFail($id);

        return response()->json([
            'id' => $ruta->id,
            'nombre' => $ruta->nombre,

            'puntos' => $ruta->puntos->sortBy('orden')->values()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sucursal_id' => $p->sucursal_id,
                    'nombre' => $p->sucursal->nombre_comercial
                ];
            }),

            'tramos' => $ruta->tramos->map(function ($t) {
                return [
                    'origen_id' => $t->punto_origen_id,
                    'destino_id' => $t->punto_destino_id,
                    'duracion' => $t->duracion_minutos,
                    'costo' => $t->costo_tramo
                ];
            })
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CargoController extends Controller
{
    public function index()
    {
        return view('cargos.index');
    }

    public function datatable(Request $request)
    {
        $cargos = Cargo::select(['id', 'descripcion']);

        return DataTables::of($cargos)
            ->addColumn('acciones', function ($cargo) {
                return '
                    <button class="btn btn-secondary btn-xs ver" data-id="' . $cargo->id . '">
                        <i class="link-icon" data-lucide="info"></i> 
                    </button>
                    <button class="btn btn-warning btn-xs editar" data-id="' . $cargo->id . '">
                        <i class="link-icon" data-lucide="pen"></i> 
                    </button>
                     <button class="btn btn-danger btn-xs eliminar" data-id="' . $cargo->id . '">
            <i class="link-icon" data-lucide="trash-2"></i> 
        </button>

                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $cargo = Cargo::create([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true, 'cargo' => $cargo]);
    }

    public function actualizar(Request $request, Cargo $cargo)
    {
        $cargo->update([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $cargo = Cargo::findOrFail($id);
        return response()->json($cargo);
    }

    public function eliminar(Cargo $cargo)
    {
        try {
            if ($cargo->empleados()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el área porque tiene empleados asociados.'
                ]);
            }

            $cargo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cargo eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}

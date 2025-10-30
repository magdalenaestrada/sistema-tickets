<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AreaController extends Controller
{
    public function index()
    {
        return view('areas.index');
    }

    public function datatable(Request $request)
    {
        $areas = Area::select(['id', 'descripcion']);

        return DataTables::of($areas)
            ->addColumn('acciones', function ($area) {
                return '
                    <button class="btn btn-secondary btn-xs ver" data-id="' . $area->id . '">
                        <i class="link-icon" data-lucide="eye"></i> 
                    </button>
                    <button class="btn btn-warning btn-xs editar" data-id="' . $area->id . '">
                        <i class="link-icon" data-lucide="pen"></i> 
                    </button>
                     <button class="btn btn-danger btn-xs eliminar" data-id="' . $area->id . '">
            <i class="link-icon" data-lucide="trash-2"></i> 
        </button>

                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $area = Area::create([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true, 'area' => $area]);
    }

    public function actualizar(Request $request, Area $area)
    {
        $area->update([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $area = Area::findOrFail($id);
        return response()->json($area);
    }

    public function eliminar(Area $area)
    {
        try {
            if ($area->empleados()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el área porque tiene empleados asociados.'
                ]);
            }

            $area->delete();

            return response()->json([
                'success' => true,
                'message' => 'Área eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}

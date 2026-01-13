<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CargoController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('cargos.index', compact('roles'));
    }

    public function datatable(Request $request)
    {
        $cargos = Cargo::select(['id', 'descripcion', 'rol_id'])->with('rol');

        return DataTables::of($cargos)
            ->addColumn('rol', function ($cargo) {
                return $cargo->rol->name ?? '-';
            })
            ->addColumn('acciones', function ($cargo) {
                return '
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
        $data = $request->validate([
            'descripcion' => 'required|string|max:255|unique:cargos,descripcion',
            'rol_id' => 'required|exists:roles,id',
        ]);

        $data['descripcion'] = Str::ucfirst(Str::lower($data['descripcion']));

        $cargo = Cargo::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cargo registrado correctamente',
            'cargo' => $cargo
        ]);
    }

    public function actualizar(Request $request, Cargo $cargo)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255|unique:cargos,descripcion,' . $cargo->id,
            'rol_id' => 'required|exists:roles,id',
        ]);

        $data['descripcion'] = Str::ucfirst(Str::lower($data['descripcion']));

        $cargo->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cargo actualizado correctamente'
        ]);
    }




    public function mostrar($id)
    {
        $cargo = Cargo::findOrFail($id);
        return response()->json($cargo);
    }

    public function eliminar(Cargo $cargo)
    {
        try {
            if ($cargo->empleados()->withTrashed()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cargo porque está asignado a uno o más empleados.'
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

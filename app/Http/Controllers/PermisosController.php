<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermisosController extends Controller
{
    public function index()
    {
        return view('permisos.index');
    }

    public function datatable(Request $request)
    {
        $permisos = Permission::select(['id', 'name']);

        return DataTables::of($permisos)
            ->addColumn('acciones', function ($permiso) {
                return '
                     <button class="btn btn-danger btn-xs eliminar" data-id="' . $permiso->id . '">
            <i class="link-icon" data-lucide="trash-2"></i> 
        </button>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $permiso = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true, 'permiso' => $permiso]);
    }

    public function actualizar(Request $request, Permission $permiso)
    {
        $permiso->update([
            'name' => $request->name,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $permiso = Permission::findOrFail($id);
        return response()->json($permiso);
    }

    public function eliminar(Permission $permiso)
    {
        try {
            if ($permiso->users()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el permiso'
                ]);
            }

            $permiso->permissions()->detach();

            $permiso->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RolesController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }

    public function datatable(Request $request)
    {
        $roles = Role::select(['id', 'name']);

        return DataTables::of($roles)
            ->addColumn('acciones', function ($rol) {
                return '
                  <button class="btn btn-info btn-xs asignar-permisos" data-id="' . $rol->id . '">
                    <i class="link-icon" data-lucide="user-star"></i> 
                </button>
                     <button class="btn btn-danger btn-xs eliminar" data-id="' . $rol->id . '">
            <i class="link-icon" data-lucide="trash-2"></i> 
        </button>
        
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function permisos(Role $rol)
    {
        $permisos = Permission::all();
        $rolPermisosIds = $rol->permissions()->pluck('id')->toArray();
        return view('roles.permisos', compact('rol', 'permisos', 'rolPermisosIds'));
    }


    public function guardarPermisos(Request $request)
    {
        $rol = Role::findOrFail($request->rol_id);

        $permisosIds = array_map('intval', $request->permisos ?? []);

        $rol->syncPermissions($permisosIds);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permisos actualizados correctamente.'
        ]);
    }


    public function guardar(Request $request)
    {
        $rol = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json(['success' => true, 'rol' => $rol]);
    }

    public function actualizar(Request $request, Role $rol)
    {
        $rol->update([
            'name' => $request->name,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $rol = Role::findOrFail($id);
        return response()->json($rol);
    }

    public function eliminar(Role $rol)
    {
        try {
            if ($rol->users()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque está asignado a uno o más usuarios.'
                ]);
            }

            $rol->permissions()->detach();

            $rol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}

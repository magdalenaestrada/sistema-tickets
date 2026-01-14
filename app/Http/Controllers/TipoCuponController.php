<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TipoCupon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TipoCuponController extends Controller
{
    public function index()
    {
        return view('tipo_cupon.index');
    }

    public function datatable()
    {
        $tipo_cupones = TipoCupon::select(['id', 'descripcion', 'estado']);

        return DataTables::of($tipo_cupones)
            ->addColumn('estado', function ($t) {
                return $t->estado === 'A'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-danger">Inactivo</span>';
            })
            ->addColumn('acciones', function ($t) {

                $btnEstado = $t->estado === 'A'
                    ? '<button class="btn btn-danger btn-xs desactivar" data-id="' . $t->id . '">
                        <i class="link-icon" data-lucide="eye-closed"></i>
                   </button>'
                    : '<button class="btn btn-success btn-xs activar" data-id="' . $t->id . '">
                        <i class="link-icon" data-lucide="eye"></i>
                   </button>';

                return $btnEstado . '
                <button class="btn btn-warning btn-xs editar" data-id="' . $t->id . '">
                    <i class="link-icon" data-lucide="pencil"></i>
                </button>
            ';
            })
            ->rawColumns(['acciones', 'estado'])
            ->make(true);
    }



    public function guardar(Request $request)
    {
        $tipo_cupon = TipoCupon::create([
            'descripcion' => $request->descripcion,
        ]);
        return response()->json(['success' => true, 'tipo_cupon' => $tipo_cupon]);
    }

    public function actualizar(Request $request, TipoCupon $tipo_cupon)
    {
        $tipo_cupon->update([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $tipo_cupon = TipoCupon::findOrFail($id);
        return response()->json($tipo_cupon);
    }

    public function activar(TipoCupon $tipo_cupon)
    {
        $tipo_cupon->update(['estado' => "A"]);
        $tipo_cupon->descuentos()->update(['activo' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de cupón activado correctamente'
        ]);
    }

    public function desactivar(TipoCupon $tipo_cupon)
    {
        $tipo_cupon->update(['estado' => "I"]);

        $tipo_cupon->descuentos()->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de cupón y sus descuentos fueron desactivados'
        ]);
    }

    public function eliminar(TipoCupon $tipo_cupon)
    {
        try {
            if ($tipo_cupon->users()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo_cupon'
                ]);
            }

            $tipo_cupon->permissions()->detach();

            $tipo_cupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de cupón eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}

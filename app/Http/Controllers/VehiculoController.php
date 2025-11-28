<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Str;
use Yajra\DataTables\Facades\DataTables;

class VehiculoController extends Controller
{
    public function index()
    {
        return view('vehiculos.index');
    }

    public function datatable(Request $request)
    {
        $vehiculos = Vehiculo::with('tipo_vehiculo')->select(['id', 'tipo_vehiculo_id', 'numero_placa', 'cantidad_conductores']);

        return DataTables::of($vehiculos)
            ->addColumn('tipo_vehiculo', function ($vehiculo) {
                return $vehiculo->tipo_vehiculo ? $vehiculo->tipo_vehiculo->descripcion : '';
            })
            ->addColumn('acciones', function ($vehiculo) {
                return '
            <button class="btn btn-secondary btn-xs ver" data-id="' . $vehiculo->id . '">
                <i class="link-icon" data-lucide="eye"></i> 
            </button>
            <button class="btn btn-warning btn-xs editar" data-id="' . $vehiculo->id . '">
                <i class="link-icon" data-lucide="pen"></i> 
            </button>
            <button class="btn btn-danger btn-xs eliminar" data-id="' . $vehiculo->id . '">
                <i class="link-icon" data-lucide="trash-2"></i> 
            </button>
        ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $hoy = Carbon::now("America/Lima")->format("Y-m-d");
        $vehiculo = Vehiculo::create([
            "tipo_vehiculo_id" => $request->tipo_vehiculo_id,
            "numero_placa" => Str::upper($request->numero_placa),
            "cantidad_conductores" => $request->cantidad_conductores,
            "fecha_creacion" => $hoy,
        ]);

        return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
    }

    public function actualizar(Request $request, Vehiculo $vehiculo)
    {
        $vehiculo->update([
            "tipo_vehiculo_id" => $request->tipo_vehiculo_id,
            "numero_placa" => $request->numero_placa,
            "cantidad_conductores" => $request->cantidad_conductores,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return response()->json($vehiculo);
    }

    /*public function eliminar(Vehiculo $vehiculo)
    {
        try {
            if ($vehiculo->empleados()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el área porque tiene empleados asociados.'
                ]);
            }

            $vehiculo->delete();

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
    }*/
}

<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
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
        $vehiculos = Vehiculo::with('tipo_vehiculo')->select(['id', 'tipo_vehiculo_id', 'numero_placa', 'estado']);

        return DataTables::of($vehiculos)
            ->addColumn('tipo_vehiculo', function ($vehiculo) {
                return $vehiculo->tipo_vehiculo ? $vehiculo->tipo_vehiculo->descripcion : '';
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
            "fecha_creacion" => $hoy,
        ]);

        return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
    }

    public function actualizar(Request $request, Vehiculo $vehiculo)
    {
        $vehiculo->update([
            "tipo_vehiculo_id" => $request->tipo_vehiculo_id,
            "numero_placa" => $request->numero_placa,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return response()->json($vehiculo);
    }

    public function filtrar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|integer'
        ]);

        $search = $request->search;

        $vehiculos = Vehiculo::where('tipo_vehiculo_id', $request->tipo)
            ->when($search, function ($q) use ($search) {
                $q->where('numero_placa', 'like', "%$search%");
            })
            ->select('id', 'numero_placa')
            ->take(20)
            ->get();

        return response()->json($vehiculos);
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

    public function mantenimiento($vehiculo, Request $request)
    {
        Vehiculo::where('id', $vehiculo)->update([
            'estado' => 'M'
        ]);

        VehiculoMantenimiento::create([
            'vehiculo_id' => $vehiculo,
            'fecha_inicio' => $request->fecha_inicio,
            'hora_inicio' => $request->hora_inicio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo enviado a mantenimiento'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RazonMantenimiento;
use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
use Carbon\Carbon;
use Database\Seeders\RazonesMantenimientoSeeder;
use Illuminate\Http\Request;
use Str;
use Yajra\DataTables\Facades\DataTables;

class VehiculoController extends Controller
{
    public function index()
    {
        $razones = RazonMantenimiento::all();
        $hoy = Carbon::now("America/Lima")->format("Y-m-d");
        return view('vehiculos.index', compact("razones", "hoy"));
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

    public function mantenimiento(Request $request, Vehiculo $vehiculo)
    {
        if ($vehiculo->estado === 'A') {
            $vehiculo->update(['estado' => 'M']);

            VehiculoMantenimiento::create([
                'vehiculo_id' => $vehiculo->id,
                'fecha_inicio' => $request->fecha_inicio,
                'descripcion' => $request->descripcion,
                'razon_id' => $request->razon_id,
                'hora_inicio' => $request->hora_inicio,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehículo enviado a mantenimiento'
            ]);
        }

        if ($vehiculo->estado === 'M') {
            $mantenimiento = VehiculoMantenimiento::where('vehiculo_id', $vehiculo->id)
                ->whereNull('fecha_fin')
                ->latest()
                ->first();

            if (!$mantenimiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró mantenimiento activo'
                ], 422);
            }

            $mantenimiento->update([
                'fecha_fin' => $request->fecha_fin,
                'hora_fin' => $request->hora_fin,
            ]);

            $vehiculo->update(['estado' => 'A']);

            return response()->json([
                'success' => true,
                'message' => 'Vehículo habilitado nuevamente'
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RazonMantenimiento;
use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
use Carbon\Carbon;
use Database\Seeders\RazonesMantenimientoSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function datatable()
    {
        $vehiculos = Vehiculo::with('tipo_vehiculo')
            ->select(['id', 'tipo_vehiculo_id', 'numero_placa', 'estado', 'marca', 'habilitacion_vehicular']);

        return DataTables::of($vehiculos)
            ->addIndexColumn()
            ->addColumn('tipo_vehiculo', function ($vehiculo) {
                return $vehiculo->tipo_vehiculo
                    ? $vehiculo->tipo_vehiculo->descripcion
                    : '';
            })
            ->addColumn('habilitacion_vehicular', function ($vehiculo) {
                return $vehiculo->habilitacion_vehicular ?? '-';
            })

            ->addColumn('marca', function ($vehiculo) {
                return $vehiculo->marca ?? '-';
            })

            ->addColumn('estado_badge', function ($vehiculo) {
                if ($vehiculo->estado === "A") {
                    return '<span class="badge rounded-pill bg-success">DISPONIBLE</span>';
                }

                if ($vehiculo->estado === "M") {
                    return '<span class="badge rounded-pill bg-danger">MANTENIMIENTO</span>';
                }

                if ($vehiculo->estado === "V") {
                    return '<span class="badge rounded-pill bg-primary">ASIGNADO</span>';
                }

                return '<span class="badge rounded-pill bg-secondary">' . $vehiculo->estado . '</span>';
            })

            ->addColumn('acciones', function ($vehiculo) {

                $btnMantenimiento = '';
                $btnEditar = '';
                $btnEliminar = '';

                if ($vehiculo->estado !== "V") {


                    $color = 'btn-secondary';

                    if ($vehiculo->estado === 'M') {
                        $color = 'btn-success';
                    } elseif ($vehiculo->estado === 'A') {
                        $color = 'btn-primary';
                    }

                    $btnMantenimiento = '
    <button class="btn btn-xs ' . $color . ' mantenimiento"
        data-id="' . $vehiculo->id . '"
        data-estado="' . $vehiculo->estado . '">
        <i data-lucide="wrench"></i>
    </button>
';
                    if ($vehiculo->estado !== "M") {

                        $btnEditar = '
        <button class="btn btn-xs btn-warning editar"
            data-id="' . $vehiculo->id . '">
            <i data-lucide="edit"></i>
        </button>
    ';

                        $btnEliminar = '
        <button class="btn btn-xs btn-danger eliminar"
            data-id="' . $vehiculo->id . '">
            <i data-lucide="trash-2"></i>
        </button>
    ';
                    }
                }
                return $btnMantenimiento . $btnEditar . $btnEliminar;
            })

            ->rawColumns(['estado_badge', 'acciones'])
            ->make(true);
    }


    public function guardar(Request $request)
    {
        $request->validate([
            'tipo_vehiculo_id' => 'required',
            'numero_placa' => 'required|unique:vehiculos,numero_placa,NULL,id,deleted_at,NULL',
            'habilitacion_vehicular' => 'nullable|unique:vehiculos,habilitacion_vehicular,NULL,id,deleted_at,NULL',
        ], [
            'numero_placa.unique' => 'La placa ya está registrada',
            'habilitacion_vehicular.unique' => 'La habilitación vehicular ya está registrada'
        ]);

        $hoy = Carbon::now("America/Lima")->format("Y-m-d");

        $vehiculo = Vehiculo::create([
            "tipo_vehiculo_id" => $request->tipo_vehiculo_id,
            "numero_placa" => Str::upper($request->numero_placa),
            "fecha_creacion" => $hoy,
            "marca" => $request->marca,
            "habilitacion_vehicular" => $request->habilitacion_vehicular,
        ]);

        return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
    }
    public function actualizar(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'tipo_vehiculo_id' => 'required',

            'numero_placa' => [
                'required',
                Rule::unique('vehiculos', 'numero_placa')
                    ->ignore($vehiculo->id)
                    ->whereNull('deleted_at'),
            ],

            'habilitacion_vehicular' => [
                'nullable',
                Rule::unique('vehiculos', 'habilitacion_vehicular')
                    ->ignore($vehiculo->id)
                    ->whereNull('deleted_at'),
            ]

        ], [
            'numero_placa.unique' => 'La placa ya está registrada'
        ]);

        $vehiculo->update([
            "tipo_vehiculo_id" => $request->tipo_vehiculo_id,
            "marca" => $request->marca,
            "habilitacion_vehicular" => $request->habilitacion_vehicular,
            "numero_placa" => Str::upper($request->numero_placa),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo actualizado correctamente'
        ]);
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

    public function eliminar(Vehiculo $vehiculo)
    {
        if ($vehiculo->estado === 'V') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un vehículo asignado'
            ], 422);
        }

        $vehiculo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehículo eliminado correctamente'
        ]);
    }
}

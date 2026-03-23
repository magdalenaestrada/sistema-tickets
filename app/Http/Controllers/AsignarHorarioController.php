<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignarHorario;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\TipoVehiculo;
use App\Models\TipoViaje;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class AsignarHorarioController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::where('estado', 'A')->get();
        $tipo_viajes = TipoViaje::all();
        $tipo_vehiculos = TipoVehiculo::all();
        $horarios = Horario::all();
        $empleados = Empleado::where('cargo_id', 3)->get();
        $vehiculos = Vehiculo::all();
        $hoy = Carbon::now("America/Lima")->format("Y-m-d");
        return view('asignaciones.index', compact('hoy', 'horarios', 'empleados', 'vehiculos', 'tipo_vehiculos', 'tipo_viajes', 'sucursales'));
    }

    public function datatable()
    {
        $asignaciones = AsignarHorario::with([
            'horario.tipo_viaje',
            'horario.punto_origen',
            'horario.punto_destino',
            'primerConductor.persona',
            'segundoConductor.persona',
            'vehiculo.tipo_vehiculo'
        ]);

        return DataTables::of($asignaciones)

            ->addColumn('horario', function ($a) {
                return ($a->horario->tipo_viaje->descripcion ?? '-') . ' (' .
                    ($a->horario->punto_origen->nombre_comercial ?? '-') . ' → ' .
                    ($a->horario->punto_destino->nombre_comercial ?? '-') . ')';
            })

            ->addColumn('primer', function ($a) {
                return $a->primerConductor->persona->nombres . ' ' .
                    $a->primerConductor->persona->apellidos;
            })

            ->addColumn('segundo', function ($a) {
                return $a->segundoConductor
                    ? $a->segundoConductor->persona->nombres . ' ' . $a->segundoConductor->persona->apellidos
                    : '-';
            })

            ->addColumn('vehiculo', function ($a) {
                return $a->vehiculo
                    ? $a->vehiculo->numero_placa . ' - ' . $a->vehiculo->tipo_vehiculo->descripcion
                    : '-';
            })

            ->addColumn('acciones', function ($vehiculo) {
                return '
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

    public function store(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor_id' => 'required|exists:empleados,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
        ]);

        AsignarHorario::create($request->all());

        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
        $vehiculo->update([
            "estado" => "V",
        ]);
        return response()->json(['message' => 'Asignación creada correctamente']);
    }

    public function show(AsignarHorario $asignacion)
    {
        return response()->json([
            'id' => $asignacion->id,
            'horario_id' => $asignacion->horario_id,
            'primer_conductor_id' => $asignacion->primer_conductor_id,
            'segundo_conductor_id' => $asignacion->segundo_conductor_id,
            'vehiculo_id' => $asignacion->vehiculo_id,
        ]);
    }

    public function update(Request $request, AsignarHorario $asignacion)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor_id' => 'required|exists:empleados,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'segundo_conductor_id' => 'nullable|exists:empleados,id',
        ]);

        $asignacion->update($request->all());

        return response()->json(['message' => 'Asignación actualizada correctamente']);
    }

    public function destroy(AsignarHorario $asignacion)
    {
        $asignacion->delete();
        return response()->json(['message' => 'Asignación eliminada correctamente']);
    }
}

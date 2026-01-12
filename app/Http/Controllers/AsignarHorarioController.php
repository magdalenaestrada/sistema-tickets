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
        return view('asignaciones.index', compact('horarios', 'empleados', 'vehiculos', 'tipo_vehiculos', 'tipo_viajes', 'sucursales'));
    }

    public function list()
    {
        $asignaciones = AsignarHorario::with(['horario', 'primerConductor', 'segundoConductor', 'vehiculoObj'])->get();

        $data = $asignaciones->map(function ($a) {
            return [
                'id' => $a->id,
                'horario' => ($a->horario->tipo_viaje->descripcion ?? '-') . ' (' . ($a->horario->punto_origen->nombre_comercial ?? '-') . ' → ' . ($a->horario->punto_destino->nombre_comercial ?? '-') . ')',
                'horario_id' => $a->horario_id,
                'primer_conductor_id' => $a->primerConductor->persona->nombres . ' ' . $a->primerConductor->persona->apellidos,
                'segundo_conductor_id' => $a->segundoConductor ? $a->segundoConductor->persona->nombres . ' ' . $a->segundoConductor->persona->apellidos : null,
                'vehiculo' => $a->vehiculoObj ? $a->vehiculoObj->numero_placa . ' - ' . $a->vehiculoObj->tipo_vehiculo->descripcion : null,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor_id' => 'required|exists:empleados,id',
            'vehiculo' => 'nullable|exists:vehiculos,id',
        ]);

        AsignarHorario::create($request->all());

        return response()->json(['message' => 'Asignación creada correctamente']);
    }

    public function show(AsignarHorario $asignacion)
    {
        return response()->json([
            'id' => $asignacion->id,
            'horario_id' => $asignacion->horario_id,
            'primer_conductor_id' => $asignacion->primer_conductor_id,
            'segundo_conductor_id' => $asignacion->segundo_conductor_id,
            'vehiculo' => $asignacion->vehiculo,
        ]);
    }

    public function update(Request $request, AsignarHorario $asignacion)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor_id' => 'required|exists:empleados,id',
            'vehiculo' => 'nullable|exists:vehiculos,id',
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

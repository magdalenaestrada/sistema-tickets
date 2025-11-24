<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignarHorario;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Vehiculo;

class AsignarHorarioController extends Controller
{
    public function index()
    {
        $horarios = Horario::all();
        $empleados = Empleado::where('cargo_id', 2)->get(); // solo conductores
        $vehiculos = Vehiculo::all();
        return view('asignaciones.index', compact('horarios', 'empleados', 'vehiculos'));
    }

    public function list()
    {
        $asignaciones = AsignarHorario::with(['horario', 'primerConductor', 'segundoConductor', 'vehiculoObj'])->get();

        $data = $asignaciones->map(function ($a) {
            return [
                'id' => $a->id,
                'horario' => ($a->horario->tipo_viaje->descripcion ?? '-') . ' (' . ($a->horario->punto_origen->nombre_comercial ?? '-') . ' → ' . ($a->horario->punto_destino->nombre_comercial ?? '-') . ')',
                'horario_id' => $a->horario_id,
                'primer_conductor' => $a->primerConductor->nombres . ' ' . $a->primerConductor->apellidos,
                'segundo_conductor' => $a->segundoConductor ? $a->segundoConductor->nombres . ' ' . $a->segundoConductor->apellidos : null,
                'vehiculo' => $a->vehiculoObj ? $a->vehiculoObj->placa . ' - ' . $a->vehiculoObj->marca : null,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor' => 'required|exists:empleados,id',
            'vehiculo' => 'nullable|exists:vehiculos,id',
            'segundo_conductor' => 'nullable|exists:empleados,id',
        ]);

        AsignarHorario::create($request->all());

        return response()->json(['message' => 'Asignación creada correctamente']);
    }

    public function show(AsignarHorario $asignacion)
    {
        return response()->json([
            'id' => $asignacion->id,
            'horario_id' => $asignacion->horario_id,
            'primer_conductor' => $asignacion->primer_conductor,
            'segundo_conductor' => $asignacion->segundo_conductor,
            'vehiculo' => $asignacion->vehiculo,
        ]);
    }

    public function update(Request $request, AsignarHorario $asignacion)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'primer_conductor' => 'required|exists:empleados,id',
            'vehiculo' => 'nullable|exists:vehiculos,id',
            'segundo_conductor' => 'nullable|exists:empleados,id',
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

<?php

namespace App\Http\Controllers;

use App\Models\AsignacionEncomienda;
use App\Models\AsignarHorario;
use App\Models\AsignarHorarioConductorVehiculo;
use App\Models\Encomienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionEncomiendaController extends Controller
{
    public function create($asignacion_id)
    {
        $asignacion = AsignarHorario::with('primerConductor', 'vehiculo')
            ->findOrFail($asignacion_id);

        return view("asignaciones.asignar", [
            "asignacion" => $asignacion
        ]);
    }

    public function datatable()
    {
        return Encomienda::where("estado", "A")
            ->whereNotIn("id", function ($q) {
                $q->select("encomienda_id")->from("asignacion_encomiendas");
            })
            ->orderBy("fecha_creacion", "DESC")
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            "asignacion_id" => "required",
            "encomiendas"   => "required|array"
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->encomiendas as $id) {

                AsignacionEncomienda::create([
                    "asignacion_id" => $request->asignacion_id,
                    "encomienda_id" => $id
                ]);

                Encomienda::where("id", $id)->update([
                    "estado" => "P",
                    "fecha_procesado" => now()
                ]);
            }
        });

        return response()->json([
            "success" => true,
            "message" => "Encomiendas asignadas correctamente"
        ]);
    }
}

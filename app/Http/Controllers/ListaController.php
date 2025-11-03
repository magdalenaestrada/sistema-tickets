<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Cargo;
use App\Models\Sucursal;
use App\Models\TipoLicencia;
use App\Models\Empleado;
use App\Models\TipoDocumentoPersona;
use App\Models\TipoVehiculo;

class ListaController extends Controller
{
    public function obtenerListas()
    {
        try {
            $areas = Area::select('id', 'descripcion')->orderBy('descripcion')->get();
            $cargos = Cargo::select('id', 'descripcion')->orderBy('descripcion')->get();
            $sucursales = Sucursal::select('id', 'nombre_comercial')->orderBy('nombre_comercial')->get();
            $tiposLicencia = TipoLicencia::select('id', 'descripcion')->orderBy('descripcion')->get();
            $tiposDocumento = TipoDocumentoPersona::select('id', 'codigo')->orderBy('id')->get();

            return response()->json([
                'success' => true,
                'areas' => $areas,
                'cargos' => $cargos,
                'sucursales' => $sucursales,
                'tipos_licencia' => $tiposLicencia,
                'tipos_documento' => $tiposDocumento,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las listas: ' . $th->getMessage(),
            ], 500);
        }
    }

    // En SucursalController.php
    public function listarJson($distrito)
    {
        return response()->json(
            Sucursal::where('distrito_id', $distrito)
                ->select('id', 'nombre_comercial')
                ->get()
        );
    }

    public function listarTipos()
    {
        // Trae todos los tipos de vehículo
        $tipos = TipoVehiculo::all(); // Suponiendo que tu modelo se llama TipoVehiculo
        return response()->json($tipos);
    }
}

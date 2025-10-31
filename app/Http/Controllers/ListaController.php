<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Cargo;
use App\Models\Sucursal;
use App\Models\TipoLicencia;
use App\Models\Empleado;
use App\Models\TipoDocumentoPersona;

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

            $supervisores = Empleado::with('persona:id,nombres,apellidos')
                ->get(['id', 'persona_id'])
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'nombre' => trim(($e->persona->nombres ?? '') . ' ' . ($e->persona->apellidos ?? '')),
                    ];
                });

            return response()->json([
                'success' => true,
                'areas' => $areas,
                'cargos' => $cargos,
                'sucursales' => $sucursales,
                'tipos_licencia' => $tiposLicencia,
                'tipos_documento' => $tiposDocumento,
                'supervisores' => $supervisores,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las listas: ' . $th->getMessage(),
            ], 500);
        }
    }
}

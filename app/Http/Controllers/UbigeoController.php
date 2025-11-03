<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Models\Sucursal;

class UbigeoController extends Controller
{
    public function getDepartamentos()
    {
        return response()->json(
            Departamento::select('id', 'nombre')->orderBy('nombre')->get()
        );
    }

    public function getProvincias($departamento_id)
    {
        return response()->json(
            Provincia::where('departamento_id', $departamento_id)
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get()

        );
    }

    public function getDistritos($provincia_id)
    {
        return response()->json(
            Distrito::where('provincia_id', $provincia_id)
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get()
        );
    }
    // Devuelve departamentos, provincias y distritos que tengan al menos 1 sucursal
    // UbigeoController
    public function getUbigeosConSucursales()
    {
        $departamentos = Departamento::whereHas('provincias.distritos.sucursales')
            ->with(['provincias' => function ($q) {
                $q->whereHas('distritos.sucursales')
                    ->with(['distritos' => function ($q2) {
                        $q2->whereHas('sucursales')
                            ->with('sucursales');
                    }]);
            }])
            ->get();

        return response()->json($departamentos);
    }

    public function getSucursalesPorDistrito($distrito_id)
    {
        $sucursales = Sucursal::where('distrito_id', $distrito_id)
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();

        return response()->json($sucursales);
    }
}

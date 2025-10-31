<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;

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
}

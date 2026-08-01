<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Pueblito;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PueblitoController extends Controller
{
    public function index()
    {
        $pueblitos = Pueblito::with(['distrito', 'sucursal'])
            ->orderByDesc('id')
            ->get();

        $distritos = Distrito::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_comercial')->get();

        return view('paradas.index', compact(
            'pueblitos',
            'distritos',
            'sucursales'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'distrito_id' => 'required|exists:distritos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $pueblito = Pueblito::create($validator->validated());

        $pueblito->load(['distrito', 'sucursal']);

        return response()->json([
            'message' => 'Pueblito registrado correctamente.',
            'data' => [
                'id' => $pueblito->id,
                'descripcion' => $pueblito->descripcion,
                'distrito_id' => $pueblito->distrito_id,
                'sucursal_id' => $pueblito->sucursal_id,
                'distrito' => $pueblito->distrito->nombre,
                'sucursal' => $pueblito->sucursal->nombre_comercial,
            ]
        ]);
    }

    public function update(Request $request, Pueblito $pueblito)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'distrito_id' => 'required|exists:distritos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $pueblito->update($validator->validated());

        $pueblito->load(['distrito', 'sucursal']);

        return response()->json([
            'message' => 'Pueblito actualizado correctamente.',
            'data' => [
                'id' => $pueblito->id,
                'descripcion' => $pueblito->descripcion,
                'distrito_id' => $pueblito->distrito_id,
                'sucursal_id' => $pueblito->sucursal_id,
                'distrito' => $pueblito->distrito->nombre,
                'sucursal' => $pueblito->sucursal->nombre_comercial,
            ]
        ]);
    }

    public function destroy(Pueblito $pueblito)
    {
        $pueblito->delete();

        return response()->json([
            'message' => 'Pueblito eliminado correctamente.'
        ]);
    }
}

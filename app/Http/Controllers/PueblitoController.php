<?php

namespace App\Http\Controllers;

use App\Models\Pueblito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PueblitoController extends Controller
{
    /**
     * Listar todos los pueblitos con sus relaciones
     */
    public function index()
    {
        $pueblitos = Pueblito::with(['distrito', 'sucursal'])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pueblitos,
        ]);
    }

    /**
     * Crear un nuevo pueblito
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => ['required', 'string', 'max:255'],
            'distrito_id' => ['required', 'integer', 'exists:distritos,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $pueblito = Pueblito::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pueblito creado correctamente',
            'data' => $pueblito->load(['distrito', 'sucursal']),
        ], 201);
    }

    /**
     * Mostrar un pueblito específico
     */
    public function show($id)
    {
        $pueblito = Pueblito::with(['distrito', 'sucursal'])->find($id);

        if (!$pueblito) {
            return response()->json([
                'success' => false,
                'message' => 'Pueblito no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pueblito,
        ]);
    }

    /**
     * Actualizar un pueblito existente
     */
    public function update(Request $request, $id)
    {
        $pueblito = Pueblito::find($id);

        if (!$pueblito) {
            return response()->json([
                'success' => false,
                'message' => 'Pueblito no encontrado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => ['sometimes', 'required', 'string', 'max:255'],
            'distrito_id' => ['sometimes', 'required', 'integer', 'exists:distritos,id'],
            'sucursal_id' => ['sometimes', 'required', 'integer', 'exists:sucursales,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $pueblito->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pueblito actualizado correctamente',
            'data' => $pueblito->load(['distrito', 'sucursal']),
        ]);
    }

    /**
     * Eliminar un pueblito
     */
    public function destroy($id)
    {
        $pueblito = Pueblito::find($id);

        if (!$pueblito) {
            return response()->json([
                'success' => false,
                'message' => 'Pueblito no encontrado',
            ], 404);
        }

        $pueblito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pueblito eliminado correctamente',
        ]);
    }
}

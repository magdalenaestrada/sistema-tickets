<?php

namespace App\Http\Controllers;

use App\Models\Encomienda;
use Illuminate\Http\Request;

class EncomiendaConsultaController extends Controller
{
    public function index()
    {
        return view('encomiendas.consulta');
    }

    public function buscar(Request $request)
    {
        $codigo = trim($request->input('codigo'));

        if (!$codigo) {
            return response()->json(['error' => 'Ingrese un código de encomienda válido.'], 422);
        }

        $encomienda = Encomienda::with([
            'emisor',
            'receptor',
            'receptor2',
            'sucursal_origen',
            'sucursal_destino',
            'origenPueblito',
            'destinoPueblito',
            'detalles.tipo_encomienda',
            'usuario',
            'entregado',
            'venta.pagos',
            'salidaActual.salida.vehiculo'
        ])
            ->where('codigo', strtoupper($codigo))
            ->first();

        if (!$encomienda) {
            return response()->json(['error' => "No se encontró la encomienda con código: {$codigo}"], 444);
        }

        return response()->json([
            'success' => true,
            'encomienda' => $encomienda,
            'total_pagado' => $encomienda->total_pagado,
        ]);
    }
}

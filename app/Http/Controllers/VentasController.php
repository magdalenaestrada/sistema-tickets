<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Venta;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function imprimir(Venta $venta)
    {
        $venta->load([
            'persona',
            'detalles',
            'pagos.metodoPago',
            'sucursal.empresa',
            'usuario',

        ]);

        return view('ventas.ticket', compact('venta'));
    }

    public function obtenerSerieCorrelativo(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursales,id',
            'tipo' => 'required|string|in:boleta,factura,nota_venta',
        ]);

        $sucursal = Sucursal::findOrFail($request->sucursal_id);

        $codigoEmision = str_pad($sucursal->codigo_emision, 3, '0', STR_PAD_LEFT);

        $prefijo = match ($request->tipo) {
            'boleta' => 'B',
            'factura' => 'F',
            default => 'N',
        };

        $serie = $prefijo . $codigoEmision;

        $ultimoCorrelativo = Venta::where('sucursal_id', $sucursal->id)
            ->where('tipo_doc_sunat', $request->tipo)
            ->max('correlativo');

        $siguiente = ((int) $ultimoCorrelativo) + 1;

        return response()->json([
            'serie' => $serie,
            'correlativo' => str_pad($siguiente, 8, '0', STR_PAD_LEFT),
        ]);
    }
}

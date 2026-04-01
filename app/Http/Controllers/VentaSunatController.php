<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\Facturacion\EmitirVentaService;

class VentaSunatController extends Controller
{
    public function emitir($id, EmitirVentaService $service)
    {
        $venta = Venta::with([
            'detalles',
            'persona',
            'sucursal.empresa',
            'tipoDocumentoFactura',
        ])->findOrFail($id);

        $response = $service->emitir($venta);

        return response()->json($response);
    }
}

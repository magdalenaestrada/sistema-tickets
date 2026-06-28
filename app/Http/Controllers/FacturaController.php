<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Sucursal;
use App\Services\Facturacion\EmitirFacturaService;

class FacturaController extends Controller
{
    public function emitir($id, EmitirFacturaService $service)
    {
        $factura = Factura::with(['detalles', 'empresa'])->findOrFail($id);
        $res = $service->emitir($factura);

        return response()->json($res);
    }
}

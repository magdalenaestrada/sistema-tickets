<?php

namespace App\Http\Controllers;

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
}

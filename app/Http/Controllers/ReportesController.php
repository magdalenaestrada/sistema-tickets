<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\VentaPago;
use App\Models\VentaDetalle;
use App\Models\Pasaje;
use App\Models\Horario;
use App\Models\Descuento;
use App\Models\Encomienda;
use App\Models\MetodoPago;
use App\Models\Pueblito;
use App\Models\RutaPunto;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoVehiculo;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use PDF;
use Yajra\DataTables\DataTables;

class ReportesController extends Controller
{

    public function resumenVentas(Request $request)
    {
        $query = Venta::query();

        if ($request->fecha_inicio) {
            $query->whereDate(
                'fecha_emision',
                '>=',
                $request->fecha_inicio
            );
        }

        if ($request->fecha_fin) {
            $query->whereDate(
                'fecha_emision',
                '<=',
                $request->fecha_fin
            );
        }

        if ($request->sucursal) {
            $query->where(
                'sucursal_id',
                $request->sucursal
            );
        }

        return response()->json([

            'total_vendido' =>
            $query->where('estado', 'E')->sum('total'),

            'comprobantes' =>
            $query->count(),

            'anulados' =>
            $query->where('estado', 'A')->count(),

            'ticket_promedio' =>
            round($query->avg('total'), 2)

        ]);
    }
}

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
use App\Models\Sucursal;
use App\Models\TipoVehiculo;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use PDF;

class ReportesController extends Controller
{
    // Página de filtros
    public function index()
    {
        $sucursales = Sucursal::all();
        $metodosPago = MetodoPago::all();
        $vehiculos = TipoVehiculo::all();
        return view('reportes.index', compact('sucursales', 'metodosPago', 'vehiculos'));
    }

    // Generar PDF según tipo de reporte
    public function generar(Request $request)
    {
        $tipo = $request->tipo; 
        $data = [];

        switch ($tipo) {
            case 'ventas':
                $query = Venta::with(['persona', 'pagos.metodoPago', 'detalles']);
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $query->whereBetween('fecha_emision', [$request->fecha_inicio, $request->fecha_fin]);
                }
                if ($request->sucursal_id) $query->where('sucursal_id', $request->sucursal_id);
                if ($request->estado) $query->where('estado', $request->estado);
                if ($request->metodo_pago_id) {
                    $query->whereHas('ventaPagos', fn($q) => $q->where('metodo_pago_id', $request->metodo_pago_id));
                }
                $data = $query->get();
                break;

            case 'pasajeros':
                $query = Pasaje::with(['persona', 'horario', 'venta.persona', 'horario.tipo_vehiculo']);
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $query->whereBetween('fecha_creacion', [$request->fecha_inicio, $request->fecha_fin]);
                }
                if ($request->horario_id) $query->where('horario_id', $request->horario_id);
                $data = $query->get();
                break;

            case 'cupones':
                $query = Descuento::with('persona');
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
                }
                if ($request->codigo) $query->where('codigo', $request->codigo);
                if ($request->persona_id) $query->where('persona_id', $request->persona_id);
                $data = $query->get();
                break;

            case 'equipaje':
                $query = Encomienda::with(['emisor', 'receptor', 'detalles', 'sucursal_origen', 'sucursal_destino']);
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $query->whereBetween('fecha_creacion', [$request->fecha_inicio, $request->fecha_fin]);
                }
                if ($request->origen) $query->where('origen', $request->origen);
                if ($request->destino) $query->where('destino', $request->destino);
                $data = $query->get();
                break;

            case 'viajes':
                $query = Horario::with(['asignaciones.vehiculo', 'pasajes']);
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $query->whereBetween('fecha_salida', [$request->fecha_inicio, $request->fecha_fin]);
                }
                if ($request->vehiculo_id) $query->whereHas('asignaciones', fn($q) => $q->where('vehiculo', $request->vehiculo_id));
                $data = $query->get();
                break;
        }

        $pdf = FacadePdf::loadView("reportes.pdf.$tipo", compact('data', 'request'));
        return $pdf->download("reporte_$tipo.pdf");
    }
}

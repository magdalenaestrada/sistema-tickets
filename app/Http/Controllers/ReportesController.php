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
use App\Models\Ruta;
use App\Models\RutaPunto;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoVehiculo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Carbon;
use PDF;
use Yajra\DataTables\DataTables;

class ReportesController extends Controller
{

    public function index()
    {
        $sucursales = Sucursal::where('estado', 'A')->get();
        $usuarios = User::with("persona")->get();
        $tipos_documento = TipoDocumentoFactura::where('estado', 'A')->get();
        $rutas = Ruta::all();
        return view('reportes.index', compact('sucursales', 'tipos_documento', 'usuarios', 'rutas'));
    }

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


    private function obtenerFechas(Request $request)
    {
        $period = $request->period ?? 'month';

        switch ($period) {
            case 'today':
                $desde = Carbon::today()->startOfDay();
                $hasta = Carbon::today()->endOfDay();
                break;

            case 'week':
                $desde = Carbon::now()->startOfWeek();
                $hasta = Carbon::now()->endOfWeek();
                break;

            case 'year':
                $desde = Carbon::now()->startOfYear();
                $hasta = Carbon::now()->endOfYear();
                break;

            case 'custom':
                $desde = Carbon::parse($request->date_from)->startOfDay();
                $hasta = Carbon::parse($request->date_to)->endOfDay();
                break;

            case 'month':
            default:
                $desde = Carbon::now()->startOfMonth();
                $hasta = Carbon::now()->endOfMonth();
                break;
        }

        return [$desde, $hasta];
    }

    private function queryVentasUsuario(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'usuario',
                'venta',
                'salida',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                /*
                 * AQUÍ debe ir la fecha de la venta.
                 *
                 * Si tu tabla ventas utiliza created_at:
                 */
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        return $query;
    }

    public function ventasPorUsuarioExcel(Request $request)
    {
        $pasajes = $this->queryVentasUsuario($request)
            ->orderBy('usuario_id')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VentasPorUsuarioExport(
                $pasajes,
                $desde,
                $hasta
            ),
            'ventas_por_usuario.xlsx'
        );
    }

    public function ventasPorUsuarioPdf(Request $request)
    {
        $pasajes = $this->queryVentasUsuario($request)
            ->orderBy('usuario_id')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.ventas.usuario',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('ventas_por_usuario.pdf');
    }


    public function queryVentasGeneral(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'usuario',
                'persona',
                'venta',
                'salida',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        // Estado del pasaje
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Agencia
        if ($request->filled('agencia_id')) {
            $query->whereHas('venta', function ($q) use ($request) {
                $q->where('sucursal_id', $request->agencia_id);
            });
        }

        // Ruta
        if ($request->filled('ruta_id')) {
            $query->whereHas('salida.horario.ruta', function ($q) use ($request) {
                $q->where('id', $request->ruta_id);
            });
        }

        return $query;
    }

    public function ventasGeneralExcel(Request $request)
    {
        $pasajes = $this->queryVentasGeneral($request)
            ->orderBy('venta_id')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VentasGeneralExport($pasajes),
            'ventas_pasajes_general.xlsx'
        );
    }

    public function ventasGeneralPdf(Request $request)
    {
        $pasajes = $this->queryVentasGeneral($request)
            ->orderBy('venta_id')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.ventas.general',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'ventas_pasajes_general.pdf'
        );
    }

    private function queryVentasAgencia(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'usuario',
                'persona',
                'venta',
                'salida',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        if ($request->filled('agencia_id')) {
            $query->whereHas('venta', function ($q) use ($request) {
                $q->where('sucursal_id', $request->agencia_id);
            });
        }

        return $query;
    }

    public function ventasPorAgenciaExcel(Request $request)
    {
        $pasajes = $this->queryVentasAgencia($request)
            ->orderBy('usuario_id')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VentasPorAgenciaExport($pasajes),
            'ventas_por_agencia.xlsx'
        );
    }

    public function ventasPorAgenciaPdf(Request $request)
    {
        $pasajes = $this->queryVentasAgencia($request)
            ->orderBy('usuario_id')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.ventas.agencia',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'ventas_por_agencia.pdf'
        );
    }

    private function queryVentasRuta(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'usuario',
                'persona',
                'venta',
                'salida.horario.ruta',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        if ($request->filled('ruta_id')) {
            $query->whereHas('salida.horario.ruta', function ($q) use ($request) {
                $q->where('id', $request->ruta_id);
            });
        }

        return $query;
    }

    public function ventasPorRutaExcel(Request $request)
    {
        $pasajes = $this->queryVentasRuta($request)
            ->orderBy('salida_id')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VentasPorRutaExport($pasajes),
            'ventas_por_ruta.xlsx'
        );
    }

    public function ventasPorRutaPdf(Request $request)
    {
        $pasajes = $this->queryVentasRuta($request)
            ->orderBy('salida_id')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.ventas.ruta',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'ventas_por_ruta.pdf'
        );
    }

    private function queryPasajerosRuta(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'usuario',
                'persona',
                'venta',
                'salida.horario.ruta',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        $query->where('estado', 'V');

        if ($request->filled('ruta_id')) {
            $query->whereHas('salida.horario.ruta', function ($q) use ($request) {
                $q->where('id', $request->ruta_id);
            });
        }

        return $query;
    }

    public function pasajerosPorRutaExcel(Request $request)
    {
        $pasajes = $this->queryPasajerosRuta($request)
            ->orderBy('salida_id')
            ->orderBy('asiento_numero')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PasajerosPorRutaExport($pasajes),
            'pasajeros_transportados_por_ruta.xlsx'
        );
    }

    public function pasajerosPorRutaPdf(Request $request)
    {
        $pasajes = $this->queryPasajerosRuta($request)
            ->orderBy('salida_id')
            ->orderBy('asiento_numero')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.pasajeros.ruta',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'pasajeros_transportados_por_ruta.pdf'
        );
    }

    private function queryHistorialPasajero(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $busqueda = trim($request->busqueda ?? '');

        $query = Pasaje::query()
            ->with([
                'usuario',
                'persona',
                'venta',
                'salida.horario.ruta',
                'origen',
                'destino',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            });

        if ($busqueda !== '') {

            $query->whereHas('persona', function ($q) use ($busqueda) {

                $q->where(function ($q) use ($busqueda) {

                    $q->where('numero_documento', 'like', "%{$busqueda}%")
                        ->orWhere('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%");
                });
            });
        }

        return $query;
    }

    private function querySobreequipaje(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Pasaje::query()
            ->with([
                'venta',
                'usuario',
                'persona',
                'origen',
                'destino',
                'salida.horario.ruta',
                'sobreEquipajes',
            ])
            ->whereHas('venta', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta]);
            })
            ->whereHas('sobreEquipajes');

        if ($request->filled('ruta_id')) {
            $query->whereHas('salida.horario.ruta', function ($q) use ($request) {
                $q->where('id', $request->ruta_id);
            });
        }

        return $query;
    }

    public function historialPasajeroPdf(Request $request)
    {
        $pasajes = $this->queryHistorialPasajero($request)
            ->orderBy('salida_id')
            ->orderBy('asiento_numero')
            ->get();

        [$desde, $hasta] = $this->obtenerFechas($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reportes.pasajeros.historial',
            compact(
                'pasajes',
                'desde',
                'hasta'
            )
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'historial_pasajero.pdf'
        );
    }
}

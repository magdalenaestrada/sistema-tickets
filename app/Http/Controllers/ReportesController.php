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
        $sucursales = Sucursal::where('estado', 'ANULADO')->get();
        $usuarios = User::with("persona")->get();
        $tipos_documento = TipoDocumentoFactura::where('estado', 'ANULADO')->get();
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
            $query->where('estado', 'EMITIDO')->sum('total'),

            'comprobantes' =>
            $query->count(),

            'anulados' =>
            $query->where('estado', 'ANULADO')->count(),

            'ticket_promedio' =>
            round($query->avg('total'), 2)

        ]);
    }

    private function obtenerFechas(Request $request)
    {
        // Compatible tanto con:
        // period/date_from/date_to
        // como con:
        // periodo/desde/hasta

        $period = $request->period
            ?? $request->periodo
            ?? 'month';

        $dateFrom = $request->date_from
            ?? $request->desde;

        $dateTo = $request->date_to
            ?? $request->hasta;

        switch ($period) {

            case 'today':
                $desde = Carbon::today('America/Lima')->startOfDay();
                $hasta = Carbon::today('America/Lima')->endOfDay();
                break;

            case 'week':
                $desde = Carbon::now('America/Lima')->startOfWeek()->startOfDay();
                $hasta = Carbon::now('America/Lima')->endOfWeek()->endOfDay();
                break;

            case 'year':
                $desde = Carbon::now('America/Lima')->startOfYear()->startOfDay();
                $hasta = Carbon::now('America/Lima')->endOfYear()->endOfDay();
                break;

            case 'custom':

                $desde = $dateFrom
                    ? Carbon::parse($dateFrom, 'America/Lima')->startOfDay()
                    : Carbon::now('America/Lima')->startOfMonth();

                $hasta = $dateTo
                    ? Carbon::parse($dateTo, 'America/Lima')->endOfDay()
                    : Carbon::now('America/Lima')->endOfDay();

                break;

            case 'month':
            default:

                // Si el frontend ya mandó fechas, respetarlas
                if ($dateFrom && $dateTo) {
                    $desde = Carbon::parse($dateFrom, 'America/Lima')->startOfDay();
                    $hasta = Carbon::parse($dateTo, 'America/Lima')->endOfDay();
                } else {
                    $desde = Carbon::now('America/Lima')->startOfMonth()->startOfDay();
                    $hasta = Carbon::now('America/Lima')->endOfMonth()->endOfDay();
                }

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


    private function queryVentasGeneral(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Venta::query()
            ->with([
                'usuario.persona',
                'sucursal',
                'persona',
                'tipoDocumentoFactura',
                'pagos.metodoPago',
                'pagos.billetera',
                'pasajes',
                'encomiendas' => function ($q) {
                    $q->select([
                        'id',
                        'venta_id',
                        'sobre_equipaje',
                        'estado',
                        'total',
                    ]);
                },
            ])
            ->whereBetween('fecha_emision', [$desde, $hasta]);

        /*
    |--------------------------------------------------------------------------
    | SUCURSAL / AGENCIA
    |--------------------------------------------------------------------------
    */
        if ($request->filled('agencia_id')) {
            $query->where('sucursal_id', $request->agencia_id);
        }

        /*
    |--------------------------------------------------------------------------
    | USUARIO
    |--------------------------------------------------------------------------
    */
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        return $query;
    }

    private function obtenerResumenVentasGeneral(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        /*
    |--------------------------------------------------------------------------
    | VENTAS DEL PERÍODO
    |--------------------------------------------------------------------------
    */
        $ventas = $this->queryVentasGeneral($request)
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | VENTAS EMITIDAS / ANULADAS
    |--------------------------------------------------------------------------
    |
    | Según tu controlador actual:
    | E = Emitida
    | A = Anulada
    |
    */

        $ventasEmitidas = $ventas->filter(function ($venta) {
            return $venta->estado instanceof \BackedEnum
                ? $venta->estado->value === 'EMITIDO'
                : $venta->estado === 'EMITIDO';
        });

        $ventasAnuladas = $ventas->filter(function ($venta) {
            return $venta->estado instanceof \BackedEnum
                ? $venta->estado->value === 'ANULADO'
                : $venta->estado === 'ANULADO';
        });

        /*
    |--------------------------------------------------------------------------
    | TOTALES GENERALES
    |--------------------------------------------------------------------------
    */

        $totalVendido = $ventasEmitidas->sum(function ($venta) {
            return (float) $venta->total;
        });

        $cantidadVentas = $ventasEmitidas->count();

        $ticketPromedio = $cantidadVentas > 0
            ? $totalVendido / $cantidadVentas
            : 0;

        dd([
            'ventas_total' => $ventas->count(),

            'ventas_emitidas' => $ventasEmitidas->count(),

            'ventas_ids' => $ventasEmitidas->pluck('id')->take(20),

            'pagos_por_venta' => $ventasEmitidas
                ->take(10)
                ->map(function ($venta) {
                    return [
                        'venta_id' => $venta->id,
                        'serie_numero' => $venta->serie . '-' . $venta->numero,
                        'total_venta' => $venta->total,
                        'cantidad_pagos' => $venta->pagos->count(),
                        'pagos' => $venta->pagos->map(function ($pago) {
                            return [
                                'id' => $pago->id,
                                'metodo_pago_id' => $pago->metodo_pago_id,
                                'billetera_id' => $pago->billetera_id,
                                'total' => $pago->total,
                                'estado' => $pago->estado,
                            ];
                        }),
                    ];
                }),
        ]);

        $cantidadPasajes = $ventasEmitidas->sum(function ($venta) {
            return $venta->pasajes->count();
        });

        $cantidadEncomiendas = $ventasEmitidas->sum(function ($venta) {
            return $venta->encomiendas
                ->where('sobre_equipaje', false)
                ->count();
        });

        $cantidadSobreEquipajes = $ventasEmitidas->sum(function ($venta) {
            return $venta->encomiendas
                ->where('sobre_equipaje', true)
                ->count();
        });

        $totalServicios =
            $cantidadPasajes +
            $cantidadEncomiendas +
            $cantidadSobreEquipajes;

        $metodosPago = collect();

        foreach ($ventasEmitidas as $venta) {

            foreach ($venta->pagos as $pago) {

                $metodoBase =
                    $pago->metodoPago?->nombre
                    ?? $pago->metodoPago?->descripcion
                    ?? 'SIN MÉTODO';

                $billetera =
                    $pago->billetera?->nombre
                    ?? $pago->billetera?->descripcion
                    ?? null;

                /*
        |--------------------------------------------------------------------------
        | NOMBRE A MOSTRAR
        |--------------------------------------------------------------------------
        |
        | Ejemplos:
        |
        | EFECTIVO
        | TARJETA
        | TRANSFERENCIA BANCARIA
        | BILLETERA DIGITAL - YAPE
        | BILLETERA DIGITAL - PLIN
        |
        */

                if ($billetera) {
                    $nombre = 'BILLETERA DIGITAL - ' . $billetera;
                } else {
                    $nombre = $metodoBase;
                }

                $nombre = mb_strtoupper(
                    str_replace('_', ' ', trim($nombre))
                );

                if (!$metodosPago->has($nombre)) {

                    $metodosPago->put($nombre, [
                        'nombre' => $nombre,
                        'operaciones' => 0,
                        'total' => 0,
                    ]);
                }

                $actual = $metodosPago->get($nombre);

                $actual['operaciones']++;
                $actual['total'] += (float) $pago->total;

                $metodosPago->put($nombre, $actual);
            }
        }

        $metodosPago = $metodosPago
            ->sortByDesc('total')
            ->values();

        $ventasPorSucursal = $ventasEmitidas
            ->groupBy(function ($venta) {
                return $venta->sucursal_id ?: 'sin_sucursal';
            })
            ->map(function ($grupo) {

                $sucursal = $grupo->first()->sucursal;

                return [
                    'sucursal' =>
                    $sucursal?->nombre_comercial
                        ?? $sucursal?->nombre
                        ?? 'SIN SUCURSAL',

                    'ventas' => $grupo->count(),

                    'total' => $grupo->sum(function ($venta) {
                        return (float) $venta->total;
                    }),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $ventasPorVendedor = $ventasEmitidas
            ->groupBy(function ($venta) {
                return $venta->usuario_id ?: 'sin_usuario';
            })
            ->map(function ($grupo) {

                $usuario = $grupo->first()->usuario;

                $nombreUsuario =
                    $usuario?->persona?->nombre_completo
                    ?? $usuario?->name
                    ?? 'SIN USUARIO';

                return [
                    'vendedor' => $nombreUsuario,

                    'ventas' => $grupo->count(),

                    'total' => $grupo->sum(function ($venta) {
                        return (float) $venta->total;
                    }),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'ventas' => $ventas,

            'ventasEmitidas' => $ventasEmitidas,
            'ventasAnuladas' => $ventasAnuladas,

            'desde' => $desde,
            'hasta' => $hasta,

            'totalVendido' => $totalVendido,
            'cantidadVentas' => $cantidadVentas,
            'cantidadAnuladas' => $ventasAnuladas->count(),
            'ticketPromedio' => $ticketPromedio,

            'cantidadPasajes' => $cantidadPasajes,
            'cantidadEncomiendas' => $cantidadEncomiendas,
            'cantidadSobreEquipajes' => $cantidadSobreEquipajes,
            'totalServicios' => $totalServicios,

            'metodosPago' => $metodosPago,
            'ventasPorSucursal' => $ventasPorSucursal,
            'ventasPorVendedor' => $ventasPorVendedor,
        ];
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
        $data = $this->obtenerResumenVentasGeneral($request);

        $pdf = FacadePdf::loadView(
            'reportes.ventas.general',
            $data
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'reporte_general_ventas_' .
                $data['desde']->format('Ymd') .
                '_' .
                $data['hasta']->format('Ymd') .
                '.pdf'
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

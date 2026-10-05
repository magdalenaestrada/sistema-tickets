<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\VentaPago;
use App\Models\VentaDetalle;
use App\Models\Pasaje;
use App\Models\Horario;
use App\Models\Descuento;
use App\Models\Encomienda;
use App\Models\MetodoPago;
use App\Models\NotaVentaAnulada;
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

        $totalVendido = $ventasEmitidas->sum(function ($venta) {
            return (float) $venta->total;
        });

        $cantidadVentas = $ventasEmitidas->count();

        $ticketPromedio = $cantidadVentas > 0
            ? $totalVendido / $cantidadVentas
            : 0;


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

    public function ventasPorSucursalPdf(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Venta::query()
            ->with(['sucursal', 'pagos.metodoPago', 'pagos.billetera'])
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', 'EMITIDO');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $ventas = $query->get();

        $sucursales = $ventas
            ->groupBy(fn($v) => $v->sucursal_id ?: 'sin_sucursal')
            ->map(function ($grupo) {
                $sucursal = $grupo->first()->sucursal;

                return [
                    'sucursal'   => $sucursal?->nombre_comercial ?? 'SIN SUCURSAL',
                    'operaciones' => $grupo->count(),
                    'total'      => $grupo->sum(fn($v) => (float) $v->total),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalGeneral = $sucursales->sum('total');

        $pdf = FacadePdf::loadView('reportes.ventas.sucursal', compact('sucursales', 'desde', 'hasta', 'totalGeneral'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'ventas_por_sucursal_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.pdf'
        );
    }

    public function ventasGeneralExcel(Request $request)
    {
        $data = $this->obtenerResumenVentasGeneral($request);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VentasGeneralExport($data),
            'reporte_general_ventas_' .
                $data['desde']->format('Ymd') . '_' .
                $data['hasta']->format('Ymd') . '.xlsx'
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

    public function anulacionesPdf(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = NotaVentaAnulada::query()
            ->with(['venta.sucursal', 'venta.persona', 'usuario.persona', 'venta.pasajes', 'venta.encomiendas'])
            ->whereBetween('fecha', [$desde, $hasta]);

        if ($request->filled('tipo_servicio')) {
            $tipo = $request->tipo_servicio;

            $query->whereHas('venta', function ($q) use ($tipo) {
                if ($tipo === 'pasaje') {
                    $q->whereHas('pasajes');
                } elseif ($tipo === 'encomienda') {
                    $q->whereHas('encomiendas', fn($eq) => $eq->where('sobre_equipaje', false));
                } elseif ($tipo === 'sobreequipaje') {
                    $q->whereHas('encomiendas', fn($eq) => $eq->where('sobre_equipaje', true));
                }
            });
        }

        $anulaciones = $query->orderBy('fecha')->get();

        $totalDevuelto = $anulaciones->sum(fn($n) => (float) $n->total);

        $pdf = FacadePdf::loadView('reportes.anulaciones.index', compact('anulaciones', 'desde', 'hasta', 'totalDevuelto'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'anulaciones_devoluciones_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.pdf'
        );
    }

    public function cuadreCajaVendedorPdf(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Caja::query()
            ->with(['usuario.persona', 'sucursal'])
            ->whereNotNull('fecha_cierre')
            ->whereBetween('fecha_cierre', [$desde, $hasta]);

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        $cajas = $query->orderBy('fecha_cierre')->get();

        $filas = $cajas->map(function ($caja) {

            $esperado = $caja->monto_actual;          // accessor: apertura + ingresos - salidas
            $declarado = (float) ($caja->monto_cierre ?? 0);
            $diferencia = $declarado - $esperado;

            return [
                'vendedor'    => $caja->usuario?->persona?->nombre_completo ?? $caja->usuario?->name ?? 'SIN USUARIO',
                'sucursal'    => $caja->sucursal?->nombre_comercial ?? 'SIN SUCURSAL',
                'apertura'    => (float) $caja->monto_apertura,
                'ingresos'    => $caja->total_ingresos,
                'salidas'     => $caja->total_salidas,
                'esperado'    => $esperado,
                'declarado'   => $declarado,
                'diferencia'  => $diferencia,
                'estado_caja' => $caja->estado,
                'fecha_apertura' => $caja->fecha_creacion,
                'fecha_cierre'   => $caja->fecha_cierre,
            ];
        });

        $totales = [
            'apertura'   => $filas->sum('apertura'),
            'ingresos'   => $filas->sum('ingresos'),
            'salidas'    => $filas->sum('salidas'),
            'esperado'   => $filas->sum('esperado'),
            'declarado'  => $filas->sum('declarado'),
            'diferencia' => $filas->sum('diferencia'),
        ];

        $pdf = FacadePdf::loadView('reportes.caja.vendedor', compact('filas', 'totales', 'desde', 'hasta'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'cuadre_caja_vendedor_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.pdf'
        );
    }

    public function cuadreCajaSucursalPdf(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = Caja::query()
            ->with(['usuario.persona', 'sucursal'])
            ->whereNotNull('fecha_cierre')
            ->whereBetween('fecha_cierre', [$desde, $hasta]);

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $cajas = $query->get();

        $sucursales = $cajas
            ->groupBy(fn($c) => $c->sucursal_id ?: 'sin_sucursal')
            ->map(function ($grupo) {
                $sucursal = $grupo->first()->sucursal;

                $esperado = $grupo->sum(fn($c) => $c->monto_actual);
                $declarado = $grupo->sum(fn($c) => (float) ($c->monto_cierre ?? 0));

                return [
                    'sucursal'      => $sucursal?->nombre_comercial ?? 'SIN SUCURSAL',
                    'num_cajas'     => $grupo->count(),
                    'apertura'      => $grupo->sum(fn($c) => (float) $c->monto_apertura),
                    'ingresos'      => $grupo->sum(fn($c) => $c->total_ingresos),
                    'salidas'       => $grupo->sum(fn($c) => $c->total_salidas),
                    'esperado'      => $esperado,
                    'declarado'     => $declarado,
                    'diferencia'    => $declarado - $esperado,
                ];
            })
            ->sortByDesc('declarado')
            ->values();

        $totales = [
            'apertura'   => $sucursales->sum('apertura'),
            'ingresos'   => $sucursales->sum('ingresos'),
            'salidas'    => $sucursales->sum('salidas'),
            'esperado'   => $sucursales->sum('esperado'),
            'declarado'  => $sucursales->sum('declarado'),
            'diferencia' => $sucursales->sum('diferencia'),
        ];

        $pdf = FacadePdf::loadView('reportes.caja.sucursal', compact('sucursales', 'totales', 'desde', 'hasta'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'cuadre_caja_sucursal_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.pdf'
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

    public function recaudacionMedioPagoPdf(Request $request)
    {
        [$desde, $hasta] = $this->obtenerFechas($request);

        $query = CajaDetalle::query()
            ->with(['metodoPago', 'billetera_digital', 'caja.sucursal'])
            ->where('amount', '>', 0) // solo ingresos, no salidas de caja
            ->where(function ($q) {
                $q->whereNull('anulado')->orWhere('anulado', false);
            })
            ->whereHas('caja', function ($q) use ($desde, $hasta) {
                $q->whereNotNull('fecha_cierre')
                    ->whereBetween('fecha_cierre', [$desde, $hasta]);
            });

        if ($request->filled('sucursal_id')) {
            $query->whereHas('caja', fn($q) => $q->where('sucursal_id', $request->sucursal_id));
        }

        // Filtro de medio de pago del select del index
        if ($request->filled('medio_pago')) {
            $medio = $request->medio_pago;

            $query->where(function ($q) use ($medio) {
                match ($medio) {
                    'efectivo' => $q->whereHas('metodoPago', fn($mp) => $mp->whereRaw('LOWER(descripcion) = ?', ['efectivo'])),
                    'tarjeta' => $q->whereHas('billetera_digital', fn($b) => $b->whereRaw('LOWER(descripcion) LIKE ?', ['%tarjeta%'])),
                    'transferencia' => $q->whereHas('billetera_digital', fn($b) => $b->whereRaw('LOWER(descripcion) LIKE ?', ['%transferencia%'])),
                    'billetera' => $q->whereHas('billetera_digital', fn($b) => $b->where(fn($w) => $w->whereRaw('LOWER(descripcion) LIKE ?', ['%yape%'])->orWhereRaw('LOWER(descripcion) LIKE ?', ['%plin%']))),
                    default => null,
                };
            });
        }

        $detalles = $query->get();

        $porMetodo = $detalles
            ->groupBy(function ($d) {
                $billetera = $d->billetera_digital?->descripcion;
                $metodo = $d->metodoPago?->descripcion ?? 'SIN MÉTODO';

                return $billetera ? "BILLETERA DIGITAL - {$billetera}" : $metodo;
            })
            ->map(function ($grupo, $nombre) {
                return [
                    'metodo' => mb_strtoupper($nombre),
                    'operaciones' => $grupo->count(),
                    'total' => $grupo->sum(fn($d) => (float) $d->amount),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalGeneral = $porMetodo->sum('total');

        $pdf = FacadePdf::loadView('reportes.caja.medio_pago', compact('porMetodo', 'totalGeneral', 'desde', 'hasta'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'recaudacion_medio_pago_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.pdf'
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

    
private function queryHistorialPasajero(
    Carbon $desde,
    Carbon $hasta,
    ?string $dni
): \Illuminate\Database\Eloquent\Builder {
    return Pasaje::query()
        ->with([
            'persona',
            'usuario.persona',
            'venta',
            'salida.horario.ruta',
            'origen',
            'destino',
        ])
        ->whereHas('venta', function ($query) use ($desde, $hasta) {
            // Se conserva la fecha de venta utilizada por tu historial actual.
            $query->whereBetween('created_at', [$desde, $hasta]);
        })
        ->when($dni !== null, function ($query) use ($dni) {
            $query->whereHas('persona', function ($persona) use ($dni) {
                // Coincidencia exacta. El DNI permanece como texto.
                $persona->where('documento', $dni);
            });
        });
}

private function obtenerDatosHistorialPasajero(Request $request): array
{
    $filtros = \Illuminate\Support\Facades\Validator::make([
        'dni' => $request->input('dni'),
        'period' => $request->input('period') ?? $request->input('periodo') ?? 'month',
        'date_from' => $request->input('date_from') ?? $request->input('desde'),
        'date_to' => $request->input('date_to') ?? $request->input('hasta'),
    ], [
        'dni' => ['bail', 'nullable', 'string', 'regex:/^[0-9]{8}$/'],
        'period' => ['required', 'in:today,week,month,year,custom'],
        'date_from' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d'],
        'date_to' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d'],
    ], [
        'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
        'date_from.required_if' => 'Selecciona la fecha inicial.',
        'date_to.required_if' => 'Selecciona la fecha final.',
    ])->validate();

    // Comprueba también las fechas recibidas, antes de resolver el período.
    if (!empty($filtros['date_from']) && !empty($filtros['date_to'])
        && $filtros['date_from'] > $filtros['date_to']) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'date_to' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);
    }

    $dni = filled($filtros['dni'] ?? null) ? $filtros['dni'] : null;
    [$desde, $hasta] = $this->obtenerFechas($request);

    if ($hasta->lt($desde)) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'date_to' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);
    }

    $filas = $this->queryHistorialPasajero($desde, $hasta, $dni)
        ->orderBy('salida_id')
        ->orderBy('asiento_numero')
        ->orderBy('id')
        ->get()
        ->map(function ($pasaje) {
            $persona = $pasaje->persona;
            $nombre = trim($persona?->nombre_completo ?? '');

            if ($nombre === '') {
                $nombre = trim(implode(' ', array_filter([
                    $persona?->nombres,
                    $persona?->apellido_paterno,
                    $persona?->apellido_materno,
                ], fn ($valor) => $valor !== null && $valor !== '')));
            }

            $comprobante = collect([$pasaje->venta?->serie, $pasaje->venta?->numero])
                ->filter(fn ($valor) => $valor !== null && $valor !== '')
                ->map(fn ($valor) => $this->textoReportePasajes($valor))
                ->implode('-');

            return [
                'fecha' => $pasaje->venta?->created_at?->format('d/m/Y H:i') ?? '-',
                'comprobante' => $comprobante ?: '-',
                'pasajero' => $nombre ?: 'SIN PASAJERO',
                'documento' => $this->textoReportePasajes($persona?->documento) ?: '-',
                'ruta' => $this->textoReportePasajes($pasaje->salida?->horario?->ruta?->nombre) ?: '-',
                'origen' => $this->textoReportePasajes($pasaje->origen?->descripcion
                    ?? $pasaje->origen?->nombre) ?: '-',
                'destino' => $this->textoReportePasajes($pasaje->destino?->descripcion
                    ?? $pasaje->destino?->nombre) ?: '-',
                'asiento' => $this->textoReportePasajes($pasaje->asiento_numero),
                'vendedor' => $this->textoReportePasajes($pasaje->usuario?->persona?->nombre_completo
                    ?? $pasaje->usuario?->name) ?: 'SIN USUARIO',
                'estado' => $this->textoReportePasajes($pasaje->estado),
                'precio' => (float) ($pasaje->precio_cobrado ?? $pasaje->precio_pasaje ?? 0),
            ];
        });

    return [
        'filas' => $filas,
        'desde' => $desde,
        'hasta' => $hasta,
        'dni' => $dni,
        'cantidadPasajes' => $filas->count(),
        // Conserva la suma de todos los importes que mostraba tu PDF original.
        'totalImporte' => $filas->sum('precio'),
    ];
}

private function textoReportePasajes(mixed $valor): string
{
    return match (true) {
        $valor instanceof \BackedEnum => (string) $valor->value,
        $valor instanceof \UnitEnum => $valor->name,
        default => (string) ($valor ?? ''),
    };
}

public function historialPasajeroPdf(Request $request)
{
    $data = $this->obtenerDatosHistorialPasajero($request);
    $alcance = $data['dni'] !== null ? 'dni_' . $data['dni'] : 'todos';

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pasajeros.historial', $data)
        ->setPaper('a4', 'landscape')
        ->download('venta_pasajes_' . $alcance . '_'
            . $data['desde']->format('Ymd') . '_' . $data['hasta']->format('Ymd') . '.pdf');
}

public function historialPasajeroExcel(Request $request)
{
    $data = $this->obtenerDatosHistorialPasajero($request);
    $alcance = $data['dni'] !== null ? 'dni_' . $data['dni'] : 'todos';

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\VentaPasajesExport($data),
        'venta_pasajes_' . $alcance . '_'
            . $data['desde']->format('Ymd') . '_' . $data['hasta']->format('Ymd') . '.xlsx'
    );
}



}

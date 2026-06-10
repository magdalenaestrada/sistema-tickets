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
    // Página de filtros
    public function index()
    {
        $sucursales = Sucursal::where('estado', 'A')->get();
        $metodosPago = MetodoPago::all();
        $vehiculos = TipoVehiculo::all();
        $tipos_documento = TipoDocumentoFactura::all();
        $tipos_vehiculo = TipoVehiculo::all();
        $tipos_viaje = TipoVehiculo::all();
        $puntos = Pueblito::orderBy('descripcion')->get();
        return view('reportes.index', compact('sucursales', 'metodosPago', 'vehiculos', 'tipos_documento', 'tipos_vehiculo', 'tipos_viaje', 'puntos'));
    }

    public function datos($tipo, Request $request)
    {
        switch ($tipo) {
            case 'ventas':
                $query = Venta::with(['persona', 'usuario.persona', 'sucursal', 'tipoDocumentoFactura']);

                if ($request->fecha_inicio) {
                    $query->whereDate('fecha_emision', '>=', $request->fecha_inicio);
                }
                if ($request->fecha_fin) {
                    $query->whereDate('fecha_emision', '<=', $request->fecha_fin);
                }
                if ($request->tipo_documento) {
                    $query->where('tipo_documento_factura_id', $request->tipo_documento);
                }
                if ($request->cliente) {
                    $query->whereHas('persona', fn($q) => $q->where('nombres', 'like', "%{$request->cliente}%")
                        ->orWhere('apellidos', 'like', "%{$request->cliente}%"));
                }
                if ($request->vendedor) {
                    $query->whereHas('usuario.persona', fn($q) => $q->where('nombres', 'like', "%{$request->vendedor}%")
                        ->orWhere('apellidos', 'like', "%{$request->vendedor}%"));
                }
                if ($request->sucursal) {
                    $query->where('sucursal_id', $request->sucursal);
                }

                if ($request->estado) {
                    $query->where('estado', $request->estado);
                }
                return DataTables::of($query)
                    ->addColumn('fecha', fn($row) => optional($row->fecha_emision)->format('Y-m-d'))
                    ->addColumn('descripcion', fn($row) => $row->tipoDocumentoFactura->descripcion ?? $row->serie . '-' . $row->numero)
                    ->addColumn('vendedor', fn($row) => optional($row->usuario->persona)->nombres . ' ' . optional($row->usuario->persona)->apellidos)
                    ->addColumn('cliente', fn($row) => optional($row->persona)->nombres . ' ' . optional($row->persona)->apellidos)
                    ->addColumn('sucursal', fn($row) => optional($row->sucursal)->nombre_comercial)
                    ->addColumn('monto', fn($row) => $row->total)
                    ->rawColumns(['fecha', 'descripcion', 'cliente', 'vendedor', 'monto'])
                    ->make(true);

            case 'pasajeros':
                $query = Pasaje::query();
                return DataTables::of($query)->make(true);

            case 'cupones':
                $query = Descuento::query();
                return DataTables::of($query)->make(true);

            case 'encomiendas':
                $query = Encomienda::query();
                return DataTables::of($query)->make(true);


            case 'viajes':

                $query = Salida::with([
                    'horario.ruta.puntos.pueblito',
                    'horario.tipo_vehiculo',
                    'horario.tipo_viaje',
                    'vehiculo'
                ]);

                if ($request->fecha_inicio) {
                    $query->whereDate(
                        'fecha_salida',
                        '>=',
                        $request->fecha_inicio
                    );
                }

                if ($request->fecha_fin) {
                    $query->whereDate(
                        'fecha_salida',
                        '<=',
                        $request->fecha_fin
                    );
                }

                if ($request->tipo_vehiculo) {
                    $query->whereHas(
                        'horario',
                        fn($q) =>
                        $q->where(
                            'tipo_vehiculo_id',
                            $request->tipo_vehiculo
                        )
                    );
                }

                if ($request->tipo_viaje) {
                    $query->whereHas(
                        'horario',
                        fn($q) =>
                        $q->where(
                            'tipo_viaje_id',
                            $request->tipo_viaje
                        )
                    );
                }

                return DataTables::of($query)

                    ->addColumn('fecha', function ($row) {
                        return $row->fecha_formateada;
                    })

                    ->addColumn('hora', function ($row) {
                        return $row->hora_salida;
                    })

                    ->addColumn('tipo_vehiculo', function ($row) {
                        return $row->horario?->tipo_vehiculo?->descripcion;
                    })

                    ->addColumn('tipo_viaje', function ($row) {
                        return $row->horario?->tipo_viaje?->descripcion;
                    })

                    ->addColumn('origen', function ($row) {

                        $ruta = $row->horario?->ruta;

                        if (!$ruta) {
                            return '';
                        }

                        $primerPunto = $ruta->puntos
                            ->sortBy('orden')
                            ->first();

                        return $primerPunto?->pueblito?->descripcion ?? '';
                    })

                    ->addColumn('destino', function ($row) {

                        $ruta = $row->horario?->ruta;

                        if (!$ruta) {
                            return '';
                        }

                        $ultimoPunto = $ruta->puntos
                            ->sortByDesc('orden')
                            ->first();

                        return $ultimoPunto?->pueblito?->descripcion ?? '';
                    })

                    ->addColumn('costo', function ($row) {

                        return $row->horario?->costo_base ?? 0;
                    })

                    ->make(true);

            default:
                abort(404);
        }
    }

    public function generar(Request $request)
    {
        $tipo = $request->tipo;
        $filtros = [
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $request->estado ?? 'todos', // por defecto todos
        ];

        if ($tipo === 'ventas') {
            $query = Venta::with(['persona', 'usuario.persona', 'sucursal', 'tipoDocumentoFactura', 'pagos.metodoPago']);

            if ($request->fecha_inicio && $request->fecha_fin) {
                $query->whereBetween('fecha_emision', [$request->fecha_inicio, $request->fecha_fin]);
            }
            if ($request->sucursal) $query->where('sucursal_id', $request->sucursal);
            if ($request->tipo_documento) $query->where('tipo_documento_factura_id', $request->tipo_documento);
            if ($request->cliente) {
                $query->whereHas(
                    'persona',
                    fn($q) =>
                    $q->where('nombres', 'like', "%{$request->cliente}%")
                        ->orWhere('apellidos', 'like', "%{$request->cliente}%")
                );
            }
            if ($request->vendedor) {
                $query->whereHas(
                    'usuario.persona',
                    fn($q) =>
                    $q->where('nombres', 'like', "%{$request->vendedor}%")
                        ->orWhere('apellidos', 'like', "%{$request->vendedor}%")
                );
            }
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            $ventas = $query->get();

            // separar emitidos y anulados
            $emitidos = $ventas->where('estado', 'E');
            $anulados = $ventas->where('estado', 'A');
        }
        if ($tipo === 'viajes') {

            $query = Salida::with([
                'horario.ruta.puntos.pueblito',
                'horario.tipo_vehiculo',
                'horario.tipo_viaje',
                'vehiculo'
            ]);

            if ($request->fecha_inicio) {
                $query->whereDate(
                    'fecha_salida',
                    '>=',
                    $request->fecha_inicio
                );
            }

            if ($request->fecha_fin) {
                $query->whereDate(
                    'fecha_salida',
                    '<=',
                    $request->fecha_fin
                );
            }

            if ($request->tipo_vehiculo) {
                $query->whereHas(
                    'horario',
                    fn($q) =>
                    $q->where(
                        'tipo_vehiculo_id',
                        $request->tipo_vehiculo
                    )
                );
            }

            if ($request->tipo_viaje) {
                $query->whereHas(
                    'horario',
                    fn($q) =>
                    $q->where(
                        'tipo_viaje_id',
                        $request->tipo_viaje
                    )
                );
            }

            $salidas = $query->get();

            $pdf = FacadePdf::loadView(
                'reportes.pdf.viajes',
                compact('salidas', 'filtros')
            )->setPaper('a4', 'landscape');

            return $pdf->download('reporte_viajes.pdf');
        }

        $pdf = FacadePdf::loadView("reportes.pdf.$tipo", compact('emitidos', 'anulados', 'filtros'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("reporte_$tipo.pdf");
    }
}

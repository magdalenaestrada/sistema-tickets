<?php

namespace App\Http\Controllers;

use App\Enums\EstadoVenta;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\Empresa;
use App\Models\Encomienda;
use App\Models\NotaVentaAnulada;
use App\Models\NotaVentaAnuladaDetalle;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\SolicitudAnulacion;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\Facturacion\EmitirVentaService;
use App\Services\Facturacion\GreenterService;
use App\Services\Facturacion\VentaDocumentBuilder;
use App\Services\VentaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacturacionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Venta::with([
            'persona',
            'tipoDocumentoFactura',
        ]);

        // Fecha hasta
        if ($request->filled('fecha')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        // Tipo de comprobante
        if ($request->filled('tipo_documento_factura_id')) {
            $query->where(
                'tipo_documento_factura_id',
                $request->tipo_documento_factura_id
            );
        }

        // Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Buscar por documento
        if ($request->filled('documento')) {

            $buscar = strtoupper(trim($request->documento));

            if (str_contains($buscar, '-')) {

                [$serie, $numero] = explode('-', $buscar);

                $query->where('serie', $serie)
                    ->where('numero', $numero);
            } else {

                $query->where(function ($q) use ($buscar) {

                    $q->where('numero', 'like', "%{$buscar}%")
                        ->orWhereRaw("CONCAT(serie,'-',numero) LIKE ?", ["%{$buscar}%"]);
                });
            }
        }

        $ventas = $query
            ->orderByDesc('fecha_emision')
            ->paginate(20)
            ->withQueryString();

        $totalVentas = Venta::count();

        $emitidas = Venta::whereIn('estado', [
            EstadoVenta::EMITIDO
        ])->count();

        $pendientes = Venta::where('estado', EstadoVenta::GENERADO)->count();

        $rechazadas = Venta::where('estado', EstadoVenta::RECHAZADO)->count();

        $cajas = Caja::with('sucursal.serie')
            ->where('estado', 'A')
            ->when(
                !$user->hasRole('Administrador'),
                fn($q) => $q->where('usuario_id', $user->id)
            )
            ->get();
        $empresa = Empresa::first();
        $cajasAbiertas = Caja::with('usuario')
            ->where('estado', 'A')
            ->get();
        $porcentajeIgv = $empresa->igv;
        
        if ($user->hasRole('Administrador')) {
            $tiposDocumento = TipoDocumentoFactura::all();
        } else {
            $tiposDocumento = TipoDocumentoFactura::whereIn('id', [1, 2, 3])->get();
        }

        $personas = Persona::all();

        return view('facturacion.index', compact(
            'ventas',
            'totalVentas',
            'emitidas',
            'pendientes',
            'rechazadas',
            'tiposDocumento',
            'personas',
            'cajas',
            'empresa',
            'porcentajeIgv',
            'cajasAbiertas'
        ));
    }

    public function showSolicitud(SolicitudAnulacion $solicitud)
    {
        $solicitud->load([
            'venta.persona',
            'venta.tipoDocumentoFactura',
            'venta.detalles',
            'solicitante.persona',
            'aprobador.persona'
        ]);

        return view(
            'facturacion.solicitudes_show',
            compact('solicitud')
        );
    }

    public function solicitudes(Request $request)
    {
        $query = SolicitudAnulacion::with([
            'venta.persona',
            'venta.tipoDocumentoFactura',
            'solicitante.persona',
            'aprobador.persona'
        ]);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_solicitud', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_solicitud', '<=', $request->fecha_hasta);
        }

        if ($request->filled('documento')) {
            $query->whereHas('venta', function ($q) use ($request) {
                $q->whereRaw("CONCAT(serie,'-',numero) LIKE ?", [
                    "%{$request->documento}%"
                ]);
            });
        }

        $solicitudes = $query
            ->latest()
            ->paginate(20);

        return view(
            'facturacion.solicitudes',
            compact('solicitudes')
        );
    }

    public function show(Venta $venta)
    {
        $venta->load([
            'persona',
            'detalles',
            'sucursal',
            'tipoDocumentoFactura',
        ]);

        return view('facturacion.show', compact('venta'));
    }

    public function emitir(
        Venta $venta,
        EmitirVentaService $emitirVentaService
    ) {
        try {

            $resultado = $emitirVentaService->emitir($venta);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function descargarXml(Venta $venta)
    {
        if (!$venta->ruta_xml) {
            abort(404, 'XML no encontrado');
        }

        return Storage::disk('public')
            ->download($venta->ruta_xml);
    }

    public function solicitarAnulacion(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'motivo' => 'required|min:10'
        ]);

        SolicitudAnulacion::create([
            'venta_id' => $request->venta_id,
            'usuario_solicitante_id' => auth()->id(),
            'motivo' => $request->motivo,
            'fecha_solicitud' => now(),
            'estado' => 'Pendiente'
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function descargarCdr(Venta $venta)
    {
        if (!$venta->ruta_cdr) {
            abort(404, 'CDR no encontrado');
        }

        return Storage::disk('public')
            ->download($venta->ruta_cdr);
    }

    public function descargarPdf(Venta $venta)
    {
        if (!$venta->ruta_pdf) {
            abort(404, 'PDF no encontrado');
        }

        return Storage::disk('public')
            ->download($venta->ruta_pdf);
    }

    public function store(Request $request, VentaService $ventaService)
    {
        $items = json_decode($request->items, true);

        if (!$items || count($items) === 0) {
            return back()->with('error', 'Debe agregar items');
        }

        $empresa = Empresa::first();

        $porcentaje = $request->tipo_servicio_id == 1
            ? ($empresa->igv / 100)
            : ($empresa->igv_encomienda / 100);

        $detalles = [];
        $total = 0;

        foreach ($items as $item) {

            $tipoServicioItemId = (int) ($item['tipo_servicio_id'] ?? $request->tipo_servicio_id);
            $precio = (float) $item['precio'];
            $total += $precio;
            $detalles[] = [
                'tipo_servicio_id' => $tipoServicioItemId,
                'descripcion' => $item['descripcion'],
                'cantidad' => $item['cantidad'],
                'costo' => $precio,
                'descuento' => 0,
            ];
        }

        $data = new \Illuminate\Http\Request([
            'tipo_documento_factura_id' => $request->tipo_documento_factura_id,
            'tipo_servicio_id'          => $request->tipo_servicio_id,
            'numero_documento_id'       => $request->documento,
            'razon_social'              => trim($request->nombres . ' ' . $request->apellidos),
            'direccion'                 => $request->direccion,
            'correo'                    => $request->correo,
            'telefono'                  => $request->telefono,
            'celular'                   => $request->celular,
            'caja_id'                   => $request->caja_id,
            'total'                     => $total,
            'detalles'                  => $detalles,
        ]);

        $resultado = $ventaService->crearVenta(
            $data,
            null,   // referencia_type
            null    // referencia_id
        );

        $ventaService->emitirVenta($resultado['venta']);

        return redirect()
            ->route('facturacion.index')
            ->with('success', 'Venta generada y enviada a SUNAT');
    }

    public function anularVenta(Venta $venta)
    {
        return DB::transaction(function () use ($venta) {

            $puedeAnularConResumen = false;
            $anularConCredito = false;
            if ($venta->tipoDocumentoFactura->codigo === '01') {
                $puedeAnularConResumen = Carbon::parse($venta->fecha_emision)->diffInDays(now()) <= 2;
                if (!$puedeAnularConResumen) {
                    $anularConCredito = true;
                }
            } else if ($venta->tipoDocumentoFactura->codigo === '03') {
                $puedeAnularConResumen = Carbon::parse($venta->fecha_emision)->diffInDays(now()) <= 7;
                if (!$puedeAnularConResumen) {
                    $anularConCredito = true;
                }
            } else if ($venta->tipoDocumentoFactura->codigo === '07') {
                if (str_starts_with($venta->serie, "B")) {
                    $puedeAnularConResumen = Carbon::parse($venta->fecha_emision)->diffInDays(now()) <= 2;
                    if (!$puedeAnularConResumen) {
                        throw new Exception("No se puede anular la NOTA DE CREDITO, han pasado mas de 2 dias");
                    }
                } else if (str_starts_with($venta->serie, "F")) {
                    $puedeAnularConResumen = Carbon::parse($venta->fecha_emision)->diffInDays(now()) <= 7;
                    if (!$puedeAnularConResumen) {
                        throw new Exception("No se puede anular la NOTA DE CREDITO, han pasado mas de 7 dias");
                    }
                }
            }
            $result = null;
            $metodo = 'Error';
            if ($puedeAnularConResumen) {
                // anular todo lo que sea ... boleta, factura, nota de credito ...
                $result = app(VentaService::class)->anularVentaDirecta($venta);
                $metodo = 'Anulación directa';
            } else if ($anularConCredito) {
                // elegir si es nota de credito de boleta o factura
                $ventaOGEstatus = mb_substr($venta->serie, 0, 1) === 'B' ? 4 : 7;
                //anular la venta con nota de credito
                $tipoNC = TipoDocumentoFactura::find($ventaOGEstatus);

                $comprobanteNC = app(VentaService::class)->reservarSerieYNumero(
                    (int) $tipoNC->id,
                    (int) $venta->sucursal_id
                );
                if (!$comprobanteNC['serie'] || !$comprobanteNC['numero']) {
                    throw new Exception("No se pudo obtener la serie para la nota de crédito");
                }
                $nc = new Venta();
                $nc->tipo_documento_factura_id = $tipoNC->id;
                $nc->sucursal_id = $venta->sucursal_id;
                $nc->persona_id = $venta->persona_id;
                $nc->tipo_servicio_id = $venta->tipo_servicio_id;
                $nc->venta_referencia_id = $venta->id;
                $nc->serie = $comprobanteNC['serie'];
                $nc->numero = $comprobanteNC['numero'];
                $nc->usuario_id = auth()->id();
                $nc->documento_referencia = $venta->serie . '-' . $venta->numero;
                // $nc->tipo_documento_referencia = $venta->tipoDocumentoFactura->codigo;
                $nc->subtotal = $venta->subtotal;
                $nc->impuesto = $venta->impuesto;
                $nc->total = $venta->total;

                $nc->estado = EstadoVenta::GENERADO;
                $nc->fecha_emision = now();
                $nc->observacion = 'ANULACION DE OPERACION';

                $nc->save();
                $nc->load([
                    'tipoDocumentoFactura',
                    'persona.tipoDocumento',
                    'sucursal.empresa',
                    'detalles'
                ]);
                foreach ($venta->detalles as $d) {
                    $nc->detalles()->create([
                        'descripcion' => $d->descripcion,
                        'tipo_servicio_id' => $d->tipo_servicio_id,
                        'descuento' => $d->descuento,
                        'cantidad' => $d->cantidad,
                        'precio_venta' => $d->precio_venta,
                        'total' => -$d->total,
                        'codigo' => $d->codigo,
                        'unidad' => $d->unidad,
                        'valor_unitario' => $d->valor_unitario,
                        'precio_unitario' => $d->precio_unitario,
                        'base_igv' => $d->base_igv,
                        'porcentaje_igv' => $d->porcentaje_igv,
                        'igv' => $d->igv,
                        'valor_venta' => $d->valor_venta,
                        'tipo_afectacion_igv' => $d->tipo_afectacion_igv,
                    ]);
                }

                $nc->load([
                    'tipoDocumentoFactura',
                    'persona.tipoDocumento',
                    'sucursal.empresa',
                    'detalles'
                ]);

                $result = app(VentaService::class)->anularVentaSunat($nc, $venta);

                $metodo = "Anulación por medio de Nota de crédito: {$comprobanteNC['serie']}-{$comprobanteNC['numero']}";
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta anulada correctamente con ' . $metodo
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la venta'
            ], 500);
        });
    }

    public function aprobarAnulacion(Request $request, SolicitudAnulacion $solicitud)
    {
        $respuesta = $this->crearNotaAnulacion(
            $solicitud->venta,
            $request
        );

        $data = $respuesta->getData(true);

        if (!$data['success']) {
            return $respuesta;
        }

        $solicitud->update([
            'estado' => 'Aprobada',
            'usuario_aprobador_id' => auth()->id(),
            'fecha_respuesta' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada y venta anulada correctamente.',
        ]);
    }


    public function ticket(Venta $venta)
    {
        $venta->load([
            'persona',
            'usuario.persona',
            'detalles',
            'sucursal.empresa',
            'tipoDocumentoFactura',

            'pasajes.salida',
            'pasajes.origen',
            'pasajes.destino',
            'pasajes.descuento',
            'pasajes.sobreEquipajes.encomienda.detalles',
            'pasajes.sobreEquipajes.encomienda.emisor',
            'pasajes.sobreEquipajes.encomienda.origenPueblito',
            'pasajes.sobreEquipajes.encomienda.destinoPueblito',
        ]);

        if ($venta->pasajes->count() > 0) {
            return view('tickets.pasaje', compact('venta'));
        }

        switch ($venta->tipo_servicio_id) {

            case 1:
                return view('tickets.pasaje', compact('venta'));

            case 2:
                return view('tickets.encomienda', compact('venta'));

            case 3:
                return view('tickets.sobreequipaje', compact('venta'));

            default:
                return view('tickets.venta', compact('venta'));
        }
    }

    public function crearNotaAnulacion(Venta $venta, Request $request)
    {
        $sumaDevoluciones = collect($request->devoluciones)->sum('total');

        if (abs($sumaDevoluciones - $venta->total) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'La suma de las devoluciones no coincide con el total de la venta.',
            ], 422);
        }
        foreach ($venta->detalles as $detalle) {

            if (!$detalle->referencia_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular la venta porque un detalle no tiene servicio asociado.',
                ], 422);
            }
        }
        try {
            $anulacion = DB::transaction(function () use ($venta, $request) {

                $anulacion = NotaVentaAnulada::create([
                    'venta_id'   => $venta->id,
                    'usuario_id' => Auth::id(),
                    'fecha'      => now(),
                    'total'      => $venta->total,
                    'motivo'     => $request->motivo,
                    'estado'     => 'A',
                ]);

                foreach ($venta->detalles as $detalle) {

                    NotaVentaAnuladaDetalle::create([
                        'anulacion_id'     => $anulacion->id,
                        'venta_detalle_id' => $detalle->id,
                        'cantidad'         => $detalle->cantidad,
                        'precio_unitario'  => $detalle->precio_unitario,
                        'subtotal'         => $detalle->total,
                    ]);

                    if ($detalle->referencia) {
                        $detalle->referencia->update([
                            'estado' => 'X',
                        ]);
                    }
                }

                $venta->update([
                    'estado' => EstadoVenta::ANULADO,
                    'fecha_anulacion' => now(),
                ]);

                foreach ($request->devoluciones as $pago) {

                    CajaDetalle::create([
                        'caja_id'                    => $request->caja_anulacion_id,
                        'subtipo_movimiento_caja_id' => 35,
                        'metodo_pago_id'             => $pago['metodo_pago_id'],
                        'billetera_digital_id'       => $pago['billetera_id'] ?? null,
                        'table_name'                 => NotaVentaAnulada::class,
                        'table_id'                   => $anulacion->id,
                        'amount'                     => -abs($pago['total']),
                        'description'                => 'Anulación Nota Venta ' . $venta->serie . '-' . $venta->numero,
                        'anulado'                    => false,
                    ]);
                }

                return $anulacion;
            });

            return response()->json([
                'success' => true,
                'message' => 'La nota de venta fue anulada correctamente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al anular la nota: ' . $e->getMessage(),
            ], 500);
        }
    }
}

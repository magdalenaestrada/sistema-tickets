<?php

namespace App\Http\Controllers;

use App\Enums\EstadoVenta;
use App\Models\Empresa;
use App\Models\Persona;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacturacionController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with([
            'persona',
            'tipoDocumentoFactura',
        ])
            ->orderByDesc('fecha_emision')
            ->paginate(20);

        $totalVentas = Venta::count();

        $emitidas = Venta::whereIn('estado', [
            EstadoVenta::EMITIDO
        ])->count();

        $pendientes = Venta::where('estado', EstadoVenta::GENERADO)->count();

        $rechazadas = Venta::where('estado', EstadoVenta::RECHAZADO)->count();

        $sucursales = Sucursal::with('serie')->get();
        $empresa = Empresa::first();
        $porcentajeIgv = $empresa->igv;
        $tiposDocumento = TipoDocumentoFactura::all();
        $personas = Persona::all();

        return view('facturacion.index', compact(
            'ventas',
            'totalVentas',
            'emitidas',
            'pendientes',
            'rechazadas',
            'tiposDocumento',
            'personas',
            'sucursales',
            'empresa',
            'porcentajeIgv'
        ));
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
    /**
     * QUE SE SUPONE QUE ES ESTO!!!
     */
    public function store(Request $request, EmitirVentaService $service)
    {
        $items = json_decode($request->items, true);

        if (!$items || count($items) === 0) {
            return back()->with('error', 'Debe agregar items');
        }
        DB::transaction(function () use ($request, $items, $service) {
            $empresa = Empresa::first();
            $porcentaje = $empresa->igv / 100;
            $subtotal = collect($items)->sum('precio');
            $igv = $subtotal * $porcentaje;
            $total = $subtotal + $igv;

            $persona = Persona::updateOrCreate(
                ['documento' => $request->documento],
                [
                    'tipo_documento_id' => strlen($request->documento) === 8 ? 1 : 2,
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'celular' => $request->celular,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'correo' => $request->correo,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $venta = Venta::create([
                'sucursal_id' => $request->sucursal_id,
                'tipo_documento_factura_id' => $request->tipo_documento_factura_id,
                'persona_id' => $persona->id,
                'fecha_emision' => now(),

                'subtotal_sin_igv' => $subtotal,
                'subtotal' => $subtotal,
                'impuesto' => $igv,
                'total' => $total,

                'estado' => 'GENERADO',
            ]);

            foreach ($items as $item) {

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'descripcion' => $item['descripcion'],

                    'cantidad' => 1,
                    'valor_unitario' => $item['precio'],
                    'precio_unitario' => $item['precio'],

                    'valor_venta' => $item['precio'],
                    'base_igv' => $item['precio'],
                    'igv' => $item['precio'] * 0.18,
                    'tipo_afectacion_igv' => '10',
                ]);
            }

            $service->emitir($venta);
        });

        return redirect()
            ->route('facturacion.index')
            ->with('success', 'Venta generada y enviada a SUNAT');
    }

    public function anularNotaVenta(Request $request, VentaService $service)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'motivo' => 'required|string',
            'detalles' => 'required|array|min:1',
            'pagos' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $service->registrarAnulacion($request);
        });

        return response()->json([
            'success' => true,
            'message' => 'Devolución registrada correctamente.'
        ]);
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
}

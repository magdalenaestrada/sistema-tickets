<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\TipoDocumentoFactura;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\Facturacion\EmitirVentaService;
use App\Services\Facturacion\GreenterService;
use App\Services\Facturacion\VentaDocumentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacturacionController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with([
            'persona',
            'tipoDocumentoFactura'
        ])
            ->orderByDesc('fecha_emision')
            ->paginate(20);
        $tiposDocumento = TipoDocumentoFactura::all();
        $personas = Persona::all();
        return view('facturacion.index', compact('ventas', 'tiposDocumento', 'personas'));
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

    public function store(Request $request, EmitirVentaService $service)
    {
        $items = json_decode($request->items, true);

        if (!$items || count($items) === 0) {
            return back()->with('error', 'Debe agregar items');
        }

        DB::transaction(function () use ($request, $items, $service) {

            $subtotal = collect($items)->sum('precio');
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;

            $venta = Venta::create([
                'tipo_documento_factura_id' => $request->tipo_documento_factura_id,
                'persona_id' => $request->persona_id,
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

    public function anularVenta(Venta $venta)
    {

        return DB::transaction(function () use ($venta) {

            $empresa = $venta->sucursal->empresa;

            $nc = new Venta();
            $tipoNC = TipoDocumentoFactura::where('codigo', '07')->first();

            $nc->tipo_documento_factura_id = $tipoNC->id;

            $nc->sucursal_id = $venta->sucursal_id;
            $nc->persona_id = $venta->persona_id;
            $nc->tipo_servicio_id = $venta->tipo_servicio_id;
            $nc->serie = $venta->serie;
            $ultimo = Venta::where('tipo_documento_factura_id', $tipoNC->id)->max('numero') ?? 0;

            $nc->numero = str_pad($ultimo + 1, 8, '0', STR_PAD_LEFT);
            $nc->usuario_id = auth()->id();
            $nc->documento_referencia = $venta->serie . '-' . $venta->numero;

            $nc->subtotal = $venta->subtotal;
            $nc->impuesto = $venta->impuesto;
            $nc->total = $venta->total;

            $nc->estado = 'GENERADA';
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

            dd($nc);
            $documento = app(VentaDocumentBuilder::class)->build($nc);
            $see = app(GreenterService::class)->getSee($empresa);

            $result = $see->send($documento);

            $folder = 'xml/' . now()->format('Y-m-d');

            Storage::disk('public')->put(
                "{$folder}/{$documento->getName()}.xml",
                $see->getFactory()->getLastXml()
            );

            $nc->ruta_xml = "{$folder}/{$documento->getName()}.xml";

            if (!$result->isSuccess()) {

                $nc->estado = 'RECHAZADA';
                $nc->observacion = $result->getError()->getMessage();
                $nc->save();

                return response()->json([
                    'success' => false,
                    'message' => $result->getError()->getMessage()
                ], 500);
            }

            $cdr = $result->getCdrResponse();

            Storage::disk('public')->put(
                "{$folder}/R-{$documento->getName()}.zip",
                $result->getCdrZip()
            );

            $nc->ruta_cdr = "{$folder}/R-{$documento->getName()}.zip";

            $nc->estado = ((int) $cdr->getCode() === 0)
                ? 'ACEPTADA'
                : 'RECHAZADA';

            $venta->estado = 'ANULADA';
            $venta->save();

            return response()->json([
                'success' => true,
                'message' => 'Venta anulada correctamente'
            ]);
        });
    }
}

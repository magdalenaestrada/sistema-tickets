<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\TipoDocumentoFactura;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\Facturacion\EmitirVentaService;
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

    public function store(Request $request)
    {
        $items = json_decode($request->items, true);

        if (!$items || count($items) === 0) {
            return back()->with('error', 'Debe agregar items');
        }

        DB::transaction(function () use ($request, $items) {

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

            app(\App\Services\Facturacion\EmitirVentaService::class)
                ->emitir($venta);
        });

        return redirect()
            ->route('facturacion.index')
            ->with('success', 'Venta generada y enviada a SUNAT');
    }
}

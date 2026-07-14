<?php

namespace App\Services\Facturacion;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmitirVentaService
{
    public function __construct(
        protected GreenterService $greenterService,
        protected VentaDocumentBuilder $builder
    ) {}

    public function emitir(Venta $venta): array
    {
        return DB::transaction(function () use ($venta) {
            $venta->loadMissing([
                'detalles',
                'persona',
                'sucursal.empresa',
                'tipoDocumentoFactura',
            ]);

            $empresa = $venta->sucursal->empresa;
            $documento = $this->builder->build($venta);
            $tipoDoc = $this->resolverTipoDocumento($venta);

            $see = $this->greenterService->getSee($empresa);

            if ($tipoDoc === '03' || ($tipoDoc === '07' && $this->notaDeBoleta($venta))) {
                return $this->guardarPendienteResumen($see, $documento, $venta);
            }

            return $this->enviarDirecto($see, $documento, $venta);
        });
    }

    protected function enviarDirecto($see, $documento, Venta $venta): array
    {
        $result = $see->send($documento);

        $folder = 'xml/' . now()->format('d-m-Y');
        $xmlPath = "{$folder}/{$documento->getName()}.xml";
        $cdrPath = "{$folder}/R-{$documento->getName()}.zip";

        Storage::disk('public')->put($xmlPath, $see->getFactory()->getLastXml());

        $venta->ruta_xml = $xmlPath;

        if (!$result->isSuccess()) {
            $venta->estado = 'ERROR';
            $venta->observacion = $result->getError()->getMessage();
            $venta->save();

            return [
                'ok' => false,
                'estado' => 'ERROR',
                'mensaje' => $result->getError()->getMessage(),
            ];
        }

        Storage::disk('public')->put($cdrPath, $result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $venta->ruta_cdr = $cdrPath;
        $venta->hash = method_exists($documento, 'getHash') ? $documento->getHash() : null;
        $venta->estado = ((int) $cdr->getCode() === 0) ? 'EMITIDO' : 'RECHAZADA';
        $venta->observacion = $cdr->getDescription();
        $venta->save();

        return [
            'ok' => true,
            'estado' => $venta->estado,
            'codigo' => $cdr->getCode(),
            'descripcion' => $cdr->getDescription(),
            'notas' => $cdr->getNotes(),
            'xml' => $venta->ruta_xml,
            'cdr' => $venta->ruta_cdr,
        ];
    }

    protected function guardarPendienteResumen($see, $documento, Venta $venta): array
    {
        $folder = 'xml/' . now()->format('d-m-Y');
        $xmlPath = "{$folder}/{$documento->getName()}.xml";

        $xml = $see->getXmlSigned($documento);
        Storage::disk('public')->put($xmlPath, $xml);

        $venta->ruta_xml = $xmlPath;
        $venta->estado = 'PENDIENTE_RESUMEN';
        $venta->observacion = 'Documento generado. Falta enviarlo en resumen diario.';
        $venta->save();

        return [
            'ok' => true,
            'estado' => 'PENDIENTE_RESUMEN',
            'xml' => $venta->ruta_xml,
        ];
    }

    protected function resolverTipoDocumento(Venta $venta): string
    {
        return $venta->tipoDocumentoFactura->codigo_sunat;
    }

    protected function notaDeBoleta(Venta $venta): bool
    {
        return str_starts_with((string) $venta->documento_referencia, 'B');
    }
}

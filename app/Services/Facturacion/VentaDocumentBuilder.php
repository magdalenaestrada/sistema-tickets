<?php

namespace App\Services\Facturacion;

use App\Models\Venta;
use DateTime;
use Greenter\Model\Client\Address as ClientAddress;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Note\NoteDetail;
use Luecano\NumeroALetras\NumeroALetras;

class VentaDocumentBuilder
{
    public function build(Venta $venta)
    {
        $empresa = $venta->sucursal->empresa;
        $persona = $venta->persona;
        $tipoDoc = $this->resolverTipoDocumento($venta);

        if ($tipoDoc === '07') {
            return $this->buildNotaCredito($venta, $empresa, $persona);
        }

        return $this->buildInvoice($venta, $empresa, $persona, $tipoDoc);
    }

    protected function buildInvoice(Venta $venta, $empresa, $persona, string $tipoDoc): Invoice
    {
        $client = (new Client())
            ->setTipoDoc($this->resolverTipoDocCliente($persona))
            ->setNumDoc($persona->documento ?? '-')
            ->setRznSocial($persona->nombre_facturacion ?? 'CLIENTE VARIOS');

        $address = (new Address())
            ->setUbigueo($venta->sucursal->ubigueo ?? $empresa->ubigueo ?? '150101')
            ->setDepartamento($venta->sucursal->departamento ?? $empresa->departamento ?? 'LIMA')
            ->setProvincia($venta->sucursal->provincia ?? $empresa->provincia ?? 'LIMA')
            ->setDistrito($venta->sucursal->distrito ?? $empresa->distrito ?? 'LIMA')
            ->setUrbanizacion($venta->sucursal->urbanizacion ?? $empresa->urbanizacion ?? '-')
            ->setDireccion($venta->sucursal->direccion ?? $empresa->direccion)
            ->setCodLocal($venta->sucursal->cod_local ?? $empresa->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDoc)
            ->setSerie($venta->serie)
            ->setCorrelativo((string) $venta->numero)
            ->setFechaEmision($venta->fecha_emision instanceof \DateTimeInterface
                ? $venta->fecha_emision
                : new DateTime($venta->fecha_emision))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas((float) ($venta->subtotal_sin_igv ?? $venta->subtotal ?? 0))
            ->setMtoIGV((float) ($venta->impuesto ?? 0))
            ->setTotalImpuestos((float) ($venta->impuesto ?? 0))
            ->setValorVenta((float) ($venta->subtotal_sin_igv ?? $venta->subtotal ?? 0))
            ->setSubTotal((float) ($venta->subtotal ?? $venta->total ?? 0))
            ->setMtoImpVenta((float) ($venta->total ?? 0));

        $invoice->setDetails($this->buildDetallesVenta($venta))
            ->setLegends([$this->buildLeyenda($venta)]);

        return $invoice;
    }

    protected function buildNotaCredito(Venta $venta, $empresa, $persona): Note
    {
        $client = (new Client())
            ->setTipoDoc($this->resolverTipoDocCliente($persona))
            ->setNumDoc($persona->documento ?? '-')
            ->setRznSocial($persona->nombre_facturacion ?? 'CLIENTE VARIOS');

        $address = (new Address())
            ->setUbigueo($venta->sucursal->ubigueo ?? $empresa->ubigueo ?? '150101')
            ->setDepartamento($venta->sucursal->departamento ?? $empresa->departamento ?? 'LIMA')
            ->setProvincia($venta->sucursal->provincia ?? $empresa->provincia ?? 'LIMA')
            ->setDistrito($venta->sucursal->distrito ?? $empresa->distrito ?? 'LIMA')
            ->setUrbanizacion($venta->sucursal->urbanizacion ?? $empresa->urbanizacion ?? '-')
            ->setDireccion($venta->sucursal->direccion ?? $empresa->direccion)
            ->setCodLocal($venta->sucursal->cod_local ?? $empresa->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);

        $note = (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($venta->serie)
            ->setCorrelativo((string) $venta->numero)
            ->setFechaEmision($venta->fecha_emision instanceof \DateTimeInterface
                ? $venta->fecha_emision
                : new DateTime($venta->fecha_emision))
            ->setTipDocAfectado($this->resolverDocumentoAfectadoTipo($venta))
            ->setNumDocfectado($venta->documento_referencia)
            ->setCodMotivo('01')
            ->setDesMotivo($venta->observacion ?: 'ANULACION DE LA OPERACION')
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas((float) ($venta->subtotal_sin_igv ?? $venta->subtotal ?? 0))
            ->setMtoIGV((float) ($venta->impuesto ?? 0))
            ->setTotalImpuestos((float) ($venta->impuesto ?? 0))
            ->setValorVenta((float) ($venta->subtotal_sin_igv ?? $venta->subtotal ?? 0))
            ->setSubTotal((float) ($venta->subtotal ?? $venta->total ?? 0))
            ->setMtoImpVenta((float) ($venta->total ?? 0));

        $details = [];
        foreach ($venta->detalles as $d) {
            $details[] = (new NoteDetail())
                ->setCodProducto($d->codigo ?? 'ITEM')
                ->setUnidad($d->unidad ?? 'NIU')
                ->setCantidad((float) ($d->cantidad ?? 1))
                ->setMtoValorUnitario((float) ($d->valor_unitario ?? 0))
                ->setDescripcion($d->descripcion ?? 'ITEM')
                ->setMtoBaseIgv((float) ($d->base_igv ?? 0))
                ->setPorcentajeIgv((float) ($d->porcentaje_igv ?? 18))
                ->setIgv((float) ($d->igv ?? 0))
                ->setTipAfeIgv($d->tipo_afectacion_igv ?? '10')
                ->setTotalImpuestos((float) ($d->igv ?? 0))
                ->setMtoValorVenta((float) ($d->valor_venta ?? 0))
                ->setMtoPrecioUnitario((float) ($d->precio_unitario ?? 0));
        }

        $note->setDetails($details)
            ->setLegends([$this->buildLeyenda($venta)]);

        return $note;
    }

    protected function buildDetallesVenta(Venta $venta): array
    {
        $detalles = [];

        foreach ($venta->detalles as $d) {
            $detalles[] = (new SaleDetail())
                ->setCodProducto($d->codigo ?? 'ITEM')
                ->setUnidad($d->unidad ?? 'NIU')
                ->setCantidad((float) ($d->cantidad ?? 1))
                ->setMtoValorUnitario((float) ($d->valor_unitario ?? 0))
                ->setDescripcion($d->descripcion ?? 'ITEM')
                ->setMtoBaseIgv((float) ($d->base_igv ?? 0))
                ->setPorcentajeIgv((float) ($d->porcentaje_igv ?? 18))
                ->setIgv((float) ($d->igv ?? 0))
                ->setTipAfeIgv($d->tipo_afectacion_igv ?? '10')
                ->setTotalImpuestos((float) ($d->igv ?? 0))
                ->setMtoValorVenta((float) ($d->valor_venta ?? 0))
                ->setMtoPrecioUnitario((float) ($d->precio_unitario ?? 0));
        }

        return $detalles;
    }

    protected function buildLeyenda(Venta $venta): Legend
    {
        $formatter = new NumeroALetras();

        return (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice((float) $venta->total, 2, 'SOLES'));
    }

    protected function resolverTipoDocumento(Venta $venta): string
    {
        if (!empty($venta->tipoDocumentoFactura?->codigo)) {
            return $venta->tipoDocumentoFactura->codigo;
        }

        $nombre = strtolower($venta->tipoDocumentoFactura->nombre ?? '');

        return match (true) {
            str_contains($nombre, 'factura') => '01',
            str_contains($nombre, 'boleta') => '03',
            str_contains($nombre, 'nota') && str_contains($nombre, 'credito') => '07',
            default => '01',
        };
    }

    protected function resolverTipoDocCliente($persona): string
    {
        $tipo = $persona->tipo_documento_id ?? '';

        return match ((string) $tipo) {
            '1', 'DNI', 'dni' => '1',
            '6', 'RUC', 'ruc' => '6',
            '4', 'CE', 'ce' => '4',
            '7', 'PASAPORTE', 'pasaporte' => '7',
            default => '0',
        };
    }

    protected function resolverDocumentoAfectadoTipo(Venta $venta): string
    {
        if (!$venta->documento_referencia) {
            return '01';
        }

        return str_starts_with($venta->documento_referencia, 'B') ? '03' : '01';
    }
}

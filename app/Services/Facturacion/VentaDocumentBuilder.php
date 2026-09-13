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
use Greenter\Model\Sale\NoteLine;
use Luecano\NumeroALetras\NumeroALetras;

class VentaDocumentBuilder
{
    public function build(Venta $venta)
    {
        $empresa = $venta->sucursal->empresa;
        $persona = $venta->persona;
        $tipoDoc = $this->resolverTipoDocumento($venta);

        if ($tipoDoc === '07' || $venta->tipo_documento_factura_id == 7) {
            return $this->buildNotaCredito($venta, $empresa, $persona);
        }

        return $this->buildInvoice($venta, $empresa, $persona, $tipoDoc);
    }

    protected function buildInvoice(Venta $venta, $empresa, $persona, string $tipoDoc): Invoice
    {
        $venta->loadMissing([
            'sucursal.distrito.provincia.departamento'
        ]);

        $distrito = $venta->sucursal->distrito;
        $provincia = $distrito->provincia;
        $departamento = $provincia->departamento;
        $client = (new Client())
            ->setTipoDoc($this->resolverTipoDocCliente($persona))
            ->setNumDoc($persona->documento ?? '-')
            ->setRznSocial($persona->nombre_facturacion ?? 'CLIENTE VARIOS');

        $address = (new Address())
            ->setUbigueo($distrito->codigo_ubigeo)
            ->setDepartamento($departamento->nombre)
            ->setProvincia($provincia->nombre)
            ->setDistrito($distrito->nombre)
            ->setDireccion($venta->sucursal->direccion)
            ->setCodLocal('0000');

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
            ->setCorrelativo(str_pad($venta->numero, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($venta->fecha_emision instanceof \DateTimeInterface
                ? $venta->fecha_emision
                : new DateTime($venta->fecha_emision))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client);

        $total = abs((float) ($venta->total ?? 0));

        if ((int) $venta->tipo_servicio_id === 1) {

            $invoice
                ->setMtoOperExoneradas($total)
                ->setMtoOperGravadas(0)
                ->setMtoIGV(0)
                ->setTotalImpuestos(0)
                ->setValorVenta($total)
                ->setSubTotal($total)
                ->setMtoImpVenta($total);
        } else {

            $subtotal = abs((float) ($venta->subtotal_sin_igv ?? $venta->subtotal ?? 0));
            $igv = abs((float) ($venta->impuesto ?? 0));

            $invoice
                ->setMtoOperExoneradas(0)
                ->setMtoOperGravadas($subtotal)
                ->setMtoIGV($igv)
                ->setTotalImpuestos($igv)
                ->setValorVenta($subtotal)
                ->setSubTotal(abs((float) ($venta->subtotal ?? $venta->total ?? 0)))
                ->setMtoImpVenta($total);
        }



        $invoice->setDetails($this->buildDetallesVenta($venta))
            ->setLegends([$this->buildLeyenda($venta)]);

        return $invoice;
    }

    protected function buildNotaCredito(Venta $venta, $empresa, $persona): Note
    {
        $venta->loadMissing([
            'sucursal.distrito.provincia.departamento',
            'detalles'
        ]);

        $distrito = $venta->sucursal->distrito;
        $provincia = $distrito->provincia;
        $departamento = $provincia->departamento;

        $client = (new Client())
            ->setTipoDoc($this->resolverTipoDocCliente($persona))
            ->setNumDoc($persona->documento ?? '-')
            ->setRznSocial($persona->nombre_facturacion ?? 'CLIENTE VARIOS');

        $address = (new Address())
            ->setUbigueo($distrito->codigo_ubigeo)
            ->setDepartamento($departamento->nombre)
            ->setProvincia($provincia->nombre)
            ->setDistrito($distrito->nombre)
            ->setDireccion($venta->sucursal->direccion)
            ->setCodLocal('0000');

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
            ->setFechaEmision(
                $venta->fecha_emision instanceof \DateTimeInterface
                    ? $venta->fecha_emision
                    : new DateTime($venta->fecha_emision)
            )
            ->setTipDocAfectado($venta->tipo_documento_referencia)
            ->setNumDocfectado($venta->documento_referencia)
            ->setCodMotivo('01')
            ->setDesMotivo(
                $venta->observacion ?: 'ANULACION DE LA OPERACION'
            )
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client);


        $esPasaje = (int) $venta->tipo_servicio_id === 1;

        $total = abs((float) ($venta->total ?? 0));

        if ($esPasaje) {

            // PASAJE = EXONERADO
            $note
                ->setMtoOperExoneradas($total)
                ->setMtoOperGravadas(0)
                ->setMtoIGV(0)
                ->setTotalImpuestos(0)
                ->setValorVenta($total)
                ->setSubTotal($total)
                ->setMtoImpVenta($total);
        } else {

            // OPERACIÓN GRAVADA
            $subtotal = abs((float) (
                $venta->subtotal_sin_igv
                ?? $venta->subtotal
                ?? 0
            ));

            $igv = abs((float) ($venta->impuesto ?? 0));

            $note
                ->setMtoOperExoneradas(0)
                ->setMtoOperGravadas($subtotal)
                ->setMtoIGV($igv)
                ->setTotalImpuestos($igv)
                ->setValorVenta($subtotal)
                ->setSubTotal(
                    abs((float) ($venta->subtotal ?? $venta->total ?? 0))
                )
                ->setMtoImpVenta($total);
        }


        if ($venta->detalles->isEmpty()) {
            throw new \Exception(
                'No hay detalles en la venta para generar la nota de crédito SUNAT'
            );
        }

        $details = [];

        foreach ($venta->detalles as $d) {

            $cantidad = abs((float) ($d->cantidad ?? 1));

            if ($esPasaje) {

                $precio = abs((float) (
                    $d->precio_unitario
                    ?? $d->valor_unitario
                    ?? 0
                ));

                $valorVenta = round($precio * $cantidad, 2);

                $details[] = (new SaleDetail())
                    ->setCodProducto($d->codigo ?? 'ITEM')
                    ->setUnidad($d->unidad ?? 'NIU')
                    ->setCantidad($cantidad)
                    ->setDescripcion($d->descripcion ?? 'PASAJE')

                    ->setMtoValorUnitario($precio)
                    ->setMtoValorVenta($valorVenta)

                    ->setMtoBaseIgv($valorVenta)
                    ->setPorcentajeIgv(0)
                    ->setIgv(0)

                    // 20 = EXONERADO
                    ->setTipAfeIgv('20')

                    ->setTotalImpuestos(0)
                    ->setMtoPrecioUnitario($precio);

                continue;
            }

            $details[] = (new SaleDetail())
                ->setCodProducto($d->codigo ?? 'ITEM')
                ->setUnidad($d->unidad ?? 'NIU')
                ->setCantidad($cantidad)
                ->setDescripcion($d->descripcion ?? 'ITEM')

                ->setMtoValorUnitario(
                    abs((float) ($d->valor_unitario ?? 0))
                )

                ->setMtoBaseIgv(
                    abs((float) ($d->base_igv ?? 0))
                )

                ->setPorcentajeIgv(
                    (float) ($d->porcentaje_igv ?? 18)
                )

                ->setIgv(
                    abs((float) ($d->igv ?? 0))
                )

                ->setTipAfeIgv(
                    $d->tipo_afectacion_igv ?? '10'
                )

                ->setTotalImpuestos(
                    abs((float) ($d->igv ?? 0))
                )

                ->setMtoValorVenta(
                    abs((float) ($d->valor_venta ?? 0))
                )

                ->setMtoPrecioUnitario(
                    abs((float) ($d->precio_unitario ?? 0))
                );
        }

        $note
            ->setDetails($details)
            ->setLegends([
                $this->buildLeyenda($venta)
            ]);

        return $note;
    }
    protected function buildDetallesVenta(Venta $venta): array
    {
        $detalles = [];

        $esPasaje = (int) $venta->tipo_servicio_id === 1;

        foreach ($venta->detalles as $d) {

            if ($esPasaje) {

                $precio = abs((float) ($d->precio_unitario ?? $d->valor_unitario ?? 0));
                $cantidad = abs((float) ($d->cantidad ?? 1));

                $valorVenta = round($precio * $cantidad, 2);

                $detalles[] = (new SaleDetail())
                    ->setCodProducto($d->codigo ?? 'ITEM')
                    ->setUnidad($d->unidad ?? 'NIU')
                    ->setCantidad($cantidad)
                    ->setDescripcion($d->descripcion ?? 'PASAJE')

                    ->setMtoValorUnitario($precio)
                    ->setMtoValorVenta($valorVenta)
                    ->setMtoBaseIgv($valorVenta)
                    ->setPorcentajeIgv(0)
                    ->setIgv(0)
                    ->setTipAfeIgv('20')
                    ->setTotalImpuestos(0)

                    ->setMtoPrecioUnitario($precio);

                continue;
            }

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

        $total = (float) $venta->total;

        if ($total < 0) {
            $total = abs($total);
        }

        return (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice($total, 2, 'SOLES'));
    }

    protected function resolverTipoDocumento(Venta $venta): string
    {
        return $venta->tipoDocumentoFactura->codigo_sunat;
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
        if (!$venta->serie) {
            return '01';
        }

        return str_starts_with($venta->serie, 'B') ? '03' : '01';
    }
}

<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Venta;
use App\Services\Facturacion\GreenterService;
use App\Services\Facturacion\VentaDocumentBuilder;
use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Response\SummaryResult;
use Greenter\Model\Sale\Document;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;

/**
 * Class FixService.
 */
class FixService
{
    public function fix($correlativo)
    {
        $note = new Note();
        $empresa = Empresa::first();

        $client = (new Client())
            ->setTipoDoc('1')
            ->setNumDoc('21459881')
            ->setRznSocial(trim('CARMEN ADALIA ESTRADA HIDALGO'))
            ->setAddress(
                (new Address())->setDireccion('-')
            );

        $address = (new Address())
            ->setUbigueo('150101')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setDireccion('AV. EMANCIPACION NRO S/N')
            ->setCodLocal('0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);

        $note
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie('BBB1')
            ->setCorrelativo($correlativo)
            ->setFechaEmision(new DateTime('2026-06-26 10:05:00'))
            ->setTipDocAfectado('03') // Tipo Doc: Factura
            ->setNumDocfectado('BBB1-1') // Factura: Serie-Correlativo
            ->setCodMotivo('01') // Catalogo. 09
            ->setDesMotivo('ANULACION DE LA OPERACION')
            ->setTipoMoneda('PEN')

            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(8.47)
            ->setMtoIGV(1.53)
            ->setTotalImpuestos(1.53)
            ->setMtoImpVenta(10)
        ;

        $detail1 = new SaleDetail();
        $detail1
            ->setCodProducto('ITEM')
            ->setUnidad('NIU')
            ->setCantidad(1)
            ->setDescripcion('Pasaje CRUCE AYROCA - COLLONI | Asiento 1')
            ->setMtoBaseIgv(8.47)
            ->setPorcentajeIgv(18.00)
            ->setIgv(1.53)
            ->setTipAfeIgv('10')
            ->setTotalImpuestos(1.53)
            ->setMtoValorVenta(8.47)
            ->setMtoValorUnitario(8.47)
            ->setMtoPrecioUnitario(10);

        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue('DIEZ CON 00/100 SOLES');

        $note->setDetails([$detail1])
            ->setLegends([$legend]);

        $service = new GreenterService;
        $see = $service->getSee($empresa);

        $res = $see->send($note);

        if (!$res->isSuccess()) {
            var_dump($res->getError());
            exit();
        }
    }

    public function anular($serie, $numero, $correlativo_resumen)
    {
        $service = new GreenterService;
        $empresa = Empresa::first();
        $detiail1 = new SummaryDetail();
        $detiail1->setTipoDoc('07')
            ->setSerieNro($serie . '-' . $numero)
            ->setEstado('3')
            ->setClienteTipo('1')
            ->setClienteNro('21459881')
            ->setTotal(10)
            ->setMtoOperGravadas(8.47)
            ->setMtoOperInafectas(0)
            ->setMtoOperExoneradas(0)
            ->setMtoOperExportacion(0)
            ->setMtoOtrosCargos(0)
            ->setMtoIGV(1.53);


        $sum = new Summary();

        $address = (new Address())
            ->setUbigueo('150101')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setDireccion('AV. EMANCIPACION NRO S/N')
            ->setCodLocal('0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);

        $sum->setFecGeneracion(new DateTime('2026-06-26 10:00:00'))
            ->setFecResumen(new DateTime())
            ->setCorrelativo($correlativo_resumen)
            ->setCompany($company)
            ->setDetails([$detiail1]);

        $see = $service->getSee($empresa);
        $res = $see->send($sum);

        if (!$res->isSuccess()) {
            var_dump($res->getError());
            return;
        }

        /**@var $res SummaryResult*/
        $ticket = $res->getTicket();
        echo 'Ticket :<strong>' . $ticket . '</strong>';

        $res = $see->getStatus($ticket);
        if (!$res->isSuccess()) {
            var_dump($res->getError());
            return;
        }
    }


    // public function anular($serie, $numero)
    // {

    //     $detail1 = new VoidedDetail();
    //     $detail1->setTipoDoc('03')
    //         ->setSerie($serie)
    //         ->setCorrelativo($numero)
    //         ->setDesMotivoBaja('ERROR DE SISTEMA');

    //     $voided = new Voided();
    //     $voided->setCorrelativo('00111')
    //         ->setFecGeneracion(new DateTime('-3days'))
    //         ->setFecComunicacion(new DateTime('-1days'))
    //         ->setCompany($util->shared->getCompany())
    //         ->setDetails([$detail1]);

    //     // Envio a SUNAT.
    //     $see = $util->getSee(SunatEndpoints::FE_BETA);

    //     $res = $see->send($voided);
    //     $util->writeXml($voided, $see->getFactory()->getLastXml());

    //     if (!$res->isSuccess()) {
    //         echo $util->getErrorResponse($res->getError());
    //         return;
    //     }

    //     /**@var SummaryResult $res */
    //     $ticket = $res->getTicket();
    //     echo 'Ticket :<strong>' . $ticket . '</strong>';

    //     $res = $see->getStatus($ticket);
    //     if (!$res->isSuccess()) {
    //         echo $util->getErrorResponse($res->getError());
    //         return;
    //     }

    //     $cdr = $res->getCdrResponse();
    //     $util->writeCdr($voided, $res->getCdrZip());

    //     $util->showResponse($voided, $cdr);
    // }
}

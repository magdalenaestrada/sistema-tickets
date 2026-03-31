<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\Venta;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Illuminate\Support\Facades\Storage;

class VentaService
{
    public function crearVenta($request, $servicio_model, $servicio_id)
    {
        $user = Auth::user();

        $comprobante = $this->generarSerieYNumero($request['tipo_documento_factura_id'], $user->sucursal_id);
        $request['serie'] = $comprobante['serie'];
        $request['numero'] = $comprobante['numero'];

        DB::beginTransaction();
        try {
            $personaVenta = Persona::updateOrCreate(
                ['documento' => $request->numero_documento_id],
                [
                    'tipo_documento_id' => $request->tipo_documento_factura_id,
                    'nombres' => $request->razon_social,
                    'estado' => 'A',
                    'fecha_creacion' => now(),

                ]
            );
            $venta = Venta::create([
                'tipo_servicio_id'  => $request['tipo_servicio_id'],
                'sucursal_id'       => $user->sucursal_id,
                'usuario_id'        => $user->id,
                'persona_id' => $personaVenta->id,
                'tipo_documento_factura_id' => $request['tipo_documento_factura_id'],
                'serie'             => $request['serie'],
                'numero'            => $request['numero'],
                'total'             => $request['total'],
                'fecha_emision'     => now(),
            ]);

            foreach ($request['detalles'] as $detalle) {

                if ($request['tipo_servicio_id'] == 1) {
                    $descripcion = $request['origen_nombre'] . ' → ' . $request['destino_nombre'];
                    $tipoServicio = 1;
                } else if ($request['tipo_servicio_id'] == 2) {
                    $descripcion = 'Encomienda: ' . $detalle['tipo_encomienda_nombre'] . ' - ' . $detalle['peso'] . 'kg';
                    $tipoServicio = 2;
                } else if ($request['tipo_servicio_id'] == 3) {
                    $descripcion = 'Equipaje extra - ' . $detalle['peso'] . 'kg';
                    $tipoServicio = 3;
                }

                $venta->detalles()->create([
                    'tipo_servicio_id' => $tipoServicio,
                    'descripcion'      => $descripcion,
                    'cantidad'         => 1,
                    'precio_venta'     => $detalle['costo'],
                    'total'            => $detalle['costo'],
                    'descuento'        => $detalle['descuento'] ?? 0,
                ]);
            }

            DB::commit();
            return [
                'venta'          => $venta,
                'servicio_model' => $servicio_model,
                'servicio_id'    => $servicio_id,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function crearVentaPasaje($horario, $asiento, $precio, $descuento, $tipo_documento_factura_id = 1)
    {
        $user = Auth::user();
        $precioFinal = $precio - $descuento;

        $comprobante = $this->generarSerieYNumero($tipo_documento_factura_id, $user->sucursal_id);

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'tipo_servicio_id'  => 1, // Pasajes
                'sucursal_id'       => $user->sucursal_id,
                'usuario_id'        => $user->id,
                'persona_id'        => $user->persona_id,
                'tipo_documento_factura_id' => $tipo_documento_factura_id,
                'serie'             => $comprobante['serie'],
                'numero'            => $comprobante['numero'],
                'total'             => $precioFinal,
                'fecha_emision'     => now(),
            ]);

            $descripcion = $horario->punto_origen->nombre_comercial . ' → ' .
                $horario->punto_destino->nombre_comercial .
                ' - Asiento ' . $asiento;

            $venta->detalles()->create([
                'tipo_servicio_id' => 1,
                'descripcion'      => $descripcion,
                'cantidad'         => 1,
                'precio_venta'     => $precio,
                'total'            => $precioFinal,
                'descuento'        => $descuento,
            ]);

            DB::commit();

            return [
                'venta' => $venta,
                'servicio_model' => Pasaje::class,
                'servicio_id' => null,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    private function generarSerieYNumero($tipo_documento_factura_id, $sucursal_id)
    {

        $series = [
            1 => "B001",
            2 => "F001",
        ];

        $prefijoSucursal = str_pad($sucursal_id, 2, "0", STR_PAD_LEFT);
        $serieBase = $series[$tipo_documento_factura_id] ?? "B001";
        $serie = $prefijoSucursal . $serieBase;

        $ultimoNumero = Venta::where('tipo_documento_factura_id', $tipo_documento_factura_id)
            ->where('sucursal_id', $sucursal_id)
            ->where('serie', $serie)
            ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        return [
            'serie'  => $serie,
            'numero' => $numero,
        ];
    }

    public function anularVenta(Venta $venta): void
    {
        $venta->update(['estado' => 'A']);
        $venta->pagos()->update(['estado' => 'AN']);
    }
    public function reemplazarVenta(
        ?Venta $ventaAnterior,
        $data,
        $servicio_model,
        $servicio_id
    ): array {
        if ($ventaAnterior) {
            $this->anularVenta($ventaAnterior);
        }

        return $this->crearVenta($data, $servicio_model, $servicio_id);
    }

    public function generar_archivo($request)
    {
        $empresa = Empresa::first();

        $client = (new Client())
            ->setTipoDoc($request->tipo_documento_factura_id)
            ->setNumDoc($request->numero_documento_id)
            ->setRznSocial($request->razon_social);

        $address = (new Address())
            ->setUbigueo('15003')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setUrbanizacion('-')
            ->setDireccion($empresa->razon_social)
            ->setCodLocal('0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc('01')
            ->setSerie('F001')
            ->setCorrelativo('1')
            ->setFechaEmision(new DateTime('2020-08-24 13:05:00-05:00'))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(100.00)
            ->setMtoIGV(18.00)
            ->setTotalImpuestos(18.00)
            ->setValorVenta(100.00)
            ->setSubTotal(118.00)
            ->setMtoImpVenta(118.00);

        $item = (new SaleDetail())
            ->setCodProducto('P001')
            ->setUnidad('NIU')
            ->setCantidad(2)
            ->setMtoValorUnitario(50.00)
            ->setDescripcion('PRODUCTO 1')
            ->setMtoBaseIgv(100)
            ->setPorcentajeIgv(18.00)
            ->setIgv(18.00)
            ->setTipAfeIgv('10')
            ->setTotalImpuestos(18.00)
            ->setMtoValorVenta(100.00)
            ->setMtoPrecioUnitario(59.00);

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

        $invoice->setDetails([$item])
            ->setLegends([$legend]);
    }

    public function prueba()
    {
        $see = new See();
        $see->setCertificate(Storage::disk('public')->get("certificado/certificate.pem"));
        $see->setService("https://demo-ose.nubefact.com/ol-ti-itcpe/billService?wsdl");
        $see->setClaveSOL('20605498630MODDATOS', 'MODDATOS', 'MODDATOS');


        $client = (new Client())
            ->setTipoDoc('6')
            ->setNumDoc('20000000001')
            ->setRznSocial('EMPRESA X')
            ->setAddress((new Address())->setDireccion("DIRECCION X"));


        // Emisor
        $address = (new Address())
            ->setUbigueo('150101')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setUrbanizacion('-')
            ->setDireccion('Av. Villa Nueva 221')
            ->setCodLocal('0000'); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.

        $company = (new Company())
            ->setRuc('20605498630')
            ->setRazonSocial('GREEN SAC')
            ->setNombreComercial('GREEN')
            ->setAddress($address);

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta - Catalog. 51
            ->setTipoDoc('01') // Factura - Catalog. 01 
            ->setSerie('F001')
            ->setCorrelativo('1')
            ->setFechaEmision(new DateTime()) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda('PEN') // Sol - Catalog. 02
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(100.00)
            ->setMtoIGV(18.00)
            ->setTotalImpuestos(18.00)
            ->setValorVenta(100.00)
            ->setSubTotal(118.00)
            ->setMtoImpVenta(118.00);

        $item = (new SaleDetail())
            ->setCodProducto('P001')
            ->setUnidad('NIU') // Unidad - Catalog. 03
            ->setCantidad(2)
            ->setMtoValorUnitario(50.00)
            ->setDescripcion('PRODUCTO 1')
            ->setMtoBaseIgv(100)
            ->setPorcentajeIgv(18.00) // 18%
            ->setIgv(18.00)
            ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07
            ->setTotalImpuestos(18.00) // Suma de impuestos en el detalle
            ->setMtoValorVenta(100.00)
            ->setMtoPrecioUnitario(59.00);

        $legend = (new Legend())
            ->setCode('1000') // Monto en letras - Catalog. 52
            ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

        $invoice->setDetails([$item])
            ->setLegends([$legend]);


        $result = $see->send($invoice);

        // Guardar XML firmado digitalmente separado por fechas diarias 
        Storage::disk('public')->put("xml/30-01-2026/" . $invoice->getName() . '.xml', $see->getFactory()->getLastXml());

        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$result->isSuccess()) {
            // Mostrar error al conectarse a SUNAT.
            echo 'Codigo Error: ' . $result->getError()->getCode();
            echo 'Mensaje Error: ' . $result->getError()->getMessage();
            exit();
        }

        // Guardamos el CDR en la mismca carpeta del xml

        Storage::disk('public')->put("xml/30-01-2026/" . 'R-' . $invoice->getName() . '.zip', $result->getCdrZip());


        $cdr = $result->getCdrResponse();

        $code = (int)$cdr->getCode();

        if ($code === 0) {
            echo 'ESTADO: ACEPTADA' . PHP_EOL;
            if (count($cdr->getNotes()) > 0) {
                echo 'OBSERVACIONES:' . PHP_EOL;
                // Corregir estas observaciones en siguientes emisiones.
                var_dump($cdr->getNotes());
            }
        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA' . PHP_EOL;
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription() . PHP_EOL;
    }
}

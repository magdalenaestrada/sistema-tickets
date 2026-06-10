<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\CorrelativoVenta;
use App\Models\Empresa;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\SubtipoMovimientoCaja;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\Venta;
use App\Models\VentaPago;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Luecano\NumeroALetras\NumeroALetras;

class VentaService
{
    public function crearVenta($request, $servicio_model, $servicio_id): array
    {
        $user = Auth::user();

        return DB::transaction(function () use ($request, $servicio_model, $servicio_id, $user) {

            $tipoDocumentoFacturaId = (int) data_get($request, 'tipo_documento_factura_id');
            $tipoServicioId = (int) data_get($request, 'tipo_servicio_id');
            $numeroDocumento = trim((string) data_get($request, 'numero_documento_id'));
            $direccion = trim((string) data_get($request, 'direccion'));
            $razonSocial = data_get($request, 'razon_social');
            $total = (float) data_get($request, 'total', 0);
            $detalles = data_get($request, 'detalles', []);

            $cajaSucursal = $this->resolverCajaYSucursalVenta($request, $user);

            $cajaId = $cajaSucursal['caja_id'];
            $sucursalId = $cajaSucursal['sucursal_id'];

            $comprobante = $this->reservarSerieYNumero(
                $tipoDocumentoFacturaId,
                $sucursalId
            );

            $personaVenta = Persona::updateOrCreate(
                ['documento' => $numeroDocumento],
                [
                    'tipo_documento_id' => $this->resolverTipoDocumentoCliente(
                        $numeroDocumento,
                        $tipoDocumentoFacturaId
                    ),
                    'nombres' => $razonSocial,
                    'direccion' => $direccion,
                    'estado' => 'A',
                    'fecha_creacion' => now(),

                ]
            );

            $venta = Venta::create([
                'tipo_servicio_id'          => $tipoServicioId,
                'sucursal_id'               => $sucursalId,
                'usuario_id'                => $user->id,
                'persona_id'                => $personaVenta->id,
                'tipo_documento_factura_id' => $tipoDocumentoFacturaId,
                'serie'                     => $comprobante['serie'],
                'numero'                    => $comprobante['numero'],
                'total'                     => $total,
                'caja_id'                   => $cajaId,
                'estado'                    => 'P',
                'fecha_emision'             => now(),
            ]);

            foreach ($detalles as $detalle) {
                if ((int) $tipoServicioId === 1) {

                    $descripcion = trim(
                        (string) ($detalle['descripcion'] ?? '')
                    );

                    if ($descripcion === '') {
                        $descripcion = 'Pasaje de viaje';
                    }

                    $tipoServicioDetalle = 1;
                } elseif ((int) $tipoServicioId === 2) {
                    $descripcion = 'Encomienda: '
                        . ($detalle['tipo_encomienda_nombre'] ?? 'Servicio')
                        . ' - '
                        . ($detalle['peso'] ?? 0)
                        . 'kg';
                    $tipoServicioDetalle = 2;
                } else {
                    $descripcion = 'Equipaje extra - ' . ($detalle['peso'] ?? 0) . 'kg';
                    $tipoServicioDetalle = 3;
                }

                $venta->detalles()->create([
                    'tipo_servicio_id' => $tipoServicioDetalle,
                    'descripcion'      => $descripcion,
                    'cantidad'         => 1,
                    'precio_venta'     => (float) ($detalle['costo'] ?? 0),
                    'total'            => (float) ($detalle['costo'] ?? 0),
                    'descuento'        => (float) ($detalle['descuento'] ?? 0),
                ]);
            }

            $venta->load(['persona', 'detalles']);

            return [
                'venta'          => $venta,
                'servicio_model' => $servicio_model,
                'servicio_id'    => $servicio_id,
            ];
        });
    }

    private function resolverCajaYSucursalVenta($request, $user): array
    {
        if ($user->hasRole('Administrador')) {
            $caja = Caja::with('sucursal.serie')
                ->where('id', data_get($request, 'caja_id'))
                ->where('estado', 'A')
                ->first();

            if (!$caja) {
                throw new Exception('Debe seleccionar una caja abierta para la venta.');
            }

            return [
                'caja_id' => $caja->id,
                'sucursal_id' => $caja->sucursal_id,
            ];
        }

        $caja = Caja::with('sucursal.serie')
            ->where('usuario_id', $user->id)
            ->where('estado', 'A')
            ->first();

        if (!$caja) {
            throw new Exception('El usuario no tiene una caja abierta asignada.');
        }

        return [
            'caja_id' => $caja->id,
            'sucursal_id' => $caja->sucursal_id,
        ];
    }

    public function crearVentaPasaje($horario, $asiento, $precio, $descuento, $tipo_documento_factura_id = 1, $request, $sucursal_id = null): array
    {
        $user = Auth::user();
        $precioFinal = $precio - $descuento;

        $cajaId = data_get($request, 'caja_id');

        if ($user->hasRole('Administrador')) {
            $caja = Caja::with('sucursal')
                ->where('id', $cajaId)
                ->where('estado', 'A')
                ->first();

            if (!$caja) {
                throw new \Exception('Debe seleccionar una caja válida.');
            }

            $sucursalId = $caja->sucursal_id;
        } else {
            $caja = Caja::with('sucursal')
                ->where('usuario_id', $user->id)
                ->where('estado', 'A')
                ->first();

            if (!$caja) {
                throw new \Exception('El usuario no tiene caja abierta.');
            }

            $cajaId = $caja->id;
            $sucursalId = $caja->sucursal_id;
        }

        $venta = DB::transaction(function () use ($horario, $asiento, $precio, $descuento, $precioFinal, $tipo_documento_factura_id, $user, $sucursalId, $cajaId) {
            $comprobante = $this->reservarSerieYNumero((int) $tipo_documento_factura_id, $sucursalId);

            $venta = Venta::create([
                'tipo_servicio_id'          => 1,
                'sucursal_id'               => $sucursalId,
                'usuario_id'                => $user->id,
                'persona_id'                => $user->persona_id,
                'tipo_documento_factura_id' => $tipo_documento_factura_id,
                'caja_id' => $cajaId,
                'serie'                     => $comprobante['serie'],
                'numero'                    => $comprobante['numero'],
                'total'                     => $precioFinal,
                'estado'                    => 'P',
                'fecha_emision'             => now(),
            ]);

            $descripcion = $horario->punto_origen->nombre_comercial . ' - '
                . $horario->punto_destino->nombre_comercial
                . ' - Asiento ' . $asiento;

            $venta->detalles()->create([
                'tipo_servicio_id' => 1,
                'descripcion'      => $descripcion,
                'cantidad'         => 1,
                'precio_venta'     => $precio,
                'total'            => $precioFinal,
                'descuento'        => $descuento,
            ]);

            $venta->load(['persona', 'detalles']);

            return $venta;
        });

        return [
            'venta'          => $venta,
            'servicio_model' => Pasaje::class,
            'servicio_id'    => null,
        ];
    }

    public function emitirVenta(Venta $venta): array
    {
        $venta->refresh();
        $tipo = TipoDocumentoFactura::find($venta->tipo_documento_factura_id);

        if (!$tipo) {
            throw new Exception('Tipo de documento no válido.');
        }

        if (in_array($venta->estado, ['E', 'O'], true)) {
            return [
                'success'     => true,
                'estado'      => 'YA_EMITIDA',
                'codigo'      => null,
                'descripcion' => 'La venta ya fue emitida previamente.',
                'notas'       => [],
                'xml_path'    => $venta->ruta_xml,
                'cdr_path'    => $venta->ruta_cdr,
                'nombre'      => $venta->serie . '-' . $venta->numero,
            ];
        }

        if ((string) $tipo->codigo === 'NV') {
            $venta->update([
                'estado' => 'E',
                'observacion' => 'Nota de venta emitida internamente.',
            ]);

            return [
                'success'     => true,
                'estado'      => 'EMITIDA_INTERNA',
                'codigo'      => null,
                'descripcion' => 'La nota de venta fue emitida internamente y no se envía a SUNAT.',
                'notas'       => [],
                'xml_path'    => null,
                'cdr_path'    => null,
                'nombre'      => $venta->serie . '-' . $venta->numero,
            ];
        }

        return $this->emitirComprobante($venta);
    }

    private function mapTipoDocumentoComprobante($tipoDocumentoFacturaId): string
    {
        $tipo = TipoDocumentoFactura::find($tipoDocumentoFacturaId);

        if (!$tipo) {
            throw new Exception('Tipo de documento de factura no válido.');
        }

        return match ((string) $tipo->codigo) {
            '01' => '01',
            '03' => '03',
            '07' => '07',
            default => throw new Exception('Código SUNAT no soportado: ' . $tipo->codigo),
        };
    }

    private function resolverSeriePorTipoYSucursal(string $codigoTipoDocumento, int $sucursalId): string
    {
        $sucursal = \App\Models\Sucursal::with('serie')->findOrFail($sucursalId);

        $codigo = $sucursal->serie->codigo ?? '001';
        $numero = (int) $codigo;

        return match ($codigoTipoDocumento) {
            '01' => 'FFF' . $numero,
            '03' => 'BBB' . $numero,
            '07' => 'NC' . $numero,
            default => throw new \Exception('Código no soportado: ' . $codigoTipoDocumento),
        };
    }

    private function reservarSerieYNumero(int $tipo_documento_factura_id, int $sucursal_id): array
    {
        $tipo = TipoDocumentoFactura::find($tipo_documento_factura_id);

        if (!$tipo) {
            throw new Exception('Tipo de documento de factura no válido.');
        }

        $serie = $this->resolverSeriePorTipoYSucursal((string) $tipo->codigo, $sucursal_id);

        $correlativo = CorrelativoVenta::where('tipo_documento_factura_id', $tipo_documento_factura_id)
            ->where('sucursal_id', $sucursal_id)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->first();

        if (!$correlativo) {
            $correlativo = CorrelativoVenta::create([
                'tipo_documento_factura_id' => $tipo_documento_factura_id,
                'sucursal_id'               => $sucursal_id,
                'serie'                     => $serie,
                'ultimo_numero'             => 0,
            ]);

            $correlativo = CorrelativoVenta::whereKey($correlativo->id)
                ->lockForUpdate()
                ->first();
        }

        $nuevoNumero = (int) $correlativo->ultimo_numero + 1;

        $correlativo->update([
            'ultimo_numero' => $nuevoNumero,
        ]);

        return [
            'serie'  => $serie,
            'numero' => $nuevoNumero,
        ];
    }

    public function anularVentaSunat(Venta $venta): array
    {
        $venta->loadMissing(['persona', 'detalles']);

        if (!in_array($venta->estado, ['E', 'O'], true)) {
            throw new Exception('Solo se puede anular en SUNAT una venta emitida.');
        }

        $tipoNotaCredito = TipoDocumentoFactura::where('codigo', '07')->first();

        if (!$tipoNotaCredito) {
            throw new Exception('No existe configurado el tipo de documento Nota de Crédito código 07.');
        }

        $empresa = Empresa::first();

        if (!$empresa) {
            throw new Exception('No existe configuración de empresa.');
        }

        $comprobanteNC = $this->reservarSerieYNumero(
            (int) $tipoNotaCredito->id,
            (int) $venta->sucursal_id
        );

        $see = $this->crearSee();

        $note = $this->buildNotaCreditoAnulacion(
            $venta,
            $empresa,
            $comprobanteNC['serie'],
            $comprobanteNC['numero']
        );

        $result = $see->send($note);

        $folder = 'xml/' . now()->format('d-m-Y');
        $xmlPath = $folder . '/' . $note->getName() . '.xml';
        $cdrPath = $folder . '/R-' . $note->getName() . '.zip';

        Storage::disk('public')->put(
            $xmlPath,
            $see->getFactory()->getLastXml()
        );

        if (!$result->isSuccess()) {
            $errorCode = optional($result->getError())->getCode();
            $errorMessage = optional($result->getError())->getMessage();

            Log::error('Error al anular venta en SUNAT', [
                'venta_id' => $venta->id,
                'codigo' => $errorCode,
                'mensaje' => $errorMessage,
            ]);

            throw new Exception(
                'SUNAT rechazó la anulación: ' . trim($errorCode . ' - ' . $errorMessage, ' -')
            );
        }

        Storage::disk('public')->put(
            $cdrPath,
            $result->getCdrZip()
        );

        $cdr = $result->getCdrResponse();

        if ((int) $cdr->getCode() !== 0) {
            throw new Exception('SUNAT no aceptó la nota de crédito: ' . $cdr->getDescription());
        }

        DB::transaction(function () use ($venta, $xmlPath, $cdrPath, $cdr) {
            $venta->update([
                'estado' => 'A',
                'fecha_anulacion' => now(),
                'observacion' => 'Venta anulada en SUNAT: ' . $cdr->getDescription(),
            ]);

            $venta->pagos()->update([
                'estado' => 'AN',
            ]);

            CajaDetalle::where('table_name', Venta::class)
                ->where('table_id', $venta->id)
                ->update([
                    'anulado' => true,
                ]);
        });

        return [
            'success' => true,
            'estado' => 'ANULADA_SUNAT',
            'codigo' => $cdr->getCode(),
            'descripcion' => $cdr->getDescription(),
            'notas' => $cdr->getNotes(),
            'xml_path' => $xmlPath,
            'cdr_path' => $cdrPath,
            'nombre' => $note->getName(),
        ];
    }

    public function reemplazarVenta(?Venta $ventaAnterior, $data, $servicio_model, $servicio_id): array
    {
        if ($ventaAnterior) {
            $this->anularVentaSunat($ventaAnterior);
        }

        return $this->crearVenta($data, $servicio_model, $servicio_id);
    }

    public function emitirComprobante(Venta $venta): array
    {
        $venta->loadMissing(['persona', 'detalles']);

        $empresa = Empresa::first();
        if (!$empresa) {
            throw new Exception('No existe configuración de empresa.');
        }

        $see = $this->crearSee($tipo->codigo);
        $invoice = $this->buildInvoice($venta, $empresa);
        $result = $see->send($invoice);

        $folder = 'xml/' . now()->format('d-m-Y');
        $xmlPath = $folder . '/' . $invoice->getName() . '.xml';
        $cdrPath = $folder . '/R-' . $invoice->getName() . '.zip';

        Storage::disk('public')->put(
            $xmlPath,
            $see->getFactory()->getLastXml()
        );

        if (!$result->isSuccess()) {
            $errorCode = optional($result->getError())->getCode();
            $errorMessage = optional($result->getError())->getMessage();

            Log::error('Error SUNAT/OSE', [
                'venta_id' => $venta->id,
                'serie'    => $venta->serie,
                'numero'   => $venta->numero,
                'codigo'   => $errorCode,
                'mensaje'  => $errorMessage,
            ]);

            $venta->update([
                'ruta_xml'    => $xmlPath,
                'ruta_cdr'    => null,
                'hash'        => null,
                'estado'      => 'R',
                'observacion' => trim($errorCode . ' - ' . $errorMessage, ' -'),
            ]);

            if ((string) $errorCode === '1033') {
                throw new Exception(
                    "SUNAT rechazó el comprobante {$venta->serie}-{$venta->numero}: ya fue registrado previamente con otros datos."
                );
            }

            throw new Exception(
                'Error al enviar comprobante: ' . trim($errorCode . ' - ' . $errorMessage, ' -')
            );
        }

        Storage::disk('public')->put(
            $cdrPath,
            $result->getCdrZip()
        );

        $cdr = $result->getCdrResponse();
        $code = (int) $cdr->getCode();

        $estadoSunat = match (true) {
            $code === 0 => 'ACEPTADA',
            $code >= 2000 && $code <= 3999 => 'RECHAZADA',
            default => 'OBSERVADA',
        };

        $estadoInterno = match (true) {
            $code === 0 => 'E',
            $code >= 2000 && $code <= 3999 => 'R',
            default => 'O',
        };

        $venta->update([
            'ruta_xml'    => $xmlPath,
            'ruta_cdr'    => $cdrPath,
            'hash'        => method_exists($result, 'getHashCdr') ? $result->getHashCdr() : null,
            'estado'      => $estadoInterno,
            'observacion' => $cdr->getDescription(),
        ]);

        Log::info('Comprobante emitido', [
            'venta_id'     => $venta->id,
            'comprobante'  => $invoice->getName(),
            'estado_sunat' => $estadoSunat,
            'code'         => $code,
            'descripcion'  => $cdr->getDescription(),
            'notas'        => $cdr->getNotes(),
        ]);

        return [
            'success'     => true,
            'estado'      => $estadoSunat,
            'codigo'      => $code,
            'descripcion' => $cdr->getDescription(),
            'notas'       => $cdr->getNotes(),
            'xml_path'    => $xmlPath,
            'cdr_path'    => $cdrPath,
            'nombre'      => $invoice->getName(),
        ];
    }

    private function crearSee(string $tipoDocumento): See
    {
        $see = new See();

        $empresa = Empresa::first();

        if (!$empresa) {
            throw new Exception('No existe configuración de empresa.');
        }

        if ($tipoDocumento !== 'NV') {


            $certDisk = config('services.greenter.cert_disk', 'public');
            $certPath = config('services.greenter.cert_path', 'certificado/certificate.pem');

            if (!Storage::disk($certDisk)->exists($certPath)) {
                throw new Exception("No se encontró el certificado en: {$certPath}");
            }

            $see->setCertificate(
                Storage::disk($certDisk)->get($certPath)
            );
        }
        $modo = config('services.greenter.mode', 'beta');

        if ($modo === 'production') {
            $see->setService(SunatEndpoints::FE_PRODUCCION);
        } else {
            $see->setService(config('services.greenter.beta_url'));
        }

        $see->setClaveSOL(
            $empresa->documento,
            $empresa->usuario_facturacion,
            $empresa->contrasena_facturacion
        );

        return $see;
    }

    private function buildInvoice(Venta $venta, Empresa $empresa): Invoice
    {
        $cliente = $venta->persona;

        if (!$cliente) {
            throw new Exception('La venta no tiene cliente asociado.');
        }

        $tipoDocComprobante = $this->mapTipoDocumentoComprobante($venta->tipo_documento_factura_id);
        $tipoDocCliente = $this->mapTipoDocumentoClienteSunat($cliente->tipo_documento_id, $cliente->documento);

        $companyAddress = (new Address())
            ->setUbigueo($empresa->ubigueo ?? '150101')
            ->setDepartamento($empresa->departamento ?? 'LIMA')
            ->setProvincia($empresa->provincia ?? 'LIMA')
            ->setDistrito($empresa->distrito ?? 'LIMA')
            ->setUrbanizacion($empresa->urbanizacion ?? '-')
            ->setDireccion($empresa->direccion ?? $empresa->razon_social)
            ->setCodLocal($empresa->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial ?? $empresa->razon_social)
            ->setAddress($companyAddress);

        $client = (new Client())
            ->setTipoDoc($tipoDocCliente)
            ->setNumDoc($cliente->documento ?: '00000000')
            ->setRznSocial(trim($cliente->nombres . ' ' . ($cliente->apellidos ?? '')))
            ->setAddress(
                (new Address())->setDireccion($cliente->direccion ?? '-')
            );

        $detalles = [];
        $mtoOperGravadas = 0;
        $mtoIGV = 0;
        $valorVenta = 0;
        $subTotal = 0;
        $totalVenta = 0;

        foreach ($venta->detalles as $detalle) {
            $cantidad = (float) ($detalle->cantidad ?? 1);
            $totalLinea = (float) ($detalle->total ?? 0);

            if ($cantidad <= 0) {
                throw new Exception("La cantidad del detalle {$detalle->id} no puede ser menor o igual a cero.");
            }

            $igv = (float) $empresa->igv;

            if ($igv > 1) {
                $igv = $igv / 100;
            }
            $porcentajeIgv = 0.18;
            $totalLinea = round($totalLinea, 2);
            $valorVentaLinea = round(
                $totalLinea / (1 + $porcentajeIgv),
                2
            );
            $igvLinea = round(
                $totalLinea - $valorVentaLinea,
                2
            );
            $precioUnitario = round(
                $totalLinea / $cantidad,
                10
            );
            $valorUnitarioSinIgv = round(
                $valorVentaLinea / $cantidad,
                10
            );
            $detalles[] = (new SaleDetail())
                ->setCodProducto((string) ($detalle->id ?? 'ITEM'))
                ->setUnidad('NIU')
                ->setCantidad($cantidad)
                ->setMtoValorUnitario($valorUnitarioSinIgv)
                ->setDescripcion($detalle->descripcion)
                ->setMtoBaseIgv($valorVentaLinea)
                ->setPorcentajeIgv($igv * 100)->setIgv($igvLinea)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($valorVentaLinea)
                ->setMtoPrecioUnitario($precioUnitario);

            $mtoOperGravadas += $valorVentaLinea;
            $mtoIGV += $igvLinea;
            $valorVenta += $valorVentaLinea;
            $subTotal += $totalLinea;
            $totalVenta += $totalLinea;
        }

        $formatter = new NumeroALetras();

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice($totalVenta, 2, 'SOLES'));

        return (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDocComprobante)
            ->setSerie($venta->serie)
            ->setCorrelativo((string) $venta->numero)
            ->setFechaEmision(new DateTime(now()->format('Y-m-d H:i:sP')))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(round($mtoOperGravadas, 2))
            ->setMtoIGV(round($mtoIGV, 2))
            ->setTotalImpuestos(round($mtoIGV, 2))
            ->setValorVenta(round($valorVenta, 2))
            ->setSubTotal(round($subTotal, 2))
            ->setMtoImpVenta(round($totalVenta, 2))
            ->setDetails($detalles)
            ->setLegends([$legend]);
    }

    private function resolverTipoDocumentoCliente(?string $numeroDocumento, ?int $tipoDocumentoFacturaId): int
    {
        $numeroDocumento = trim((string) $numeroDocumento);

        if (strlen($numeroDocumento) === 11) {
            return 2; // RUC
        }

        if (strlen($numeroDocumento) === 8) {
            return 1; // DNI
        }

        return 6; // SIN DOCUMENTO
    }

    private function mapTipoDocumentoClienteSunat(?int $tipoDocumentoId, ?string $numeroDocumento): string
    {
        $tipo = TipoDocumentoPersona::find($tipoDocumentoId);

        if (!$tipo) {
            return '0';
        }

        return (string) $tipo->codigo_sunat;
    }

    private function buildNotaCreditoAnulacion(
        Venta $venta,
        Empresa $empresa,
        string $serieNC,
        int $numeroNC
    ): Note {
        $cliente = $venta->persona;

        if (!$cliente) {
            throw new Exception('La venta no tiene cliente asociado.');
        }

        $tipoDocAfectado = $this->mapTipoDocumentoComprobante($venta->tipo_documento_factura_id);
        $tipoDocCliente = $this->mapTipoDocumentoClienteSunat($cliente->tipo_documento_id, $cliente->documento);

        $companyAddress = (new Address())
            ->setUbigueo($empresa->ubigueo ?? '150101')
            ->setDepartamento($empresa->departamento ?? 'LIMA')
            ->setProvincia($empresa->provincia ?? 'LIMA')
            ->setDistrito($empresa->distrito ?? 'LIMA')
            ->setUrbanizacion($empresa->urbanizacion ?? '-')
            ->setDireccion($empresa->direccion ?? $empresa->razon_social)
            ->setCodLocal($empresa->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($empresa->documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial ?? $empresa->razon_social)
            ->setAddress($companyAddress);

        $client = (new Client())
            ->setTipoDoc($tipoDocCliente)
            ->setNumDoc($cliente->documento ?: '00000000')
            ->setRznSocial(trim($cliente->nombres . ' ' . ($cliente->apellidos ?? '')))
            ->setAddress(
                (new Address())->setDireccion($cliente->direccion ?? '-')
            );

        $detalles = [];
        $mtoOperGravadas = 0;
        $mtoIGV = 0;
        $valorVenta = 0;
        $subTotal = 0;
        $totalVenta = 0;

        foreach ($venta->detalles as $detalle) {
            $cantidad = (float) ($detalle->cantidad ?? 1);
            $totalLinea = (float) ($detalle->total ?? 0);

            if ($cantidad <= 0) {
                throw new Exception("La cantidad del detalle {$detalle->id} no puede ser menor o igual a cero.");
            }

            $valorUnitario = round($totalLinea / 1.18, 10);
            $igvLinea = round($totalLinea - $valorUnitario, 2);
            $valorVentaLinea = round($totalLinea - $igvLinea, 2);
            $precioUnitario = round($totalLinea / $cantidad, 10);
            $valorUnitarioSinIgv = round($valorVentaLinea / $cantidad, 10);

            $detalles[] = (new SaleDetail())
                ->setCodProducto((string) ($detalle->id ?? 'ITEM'))
                ->setUnidad('NIU')
                ->setCantidad($cantidad)
                ->setMtoValorUnitario($valorUnitarioSinIgv)
                ->setDescripcion($detalle->descripcion)
                ->setMtoBaseIgv($valorVentaLinea)
                ->setPorcentajeIgv(18.00)
                ->setIgv($igvLinea)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($valorVentaLinea)
                ->setMtoPrecioUnitario($precioUnitario);

            $mtoOperGravadas += $valorVentaLinea;
            $mtoIGV += $igvLinea;
            $valorVenta += $valorVentaLinea;
            $subTotal += $totalLinea;
            $totalVenta += $totalLinea;
        }

        $formatter = new NumeroALetras();

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice($totalVenta, 2, 'SOLES'));

        return (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($serieNC)
            ->setCorrelativo((string) $numeroNC)
            ->setFechaEmision(new DateTime(now()->format('Y-m-d H:i:sP')))
            ->setTipDocAfectado($tipoDocAfectado)
            ->setNumDocfectado($venta->serie . '-' . $venta->numero)
            ->setCodMotivo('01')
            ->setDesMotivo('ANULACION DE LA OPERACION')
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(round($mtoOperGravadas, 2))
            ->setMtoIGV(round($mtoIGV, 2))
            ->setTotalImpuestos(round($mtoIGV, 2))
            ->setValorVenta(round($valorVenta, 2))
            ->setSubTotal(round($subTotal, 2))
            ->setMtoImpVenta(round($totalVenta, 2))
            ->setDetails($detalles)
            ->setLegends([$legend]);
    }
}

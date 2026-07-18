<?php

namespace App\Services;

use App\Enums\EstadoVenta;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\ComunicacionBaja;
use App\Models\CorrelativoVenta;
use App\Models\Empresa;
use App\Models\Encomienda;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\SubtipoMovimientoCaja;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\Venta;
use App\Models\VentaPago;
use DateTime;
use Exception;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
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

            $tipoDocumentoFacturaId = (int) data_get($request, 'tipo_documento_factura_id')
                ?: (int) data_get($request, 'tipo_doc_sunat');
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

            $empresa = Empresa::first();
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
                'sucursal_id' => $sucursalId,
                'usuario_id' => $user->id,
                'persona_id' => $personaVenta->id,
                'tipo_documento_factura_id' => $tipoDocumentoFacturaId,
                'serie' => $comprobante['serie'],
                'numero' => $comprobante['numero'],
                'total' => $total,
                'caja_id' => $cajaId,
                'estado' => EstadoVenta::GENERADO,
                'fecha_emision' => now(),
            ]);

            foreach ($detalles as $detalle) {
                if ((int) $tipoServicioId === 1) {

                    $descripcion = trim(
                        (string) ($detalle['descripcion'] ?? '')
                    );

                    if ($descripcion === '') {
                        $descripcion = 'Pasaje de viaje';
                    }
                    $referenciaType = Pasaje::class;
                    $tipoServicioDetalle = 1;
                    $igv = $empresa->igv;
                } elseif ((int) $tipoServicioId === 2) {
                    $descripcion = 'Encomienda: '
                        . ($detalle['tipo_encomienda_nombre'] ?? 'Servicio')
                        . ' - '
                        . ($detalle['peso'] ?? 0)
                        . 'kg';
                    $tipoServicioDetalle = 2;
                    $referenciaType = Encomienda::class;
                    $igv = $empresa->igv_encomienda;
                } else {
                    $descripcion = 'Equipaje extra - ' . ($detalle['peso'] ?? 0) . 'kg';
                    $tipoServicioDetalle = 3;
                    $referenciaType = Encomienda::class;
                    $igv = $empresa->igv_encomienda;
                }

                $type = $servicio_model;

                $venta->detalles()->create([
                    'tipo_servicio_id' => $tipoServicioDetalle,
                    'descripcion' => $descripcion,
                    'porcentaje_igv' => $igv,
                    'referencia_type' => $referenciaType,
                    'referencia_id' => $servicio_id ?: null,
                    'cantidad' => 1,
                    'precio_venta' => (float) ($detalle['costo'] ?? 0),
                    'total' => (float) ($detalle['costo'] ?? 0),
                    'descuento' => (float) ($detalle['descuento'] ?? 0),
                ]);
            }

            $venta->load(['persona', 'detalles']);

            return [
                'venta' => $venta,
                'servicio_model' => $servicio_model,
                'servicio_id' => $servicio_id,
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

    // public function crearVentaPasaje($horario, $asiento, $precio, $descuento, $tipo_documento_factura_id = 1, $request, $sucursal_id = null): array
    // {
    //     $user = Auth::user();
    //     $precioFinal = $precio - $descuento;

    //     $cajaId = data_get($request, 'caja_id');

    //     if ($user->hasRole('Administrador')) {
    //         $caja = Caja::with('sucursal')
    //             ->where('id', $cajaId)
    //             ->where('estado', 'A')
    //             ->first();

    //         if (!$caja) {
    //             throw new \Exception('Debe seleccionar una caja válida.');
    //         }

    //         $sucursalId = $caja->sucursal_id;
    //     } else {
    //         $caja = Caja::with('sucursal')
    //             ->where('usuario_id', $user->id)
    //             ->where('estado', 'A')
    //             ->first();

    //         if (!$caja) {
    //             throw new \Exception('El usuario no tiene caja abierta.');
    //         }

    //         $cajaId = $caja->id;
    //         $sucursalId = $caja->sucursal_id;
    //     }

    //     $venta = DB::transaction(function () use ($horario, $asiento, $precio, $descuento, $precioFinal, $tipo_documento_factura_id, $user, $sucursalId, $cajaId) {
    //         $comprobante = $this->reservarSerieYNumero((int) $tipo_documento_factura_id, $sucursalId);

    //         $venta = Venta::create([
    //             'tipo_servicio_id' => 1,
    //             'sucursal_id' => $sucursalId,
    //             'usuario_id' => $user->id,
    //             'persona_id' => $user->persona_id,
    //             'tipo_documento_factura_id' => $tipo_documento_factura_id,
    //             'caja_id' => $cajaId,
    //             'serie' => $comprobante['serie'],
    //             'numero' => $comprobante['numero'],
    //             'total' => $precioFinal,
    //             'estado' => 'P',
    //             'fecha_emision' => now(),
    //         ]);

    //         $descripcion = $horario->punto_origen->nombre_comercial . ' - '
    //             . $horario->punto_destino->nombre_comercial
    //             . ' - Asiento ' . $asiento;

    //         $venta->detalles()->create([
    //             'tipo_servicio_id' => 1,
    //             'descripcion' => $descripcion,
    //             'cantidad' => 1,
    //             'precio_venta' => $precio,
    //             'total' => $precioFinal,
    //             'descuento' => $descuento,
    //         ]);

    //         $venta->load(['persona', 'detalles']);

    //         return $venta;
    //     });

    //     return [
    //         'venta' => $venta,
    //         'servicio_model' => Pasaje::class,
    //         'servicio_id' => null,
    //     ];
    // }

    public function emitirVenta(Venta $venta): array
    {
        $venta->refresh();

        $tipo = TipoDocumentoFactura::find($venta->tipo_documento_factura_id);

        if (!$tipo) {
            throw new Exception('Tipo de documento no válido.');
        }

        // significa que ya está emitido ... aunque porque estaria emitido si, por lo que he visto, este
        // metodo solo se usa despues de que se haya guardado la venta en DB ...
        // pero entiendo que esto puede funcionar para cuando una venta no se haya emitido o quieras
        // implementar esta funcionalidad
        // - Zahovic 2026/07/03
        if (in_array($venta->estado, [EstadoVenta::EMITIDO->value], true)) {
            return [
                'success' => true,
                'estado' => 'YA_EMITIDA',
                'codigo' => null,
                'descripcion' => 'La venta ya fue emitida previamente.',
                'notas' => [],
                'xml_path' => $venta->ruta_xml,
                'cdr_path' => $venta->ruta_cdr,
                'nombre' => $venta->serie . '-' . $venta->numero,
            ];
        }

        // correcto
        if ((string) $tipo->codigo === '00') {
            $venta->update([
                'estado' => EstadoVenta::EMITIDO,
                'observacion' => 'Nota de venta emitida internamente.',
            ]);

            return [
                'success' => true,
                'estado' => 'EMITIDA_INTERNA',
                'codigo' => null,
                'descripcion' => 'La nota de venta fue emitida internamente y no se envía a SUNAT.',
                'notas' => [],
                'xml_path' => null,
                'cdr_path' => null,
                'nombre' => $venta->serie . '-' . $venta->numero,
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

    private function resolverSeriePorTipoYSucursal(int $tipo_documento_factura_id, int $sucursalId): string
    {
        $sucursal = \App\Models\Sucursal::with([
            'serie' => function ($query) use ($tipo_documento_factura_id) {
                $query->where("tipo_documento_factura_id", $tipo_documento_factura_id)
                    ->with('tipoDocumentoFactura');
            }
        ])->findOrFail($sucursalId);
        // para notas de credito, hacer algo más para saber si emitir con la serie de NC BOLETAS o NC FACTURAS
        if ($sucursal?->serie?->isEmpty()) {
            throw new Exception("No exite la serie correspondiente para el documento", 1);
        }
        return $sucursal->serie?->first()->serie;
    }

    public function reservarSerieYNumero(int $tipo_documento_factura_id, int $sucursal_id): array
    {
        $tipo = TipoDocumentoFactura::find($tipo_documento_factura_id);

        if (!$tipo) {
            throw new Exception('Tipo de documento de factura no válido.');
        }

        $serie = $this->resolverSeriePorTipoYSucursal($tipo_documento_factura_id, $sucursal_id);

        $correlativo = CorrelativoVenta::where('tipo_documento_factura_id', $tipo_documento_factura_id)
            ->where('sucursal_id', $sucursal_id)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->first();

        if (!$correlativo) {
            $correlativo = CorrelativoVenta::create([
                'tipo_documento_factura_id' => $tipo_documento_factura_id,
                'sucursal_id' => $sucursal_id,
                'serie' => $serie,
                'ultimo_numero' => 0,
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
            'serie' => $serie,
            'numero' => $nuevoNumero,
        ];
    }

    public function anularVentaSunat(Venta $notaCredito, Venta $ventaOriginal): array
    {
        $notaCredito->loadMissing(['persona', 'detalles']);

        if (!in_array($ventaOriginal->estado, [EstadoVenta::EMITIDO], true)) {
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

        $see = $this->crearSee($tipoNotaCredito->codigo);

        $note = $this->buildNotaCreditoAnulacion(
            $notaCredito,
            $ventaOriginal,
            $empresa,
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
                'venta_id' => $notaCredito->id,
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

        DB::transaction(function () use ($notaCredito, $ventaOriginal, $xmlPath, $cdrPath, $cdr, $result) {
            $notaCredito->update([
                'ruta_xml' => $xmlPath,
                'ruta_cdr' => $cdrPath,
                'estado' => EstadoVenta::EMITIDO,
                'hash' => method_exists($result, 'getHashCdr') ? $result->getHashCdr() : null,
                'fecha_anulacion' => now(),
                'observacion' => 'Venta anulada en SUNAT: ' . $cdr->getDescription(),
            ]);

            $ventaOriginal->update([
                'estado' => EstadoVenta::ANULADO_CON_NOTA_CREDITO,
                'observacion' => 'Venta anulada en SUNAT: ' . $cdr->getDescription(),
            ]);

            // $notaCredito->pagos()->update([
            //     'estado' => 'AN',
            // ]);

            CajaDetalle::where('venta_id', $notaCredito->id)
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

    public function anularVentaDirecta(Venta $venta)
    {
        $venta->loadMissing(['persona', 'detalles']);

        if (!in_array($venta->estado, [EstadoVenta::EMITIDO], true)) {
            throw new Exception('Solo se puede anular en SUNAT una venta emitida.');
        }

        // necesitas guardar una cosa para los resumenes diarios
        // $comprobanteNC = $this->reservarSerieYNumero(
        //     (int) $tipoNotaCredito->id,
        //     (int) $venta->sucursal_id
        // );

        $see = $this->crearSee();
        $serie = str_starts_with($venta->serie, "F") ? 'RA' : 'RC';
        $numero = ComunicacionBaja::where("serie", $serie)->count() + 1;

        $note = $this->buildResumenAnulacion(
            $venta,
            $serie,
            $numero
        );
        // para ver su funcionamiento
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

        $ticket = null;
        $filename = null;
        DB::transaction(function () use ($venta, $serie, $numero, $ticket, $filename) {
            $venta->update([
                'estado' => EstadoVenta::ANULADO,
                'fecha_anulacion' => now(),
                'observacion' => 'Venta anulada en SUNAT',
            ]);

            $venta->pagos()->update([
                'estado' => 'AN',
            ]);

            CajaDetalle::where('venta_id', $venta->id)
                ->update([
                    'anulado' => true,
                ]);

            ComunicacionBaja::create([
                'venta_id' => $venta->id,
                'serie' => $serie,
                'correlativo' => $numero,
                'ticket' => $ticket,
                'filename' => $filename,
            ]);

            if ($venta->venta_referencia_id) {
                $venta->loadMissing('ventaReferencia');
                $venta->ventaReferencia->update([
                    'estado' => EstadoVenta::EMITIDO
                ]);
            }
        });

        return [
            'success' => true,
            'estado' => 'ANULADA_SUNAT',
            'codigo' => 200,
            'descripcion' => 'Venta anulada en SUNAT',
            'notas' => [],
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

        $see = $this->crearSee();
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
                'serie' => $venta->serie,
                'numero' => $venta->numero,
                'codigo' => $errorCode,
                'mensaje' => $errorMessage,
            ]);

            $venta->delete();

            // $venta->update([
            //     'ruta_xml' => $xmlPath,
            //     'ruta_cdr' => null,
            //     'hash' => null,
            //     'estado' => ,
            //     'observacion' => trim($errorCode . ' - ' . $errorMessage, ' -'),
            // ]);

            // if ((string) $errorCode === '1033') {
            //     throw new Exception(
            //         "SUNAT rechazó el comprobante {$venta->serie}-{$venta->numero}: ya fue registrado previamente con otros datos."
            //     );
            // }

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
            $code === 0 => EstadoVenta::EMITIDO,
            $code >= 2000 && $code <= 3999 => EstadoVenta::RECHAZADO,
            default => 'O',
        };

        $venta->update([
            'ruta_xml' => $xmlPath,
            'ruta_cdr' => $cdrPath,
            'hash' => method_exists($result, 'getHashCdr') ? $result->getHashCdr() : null,
            'estado' => $estadoInterno,
            'observacion' => $cdr->getDescription(),
        ]);

        Log::info('Comprobante emitido', [
            'venta_id' => $venta->id,
            'comprobante' => $invoice->getName(),
            'estado_sunat' => $estadoSunat,
            'code' => $code,
            'descripcion' => $cdr->getDescription(),
            'notas' => $cdr->getNotes(),
        ]);

        return [
            'success' => true,
            'estado' => $estadoSunat,
            'codigo' => $code,
            'descripcion' => $cdr->getDescription(),
            'notas' => $cdr->getNotes(),
            'xml_path' => $xmlPath,
            'cdr_path' => $cdrPath,
            'nombre' => $invoice->getName(),
        ];
    }

    private function crearSee(?string $tipoDocumento = null): See
    {
        $see = new See();

        $empresa = Empresa::first();

        if (!$empresa) {
            throw new Exception('No existe configuración de empresa.');
        }

        /**
         * PORQUE CAUDNO ES 07 RETORNAS SEE????
         */

        // if (trim(strtoupper($tipoDocumento)) === '07') {
        //     return $see;
        // }

        $certDisk = config('services.greenter.cert_disk', 'public');
        $certPath = $empresa->certificado_path;
        if (!Storage::disk($certDisk)->exists($certPath)) {
            throw new Exception("No se encontró el certificado en: {$certPath}");
        }

        $modo = $empresa->modo;

        if ($modo === 'produccion') {
            $see->setService(config('services.greenter.beta_url'));
        } else {
            $see->setService(config('services.greenter.demo_url'));
        }

        $see->setCertificate(
            Storage::disk($certDisk)->get($certPath)
        );

        $see->setCredentials("{$empresa->documento}{$empresa->usuario_facturacion}", $empresa->contrasena_facturacion);
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
            ->setUbigueo($venta->sucursal->distrito->ubigeo)
            ->setDepartamento($venta->sucursal->distrito->departamento->nombre ?? 'LIMA')
            ->setProvincia($venta->sucursal->distrito->provincia->nombre ?? 'LIMA')
            ->setDistrito($venta->sucursal->distrito->nombre ?? 'LIMA')
            ->setUrbanizacion($empresa->urbanizacion ?? '-')
            ->setDireccion($venta->sucursal->direccion)
            ->setCodLocal($venta->sucursal->codigo_sucursal ?? '0000');
        // venta->sucursal->codigo_sucursal te falta llenar

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

        /**
         * MODIFICADO PARA AMBAS OPERACIONES
         */
        $mtoOperGravadas = 0.0;
        $mtoOperExoneradas = 0.0;
        $mtoOperInafectas = 0.0; // por si acaso también manejas inafectas o si german dice que las boletas son inafectas jsjs
        $mtoIGV = 0.0;
        $valorVenta = 0.0;
        $subTotal = 0.0;
        $totalVenta = 0.0;
        $detalles = [];

        // 18, 10.5, etc. — tasa vigente para líneas gravadas
        $porcentajeIgv = $this->obtenerPorcentajeIgv($venta);

        foreach ($venta->detalles as $detalle) {
            $cantidad = (float) ($detalle->cantidad ?? 1);
            $totalLinea = round((float) ($detalle->total ?? 0), 2);

            if ($cantidad <= 0) {
                throw new Exception("La cantidad del detalle {$detalle->id} no puede ser menor o igual a cero.");
            }

            // Determina el tipo de afectación de ESTA línea.
            // hay que ponerlo en algun lado el ->exonerado, de momento true
            $esExonerado = $porcentajeIgv == 0;
            // $esExonerado = (bool) ($detalle->exonerado ?? false);

            if ($esExonerado) {
                // Exonerado (tipAfeIgv 20): el total de línea ES el valor de venta, IGV = 0
                $valorVentaLinea = $totalLinea;
                $igvLinea = 0.0;
                $porcentajeLinea = 0.0;
                $tipAfeIgv = '20';
            } else {
                // Gravado (tipAfeIgv 10): se extrae el IGV del total
                $valorVentaLinea = round($totalLinea / (1 + $porcentajeIgv), 2);
                $igvLinea = round($totalLinea - $valorVentaLinea, 2);
                $porcentajeLinea = $porcentajeIgv * 100;
                $tipAfeIgv = '10';
            }

            $precioUnitario = round($totalLinea / $cantidad, 10);
            $valorUnitarioSinIgv = round($valorVentaLinea / $cantidad, 10);

            $detalles[] = (new SaleDetail())
                ->setCodProducto((string) ($detalle->id ?? 'ITEM'))
                ->setUnidad('NIU')
                ->setCantidad($cantidad)
                ->setMtoValorUnitario($valorUnitarioSinIgv)
                ->setDescripcion($detalle->descripcion)
                ->setMtoBaseIgv($valorVentaLinea)
                ->setPorcentajeIgv($porcentajeLinea)
                ->setIgv($igvLinea)
                ->setTipAfeIgv($tipAfeIgv)
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($valorVentaLinea)
                ->setMtoPrecioUnitario($precioUnitario);

            if ($esExonerado) {
                $mtoOperExoneradas += $valorVentaLinea;
            } else {
                $mtoOperGravadas += $valorVentaLinea;
            }

            $mtoIGV += $igvLinea;
            $valorVenta += $valorVentaLinea;
            $subTotal += $totalLinea;
            $totalVenta += $totalLinea;
        }

        $formatter = new NumeroALetras();

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice($totalVenta, 2, 'SOLES'));

        $invoice = (new Invoice())
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
            ->setMtoOperExoneradas(round($mtoOperExoneradas, 2))
            ->setMtoIGV(round($mtoIGV, 2))
            ->setTotalImpuestos(round($mtoIGV, 2))
            ->setValorVenta(round($valorVenta, 2))
            ->setSubTotal(round($subTotal, 2))
            ->setMtoImpVenta(round($totalVenta, 2))
            ->setDetails($detalles)
            ->setLegends([$legend]);

        return $invoice;
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

    private function obtenerPorcentajeIgv(Venta $venta): float
    {
        return $venta->tipo_servicio_id == 1 ? 0.0 : 0.18;
    }

    private function buildNotaCreditoAnulacion(
        Venta $notaCredito,
        Venta $ventaOriginal,
        Empresa $empresa,
    ): Note {
        $cliente = $notaCredito->persona;

        if (!$cliente) {
            throw new Exception('La venta no tiene cliente asociado.');
        }

        $tipoDocAfectado = $this->mapTipoDocumentoComprobante($ventaOriginal->tipo_documento_factura_id);
        $tipoDocCliente = $this->mapTipoDocumentoClienteSunat($cliente->tipo_documento_id, $cliente->documento);

        $companyAddress = (new Address())
            ->setUbigueo($notaCredito->sucursal->distrito->ubigeo)
            ->setDepartamento($notaCredito->sucursal->distrito->departamento->nombre ?? 'LIMA')
            ->setProvincia($notaCredito->sucursal->distrito->provincia->nombre ?? 'LIMA')
            ->setDistrito($notaCredito->sucursal->distrito->nombre ?? 'LIMA')
            ->setUrbanizacion($empresa->urbanizacion ?? '-')
            ->setDireccion($notaCredito->sucursal->direccion)
            ->setCodLocal($notaCredito->sucursal->codigo_sucursal ?? '0000');
        // venta->sucursal->codigo_sucursal te falta llenar

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

        /**
         * MODIFICADO PARA AMBAS OPERACIONES Y SEA EL CALCULO CORRECTO
         */
        $mtoOperGravadas = 0.0;
        $mtoOperExoneradas = 0.0;
        $mtoOperInafectas = 0.0;
        $mtoIGV = 0.0;
        $valorVenta = 0.0;
        $subTotal = 0.0;
        $totalVenta = 0.0;
        $detalles = [];


        $porcentajeIgv = $this->obtenerPorcentajeIgv($ventaOriginal);

        foreach ($notaCredito->detalles as $detalle) {
            $cantidad = abs((float) ($detalle->cantidad ?? 1));
            $totalLinea = abs(round((float) ($detalle->total ?? 0), 2));

            if ($cantidad <= 0) {
                throw new Exception("La cantidad del detalle {$detalle->id} no puede ser menor o igual a cero.");
            }

            // Debe coincidir con el mismo criterio usado en buildInvoice
            // para el comprobante original que se está anulando.
            $esExonerado = $porcentajeIgv == 0;
            // $esExonerado = (bool) ($detalle->exonerado ?? false);

            if ($esExonerado) {
                $valorVentaLinea = $totalLinea;
                $igvLinea = 0.0;
                $porcentajeLinea = 0.0;
                $tipAfeIgv = '20';
            } else {
                $valorVentaLinea = round($totalLinea / (1 + $porcentajeIgv), 2);
                $igvLinea = round($totalLinea - $valorVentaLinea, 2);
                $porcentajeLinea = $porcentajeIgv * 100;
                $tipAfeIgv = '10';
            }

            $precioUnitario = round($totalLinea / $cantidad, 10);
            $valorUnitarioSinIgv = round($valorVentaLinea / $cantidad, 10);

            $detalles[] = (new SaleDetail())
                ->setCodProducto((string) ($detalle->id ?? 'ITEM'))
                ->setUnidad('NIU')
                ->setCantidad($cantidad)
                ->setMtoValorUnitario($valorUnitarioSinIgv)
                ->setDescripcion($detalle->descripcion)
                ->setMtoBaseIgv($valorVentaLinea)
                ->setPorcentajeIgv($porcentajeLinea)
                ->setIgv($igvLinea)
                ->setTipAfeIgv($tipAfeIgv)
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($valorVentaLinea)
                ->setMtoPrecioUnitario($precioUnitario);

            if ($esExonerado) {
                $mtoOperExoneradas += $valorVentaLinea;
            } else {
                $mtoOperGravadas += $valorVentaLinea;
            }

            $mtoIGV += $igvLinea;
            $valorVenta += $valorVentaLinea;
            $subTotal += $totalLinea;
            $totalVenta += $totalLinea;
        }

        $formatter = new NumeroALetras();

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($formatter->toInvoice(abs($totalVenta), 2, 'SOLES'));

        return (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($notaCredito->serie)
            ->setCorrelativo((string) $notaCredito->numero)
            ->setFechaEmision(new DateTime(now()->format('Y-m-d H:i:sP')))
            ->setTipDocAfectado($tipoDocAfectado)
            ->setNumDocfectado($ventaOriginal->serie . '-' . $ventaOriginal->numero)
            ->setCodMotivo('01')
            ->setDesMotivo('ANULACION DE LA OPERACION')
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(abs(round($mtoOperGravadas, 2)))
            ->setMtoOperExoneradas(abs(round($mtoOperExoneradas, 2)))
            ->setMtoIGV(abs(round($mtoIGV, 2)))
            ->setTotalImpuestos(abs(round($mtoIGV, 2)))
            ->setValorVenta(abs(round($valorVenta, 2)))
            ->setSubTotal(abs(round($subTotal, 2)))
            ->setMtoImpVenta(abs(round($totalVenta, 2)))
            ->setDetails($detalles)
            ->setLegends([$legend]);
    }

    private function buildResumenAnulacion(Venta $venta, $serie, $numero)
    {
        // factura
        if ($venta->tipo_documento_factura_id == 1) {
            return $this->buildCancelledInvoice($venta, $numero);
        }
        // boleta
        else if ($venta->tipo_documento_factura_id == 2) {
            return $this->buildCancelledBoleta($venta, $numero);
        }
        // nota de venta - no hacer nada, simplemente anular internamente.
        else if ($venta->tipo_documento_factura_id == 3) {
        }
        // nota de credito
        else if (in_array($venta->tipo_documento_factura_id, [4, 5])) {
            return $this->buildCancelledNotaCredito($venta, $numero);
        } else {
            throw new Exception("No se puede procesar el documento para su anulación", 1);
        }
    }

    private function buildCancelledInvoice(Venta $venta, int $correlativoBaja, string $code = '01')
    {
        $venta->loadMissing('sucursal.empresa');

        $companyAddress = (new Address())
            ->setUbigueo($venta->sucursal->ubigueo ?? '150101')
            ->setDepartamento($venta->sucursal->departamento ?? 'LIMA')
            ->setProvincia($venta->sucursal->provincia ?? 'LIMA')
            ->setDistrito($venta->sucursal->distrito ?? 'LIMA')
            ->setUrbanizacion($venta->sucursal->urbanizacion ?? '-')
            ->setDireccion($venta->sucursal->direccion ?? $venta->sucursal->razon_social)
            ->setCodLocal($venta->sucursal->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($venta->sucursal->empresa->documento)
            ->setRazonSocial($venta->sucursal->empresa->razon_social)
            ->setNombreComercial($venta->sucursal->empresa->nombre_comercial ?? $venta->sucursal->empresa->razon_social)
            ->setAddress($companyAddress);

        $detail = (new VoidedDetail())
            ->setTipoDoc($code)
            ->setSerie($venta->serie)
            ->setCorrelativo((string) $venta->numero)
            ->setDesMotivoBaja('ERROR EN LA EMISION'); // ajusta el motivo según tu caso real

        $voided = (new Voided())
            ->setCorrelativo((string) $correlativoBaja)
            ->setFecGeneracion($venta->fecha_emision) // fecha de emisión del comprobante original
            ->setFecComunicacion(new DateTime(now()->format('Y-m-d H:i:sP'))) // fecha de hoy
            ->setCompany($company)
            ->setDetails([$detail]);

        return $voided;
    }

    private function buildCancelledBoleta(Venta $venta, int $correlativoBaja, string $code = '03')
    {
        $venta->loadMissing('persona', 'sucursal.empresa');
        $cliente = $venta->persona;

        if (!$cliente) {
            throw new Exception('La venta no tiene cliente asociado.');
        }

        $tipoDocCliente = $this->mapTipoDocumentoClienteSunat($cliente->tipo_documento_id, $cliente->documento);

        $companyAddress = (new Address())
            ->setUbigueo($venta->sucursal->ubigueo ?? '150101')
            ->setDepartamento($venta->sucursal->departamento ?? 'LIMA')
            ->setProvincia($venta->sucursal->provincia ?? 'LIMA')
            ->setDistrito($venta->sucursal->distrito ?? 'LIMA')
            ->setUrbanizacion($venta->sucursal->urbanizacion ?? '-')
            ->setDireccion($venta->sucursal->direccion ?? $venta->sucursal->razon_social)
            ->setCodLocal($venta->sucursal->cod_local ?? '0000');

        $company = (new Company())
            ->setRuc($venta->sucursal->empresa->documento)
            ->setRazonSocial($venta->sucursal->empresa->razon_social)
            ->setNombreComercial($venta->sucursal->empresa->nombre_comercial ?? $venta->sucursal->empresa->razon_social)
            ->setAddress($companyAddress);

        // Recalcula los mismos montos que tuvo la boleta original,
        // respetando gravado/exonerado por línea (igual que buildInvoice)
        $mtoOperGravadas = 0.0;
        $mtoOperExoneradas = 0.0;
        $mtoIGV = 0.0;
        $totalVenta = 0.0;

        $igv = (float) $venta->sucursal->empresa->igv;
        $porcentajeIgv = $this->obtenerPorcentajeIgv($venta);

        foreach ($venta->detalles as $detalle) {
            $totalLinea = round((float) ($detalle->total ?? 0), 2);

            $esExonerado = $porcentajeIgv == 0;
            // $esExonerado = (bool) ($detalle->exonerado ?? false);

            if ($esExonerado) {
                $valorVentaLinea = $totalLinea;
                $igvLinea = 0.0;
            } else {
                $valorVentaLinea = round($totalLinea / (1 + $porcentajeIgv), 2);
                $igvLinea = round($totalLinea - $valorVentaLinea, 2);
            }

            if ($esExonerado) {
                $mtoOperExoneradas += $valorVentaLinea;
            } else {
                $mtoOperGravadas += $valorVentaLinea;
            }

            $mtoIGV += $igvLinea;
            $totalVenta += $totalLinea;
        }

        $item = (new SummaryDetail())
            ->setTipoDoc($code)
            ->setSerieNro($venta->serie . '-' . $venta->numero)
            ->setEstado('3') // 3 = de baja / anulado
            ->setClienteTipo($tipoDocCliente)
            ->setClienteNro($cliente->documento ?: '00000000')
            ->setTotal(abs(round($totalVenta, 2)))
            ->setMtoOperGravadas(abs(round($mtoOperGravadas, 2)))
            ->setMtoOperExoneradas(abs(round($mtoOperExoneradas, 2)))
            ->setMtoIGV(abs(round($mtoIGV, 2)));

        $summary = (new Summary())
            ->setFecGeneracion($venta->fecha_emision) // fecha real de emisión de la boleta
            ->setFecResumen(new DateTime(now()->format('Y-m-d H:i:sP')))
            ->setCorrelativo((string) $correlativoBaja)
            ->setCompany($company)
            ->setDetails([$item]);

        return $summary;
    }

    private function buildCancelledNotaCredito(Venta $venta, int $correlativoBaja)
    {
        $venta->loadMissing('persona', 'sucursal.empresa');

        // POR ESTE MOTIVO, TUS NC DEBEN EMPEZAR CON F O B DEPENDIENDO DEL TIPO DE DOCUMENTO
        // es boleta
        if (str_starts_with($venta->serie, "B")) {
            return $this->buildCancelledBoleta($venta, $correlativoBaja, '07');
        } else {
            // es factura
            return $this->buildCancelledInvoice($venta, $correlativoBaja, '07');
        }
    }
}

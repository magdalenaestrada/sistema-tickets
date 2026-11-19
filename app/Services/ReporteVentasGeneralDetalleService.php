<?php

namespace App\Services;

use App\Models\Venta;
use BackedEnum;
use Illuminate\Support\Collection;

class ReporteVentasGeneralDetalleService
{
    /**
     * Genera una fila por comprobante.
     * Evita duplicar importes por servicios o medios de pago.
     */
    public function construir(Collection $ventas): Collection
    {
        $ventas->loadMissing([
            'detalles.tipoServicio',
            'pasajes',
            'encomiendas',
            'pagos.metodoPago',
            'pagos.billetera',
            'persona',
            'usuario.persona',
            'sucursal',
        ]);

        return $ventas->map(
            fn(Venta $venta) => $this->fila($venta)
        );
    }

    private function fila(Venta $venta): array
    {
        $detalles = $venta->detalles;

        // TIPOS DE SERVICIO
        $categorias = $detalles->map(function ($detalle) {
            return trim((string) (
                $detalle->tipoServicio?->nombre
                ?? $detalle->tipoServicio?->descripcion
                ?? 'SERVICIO NO IDENTIFICADO'
            ));
        })->unique()->values();

        if ($detalles->isEmpty()) {
            $categorias = collect();

            if ($venta->pasajes->isNotEmpty()) {
                $categorias->push('PASAJE');
            }

            if ($venta->encomiendas
                ->where('sobre_equipaje', false)
                ->isNotEmpty()
            ) {
                $categorias->push('ENCOMIENDA');
            }

            if ($venta->encomiendas
                ->where('sobre_equipaje', true)
                ->isNotEmpty()
            ) {
                $categorias->push('SOBREEQUIPAJE');
            }
        }

        // MEDIOS DE PAGO
        $medios = $venta->pagos->map(function ($pago) {
            $nombre = $pago->metodoPago?->nombre
                ?? $pago->metodoPago?->descripcion
                ?? 'SIN MÉTODO';

            $billetera = $pago->billetera?->nombre
                ?? $pago->billetera?->descripcion;

            return mb_strtoupper(
                $billetera
                    ? "BILLETERA DIGITAL - {$billetera}"
                    : $nombre
            );
        })->unique()->implode(', ');

        /*
        |--------------------------------------------------------------------------
        | CÁLCULO TRIBUTARIO
        |--------------------------------------------------------------------------
        | 10 = Gravada
        | 20 = Exonerada
        | 30 = Inafecta
        |
        | Compatibilidad temporal:
        | El Excel y PDF actuales leen la operación
        | exonerada desde la clave 'inafecta'.
        |--------------------------------------------------------------------------
        */

        $baseImponible = 0.0;
        $igv = 0.0;
        $exonerada = 0.0;
        $inafectaReal = 0.0;
        $hayAfectacion = false;

        foreach ($detalles as $detalle) {

            $codigo = trim(
                (string) ($detalle->tipo_afectacion_igv ?? '')
            );

            $valorVenta = (float) (
                $detalle->valor_venta ?? 0
            );

            switch ($codigo) {
                case '10':
                    $hayAfectacion = true;

                    $baseImponible += (float) (
                        $detalle->base_igv ?? 0
                    );

                    $igv += (float) (
                        $detalle->igv ?? 0
                    );
                    break;

                case '20':
                    $hayAfectacion = true;
                    $exonerada += $valorVenta;
                    break;

                case '30':
                    $hayAfectacion = true;
                    $inafectaReal += $valorVenta;
                    break;
            }
        }

        // Para registros antiguos sin clasificación tributaria,
        // no inventamos una distribución entre gravada y exonerada.
        $baseReporte = $hayAfectacion
            ? round($baseImponible, 2)
            : 0.0;

        $igvReporte = $hayAfectacion
            ? round($igv, 2)
            : (float) ($venta->impuesto ?? 0);

        $estado = $venta->estado instanceof BackedEnum
            ? $venta->estado->value
            : (string) $venta->estado;

        return [
            'fecha' => $venta->fecha_emision
                ?->format('d/m/Y H:i') ?? '',

            'comprobante' => trim(
                ($venta->serie ?? '') . '-' .
                    ($venta->numero ?? ''),
                '-'
            ),

            'serie' => (string) ($venta->serie ?? ''),
            'numero' => (string) ($venta->numero ?? ''),

            'operacion' => (string) $venta->id,

            'documento' => (string) (
                $venta->persona?->documento ?? ''
            ),

            'cliente' => $venta->persona?->nombre_completo
                ?? $venta->persona?->razon_social
                ?? 'SIN CLIENTE',

            'tipo_servicio' => $categorias->isNotEmpty()
                ? $categorias->implode(', ')
                : 'SIN DETALLE',

            'detalle_servicio' => $detalles
                ->pluck('descripcion')
                ->filter()
                ->unique()
                ->implode(' | ')
                ?: ($venta->observacion ?: '-'),

            // BASE IMPONIBLE: venta_detalles.base_igv
            'subtotal' => $baseReporte,

            // IMPUESTO: venta_detalles.igv
            'igv' => $igvReporte,

            // Valor exonerado real de los detalles
            'exonerada' => round($exonerada + $inafectaReal, 2),
            'inafecta' => round($inafectaReal, 2),

            'total' => (float) $venta->total,

            'medio_pago' => $medios ?: 'SIN MÉTODO',

            'vendedor' => $venta->usuario?->persona
                ?->nombre_completo
                ?? $venta->usuario?->name
                ?? 'SIN USUARIO',

            'estado' => $estado,

            'sucursal' => $venta->sucursal?->nombre_comercial
                ?? $venta->sucursal?->nombre
                ?? 'SIN SUCURSAL',
        ];
    }
}

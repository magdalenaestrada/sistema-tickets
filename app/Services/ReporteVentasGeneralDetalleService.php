<?php

namespace App\Services;

use App\Models\Venta;
use BackedEnum;
use Illuminate\Support\Collection;

/** Una fila por comprobante para evitar duplicar importes por pago o detalle. */
class ReporteVentasGeneralDetalleService
{
    public function construir(Collection $ventas): Collection
    {
        return $ventas->map(fn (Venta $venta) => $this->fila($venta));
    }

    private function fila(Venta $venta): array
    {
        $detalles = $venta->detalles;
        $categorias = $detalles->map(function ($detalle) {
            $nombre = trim((string) ($detalle->tipoServicio?->nombre ?? $detalle->tipoServicio?->descripcion ?? ''));
            if ($nombre !== '') return $nombre;
            return match ((string) ($detalle->tipo_servicio_id ?? '')) {
                default => 'SERVICIO NO IDENTIFICADO',
            };
        })->unique()->values();

        // Respaldo para comprobantes históricos sin VentaDetalle.
        if ($categorias->isEmpty()) {
            if ($venta->pasajes->isNotEmpty()) $categorias->push('PASAJE');
            if ($venta->encomiendas->where('sobre_equipaje', false)->isNotEmpty()) $categorias->push('ENCOMIENDA');
            if ($venta->encomiendas->where('sobre_equipaje', true)->isNotEmpty()) $categorias->push('SOBREEQUIPAJE');
        }

        $medios = $venta->pagos->map(function ($pago) {
            $nombre = $pago->metodoPago?->nombre ?? $pago->metodoPago?->descripcion ?? 'SIN MÉTODO';
            $billetera = $pago->billetera?->nombre ?? $pago->billetera?->descripcion;
            return mb_strtoupper($billetera ? "BILLETERA DIGITAL - {$billetera}" : $nombre);
        })->unique()->implode(', ');

        // Códigos SUNAT 10: gravada; 20: exonerada; 30: inafecta.
        // No derivar exonerado/inafecto del total del comprobante: pueden coexistir afectaciones.
        $gravada = 0.0;
        $igvDetalles = 0.0;
        $exonerada = 0.0;
        $inafecta = 0.0;
        $hayAfectacion = false;

        foreach ($detalles as $detalle) {
            $codigo = trim((string) ($detalle->tipo_afectacion_igv ?? ''));
            if ($codigo === '') continue;

            $hayAfectacion = true;
            $valor = (float) ($detalle->valor_venta ?? 0);

            if (str_starts_with($codigo, '10')) {
                $gravada += $valor;
                $igvDetalles += (float) ($detalle->igv ?? 0);
            } elseif (str_starts_with($codigo, '20')) {
                $exonerada += $valor;
            } elseif (str_starts_with($codigo, '30')) {
                $inafecta += $valor;
            }
        }

        // Para comprobantes con detalle tributario confiamos en sus líneas.
        // Solo en comprobantes históricos sin afectación usamos la cabecera.
        $subtotalReporte = $hayAfectacion
            ? round($gravada, 2)
            : (float) ($venta->subtotal ?? 0);
        $igvReporte = $hayAfectacion
            ? round($igvDetalles, 2)
            : (float) ($venta->impuesto ?? 0);

        $estado = $venta->estado instanceof BackedEnum
            ? $venta->estado->value : (string) $venta->estado;

        return [
            'fecha' => $venta->fecha_emision?->format('d/m/Y H:i') ?? '',
            'comprobante' => trim(($venta->serie ?? '') . '-' . ($venta->numero ?? ''), '-'),
            'serie' => (string) ($venta->serie ?? ''),
            'numero' => (string) ($venta->numero ?? ''),
            // ID interno, NO número de operación de banco/Yape.
            'operacion' => (string) $venta->id,
            'documento' => (string) ($venta->persona?->documento ?? ''),
            'cliente' => $venta->persona?->nombre_completo ?? $venta->persona?->razon_social ?? 'SIN CLIENTE',
            'tipo_servicio' => $categorias->isNotEmpty() ? $categorias->implode(', ') : 'SIN DETALLE',
            'detalle_servicio' => $detalles->pluck('descripcion')->filter()->unique()->implode(' | ') ?: ($venta->observacion ?: '-'),
            'subtotal' => $subtotalReporte,
            'igv' => $igvReporte,
            'exonerada' => $hayAfectacion ? $exonerada : null,
            'inafecta' => $hayAfectacion ? $inafecta : null,
            'total' => (float) ($venta->total ?? 0),
            'medio_pago' => $medios ?: 'SIN MÉTODO',
            'vendedor' => $venta->usuario?->persona?->nombre_completo ?? $venta->usuario?->name ?? 'SIN USUARIO',
            'estado' => $estado,
            'sucursal' => $venta->sucursal?->nombre_comercial ?? $venta->sucursal?->nombre ?? 'SIN SUCURSAL',
        ];
    }
}

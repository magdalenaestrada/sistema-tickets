@php
    $empresa = $venta->sucursal?->empresa;
    $cliente = $venta->persona;

    $opGravada = 0;
    $opInafecta = 0;
    $opExonerada = 0;
    $igvTotal = 0;

    foreach ($venta->detalles as $detalle) {
        if ($detalle->tipo_afectacion_igv == 10) {
            $opGravada += $detalle->base_igv ?? 0;
            $igvTotal += $detalle->igv ?? 0;
        } elseif ($detalle->tipo_afectacion_igv == 20) {
            $opExonerada += $detalle->total ?? 0;
        } elseif ($detalle->tipo_afectacion_igv == 30) {
            $opInafecta += $detalle->total ?? 0;
        }
    }
@endphp

{{-- ENCABEZADO --}}
<div class="center">

    @if ($venta->estado === \App\Enums\EstadoVenta::ANULADO || $venta->fecha_anulacion)
        <div class="anulado">*** ANULADO ***</div>
    @endif

    @if ($empresa?->logo)
        <div class="logo-container">
            <img src="{{ !empty($esPdf) && !empty($logoPath) ? $logoPath : asset('storage/' . $empresa->logo) }}"
                alt="Logo">
        </div>
    @endif

    <div class="bold" style="font-size: 12px;">
        {{ $empresa?->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
    </div>

    <div class="bold" style="font-size: 10px;">
        RUC: {{ $empresa?->documento ?? '—' }}
    </div>

    <div style="font-size: 10px;">
        {{ $venta->sucursal?->direccion ?? ($empresa?->direccion ?? '—') }}
    </div>

    <div class="line"></div>

    <div class="documento">
        <div class="tipo bold" style="font-size: 12px; text-transform: uppercase;">
            {{ $venta->tipoDocumentoFactura?->descripcion ?? 'COMPROBANTE' }}
        </div>

        <div class="numero bold" style="font-size: 14px;">
            {{ $venta->serie }} - {{ $venta->numero }}
        </div>
    </div>
</div>

<div class="line"></div>

{{-- CLIENTE Y EMISIÓN --}}
<div class="bold" style="font-size: 10px; margin-bottom: 4px;">
    DATOS DEL COMPROBANTE
</div>

<table class="table-data w-100">
    <tr>
        <td class="bold" style="width: 35%;">Cliente:</td>
        <td class="right bold">
            {{ $cliente?->nombre_facturacion ?? ($cliente?->nombre_completo ?? 'CLIENTE VARIOS') }}
        </td>
    </tr>

    @if ($cliente?->documento)
        <tr>
            <td class="bold">DNI/RUC:</td>
            <td class="right">{{ $cliente->documento }}</td>
        </tr>
    @endif

    @if ($cliente?->direccion)
        <tr>
            <td class="bold">Dirección:</td>
            <td class="right">{{ $cliente->direccion }}</td>
        </tr>
    @endif

    <tr>
        <td class="bold">F. Emisión:</td>
        <td class="right">
            {{ ($venta->fecha_emision ?? $venta->created_at)?->format('d/m/Y H:i') }}
        </td>
    </tr>

    <tr>
        <td class="bold">Cajero:</td>
        <td class="right">
            {{ $venta->usuario?->persona?->nombre_completo ?? 'Sistema' }}
        </td>
    </tr>
</table>

<div class="line"></div>

{{-- DETALLE --}}
<div class="bold" style="font-size: 10px; margin-bottom: 4px;">
    DETALLE DEL COMPROBANTE
</div>

<table class="table-items w-100">
    <thead>
        <tr>
            <th class="left" style="width: 60%;">Descripción</th>
            <th class="center" style="width: 15%;">Cant.</th>
            <th class="right" style="width: 25%;">Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($venta->detalles as $detalle)
            <tr>
                <td class="left">
                    {{ $detalle->descripcion }}
                </td>

                <td class="center">
                    {{ number_format($detalle->cantidad, 0) }}
                </td>

                <td class="right">
                    S/ {{ number_format($detalle->total, 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

{{-- RESUMEN --}}
<table class="table-data w-100">

    @if ($opGravada > 0)
        <tr>
            <td class="bold">Op. Gravada:</td>
            <td class="right">S/ {{ number_format($opGravada, 2) }}</td>
        </tr>
    @endif

    @if ($opInafecta > 0)
        <tr>
            <td class="bold">Op. Inafecta:</td>
            <td class="right">S/ {{ number_format($opInafecta, 2) }}</td>
        </tr>
    @endif

    @if ($opExonerada > 0)
        <tr>
            <td class="bold">Op. Exonerada:</td>
            <td class="right">S/ {{ number_format($opExonerada, 2) }}</td>
        </tr>
    @endif

    @if ($igvTotal > 0)
        <tr>
            <td class="bold">IGV:</td>
            <td class="right">S/ {{ number_format($igvTotal, 2) }}</td>
        </tr>
    @endif

    <tr style="border-top: 1px dashed #000;">
        <td class="bold" style="padding-top: 5px; font-size: 12px;">
            TOTAL:
        </td>
        <td class="right bold" style="padding-top: 5px; font-size: 14px;">
            S/ {{ number_format($venta->total, 2) }}
        </td>
    </tr>
</table>

<div class="line"></div>

{{-- OBSERVACIONES --}}
@if ($venta->observacion)
    <div style="font-size: 10px; margin-bottom: 5px;">
        <strong>Observaciones:</strong>
        {{ $venta->observacion }}
    </div>
    <div class="line"></div>
@endif

{{-- PIE --}}
<div class="center" style="font-size: 10px; margin-top: 5px;">
    <div class="bold">¡Gracias por su preferencia!</div>
    <div style="margin-top: 3px;">
        Conserve este comprobante.
    </div>
</div>

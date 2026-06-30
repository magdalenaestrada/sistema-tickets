{{-- resources/views/encomiendas/ticket.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $venta->serie }}-{{ $venta->numero }}</title>

    <style>
        @page { size: 80mm auto; margin: 0; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, monospace;
            width: 260px;
            margin: 0 auto;
            padding: 10px 5px;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            background-color: #fff;
        }

        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .right  { text-align: right; }
        .left   { text-align: left; }

        .logo-container     { text-align: center; margin-bottom: 4px; }
        .logo-container img { max-width: 110px; height: auto; display: inline-block; }

        .line { border-top: 1px dashed #000; margin: 5px 0; height: 0; }

        .anulado {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            margin-bottom: 5px;
            padding: 2px;
        }

        .w-100 { width: 100%; border-collapse: collapse; }

        .table-data td { padding: 1px 0; vertical-align: top; font-size: 11px; }

        .table-items th {
            border-bottom: 1px dashed #000;
            font-weight: bold;
            font-size: 11px;
            padding-bottom: 2px;
        }
        .table-items td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11px;
            word-break: break-word;
        }

        .btn-print {
            display: block;
            width: 100%;
            background-color: #000;
            color: #fff;
            border: none;
            padding: 6px;
            margin-top: 15px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-weight: bold;
            border-radius: 4px;
            text-align: center;
            font-size: 11px;
        }

        @media print {
            .btn-print { display: none !important; }
            body { width: 100%; padding: 5px; }
        }
    </style>

    <script>window.onload = function () { window.print(); };</script>
</head>

<body>

@if ($venta->estado == 'ANULADO' || $venta->fecha_anulacion)
    <div class="anulado">*** ANULADO ***</div>
@endif

@php
    $empresa = $venta->sucursal?->empresa;
    $emisor  = $encomienda->emisor;      // quien envía
    $receptor = $encomienda->receptor;   // quien recibe

    // Descuento (ajusta el campo según tu modelo)
    $montoDescuento = 0;

    // Op. Gravada
    $opGravada = $venta->subtotal ?? ($venta->total - $venta->impuesto);
@endphp

{{-- ── ENCABEZADO ── --}}
<div class="center">
    @if ($empresa && $empresa->logo)
        <div class="logo-container">
            <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo">
        </div>
    @endif

    <div class="bold" style="font-size: 12px;">
        {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
    </div>
    <div class="bold">RUC: {{ $empresa->documento ?? '20513247495' }}</div>
    <div style="font-size: 10px;">
        {{ $venta->sucursal->direccion ?? ($empresa->direccion ?? 'Av. El Sol 789') }}
    </div>

    <div class="line"></div>

    <div class="bold" style="text-transform: uppercase;">
        {{ $venta->tipoDocumentoFactura->descripcion ?? 'NOTA DE VENTA' }}
    </div>
    <div class="bold" style="font-size: 12px;">{{ $venta->serie }} - {{ $venta->numero }}</div>

    <div class="line"></div>
</div>

{{-- ── EMISIÓN ── --}}
<table class="table-data w-100">
    <tr>
        <td class="bold">F. Emisión:</td>
        <td class="right">
            {{ $venta->fecha_emision
                ? $venta->fecha_emision->format('d/m/Y H:i')
                : $venta->created_at->format('d/m/Y H:i') }}
        </td>
    </tr>
    <tr>
        <td class="bold">Cajero:</td>
        <td class="right">{{ $venta->usuario->persona->nombre_completo ?? 'Sistema' }}</td>
    </tr>
</table>

<div class="line"></div>

{{-- ── DATOS DEL EMISOR ── --}}
<div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DEL EMISOR</div>
<table class="table-data w-100">
    <tr>
        <td class="bold" style="width: 35%;">Nombre:</td>
        <td class="right">
            {{ $emisor
                ? $emisor->nombres . ' ' . $emisor->apellidos
                : 'CLIENTE VARIOS' }}
        </td>
    </tr>
    <tr>
        <td class="bold">Documento:</td>
        <td class="right">{{ $emisor->documento ?? '00000000' }}</td>
    </tr>
    @if (!empty($emisor->telefono))
    <tr>
        <td class="bold">Teléfono:</td>
        <td class="right">{{ $emisor->telefono }}</td>
    </tr>
    @endif
</table>

<div class="line"></div>

{{-- ── DATOS DEL RECEPTOR ── --}}
<div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DEL RECEPTOR</div>
<table class="table-data w-100">
    <tr>
        <td class="bold" style="width: 35%;">Nombre:</td>
        <td class="right">
            {{ $receptor
                ? $receptor->nombres . ' ' . $receptor->apellidos
                : 'CLIENTE VARIOS' }}
        </td>
    </tr>
    <tr>
        <td class="bold">Documento:</td>
        <td class="right">{{ $receptor->documento ?? '00000000' }}</td>
    </tr>
    @if (!empty($receptor->telefono))
    <tr>
        <td class="bold">Teléfono:</td>
        <td class="right">{{ $receptor->telefono }}</td>
    </tr>
    @endif
</table>

<div class="line"></div>

{{-- ── DATOS DE LA ENCOMIENDA ── --}}
<div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DE LA ENCOMIENDA</div>
<table class="table-data w-100">
    <tr>
        <td class="bold" style="width: 35%;">Origen:</td>
        <td class="right">
            {{ $encomienda->origenPueblito?->descripcion ?? '—' }}
        </td>
    </tr>
    <tr>
        <td class="bold">Destino:</td>
        <td class="right">
            {{ $encomienda->destinoPueblito?->descripcion ?? '—' }}
        </td>
    </tr>
    <tr>
        <td class="bold">F. Registro:</td>
        <td class="right">
            {{ $encomienda->created_at->format('d/m/Y') }}
        </td>
    </tr>
    {{-- Si la encomienda va en una salida programada, muestra la hora --}}
    @if (!empty($encomienda->salida?->hora_salida ?? $encomienda->salida?->hora))
    <tr>
        <td class="bold">H. Salida:</td>
        <td class="right">
            {{ $encomienda->salida->hora_salida ?? $encomienda->salida->hora }}
        </td>
    </tr>
    @endif
    @if (!empty($encomienda->codigo))
    <tr>
        <td class="bold">Cód. Envío:</td>
        <td class="right bold">{{ $encomienda->codigo }}</td>
    </tr>
    @endif
</table>

<div class="line"></div>

{{-- ── ÍTEMS ── --}}
<table class="table-items w-100">
    <thead>
        <tr>
            <th class="left"   style="width: 60%;">Descripción</th>
            <th class="center" style="width: 15%;">Peso</th>
            <th class="right"  style="width: 25%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($encomienda->detalles as $detalle)
            <tr>
                <td class="left">
                    {{ $detalle->tipo_encomienda?->descripcion ?? '-' }}
                    @if ($detalle->descripcion)
                        <br><span style="font-size: 10px;">{{ $detalle->descripcion }}</span>
                    @endif
                </td>
                <td class="center">
                    {{ $detalle->peso ? number_format($detalle->peso, 1) . ' kg' : '—' }}
                </td>
                <td class="right">S/ {{ number_format($detalle->costo, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

@php
    $monto_total = $encomienda->detalles->sum('costo');
@endphp
<table class="table-data w-100">
    <tr>
        <td class="bold">Op. Gravada:</td>
        <td class="right">S/ {{ number_format($opGravada, 2) }}</td>
    </tr>
    <tr>
        <td class="bold">IGV ({{ $empresa->igv ?? 18 }}.%):</td>
        <td class="right">S/ {{ number_format($venta->impuesto, 2) }}</td>
    </tr>
    <tr>
        <td class="bold">Descuentos:</td>
        <td class="right">- S/ {{ number_format($montoDescuento, 2) }}</td>
    </tr>
    <tr style="font-size: 12px; border-top: 1px dashed #000;">
        <td class="bold" style="padding-top: 3px;">TOTAL A PAGAR:</td>
        <td class="right bold" style="padding-top: 3px;">
            S/ {{ number_format($monto_total, 2) }}
        </td>
    </tr>
</table>

<div class="line"></div>

{{-- ── PAGOS (opcional, si cargas la relación) ── --}}
@if ($venta->pagos?->isNotEmpty())
    <div class="bold" style="font-size: 10px; margin-bottom: 2px;">FORMA DE PAGO</div>
    <table class="table-data w-100">
        @foreach ($venta->pagos as $pago)
            <tr>
                <td class="bold">{{ $pago->metodoPago->descripcion ?? 'Efectivo' }}:</td>
                <td class="right">S/ {{ number_format($pago->monto, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="line"></div>
@endif

{{-- ── PIE ── --}}
@if ($venta->observacion)
    <div style="font-size: 10px; font-style: italic; margin-bottom: 5px; word-break: break-word;">
        <strong>Obs:</strong> {{ $venta->observacion }}
    </div>
    <div class="line"></div>
@endif

<div class="center" style="font-size: 10px;">
    <div>¡Gracias por su compra!</div>
    <div>Representación impresa de la</div>
    <div class="bold">
        {{ $venta->tipoDocumentoFactura->descripcion ?? 'Nota de venta' }} Electrónica.
    </div>
</div>

<button class="btn-print" onclick="window.print()">Imprimir Ticket</button>

</body>
</html>
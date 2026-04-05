<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket Pasaje</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 11px;
        }

        .xs {
            font-size: 10px;
        }

        .mt-1 {
            margin-top: 4px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
        }

        .logo {
            max-width: 140px;
            max-height: 70px;
            margin-bottom: 5px;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 3mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    @php
        $empresa = $pasaje->origen?->empresa;
        $venta = $pasaje->venta;
    @endphp

    <div class="center">
        {{-- Si luego agregas logo en empresa --}}
        {{-- @if ($empresa?->logo)
            <img src="{{ asset('storage/' . $empresa->logo) }}" class="logo" alt="Logo">
        @endif --}}
    </div>

    <div class="center bold">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
    <div class="center small">RUC: {{ $empresa->documento ?? '-' }}</div>
    <div class="center small">{{ $empresa->direccion ?? '-' }}</div>
    <div class="center small">Sucursal: {{ $pasaje->origen?->nombre_comercial ?? '-' }}</div>

    <div class="line"></div>

    <div class="center bold">TICKET DE PASAJE</div>
    <div class="center small">
        {{ $venta?->serie ? $venta->serie . '-' . $venta->numero : 'Sin comprobante' }}
    </div>

    <div class="line"></div>

    <table>
        <tr>
            <td><strong>Fecha emisión:</strong></td>
            <td class="right">{{ optional($venta?->fecha_emision)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Estado:</strong></td>
            @if ($pasaje->estado === 'V')
                <td class="right">Vendido</td>
            @endif
        </tr>
        <tr>
            <td><strong>Cajero:</strong></td>
            <td class="right">{{ $pasaje->usuario?->persona?->nombres ?? ($pasaje->usuario?->username ?? '-') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div><strong>Pasajero:</strong></div>
    <div>{{ $pasaje->persona?->nombres }} {{ $pasaje->persona?->apellidos }}</div>
    <div><strong>Documento:</strong> {{ $pasaje->persona?->documento ?? '-' }}</div>

    <div class="line"></div>

    <table>
        <tr>
            <td><strong>Origen:</strong></td>
            <td class="right">{{ $pasaje->origen?->nombre_comercial ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Destino:</strong></td>
            <td class="right">{{ $pasaje->destino?->nombre_comercial ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Fecha viaje:</strong></td>
            <td class="right">{{ optional($pasaje->salida?->fecha_salida)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Asiento:</strong></td>
            <td class="right">{{ $pasaje->asiento_numero ?? '-' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="bold">DETALLE</div>
    <table>
        <tr>
            <td>Pasaje</td>
            <td class="right">S/ {{ number_format($pasaje->precio_cobrado ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="bold">PAGOS</div>
    @forelse($venta?->pagos ?? [] as $pago)
        <table>
            <tr>
                <td>{{ $pago->metodoPago?->descripcion ?? 'Método' }}</td>
                <td class="right">S/ {{ number_format($pago->total, 2) }}</td>
            </tr>
        </table>
    @empty
        <div class="small">Sin pagos registrados</div>
    @endforelse

    <div class="line"></div>

    <table>
        <tr>
            <td class="bold">TOTAL</td>
            <td class="right bold">S/ {{ number_format($pasaje->precio_cobrado ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="center xs">Gracias por su preferencia</div>
    <div class="center xs">Conserve este comprobante</div>

    <div class="mt-3 center no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>

</html>

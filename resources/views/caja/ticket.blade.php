<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $venta->serie }}-{{ $venta->numero }}</title>

    <style>
        /* CONFIGURACIÓN CRUCIAL PARA PDF / IMPRESIÓN DE TICKETERA */
        @page {
            /* 80mm de ancho. Si usas papel de 58mm, cambia a '58mm auto' */
            size: 80mm auto;
            margin: 0;
            /* Elimina los márgenes del PDF (encabezados de fecha/URL del navegador) */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            /* Ancho fijo para simular el rollo en pantalla y centrar el contenido */
            width: 260px;
            margin: 0 auto;
            padding: 10px 5px;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            background-color: #fff;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        /* Contenedor del Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-container img {
            max-width: 110px;
            height: auto;
            display: inline-block;
        }

        /* Líneas discontinuas idénticas a las de tu imagen */
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            height: 0;
        }

        .anulado {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            margin-bottom: 5px;
            padding: 2px;
        }

        .w-100 {
            width: 100%;
            border-collapse: collapse;
        }

        /* Tablas de datos clave-valor */
        .table-data td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 11px;
        }

        /* Tabla de ítems */
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

        /* Botón de control en pantalla */
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

        /* Comportamiento estricto al generar el PDF / Imprimir */
        @media print {
            .btn-print {
                display: none !important;
            }

            body {
                width: 100%;
                /* Toma el ancho definido en @page */
                padding: 5px;
                /* Pequeño colchón interno para que no pegue al borde del papel */
            }
        }
    </style>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>

<body>

    @if ($venta->estado == 'ANULADO' || $venta->fecha_anulacion)
        <div class="anulado">*** ANULADO ***</div>
    @endif

    @php
        $empresa = $venta->sucursal?->empresa;
        $cliente = $venta->persona;
    @endphp

    {{-- ENCABEZADO --}}
    <div class="center">
        @if ($empresa && $empresa->logo)
            <div class="logo-container">
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo">
            </div>
        @endif

        <div class="bold" style="font-size: 12px;">{{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}</div>
        <div class="bold">RUC: {{ $empresa->documento ?? '20513247495' }}</div>
        <div style="font-size: 10px;">{{ $venta->sucursal->direccion ?? ($empresa->direccion ?? 'Av. El Sol 789') }}
        </div>

        <div class="line"></div>

        <div class="bold" style="text-transform: uppercase;">
            {{ $venta->tipoDocumentoFactura->descripcion ?? 'NOTA DE VENTA' }}</div>
        <div class="bold" style="font-size: 12px;">{{ $venta->serie }} - {{ $venta->numero }}</div>

        <div class="line"></div>
    </div>

    {{-- EMISIÓN --}}
    <table class="table-data w-100">
        <tr>
            <td class="bold">F. Emisión:</td>
            <td class="right">
                {{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="bold">Cajero:</td>
            <td class="right">{{ $venta->usuario->persona->nombre_completo ?? 'Sistema' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- CLIENTE --}}
    <div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DEL CLIENTE</div>
    <table class="table-data w-100">
        <tr>
            <td class="bold" style="width: 30%;">Cliente:</td>
            <td class="right">{{ $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : 'CLIENTE VARIOS' }}</td>
        </tr>
        <tr>
            <td class="bold">Documento:</td>
            <td class="right">{{ $cliente->documento ?? '00000000' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- ÍTEMS --}}
    <table class="table-items w-100">
        <thead>
            <tr>
                <th class="left" style="width: 65%;">Descripción</th>
                <th class="center" style="width: 10%;">Cant</th>
                <th class="right" style="width: 25%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $detalle)
                <tr>
                    <td class="left">
                        {{ $detalle->descripcion }}
                       
                    </td>
                    <td class="center">{{ number_format($detalle->cantidad, 0) }}</td>
                    <td class="right">S/ {{ number_format($detalle->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- TOTALES --}}
    <table class="table-data w-100">
        <tr>
            <td class="bold">Op. Gravada:</td>
            <td class="right">S/ {{ number_format($venta->subtotal ?? $venta->total - $venta->impuesto, 2) }}</td>
        </tr>
        <tr>
            <td class="bold">IGV ({{ $empresa->igv ?? 18 }}.%):</td>
            <td class="right">S/ {{ number_format($venta->impuesto, 2) }}</td>
        </tr>
        <tr style="font-size: 12px;">
            <td class="bold">TOTAL A PAGAR:</td>
            <td class="right bold">S/ {{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- PIE DE PÁGINA --}}
    @if ($venta->observacion)
        <div style="font-size: 10px; font-style: italic; margin-bottom: 5px; word-break: break-word;">
            <strong>Obs:</strong> {{ $venta->observacion }}
        </div>
        <div class="line"></div>
    @endif

    <div class="center" style="font-size: 10px;">
        <div>¡Gracias por su compra!</div>
        <div>Representación impresa de la</div>
        <div class="bold">{{ $venta->tipoDocumentoFactura->descripcion ?? 'Nota de venta' }} Electrónica.</div>
    </div>

    <button class="btn-print" onclick="window.print()">Imprimir Ticket</button>

</body>

</html>

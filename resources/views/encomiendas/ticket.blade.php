{{-- resources/views/encomiendas/ticket.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $venta->serie }}-{{ $venta->numero }}</title>

    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 270px;
            margin: auto;
            padding: 8px;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            line-height: 1.3;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .logo-container {
            margin-bottom: 6px;
        }

        .logo-container img {
            max-width: 95px;
            max-height: 70px;
        }

        .empresa {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .ruc {
            font-size: 11px;
            font-weight: bold;
        }

        .direccion {
            font-size: 10px;
        }

        .documento {
            border: 1px solid #000;
            padding: 4px;
            margin: 6px 0;
        }

        .documento .tipo {
            font-size: 11px;
            font-weight: bold;
        }

        .documento .numero {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            background: #efefef;
            padding: 3px;
            margin-bottom: 4px;
            border: 1px solid #d9d9d9;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
        }

        .table-data td {
            padding: 2px 0;
            vertical-align: top;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
        }

        .table-items thead th {
            padding: 4px 0;
            font-size: 11px;
        }

        .table-items tbody td {
            padding: 4px 0;
            vertical-align: top;
        }

        .box {
            padding: 3px;
            margin-top: 3px;
            margin-bottom: 3px;
        }

        .alerta {
            border: 2px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            margin-top: 6px;
            font-size: 11px;
        }

        .total-box {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 0;
            margin-top: 5px;
        }

        .total-label {
            font-size: 12px;
            font-weight: bold;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 8px;
        }

        .anulado {
            border: 2px solid #000;
            padding: 4px;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .btn-print {
            width: 100%;
            margin-top: 10px;
            padding: 7px;
            border: none;
            background: #000;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        @media print {
            .btn-print {
                display: none;
            }

            body {
                width: 100%;
                padding: 5px;
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

    @if ($venta->estado === \App\Enums\EstadoVenta::ANULADO || $venta->fecha_anulacion)
        <div class="anulado">*** ANULADO ***</div>
    @endif

    @php
        $empresa = $venta->sucursal?->empresa;
        $emisor = $encomienda->emisor; // quien envía
        $receptor = $encomienda->receptor; // quien recibe

        // Descuento (ajusta el campo según tu modelo)
        $montoDescuento = 0;

        // Op. Gravada
        $opGravada = $venta->subtotal ?? $venta->total - $venta->impuesto;
    @endphp

    {{-- ── ENCABEZADO ── --}}
    {{-- ───────────── ENCABEZADO ───────────── --}}

    <div class="center">

        @if ($empresa && $empresa->logo)
            <div class="logo-container">
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo">
            </div>
        @endif

        <div class="empresa">
            {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
        </div>

        <div class="ruc">
            RUC {{ $empresa->documento ?? '20513247495' }}
        </div>

        @if ($venta->sucursal?->direccion || $empresa?->direccion)
            <div class="direccion">
                {{ $venta->sucursal->direccion ?? $empresa->direccion }}
            </div>
        @endif

        <div class="documento">

            <div class="tipo">
                {{ strtoupper($venta->tipoDocumentoFactura->descripcion ?? 'NOTA DE VENTA') }}
            </div>

            <div class="numero">
                {{ $venta->serie }} - {{ $venta->numero }}
            </div>

        </div>

    </div>

    <div class="section-title">
        INFORMACIÓN DE EMISIÓN
    </div>

    <table class="table-data">

        <tr>
            <td width="38%"><strong>Fecha</strong></td>
            <td class="right">
                {{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
            </td>
        </tr>

        <tr>
            <td><strong>Cajero</strong></td>
            <td class="right">
                {{ $venta->usuario->persona->nombre_completo ?? 'Sistema' }}
            </td>
        </tr>

    </table>

    <div class="section-title">
        REMITENTE
    </div>

    <table class="table-data">
        <tr>
            <td width="28%"><strong>Nombre</strong></td>
            <td>
                {{ $emisor ? $emisor->nombres . ' ' . $emisor->apellidos : 'CLIENTE VARIOS' }}
            </td>
        </tr>

        <tr>
            <td><strong>DNI</strong></td>
            <td>{{ $emisor->documento ?? '---' }}</td>
        </tr>

        @if ($emisor?->telefono)
            <tr>
                <td><strong>Celular</strong></td>
                <td>{{ $emisor->telefono }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">
        PERSONAS AUTORIZADAS PARA EL RECOJO
    </div>

    <div class="box">

        <div class="bold center" style="margin-bottom:5px;">
            RECEPTOR PRINCIPAL
        </div>

        <table class="table-data">

            <tr>
                <td width="28%"><strong>Nombre</strong></td>
                <td>
                    {{ $receptor ? $receptor->nombres . ' ' . $receptor->apellidos : '---' }}
                </td>
            </tr>

            <tr>
                <td><strong>DNI</strong></td>
                <td>{{ $receptor->documento ?? '---' }}</td>
            </tr>

            @if ($receptor?->telefono)
                <tr>
                    <td><strong>Celular</strong></td>
                    <td>{{ $receptor->telefono }}</td>
                </tr>
            @endif

        </table>

        @if ($encomienda->receptor2)
            <div class="line"></div>

            <div class="bold center" style="margin-bottom:5px;">
                RESPONSABLE ADICIONAL
            </div>

            <table class="table-data">

                <tr>
                    <td width="28%"><strong>Nombre</strong></td>
                    <td>
                        {{ $encomienda->receptor2->nombres }}
                        {{ $encomienda->receptor2->apellidos }}
                    </td>
                </tr>

                <tr>
                    <td><strong>DNI</strong></td>
                    <td>{{ $encomienda->receptor2->documento }}</td>
                </tr>

            </table>
        @endif

    </div>

    <div class="section-title">
        DATOS DE LA ENCOMIENDA
    </div>

    <div class="box">

        <table class="table-data">

            <tr>
                <td width="32%"><strong>Origen</strong></td>
                <td>
                    {{ $encomienda->origenPueblito?->descripcion ?? '---' }}
                </td>
            </tr>

            <tr>
                <td><strong>Destino</strong></td>
                <td>
                    {{ $encomienda->destinoPueblito?->descripcion ?? '---' }}
                </td>
            </tr>

            <tr>
                <td><strong>Registro</strong></td>
                <td>
                    {{ optional($encomienda->created_at)->format('d/m/Y H:i') }}
                </td>
            </tr>

            @php
                $salida = $encomienda->salidaActual?->salida;
            @endphp

            @if ($salida)
                <tr>
                    <td><strong>Ruta</strong></td>
                    <td>
                        {{ $salida->horario?->ruta?->nombre ?? '---' }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Salida</strong></td>
                    <td>
                        {{ \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y') }}
                        -
                        {{ \Carbon\Carbon::parse($salida->horario->hora_salida)->format('h:i A') }}
                    </td>
                </tr>

                @if ($encomienda->observaciones)
                    <tr>
                        <td><strong>Observaciones</strong></td>
                        <td>
                            {{ $encomienda->observaciones }}
                        </td>
                    </tr>
                @endif

            @endif

            @if ($encomienda->codigo)
                <tr>
                    <td><strong>Código</strong></td>
                    <td class="bold">
                        {{ $encomienda->codigo }}
                    </td>
                </tr>
            @endif

        </table>

    </div>



    <div class="section-title">
        DETALLE DE LA ENCOMIENDA
    </div>

    <table class="table-items">

        <thead>
            <tr>
                <th class="left" width="55%">Descripción</th>
                <th class="center" width="18%">Peso</th>
                <th class="right" width="27%">Importe</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($encomienda->detalles as $detalle)
                <tr>

                    <td>

                        <strong>
                            {{ $detalle->tipo_encomienda?->descripcion ?? 'ENCOMIENDA' }}
                        </strong>

                        @if ($detalle->descripcion)
                            <div style="font-size:10px;margin-top:2px;">
                                {{ $detalle->descripcion }}
                            </div>
                        @endif

                    </td>

                    <td class="center">

                        @if ($detalle->peso)
                            {{ number_format($detalle->peso, 1) }} kg
                        @else
                            —
                        @endif

                    </td>

                    <td class="right bold">
                        S/ {{ number_format($detalle->costo, 2) }}
                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    <div class="section-title">
        DETALLE DE PAGOS
    </div>
    <table class="table-data">

        <tr>
            <td>Op. Gravada</td>
            <td class="right">
                S/ {{ number_format($opGravada, 2) }}
            </td>
        </tr>

        <tr>
            <td>IGV ({{ $empresa->igv_encomienda ?? 18 }}%)</td>
            <td class="right">
                S/ {{ number_format($venta->impuesto, 2) }}
            </td>
        </tr>

        @if ($montoDescuento > 0)
            <tr>
                <td>Descuento</td>
                <td class="right">
                    - S/ {{ number_format($montoDescuento, 2) }}
                </td>
            </tr>
        @endif

        <tr>
            <td><strong>Total pagado</strong></td>
            <td class="right">
                S/ {{ number_format($encomienda->total, 2) }}
            </td>
        </tr>

    </table>

    @if ($venta->pagos?->isNotEmpty())

        <div class="section-title">
            FORMA DE PAGO
        </div>

        <table class="table-data">

            @foreach ($venta->pagos as $pago)
                <tr>

                    <td width="65%">
                        {{ $pago->metodoPago->descripcion ?? 'Efectivo' }}

                        @if ($pago->billetera_digital)
                            <br>
                            <small>{{ $pago->billetera_digital->descripcion }}</small>
                        @endif

                    </td>

                    <td class="right bold">
                        S/ {{ number_format($pago->monto, 2) }}
                    </td>

                </tr>
            @endforeach

        </table>

        <div class="line"></div>

    @endif
    <br>
    @if ($encomienda->observaciones)
        <div class="line"></div>
        <div class="box">

            <div class="bold center" style="margin-bottom:4px;">
                OBSERVACIONES
            </div>

            <div style="word-break: break-word;">
                {{ $encomienda->observaciones }}
            </div>

        </div>
        <div class="line"></div>
    @endif


    <div class="footer">

        <div style="font-size:11px;">
            {{ $empresa->mensaje }}
        </div>

    </div>

    <button class="btn-print" onclick="window.print()">
        IMPRIMIR
    </button>

</body>

</html>

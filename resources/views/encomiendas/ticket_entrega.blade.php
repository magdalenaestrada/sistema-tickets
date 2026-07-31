<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, monospace;
            font-size: 10px;
            width: 58mm;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        h3 {
            margin: 2px 0;
        }
    </style>

</head>

<body>

    <div class="center">

        @if ($empresa && $empresa->logo)
            <div class="logo-container">
                <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo"
                    style="max-width:120px; max-height:80px;">
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
<br>
        <strong>CONSTANCIA DE ENTREGA</strong>

    </div>

    <hr>

    <table>

        <tr>
            <td><strong>Venta</strong></td>
            <td>{{ $encomienda->venta->serie }} - {{ $encomienda->venta->numero }}</td>
        </tr>

        <tr>
            <td><strong>Fecha emisión</strong></td>
            <td>{{ optional($encomienda->fecha_creacion)->format('d/m/Y H:i') }}</td>
        </tr>

        <tr>
            <td><strong>Fecha entrega</strong></td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>

        <tr>
            <td><strong>Estado</strong></td>
            <td>ENTREGADO</td>
        </tr>

    </table>

    <hr>

    <b>REMITENTE</b>

    <table>

        <tr>
            <td>Nombre</td>
            <td>{{ $encomienda->emisor?->nombre_completo }}</td>
        </tr>

        <tr>
            <td>Documento</td>
            <td>{{ $encomienda->emisor?->documento }}</td>
        </tr>

        <tr>
            <td>Celular</td>
            <td>{{ $encomienda->emisor?->celular ?? "-" }}</td>
        </tr>

    </table>

    <hr>

    <b>DESTINATARIO</b>

    <table>

        <tr>
            <td>Nombre</td>
            <td>{{ $encomienda->receptor?->nombre_completo }}</td>
        </tr>

        <tr>
            <td>Documento</td>
            <td>{{ $encomienda->receptor?->documento }}</td>
        </tr>

        <tr>
            <td>Celular</td>
            <td>{{ $encomienda->receptor?->celular  ?? "-" }}</td>
        </tr>

    </table>

    <hr>

    <b>RUTA</b>

    <table>

        <tr>
            <td>Origen</td>
            <td>
                @if ($encomienda->origenPueblito)
                    {{ $encomienda->origenPueblito->descripcion }}
                @endif

            </td>
        </tr>

        <tr>
            <td>Destino</td>
            <td>
                @if ($encomienda->destinoPueblito)
                    {{ $encomienda->destinoPueblito->descripcion }}
                @endif

            </td>
        </tr>

    </table>

    <hr>

    <b>DETALLE</b>

    <table border="1">

        <thead>

            <tr>

                <th>Tipo</th>

                <th>Descripción</th>

                <th>Peso</th>

                <th>Costo</th>

            </tr>

        </thead>

        <tbody>

            @foreach ($encomienda->detalles as $detalle)
                <tr>

                    <td>{{ $detalle->tipo_encomienda?->descripcion }}</td>

                    <td>{{ $detalle->descripcion }}</td>

                    <td class="right">{{ number_format($detalle->peso, 2) }}</td>

                    <td class="right">S/ {{ number_format($detalle->costo, 2) }}</td>

                </tr>
            @endforeach

        </tbody>

        <tfoot>

            <tr>

                <th colspan="2">
                    Peso total
                </th>

                <th class="right">

                    {{ number_format($encomienda->detalles->sum('peso'), 2) }}

                </th>

                <th class="right">

                    S/ {{ number_format($encomienda->total, 2) }}

                </th>

            </tr>

        </tfoot>

    </table>

    <hr>

    <table>

        <tr>

            <td>Entregado por</td>
            <td>{{ $encomienda->entregado?->persona?->nombre_completo ?? $encomienda->usuario?->persona?->nombre_completo }}
            </td>

        </tr>

    </table>

    <hr>

    <div class="center">

        He recibido conforme la encomienda.

        <br><br><br>

        _________________________________

        <br>

        Firma del receptor

    </div>

</body>

</html>

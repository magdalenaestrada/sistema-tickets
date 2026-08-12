<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia de Entrega</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            width: 48mm;
            /* Ancho imprimible óptimo para papel de 58mm */
            margin: 0 auto;
            padding: 0;
            color: #000;
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

        .text-uppercase {
            text-transform: uppercase;
        }

        .company-title {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
        }

        .doc-title {
            font-size: 10px;
            font-weight: bold;
            margin: 4px 0;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0;
        }

        td,
        th {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8px;
        }

        /* Formato de pares clave-valor */
        .info-table td:first-child {
            width: 40%;
            font-weight: bold;
        }

        .info-table td:last-child {
            width: 60%;
        }

        /* Tabla de detalle de encomiendas */
        .items-table {
            margin-top: 3px;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
            text-align: left;
        }

        .items-table td {
            padding: 2px 0;
        }

        .items-table .total-row th {
            border-top: 1px dashed #000;
            padding-top: 3px;
        }

        .section-title {
            font-size: 8px;
            font-weight: bold;
            display: block;
            margin-top: 4px;
        }

        .signature-box {
            margin-top: 25px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 2px auto;
        }

        .logo-container img {
            max-width: 100px;
            max-height: 50px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

    <div class="center">
        @if ($empresa && $empresa->logo)
            <div class="logo-container">
                <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo">
            </div>
        @endif

        <div class="company-title">
            {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
        </div>
        <div>RUC: {{ $empresa->documento ?? '20513247495' }}</div>
        <div>{{ $venta->sucursal->direccion ?? ($empresa->direccion ?? 'Av. El Sol 789') }}</div>

        <hr>
        <div class="doc-title">CONSTANCIA DE ENTREGA</div>
    </div>

    <hr>

    <table class="info-table">
        <tr>
            <td>Venta:</td>
            <td>{{ $encomienda->venta->serie }} - {{ $encomienda->venta->numero }}</td>
        </tr>
        <tr>
            <td>F. Emisión:</td>
            <td>{{ optional($encomienda->fecha_creacion)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>F. Entrega:</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Estado:</td>
            <td class="bold">ENTREGADO</td>
        </tr>
    </table>

    <hr>

    <span class="section-title">REMITENTE</span>
    <table class="info-table">
        <tr>
            <td>Nombre:</td>
            <td>{{ $encomienda->emisor?->nombre_completo }}</td>
        </tr>
        <tr>
            <td>Doc:</td>
            <td>{{ $encomienda->emisor?->documento }}</td>
        </tr>
        <tr>
            <td>Celular:</td>
            <td>{{ $encomienda->emisor?->celular ?? '-' }}</td>
        </tr>
    </table>

    <hr>

    <span class="section-title">DESTINATARIO</span>
    <table class="info-table">
        <tr>
            <td>Nombre:</td>
            <td>{{ $encomienda->receptor?->nombre_completo }}</td>
        </tr>
        <tr>
            <td>Doc:</td>
            <td>{{ $encomienda->receptor?->documento }}</td>
        </tr>
        <tr>
            <td>Celular:</td>
            <td>{{ $encomienda->receptor?->celular ?? '-' }}</td>
        </tr>
    </table>

    @if ($encomienda->receptor2)
        <hr>
        <span class="section-title">DESTINATARIO 2</span>
        <table class="info-table">
            <tr>
                <td>Nombre:</td>
                <td>{{ $encomienda->receptor2?->nombre_completo }}</td>
            </tr>
            <tr>
                <td>Doc:</td>
                <td>{{ $encomienda->receptor2?->documento }}</td>
            </tr>
            <tr>
                <td>Celular:</td>
                <td>{{ $encomienda->receptor2?->celular ?? '-' }}</td>
            </tr>
        </table>
    @endif

    <hr>

    <span class="section-title">RUTA</span>
    <table class="info-table">
        <tr>
            <td>Origen:</td>
            <td>{{ $encomienda->origenPueblito?->descripcion }}</td>
        </tr>
        <tr>
            <td>Destino:</td>
            <td>{{ $encomienda->destinoPueblito?->descripcion }}</td>
        </tr>
    </table>

    <hr>

    <span class="section-title">DETALLE</span>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Desc.</th>
                <th style="width: 20%;" class="right">Peso</th>
                <th style="width: 30%;" class="right">Costo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($encomienda->detalles as $detalle)
                <tr>
                    <td>
                        {{ $detalle->descripcion }}
                        @if ($detalle->tipo_encomienda?->descripcion)
                            <br><small>({{ $detalle->tipo_encomienda->descripcion }})</small>
                        @endif
                    </td>
                    <td class="right">{{ number_format($detalle->peso, 2) }}</td>
                    <td class="right">S/ {{ number_format($detalle->costo, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th class="bold">TOTAL</th>
                <th class="right bold">{{ number_format($encomienda->detalles->sum('peso'), 2) }}</th>
                <th class="right bold">S/ {{ number_format($encomienda->total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <hr>

    <table class="info-table">
        <tr>
            <td>Entregado por:</td>
            <td>{{ $encomienda->entregado?->persona?->nombre_completo ?? $encomienda->usuario?->persona?->nombre_completo }}
            </td>
        </tr>
    </table>

    <hr>

    <div class="center" style="margin-top: 28px;">

        <div class="signature-box">
            <div class="signature-line"></div>
            Firma del receptor
        </div>
    </div>

</body>

</html>

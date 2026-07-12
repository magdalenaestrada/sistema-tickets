<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manifiesto de Pasajeros</title>
    <style>
        @page {
            margin: 16px 18px 18px 18px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
        }

        .header-wrap {
            margin-bottom: 10px;
        }

        .box,
        .fecha-box,
        .titulo-box {
            border: 1px solid #444;
        }

        .empresa-nombre {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .empresa-texto {
            font-size: 10px;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .empresa-ruc {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
        }

        .fecha-box td {
            border: 1px solid #444;
            text-align: center;
            padding: 1px 1px;
        }

        .fecha-head {
            font-size: 9px;
            font-weight: bold;
            background: #f1f1f1;
        }

        .fecha-val {
            font-size: 10px;
        }

        .titulo-ruc {
            border-bottom: 1px solid #444;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 6px;
        }

        .titulo-main {
            background: #1f6fb2;
            color: #fff;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 6px;
            line-height: 1.1;
        }

        .titulo-sub {
            border-top: 1px solid #444;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 6px;
            text-align: center;

        }

        @page {
            margin: 18px 20px 20px 20px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .w-100 {
            width: 100%;
        }

        .mt-1 {
            margin-top: 6px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .mt-3 {
            margin-top: 18px;
        }

        .header-table,
        .info-table,
        .main-table,
        .firma-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-box {
            border: 1px solid #444;
            padding: 10px 12px;
            height: 92px;
        }

        .company-name {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .company-data {
            font-size: 10.5px;
            line-height: 1.45;
        }

        .date-box {
            border: 1px solid #444;
            text-align: center;
        }

        .date-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .date-box td {
            border: 1px solid #444;
            padding: 4px;
            font-size: 10px;
        }

        .date-box .head {
            background: #e9eef5;
            font-weight: bold;
        }

        .title-box {
            border: 1px solid #444;
            height: 92px;
        }

        .title-ruc {
            border-bottom: 1px solid #444;
            padding: 8px 10px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .title-main {
            background: #1f6fb2;
            color: #fff;
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            padding: 8px 10px;
            letter-spacing: 0.5px;
        }

        .title-sub {
            text-align: center;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: bold;
            border-top: 1px solid #444;
        }

        .section-title {
            background: #dfe8f3;
            border: 1px solid #444;
            border-bottom: 0;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table td {
            border: 1px solid #444;
            padding: 2px 2px;
            vertical-align: top;
            font-size: 10.5px;
        }

        .label {
            font-weight: bold;
            color: #000;
        }

        .main-table {
            margin-top: 0;
        }

        .main-table th {
            background: #d9e4f0;
            border: 1px solid #444;
            padding: 1px 1px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .main-table td {
            border: 1px solid #444;
            padding: 2px 2px;
            font-size: 10px;
            vertical-align: middle;
        }

        .main-table tbody tr:nth-child(even) {
            background: #f7f9fc;
        }

        .col-item {
            width: 6%;
            text-align: center;
        }

        .col-seat {
            width: 8%;
            text-align: center;
        }

        .col-name {
            width: 26%;
        }

        .col-doc-type {
            width: 9%;
            text-align: center;
        }

        .col-origen {
            width: 9%;
            text-align: center;
        }

        .col-doc {
            width: 12%;
            text-align: center;
        }

        .col-dest {
            width: 14%;
            text-align: center;
        }

        .col-ticket {
            width: 15%;
            text-align: center;
        }

        .col-amount {
            width: 10%;
            text-align: right;
        }

        .summary-box {
            margin-top: 10px;
            border: 1px solid #444;
            padding: 8px 10px;
            font-size: 10.5px;
            background: #fafbfd;
        }

        .firma-table {
            margin-top: 38px;
        }

        .firma-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 24px;
        }

        .firma-line {
            width: 75%;
            margin: 0 auto 6px auto;
            border-top: 1px solid #444;
            height: 1px;
        }

        .firma-label {
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <table class="header-wrap" width="100%">
        <tr>
            <!-- EMPRESA -->
            <td width="42%" style="vertical-align: top; padding-right: 8px;">
                <table class="box" width="100%">
                    <tr>
                        <td style="padding: 6px 8px;">
                            <div class="empresa-nombre">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
                            <div class="empresa-texto">{{ $empresa->direccion ?? '' }}</div>
                            <div class="empresa-ruc">RUC: {{ $empresa->documento ?? '' }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- FECHA / HORA -->
            <td width="18%" style="vertical-align: top; padding-right: 8px;">
                <table class="fecha-box" width="100%">
                    <tr>
                        <td class="fecha-head">DÍA</td>
                        <td class="fecha-head">MES</td>
                        <td class="fecha-head">AÑO</td>
                    </tr>
                    <tr>
                        <td class="fecha-val">{{ $salida->fecha_salida?->format('d') }}</td>
                        <td class="fecha-val">{{ $salida->fecha_salida?->format('m') }}</td>
                        <td class="fecha-val">{{ $salida->fecha_salida?->format('Y') }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="fecha-head">HORA</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="fecha-val">{{ $salida->horario?->hora_formateada }}</td>
                    </tr>
                </table>
            </td>

            <td width="40%" style="vertical-align: top;">
                <table class="titulo-box" width="100%">
                    <tr>
                        <td class="titulo-ruc">
                            RUC: {{ $empresa->documento ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="titulo-main">
                            MANIFIESTO DE PASAJEROS
                        </td>
                    </tr>
                    <tr>
                        <td class="titulo-sub">
                            SALIDA | {{ $origenNombre }} - {{ $destinoNombre }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title mt-2" align="center">Información general del viaje</div>
    <table class="info-table">
        <tr>
            <td width="50%"><span class="label">Origen:</span> {{ $origenNombre }}</td>
            <td colspan="2"><span class="label">Destino:</span> {{ $destinoNombre }}</td>
        </tr>
        <tr>
            <td width="34%"><span class="label">Marca de vehículo:</span> {{ $salida->vehiculo->marca ?? '-' }}
            </td>
            <td width="33%"><span class="label">Placa:</span> {{ $salida->vehiculo->numero_placa ?? '-' }}</td>
            <td width="33%"><span class="label">Hab. vehicular:</span>
                {{ $salida->vehiculo->habilitacion_vehicular ?? '-' }}</td>
        </tr>
        <tr>
            <td><span class="label">Conductor 1:</span>
                {{ $salida->conductorPrincipal?->persona->nombres }} {{ $salida->conductorPrincipal?->persona->apellidos }}
            </td>
            <td colspan="2"><span class="label">Licencia:</span>
                {{ $salida->conductorPrincipal?->licencia_conducir ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><span class="label">Conductor 2:</span>
                {{ $salida->conductorSecundario?->persona->nombres }} {{ $salida->conductorSecundario?->persona->apellidos }}
            </td>
            <td colspan="2"><span class="label">Licencia:</span>
                {{ $salida->conductorSecundario?->licencia_conducir ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><span class="label">Cantidad máx. asientos:</span> {{ $capacidad }}</td>
            <td colspan="2"><span class="label">Pasajeros embarcados:</span> {{ $pasajes->count() }}</td>
        </tr>
    </table>

    <div class="section-title mt-2" align="center">Detalle de pasajeros</div>
    <table class="main-table">
        <thead>
            <tr>
                <th class="col-item">ITEM</th>
                <th class="col-seat">N° ASIENTO</th>
                <th class="col-name">NOMBRES Y APELLIDOS</th>
                <th class="col-doc-type">TIPO DOC.</th>
                <th class="col-doc">N° DOC</th>
                <th class="col-dest">ORIGEN</th>
                <th class="col-dest">DESTINO</th>
                <th class="col-ticket">N° BOLETO</th>
                <th class="col-amount">IMPORTE S/</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pasajes as $i => $pasaje)
                <tr>
                    <td class="col-item">{{ $i + 1 }}</td>
                    <td class="col-seat">{{ $pasaje->asiento_numero }}</td>
                    <td class="col-name">
                        {{ $pasaje->persona?->apellidos }} {{ $pasaje->persona?->nombres }}
                    </td>
                    <td class="col-doc-type">{{ $pasaje->persona?->tipoDocumento?->codigo ?? 'DNI' }}</td>
                    <td class="col-doc">{{ $pasaje->persona?->documento }}</td>
                    <td class="col-origen">{{ $origenNombre }}</td>
                    <td class="col-dest">{{ $destinoNombre }}</td>
                    <td class="col-ticket">{{ $pasaje->venta?->serie }} - {{ $pasaje->venta?->numero }}</td>
                    <td class="col-amount">{{ number_format((float) $pasaje->precio_cobrado, 2) }}</td>
                </tr>
            @endforeach

            @for ($j = $pasajes->count(); $j < $capacidad; $j++)
                <tr>
                    <td class="col-item">{{ $j + 1 }}</td>
                    <td class="col-seat">&nbsp;</td>
                    <td class="col-name"></td>
                    <td class="col-doc-type"></td>
                    <td class="col-doc"></td>
                    <td class="col-origen"></td>
                    <td class="col-dest"></td>
                    <td class="col-ticket"></td>
                    <td class="col-amount"></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="firma-table">
        <tr>
            <td>
                <table width="80%" align="center">
                    <tr>
                        <td style="border-top: 1px solid #000; height: 15px;"></td>
                    </tr>
                </table>
                <div class="firma-label">CHOFER</div>
            </td>
            <td>
                <table width="80%" align="center">
                    <tr>
                        <td style="border-top: 1px solid #000; height: 15px;"></td>
                    </tr>
                </table>
                <div class="firma-label">COPILOTO</div>
            </td>
        </tr>
    </table>
</body>

</html>

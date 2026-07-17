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
    @foreach ($bloques as $index => $bloque)
        <div class="{{ $index > 0 ? 'salto-pagina' : '' }}">
            @include('salidas.manifiestos._pasajeros_bloque', $bloque)
        </div>
    @endforeach
</body>

</html>

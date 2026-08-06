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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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

        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-container img {
            max-width: 110px;
            max-height: 60px;
            height: auto;
            display: inline-block;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            height: 0;
        }

        .anulado {
            border: 2px solid #000;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
            padding: 4px;
        }

        .w-100 {
            width: 100%;
            border-collapse: collapse;
        }

        .table-data td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 11px;
        }

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

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            background: #efefef;
            padding: 3px;
            margin: 6px 0 4px;
            border: 1px solid #d9d9d9;
        }

        .highlight-box {
            border: 2px solid #000;
            padding: 5px;
            margin: 6px 0;
            text-align: center;
            background-color: #fcfcfc;
        }

        .asiento-box {
            font-size: 20px;
            font-weight: bold;
            margin: 2px 0;
        }

        .ruta-box {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .fecha-hora-box {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        .box {
            padding: 3px;
            margin: 3px 0;
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

        .sobreequipaje-header {
            border: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 4px 2px;
            margin-bottom: 6px;
            letter-spacing: 1px;
            background-color: #f0f0f0;
        }

        /* separador de comprobantes dentro del mismo PDF */
        .ticket-siguiente {
            page-break-before: always;
        }

        .ticket-pasajero {
            page-break-before: always;
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
            .btn-print {
                display: none !important;
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
    @php
        $tienePasajes = $venta->pasajes->isNotEmpty();
        $tieneEncomiendas = $venta->encomiendas->isNotEmpty();
        $esPrimerBloque = true; // se va actualizando conforme se incluyen partials
    @endphp

    @if ($tienePasajes)
        @include('caja.tickets._pasajes', ['venta' => $venta, 'esPrimerBloque' => $esPrimerBloque])
        @php $esPrimerBloque = false; @endphp
    @endif

    @if ($tieneEncomiendas)
        @include('caja.tickets._encomiendas', ['venta' => $venta, 'esPrimerBloque' => $esPrimerBloque])
        @php $esPrimerBloque = false; @endphp
    @endif

    @unless ($tienePasajes || $tieneEncomiendas)
        @include('caja.tickets._vacio', ['venta' => $venta])
    @endunless
</body>

</html>

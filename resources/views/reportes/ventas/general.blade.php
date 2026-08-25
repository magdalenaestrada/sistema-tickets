<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>Reporte General de Ventas</title>

    <style>
        @page {
            margin: 24px 26px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }

        .header h1 {
            margin: 0;
            font-size: 17px;
            color: #1e3a8a;
            text-transform: uppercase;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 9px;
            color: #64748b;
            font-weight: bold;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 7px;
            padding-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1e293b;
            color: white;
            padding: 6px;
            font-size: 8px;
            text-transform: uppercase;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        /*
        |--------------------------------------------------------------------------
        | TARJETAS RESUMEN
        |--------------------------------------------------------------------------
        */

        .summary-table {
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 5px;
            margin: 0 -5px;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 7px;
            text-align: center;
        }

        .summary-label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .summary-value.money {
            color: #1e40af;
        }

        /*
        |--------------------------------------------------------------------------
        | TOTALES
        |--------------------------------------------------------------------------
        */

        .total-row td {
            background: #eff6ff;
            font-weight: bold;
            border-top: 2px solid #93c5fd;
            border-bottom: 2px solid #93c5fd;
        }

        .total-amount {
            color: #1d4ed8;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | DOS COLUMNAS
        |--------------------------------------------------------------------------
        */

        .two-columns {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-left: -10px;
            width: calc(100% + 20px);
        }

        .two-columns > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            border: none;
            padding: 0 0 0 10px;
        }

        /*
        |--------------------------------------------------------------------------
        | NOTAS
        |--------------------------------------------------------------------------
        */

        .note {
            color: #64748b;
            font-size: 7.5px;
            margin-top: 5px;
        }

        .footer {
            margin-top: 18px;
            padding-top: 7px;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- ==========================================================
        CABECERA
    =========================================================== --}}
    <div class="header">

        <h1>
            Reporte General de Ventas
        </h1>

        <p>
            PERÍODO:
            {{ $desde->format('d/m/Y') }}
            AL
            {{ $hasta->format('d/m/Y') }}
        </p>

    </div>


    {{-- ==========================================================
        1. RESUMEN GENERAL
    =========================================================== --}}
    <div class="section">

        <div class="section-title">
            1. Resumen General
        </div>

        <table class="summary-table">

            <tr>

                <td class="summary-box">

                    <div class="summary-label">
                        Ventas Emitidas
                    </div>

                    <div class="summary-value">
                        {{ number_format($cantidadVentas) }}
                    </div>

                </td>


                <td class="summary-box">

                    <div class="summary-label">
                        Total Vendido
                    </div>

                    <div class="summary-value money">
                        S/ {{ number_format($totalVendido, 2) }}
                    </div>

                </td>


                <td class="summary-box">

                    <div class="summary-label">
                        Ticket Promedio
                    </div>

                    <div class="summary-value">
                        S/ {{ number_format($ticketPromedio, 2) }}
                    </div>

                </td>


                <td class="summary-box">

                    <div class="summary-label">
                        Anuladas
                    </div>

                    <div class="summary-value">
                        {{ number_format($cantidadAnuladas) }}
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- ==========================================================
        2. SERVICIOS VENDIDOS
    =========================================================== --}}
    <div class="section">

        <div class="section-title">
            2. Servicios Vendidos
        </div>

        <table>

            <thead>
                <tr>
                    <th>Tipo de Servicio</th>
                    <th class="center" style="width: 30%;">
                        Cantidad
                    </th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>
                        Pasajes
                    </td>

                    <td class="center">
                        {{ number_format($cantidadPasajes) }}
                    </td>
                </tr>

                <tr>
                    <td>
                        Encomiendas
                    </td>

                    <td class="center">
                        {{ number_format($cantidadEncomiendas) }}
                    </td>
                </tr>

                <tr>
                    <td>
                        Sobreequipajes
                    </td>

                    <td class="center">
                        {{ number_format($cantidadSobreEquipajes) }}
                    </td>
                </tr>

                <tr class="total-row">

                    <td>
                        TOTAL DE SERVICIOS
                    </td>

                    <td class="center">
                        {{ number_format($totalServicios) }}
                    </td>

                </tr>

            </tbody>

        </table>

        <div class="note">
            La cantidad corresponde a servicios registrados, no a cantidad de comprobantes.
        </div>

    </div>


    {{-- ==========================================================
        3 + 4
        MÉTODOS DE PAGO / VENTAS POR SUCURSAL
    =========================================================== --}}
    <div class="section">

        <table class="two-columns">

            <tr>

                {{-- MÉTODOS DE PAGO --}}
                <td>

                    <div class="section-title">
                        3. Métodos de Pago
                    </div>

                    <table>

                        <thead>
                            <tr>

                                <th>
                                    Método
                                </th>

                                <th class="center">
                                    Op.
                                </th>

                                <th class="right">
                                    Total
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($metodosPago as $metodo)

                                <tr>

                                    <td>
                                        {{ $metodo['nombre'] }}
                                    </td>

                                    <td class="center">
                                        {{ $metodo['operaciones'] }}
                                    </td>

                                    <td class="right">
                                        S/ {{ number_format($metodo['total'], 2) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="center">
                                        Sin pagos registrados
                                    </td>
                                </tr>

                            @endforelse


                            <tr class="total-row">

                                <td colspan="2">
                                    TOTAL PAGADO
                                </td>

                                <td class="right total-amount">

                                    S/
                                    {{ number_format(
                                        $metodosPago->sum('total'),
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </td>


                {{-- VENTAS POR SUCURSAL --}}
                <td>

                    <div class="section-title">
                        4. Ventas por Sucursal
                    </div>

                    <table>

                        <thead>
                            <tr>

                                <th>
                                    Sucursal
                                </th>

                                <th class="center">
                                    Ventas
                                </th>

                                <th class="right">
                                    Total
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($ventasPorSucursal as $item)

                                <tr>

                                    <td>
                                        {{ $item['sucursal'] }}
                                    </td>

                                    <td class="center">
                                        {{ $item['ventas'] }}
                                    </td>

                                    <td class="right">
                                        S/ {{ number_format($item['total'], 2) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="center">
                                        Sin ventas registradas
                                    </td>
                                </tr>

                            @endforelse


                            <tr class="total-row">

                                <td>
                                    TOTAL
                                </td>

                                <td class="center">
                                    {{ $ventasPorSucursal->sum('ventas') }}
                                </td>

                                <td class="right total-amount">
                                    S/ {{ number_format($ventasPorSucursal->sum('total'), 2) }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </td>

            </tr>

        </table>

    </div>


    {{-- ==========================================================
        5. VENTAS POR VENDEDOR
    =========================================================== --}}
    <div class="section">

        <div class="section-title">
            5. Ventas por Vendedor
        </div>

        <table>

            <thead>

                <tr>

                    <th>
                        Vendedor / Cajero
                    </th>

                    <th class="center" style="width: 20%;">
                        Cantidad de Ventas
                    </th>

                    <th class="right" style="width: 25%;">
                        Total Vendido
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse ($ventasPorVendedor as $item)

                    <tr>

                        <td>
                            {{ $item['vendedor'] }}
                        </td>

                        <td class="center">
                            {{ $item['ventas'] }}
                        </td>

                        <td class="right">
                            S/ {{ number_format($item['total'], 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3" class="center">
                            No se encontraron ventas.
                        </td>

                    </tr>

                @endforelse


                <tr class="total-row">

                    <td>
                        TOTAL
                    </td>

                    <td class="center">
                        {{ $ventasPorVendedor->sum('ventas') }}
                    </td>

                    <td class="right total-amount">
                        S/ {{ number_format($ventasPorVendedor->sum('total'), 2) }}
                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    {{-- ==========================================================
        FOOTER
    =========================================================== --}}
    <div class="footer">

        Reporte generado:
        {{ now('America/Lima')->format('d/m/Y H:i') }}

    </div>

</body>

</html>
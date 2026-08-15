<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas por Usuario</title>
    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #334155;
            background-color: #ffffff;
            line-height: 1.4;
        }

        /* Encabezado */
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }

        .badge-periodo {
            display: inline-block;
            background-color: #f1f5f9;
            color: #334155;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 9px;
        }

        /* Resumen rápido / KPIs */
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: right;
        }

        .summary-card .label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }

        .summary-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Tabla principal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            padding: 7px 6px;
            border: none;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        /* Filas alternadas (Zebra) */
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Alineaciones */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-semibold {
            font-weight: 600;
        }

        /* Badges de estado */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-confirmado,
        .status-pagado,
        .status-vendido {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-pendiente,
        .status-reservado {
            background-color: #fef9c3;
            color: #a16207;
        }

        .status-anulado,
        .status-cancelado {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .status-default {
            background-color: #f1f5f9;
            color: #475569;
        }

        /* Fila de Totales */
        tr.total-row td {
            background-color: #f1f5f9;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            font-size: 10px;
            font-weight: 700;
            color: #0f172a;
            padding: 8px 6px;
        }

        /* Footer del reporte */
        .footer {
            margin-top: 20px;
            width: 100%;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    <!-- Encabezado -->
    <table class="header-container">
        <tr>
            <td style="border: none; padding: 0;">
                <h1 class="header-title">Reporte de Ventas por Usuario</h1>
                <p class="header-subtitle">
                    Período:
                    <span class="badge-periodo">
                        {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}
                    </span>
                </p>
            </td>
            <td style="border: none; padding: 0;" class="text-right">
                <p class="header-subtitle">Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
            </td>
        </tr>
    </table>

    <!-- Tabla de datos -->
    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 16%;">Usuario / Cajero</th>
                <th class="text-center" style="width: 12%;">Fecha Venta</th>
                <th class="text-left" style="width: 22%;">Pasajero</th>
                <th class="text-left" style="width: 13%;">Origen</th>
                <th class="text-left" style="width: 13%;">Destino</th>
                <th class="text-center" style="width: 6%;">N° As.</th>
                <th class="text-center" style="width: 8%;">Estado</th>
                <th class="text-right" style="width: 10%;">Precio</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp

            @foreach ($pasajes as $pasaje)
                @php
                    $precio = $pasaje->precio_cobrado ?? ($pasaje->precio_pasaje ?? 0);

                    $total += $precio;

                    // Formateo de clase para el estado
                    $estadoClean = strtolower(trim($pasaje->estado));
                @endphp
                <tr>
                    <td class="font-semibold" style="color: #1e293b;">
                        {{ $pasaje->usuario?->name ?? 'Sin usuario' }}
                    </td>

                    <td class="text-center" style="color: #64748b;">
                        {{ optional($pasaje->venta)->created_at ? $pasaje->venta->created_at->format('d/m/Y H:i') : '-' }}
                    </td>

                    <td>
                        @if ($pasaje->persona)
                            {{ trim(($pasaje->persona->nombres ?? '') . ' ' . ($pasaje->persona->apellido_paterno ?? '') . ' ' . ($pasaje->persona->apellido_materno ?? '')) }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>

                    <td>{{ $pasaje->origen?->nombre ?? '-' }}</td>

                    <td>{{ $pasaje->destino?->nombre ?? '-' }}</td>

                    <td class="text-center font-semibold">
                        {{ $pasaje->asiento_numero }}
                    </td>

                    <td class="text-center">
                        <span
                            class="status-badge 
                            @if (in_array($estadoClean, ['pagado', 'vendido', 'confirmado'])) status-vendido
                            @elseif(in_array($estadoClean, ['pendiente', 'reservado'])) status-pendiente
                            @elseif(in_array($estadoClean, ['anulado', 'cancelado'])) status-anulado
                            @else status-default @endif">
                            {{ $pasaje->estado }}
                        </span>
                    </td>

                    <td class="text-right font-semibold">
                        S/ {{ number_format($precio, 2) }}
                    </td>
                </tr>
            @endforeach

            <!-- Fila de Total General -->
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL GENERAL</td>
                <td class="text-right">S/ {{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Pie del reporte -->
    <table class="footer">
        <tr>
            <td style="border: none; padding: 0;">Sistema de Control de Pasajes</td>
            <td style="border: none; padding: 0;" class="text-right">Registros mostrados: {{ count($pasajes) }}</td>
        </tr>
    </table>

</body>

</html>

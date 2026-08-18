<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Venta de Pasajes General</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #2c3e50;
            margin: 0;
            padding: 10px;
        }

        /* Encabezado principal */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 9px;
            font-weight: bold;
        }

        /* Tabla principal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            padding: 6px 5px;
            letter-spacing: 0.3px;
        }

        td {
            padding: 5px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        /* Filas alternadas */
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Alineaciones */
        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        /* Badges e identificadores */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #334155;
        }

        /* Fila de Totales */
        .total-row td {
            background-color: #f1f5f9;
            border-top: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
            font-size: 9.5px;
            padding: 8px 5px;
        }

        .total-label {
            font-weight: bold;
            color: #0f172a;
        }

        .total-amount {
            font-weight: bold;
            color: #1e40af;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Reporte General de Venta de Pasajes</h2>
        <p>
            PERÍODO:
            <span>{{ $desde->format('d/m/Y') }}</span>
            AL
            <span>{{ $hasta->format('d/m/Y') }}</span>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 13%;">Fecha</th>
                <th style="width: 14%;">Usuario</th>
                <th style="width: 22%;">Pasajero</th>
                <th style="width: 13%;">Origen</th>
                <th style="width: 13%;">Destino</th>
                <th class="center" style="width: 7%;">Asiento</th>
                <th class="center" style="width: 8%;">Estado</th>
                <th class="right" style="width: 10%;">Precio</th>
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
                @endphp

                <tr>
                    <td>
                        {{ optional($pasaje->venta)->created_at ? $pasaje->venta->created_at->format('d/m/Y H:i') : '-' }}
                    </td>

                    <td>
                        {{ $pasaje->usuario?->name ?? '-' }}
                    </td>

                    <td>
                        @if ($pasaje->persona)
                            {{ $pasaje->persona->nombres ?? '' }}
                            {{ $pasaje->persona->apellido_paterno ?? '' }}
                            {{ $pasaje->persona->apellido_materno ?? '' }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        {{ $pasaje->origen?->nombre ?? '-' }}
                    </td>

                    <td>
                        {{ $pasaje->destino?->nombre ?? '-' }}
                    </td>

                    <td class="center">
                        <span class="badge">{{ $pasaje->asiento_numero }}</span>
                    </td>

                    <td class="center">
                        {{ $pasaje->estado }}
                    </td>

                    <td class="right">
                        S/ {{ number_format($precio, 2) }}
                    </td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="7" class="right total-label">
                    TOTAL GENERAL:
                </td>
                <td class="right total-amount">
                    S/ {{ number_format($total, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>

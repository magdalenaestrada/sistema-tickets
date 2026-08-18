<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 17px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #eeeeee;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 4px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>REPORTE DE VENTAS POR RUTA</h2>

        <p>
            Período:
            {{ $desde->format('d/m/Y') }}
            al
            {{ $hasta->format('d/m/Y') }}
        </p>
    </div>

    @php
        $total = 0;
    @endphp

    <table>

        <thead>
            <tr>
                <th>Fecha Venta</th>
                <th>Usuario</th>
                <th>Ruta</th>
                <th>Pasajero</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Asiento</th>
                <th>Estado</th>
                <th>Precio</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($pasajes as $pasaje)

                @php
                    $precio = $pasaje->precio_cobrado
                        ?? $pasaje->precio_pasaje
                        ?? 0;

                    $total += $precio;

                    $ruta = $pasaje->salida?->horario?->ruta;
                @endphp

                <tr>

                    <td>
                        {{ optional($pasaje->venta)->created_at
                            ? $pasaje->venta->created_at->format('d/m/Y H:i')
                            : '' }}
                    </td>

                    <td>
                        {{ $pasaje->usuario?->name ?? '' }}
                    </td>

                    <td>
                        {{ $ruta?->descripcion ?? '' }}
                    </td>

                    <td>
                        @if ($pasaje->persona)
                            {{ $pasaje->persona->nombres ?? '' }}
                            {{ $pasaje->persona->apellido_paterno ?? '' }}
                            {{ $pasaje->persona->apellido_materno ?? '' }}
                        @endif
                    </td>

                    <td>
                        {{ $pasaje->origen?->nombre ?? '' }}
                    </td>

                    <td>
                        {{ $pasaje->destino?->nombre ?? '' }}
                    </td>

                    <td class="center">
                        {{ $pasaje->asiento_numero }}
                    </td>

                    <td class="center">
                        {{ $pasaje->estado }}
                    </td>

                    <td class="right">
                        S/ {{ number_format($precio, 2) }}
                    </td>

                </tr>

            @endforeach

            <tr class="total">
                <td colspan="8" class="right">
                    TOTAL
                </td>

                <td class="right">
                    S/ {{ number_format($total, 2) }}
                </td>
            </tr>

        </tbody>

    </table>

</body>

</html>
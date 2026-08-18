<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>Historial de Pasajero</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 17px;
        }

        .header p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 4px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        $primerPasaje = $pasajes->first();
        $persona = $primerPasaje?->persona;

        $nombrePasajero = $persona
            ? trim(
                ($persona->nombres ?? '') . ' ' .
                ($persona->apellido_paterno ?? '') . ' ' .
                ($persona->apellido_materno ?? '')
            )
            : 'Todos los pasajeros';

        $total = 0;
    @endphp

    <div class="header">

        <h2>
            HISTORIAL DE PASAJERO
        </h2>

        @if ($persona)
            <p>
                <strong>Pasajero:</strong>
                {{ $nombrePasajero }}
            </p>

            @if (!empty($persona->numero_documento))
                <p>
                    <strong>Documento:</strong>
                    {{ $persona->numero_documento }}
                </p>
            @endif
        @endif

        <p>
            <strong>Período:</strong>
            {{ $desde->format('d/m/Y') }}
            al
            {{ $hasta->format('d/m/Y') }}
        </p>

    </div>

    <table>

        <thead>
            <tr>
                <th>Fecha Venta</th>
                <th>Ruta</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Asiento</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Precio</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($pasajes as $pasaje)

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
                        {{ $ruta?->descripcion ?? '' }}
                    </td>

                    <td>
                        {{ $pasaje->origen?->descripcion
                            ?? $pasaje->origen?->nombre
                            ?? '' }}
                    </td>

                    <td>
                        {{ $pasaje->destino?->descripcion
                            ?? $pasaje->destino?->nombre
                            ?? '' }}
                    </td>

                    <td class="center">
                        {{ $pasaje->asiento_numero }}
                    </td>

                    <td>
                        {{ $pasaje->usuario?->name ?? '' }}
                    </td>

                    <td class="center">
                        {{ $pasaje->estado }}
                    </td>

                    <td class="right">
                        S/ {{ number_format($precio, 2) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="center">
                        No se encontraron viajes para el pasajero.
                    </td>
                </tr>

            @endforelse

            @if ($pasajes->count() > 0)

                <tr class="total">

                    <td colspan="7" class="right">
                        TOTAL
                    </td>

                    <td class="right">
                        S/ {{ number_format($total, 2) }}
                    </td>

                </tr>

            @endif

        </tbody>

    </table>

</body>

</html>
<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Viajes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        ``` table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
    ```

</head>

<body>

    ```
    <h3>Reporte de Viajes</h3>

    <p>
        Desde:
        {{ $filtros['fecha_inicio'] ?? 'Inicio' }}
        -
        Hasta:
        {{ $filtros['fecha_fin'] ?? 'Fin' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>ID Salida</th>
                <th>Fecha</th>
                <th>Hora Salida</th>
                <th>Hora Llegada</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Tipo Vehículo</th>
                <th>Tipo Viaje</th>
                <th>Vehículo</th>
                <th>Pasajeros</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($salidas as $salida)
                @php

                    $ruta = $salida->horario?->ruta;

                    $primerPunto = $ruta?->puntos ? $ruta->puntos->sortBy('orden')->first() : null;

                    $ultimoPunto = $ruta?->puntos ? $ruta->puntos->sortByDesc('orden')->first() : null;

                @endphp

                <tr>

                    <td>{{ $salida->id }}</td>

                    <td>
                        {{ $salida->fecha_formateada }}
                    </td>

                    <td>
                        {{ $salida->hora_salida }}
                    </td>

                    <td>
                        {{ $salida->hora_llegada }}
                    </td>

                    <td>
                        {{ $primerPunto?->pueblito?->descripcion ?? '' }}
                    </td>

                    <td>
                        {{ $ultimoPunto?->pueblito?->descripcion ?? '' }}
                    </td>

                    <td>
                        {{ $salida->horario?->tipo_vehiculo?->descripcion ?? '' }}
                    </td>

                    <td>
                        {{ $salida->horario?->tipo_viaje?->descripcion ?? '' }}
                    </td>

                    <td>
                        {{ $salida->vehiculo?->numero_placa ?? '' }}
                    </td>

                    <td>
                        {{ $salida->pasajes->count() }}
                    </td>

                </tr>
            @endforeach

        </tbody>
    </table>

</body>

</html>

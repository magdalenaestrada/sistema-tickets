<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Pasajeros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
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
</head>

<body>
    <h3>Reporte de Pasajeros (Manifiesto)</h3>
    <p>Desde: {{ $request->fecha_inicio ?? 'Inicio' }} - Hasta: {{ $request->fecha_fin ?? 'Fin' }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pasajero</th>
                <th>DNI</th>
                <th>Horario</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Asiento</th>
                <th>Vehículo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $pasaje)
                <tr>
                    <td>{{ $pasaje->id }}</td>
                    <td>{{ $pasaje->persona->nombres ?? '' }} {{ $pasaje->persona->apellidos ?? '' }}</td>
                    <td>{{ $pasaje->persona->documento ?? '' }}</td>
                    <td>{{ $pasaje->horario->fecha_salida }} {{ $pasaje->horario->hora_embarque }}</td>
                    <td>{{ $pasaje->horario->punto_origen->nombre_comercial ?? '' }}</td>
                    <td>{{ $pasaje->horario->punto_destino->nombre_comercial ?? '' }}</td>
                    <td>{{ $pasaje->asiento_numero }}</td>
                    <td>{{ $pasaje->horario->tipoVehiculo->descripcion ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

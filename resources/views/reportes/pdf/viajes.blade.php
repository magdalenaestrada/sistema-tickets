<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Viajes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h3>Reporte de Viajes (Salidas Programadas vs Ejecutadas)</h3>
    <p>Desde: {{ $request->fecha_inicio ?? 'Inicio' }} - Hasta: {{ $request->fecha_fin ?? 'Fin' }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Vehículo</th>
                <th>Pasajeros Vendidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $horario)
            <tr>
                <td>{{ $horario->id }}</td>
                <td>{{ $horario->fecha_salida }}</td>
                <td>{{ $horario->hora_embarque }}</td>
                <td>{{ $horario->punto_origen->nombre_comercial ?? '' }}</td>
                <td>{{ $horario->punto_destino->nombre_comercial ?? '' }}</td>
                <td>
                    @foreach($horario->asignaciones as $a)
                        {{ $a->vehiculo->numero_placa ?? '' }} {{ $a->vehiculo->tipo_vehiculo->descripcion ?? '' }}<br>
                    @endforeach
                </td>
                <td>{{ $horario->pasajes->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

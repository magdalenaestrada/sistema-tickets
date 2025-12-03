<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Encomiendas</title>
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
    <h3>Reporte de Encomiendas</h3>
    <p>Desde: {{ $request->fecha_inicio ?? 'Inicio' }} - Hasta: {{ $request->fecha_fin ?? 'Fin' }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Total</th>
                <th>Detalles</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $encomienda)
                <tr>
                    <td>{{ $encomienda->id }}</td>
                    <td>{{ $encomienda->emisor->nombres ?? '' }} {{ $encomienda->emisor->apellidos ?? '' }}</td>
                    <td>{{ $encomienda->sucursal_origen->nombre_comercial ?? '' }}</td>
                    <td>{{ $encomienda->sucursal_destino->nombre_comercial ?? '' }}</td>
                    <td>{{ number_format($encomienda->total, 2) }}</td>
                    <td>
                        @foreach ($encomienda->detalles as $d)
                            {{ $d->tipo_encomienda->descripcion ?? '' }} - {{ $d->peso }}kg -
                            {{ number_format($d->costo, 2) }}<br>
                        @endforeach
                    </td>
                    <td>{{ $encomienda->estado }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

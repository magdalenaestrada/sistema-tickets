<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cupones</title>
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
    <h3>Reporte de Uso de Cupones</h3>
    <p>Desde: {{ $request->fecha_inicio ?? 'Inicio' }} - Hasta: {{ $request->fecha_fin ?? 'Fin' }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Cliente</th>
                <th>Efectivo</th>
                <th>Porcentaje</th>
                <th>Fecha de uso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $cupon)
                <tr>
                    <td>{{ $cupon->id }}</td>
                    <td>{{ $cupon->codigo }}</td>
                    <td>{{ $cupon->persona->nombre ?? '' }}</td>
                    <td>{{ $cupon->monto_efectivo }}</td>
                    <td>{{ $cupon->porcentaje . '%' }}</td>
                    <td>{{ $cupon->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

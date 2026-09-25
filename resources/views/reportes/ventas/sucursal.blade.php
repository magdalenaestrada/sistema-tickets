<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f0f0f0;
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background: #f8f8f8;
        }
    </style>
</head>

<body>
    <h3>Ventas por Sucursal</h3>
    <p>Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Sucursal</th>
                <th>Operaciones</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sucursales as $s)
                <tr>
                    <td>{{ $s['sucursal'] }}</td>
                    <td>{{ $s['operaciones'] }}</td>
                    <td>S/ {{ number_format($s['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL GENERAL</td>
                <td>S/ {{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>

<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: right; }
        th, td:first-child, td:nth-child(2) { text-align: left; }
        th { background: #f0f0f0; }
        .total-row { font-weight: bold; background: #f8f8f8; }
        .negativo { color: #b00020; }
        .positivo { color: #1a7d1a; }
    </style>
</head>
<body>
    <h3>Cuadre de Caja por Vendedor</h3>
    <p>Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Sucursal</th>
                <th>Apertura</th>
                <th>Ingresos</th>
                <th>Salidas</th>
                <th>Esperado</th>
                <th>Declarado</th>
                <th>Diferencia</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filas as $f)
                <tr>
                    <td>{{ $f['vendedor'] }}</td>
                    <td>{{ $f['sucursal'] }}</td>
                    <td>S/ {{ number_format($f['apertura'], 2) }}</td>
                    <td>S/ {{ number_format($f['ingresos'], 2) }}</td>
                    <td>S/ {{ number_format($f['salidas'], 2) }}</td>
                    <td>S/ {{ number_format($f['esperado'], 2) }}</td>
                    <td>S/ {{ number_format($f['declarado'], 2) }}</td>
                    <td class="{{ $f['diferencia'] < 0 ? 'negativo' : 'positivo' }}">
                        S/ {{ number_format($f['diferencia'], 2) }}
                    </td>
                    <td>{{ $f['estado_caja'] }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTALES</td>
                <td>S/ {{ number_format($totales['apertura'], 2) }}</td>
                <td>S/ {{ number_format($totales['ingresos'], 2) }}</td>
                <td>S/ {{ number_format($totales['salidas'], 2) }}</td>
                <td>S/ {{ number_format($totales['esperado'], 2) }}</td>
                <td>S/ {{ number_format($totales['declarado'], 2) }}</td>
                <td class="{{ $totales['diferencia'] < 0 ? 'negativo' : 'positivo' }}">
                    S/ {{ number_format($totales['diferencia'], 2) }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
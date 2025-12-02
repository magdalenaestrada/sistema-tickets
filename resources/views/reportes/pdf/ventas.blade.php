<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h3>Reporte de Ventas</h3>
    <p>Desde: {{ $request->fecha_inicio ?? 'Inicio' }} - Hasta: {{ $request->fecha_fin ?? 'Fin' }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Métodos de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $venta)
            <tr>
                <td>{{ $venta->id }}</td>
                <td>{{ $venta->persona->nombres ?? '' }} {{ $venta->persona->apellidos ?? '' }}</td>
                <td>{{ $venta->fecha_emision }}</td>
                <td>{{ number_format($venta->total, 2) }}</td>
                <td>{{ $venta->estado }}</td>
                <td>
                    @foreach($venta->pagos as $pago)
                        {{ $pago->metodoPago->descripcion ?? '' }}: {{ number_format($pago->total,2) }}<br>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

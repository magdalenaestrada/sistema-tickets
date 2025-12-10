<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            page-break-inside: auto;
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

        h3 {
            margin-bottom: 0;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <h3>Reporte de Ventas Emitidas</h3>
    <p>Desde: {{ $filtros['fecha_inicio'] ?? 'Inicio' }} - Hasta: {{ $filtros['fecha_fin'] ?? 'Fin' }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Tipo Documento</th>
                <th>Sucursal</th>
                <th>Vendedor</th>
                <th>Métodos de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emitidos as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ optional($venta->persona)->nombres ?? '' }} {{ optional($venta->persona)->apellidos ?? '' }}
                    </td>
                    <td>{{ optional($venta->fecha_emision)->format('d/m/Y') }}</td>
                    <td>{{ number_format($venta->total, 2) }}</td>
                    <td>
                        {{ $venta->estado === 'E' ? 'Emitido' : ($venta->estado === 'A' ? 'Anulado' : $venta->estado) }}
                    </td>
                    <td>{{ optional($venta->tipoDocumentoFactura)->descripcion ?? '' }}</td>
                    <td>{{ optional($venta->sucursal)->nombre_comercial ?? '' }}</td>
                    <td>{{ optional($venta->usuario->persona)->nombres ?? '' }}
                        {{ optional($venta->usuario->persona)->apellidos ?? '' }}</td>
                    <td>
                        @foreach ($venta->pagos as $pago)
                            {{ optional($pago->metodoPago)->descripcion ?? '' }}:
                            {{ number_format($pago->total, 2) }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($anulados->count())
        <div class="page-break"></div>
        <h3>Ventas Anuladas</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Tipo Documento</th>
                    <th>Sucursal</th>
                    <th>Vendedor</th>
                    <th>Métodos de Pago</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anulados as $venta)
                    <tr>
                        <td>{{ $venta->id }}</td>
                        <td>{{ optional($venta->persona)->nombres ?? '' }}
                            {{ optional($venta->persona)->apellidos ?? '' }}</td>
                        <td>{{ optional($venta->fecha_emision)->format('d/m/Y') }}</td>
                        <td>{{ number_format($venta->total, 2) }}</td>
                        <td>
                            {{ $venta->estado === 'E' ? 'Emitido' : ($venta->estado === 'A' ? 'Anulado' : $venta->estado) }}
                        </td>
                        <td>{{ optional($venta->tipoDocumentoFactura)->descripcion ?? '' }}</td>
                        <td>{{ optional($venta->sucursal)->nombre_comercial ?? '' }}</td>
                        <td>{{ optional($venta->usuario->persona)->nombres ?? '' }}
                            {{ optional($venta->usuario->persona)->apellidos ?? '' }}</td>
                        <td>
                            @foreach ($venta->pagos as $pago)
                                {{ optional($pago->metodoPago)->descripcion ?? '' }}:
                                {{ number_format($pago->total, 2) }}<br>
                            @endforeach
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>

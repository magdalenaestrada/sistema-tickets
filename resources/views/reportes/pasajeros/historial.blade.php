<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Venta de Pasajes</title>
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        .header { text-align: center; margin-bottom: 15px; }
        h2 { margin: 0; font-size: 17px; }
        p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #e8eef5; font-weight: bold; }
        th, td { border: 1px solid #aaa; padding: 4px; word-wrap: break-word; }
        .center { text-align: center; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #fff2cc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>VENTA DE PASAJES</h2>
        <p><strong>Período:</strong> {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>

        @if ($dni !== null)
            <p><strong>DNI del pasajero:</strong> {{ $dni }}</p>
        @else
            <p><strong>Pasajeros:</strong> Todos</p>
        @endif

        <p><strong>Cantidad de pasajes:</strong> {{ $cantidadPasajes }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 9%">Fecha venta</th>
                <th style="width: 9%">Comprobante</th>
                <th style="width: 17%">Pasajero</th>
                <th style="width: 8%">Documento</th>
                <th style="width: 10%">Ruta</th>
                <th style="width: 9%">Origen</th>
                <th style="width: 9%">Destino</th>
                <th style="width: 4%">Asiento</th>
                <th style="width: 11%">Vendedor</th>
                <th style="width: 7%">Estado</th>
                <th style="width: 7%">Precio</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    <td>{{ $fila['fecha'] }}</td>
                    <td>{{ $fila['comprobante'] }}</td>
                    <td>{{ $fila['pasajero'] }}</td>
                    <td>{{ $fila['documento'] }}</td>
                    <td>{{ $fila['ruta'] }}</td>
                    <td>{{ $fila['origen'] }}</td>
                    <td>{{ $fila['destino'] }}</td>
                    <td class="center">{{ $fila['asiento'] }}</td>
                    <td>{{ $fila['vendedor'] }}</td>
                    <td class="center">{{ $fila['estado'] }}</td>
                    <td class="right">S/ {{ number_format($fila['precio'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center">
                        {{ $dni !== null
                            ? 'No se encontraron pasajes para el DNI y período seleccionados.'
                            : 'No se encontraron pasajes para el período seleccionado.' }}
                    </td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="10" class="right">TOTAL DE IMPORTES DE PASAJES</td>
                <td class="right">S/ {{ number_format($totalImporte, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>

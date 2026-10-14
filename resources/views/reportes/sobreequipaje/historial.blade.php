<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de sobreequipajes</title>
    <style>
        @page { margin: 25px 23px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #273248; }
        h1 { font-size: 17px; margin: 0 0 4px; color: #222b46; }
        .subtitle { color: #718096; font-size: 10px; margin-bottom: 12px; }
        .meta { background: #f6f7ff; padding: 10px; margin-bottom: 13px; border-radius: 5px; }
        .meta span { margin-right: 17px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 7px 5px; color: #fff; background: #625cff; font-size: 8px; text-align: left; }
        td { padding: 7px 5px; border-bottom: 1px solid #e8eaf2; vertical-align: top; overflow-wrap: break-word; }
        tr:nth-child(even) td { background: #f9faff; }
        .muted { font-size: 8px; color: #8793a7; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { margin-top: 15px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Historial de venta de sobreequipajes</h1>
    <div class="subtitle">Sobreequipajes asociados a pasajes y filtrados por DNI del pasajero.</div>

    <div class="meta">
        <span><strong>Desde:</strong> {{ $desde->format('d/m/Y') }}</span>
        <span><strong>Hasta:</strong> {{ $hasta->format('d/m/Y') }}</span>
        <span><strong>DNI:</strong> {{ $dni ?? 'Todos' }}</span>
        <span><strong>Sobreequipajes:</strong> {{ $cantidadSobreequipajes }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 11%">Fecha de venta</th>
                <th style="width: 9%">Código</th>
                <th style="width: 19%">Pasajero / DNI</th>
                <th style="width: 13%">Ruta</th>
                <th style="width: 13%">Origen</th>
                <th style="width: 13%">Destino</th>
                <th style="width: 6%" class="center">Bultos</th>
                <th style="width: 7%" class="right">Peso</th>
                <th style="width: 9%" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    <td>{{ $fila['fecha'] }}</td>
                    <td>{{ $fila['codigo'] }}</td>
                    <td>{{ $fila['pasajero'] }}<div class="muted">{{ $fila['dni'] }}</div></td>
                    <td>{{ $fila['ruta'] }}</td>
                    <td>{{ $fila['origen'] }}</td>
                    <td>{{ $fila['destino'] }}</td>
                    <td class="center">{{ $fila['bultos'] }}</td>
                    <td class="right">{{ number_format($fila['peso'], 2) }}</td>
                    <td class="right">{{ number_format($fila['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center" style="padding:20px">No se encontraron sobreequipajes con estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="totals">
        <strong>Total peso:</strong> {{ number_format($totalPeso, 2) }} &nbsp;&nbsp;
        <strong>Importe total:</strong> S/ {{ number_format($totalImporte, 2) }}
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de venta de encomiendas</title>
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
    <h1>Historial de venta de encomiendas</h1>
    <div class="subtitle">Relación de envíos registrados por período, emisor o receptor.</div>

    <div class="meta">
        <span><strong>Desde:</strong> {{ $desde->format('d/m/Y') }}</span>
        <span><strong>Hasta:</strong> {{ $hasta->format('d/m/Y') }}</span>
        <span><strong>DNI:</strong> {{ $dni ?? 'Todos' }}</span>
        <span><strong>Participación:</strong> {{ ['emisor' => 'Emisor', 'receptor' => 'Receptor', 'ambos' => 'Emisor o receptor'][$tipoPersona] ?? 'Emisor o receptor' }}</span>
        <span><strong>Encomiendas:</strong> {{ $cantidadEncomiendas }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 11%">Fecha de venta</th>
                <th style="width: 9%">Código</th>
                <th style="width: 18%">Emisor / DNI</th>
                <th style="width: 18%">Receptor / DNI</th>
                <th style="width: 13%">Origen</th>
                <th style="width: 13%">Destino</th>
                <th class="center" style="width: 5%">Bultos</th>
                <th class="right" style="width: 5%">Peso</th>
                <th class="right" style="width: 8%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    <td>{{ $fila['fecha'] }}</td>
                    <td>{{ $fila['codigo'] }}</td>
                    <td>{{ $fila['emisor'] }}<div class="muted">{{ $fila['dni_emisor'] }}</div></td>
                    <td>{{ $fila['receptor'] }}<div class="muted">{{ $fila['dni_receptor'] }}</div></td>
                    <td>{{ $fila['origen'] }}</td>
                    <td>{{ $fila['destino'] }}</td>
                    <td class="center">{{ $fila['bultos'] }}</td>
                    <td class="right">{{ number_format($fila['peso'], 2) }}</td>
                    <td class="right">{{ number_format($fila['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center" style="padding:20px">No se encontraron encomiendas con estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <strong>Total de peso:</strong> {{ number_format($totalPeso, 2) }} &nbsp;&nbsp;
        <strong>Importe total:</strong> S/ {{ number_format($totalImporte, 2) }}
    </div>
</body>
</html>

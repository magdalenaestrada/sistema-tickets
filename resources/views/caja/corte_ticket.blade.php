<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Corte de Caja #{{ $caja->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 20px;
        }

        .text-center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        th {
            background: #f2f2f2;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .totales td {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="text-center mb-3">
        <div class="title">{{ $caja->sucursal->empresa->razon_social ?? 'EMPRESA' }}</div>
        <div class="subtitle">{{ $caja->sucursal->nombre ?? 'Sucursal' }}</div>
        <div>CORTE DE CAJA</div>
    </div>

    <table class="no-border mb-3">
        <tr>
            <td><strong>Caja:</strong> #{{ $caja->id }}</td>
            <td><strong>Estado:</strong> {{ in_array($caja->estado, ['C', 'cerrada']) ? 'CERRADA' : 'ABIERTA' }}</td>
        </tr>
        <tr>
            <td><strong>Cajero:</strong> {{ $caja->usuario->name ?? '---' }}</td>
            <td><strong>Fecha apertura:</strong> {{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td><strong>Sucursal:</strong> {{ $caja->sucursal->nombre ?? '---' }}</td>
            <td><strong>Fecha cierre:</strong> {{ optional($caja->fecha_cierre)->format('d/m/Y h:i A') ?? '---' }}</td>
        </tr>
    </table>

    <table class="mb-3">
        <thead>
            <tr>
                <th colspan="2">Resumen general</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monto apertura</td>
                <td>S/ {{ number_format($caja->monto_apertura, 2) }}</td>
            </tr>
            <tr>
                <td>Ingreso por Yape</td>
                <td>S/ {{ number_format($caja->ingresos_yape, 2) }}</td>
            </tr>
            <tr>
                <td>Ingreso por Transferencia</td>
                <td>S/ {{ number_format($caja->ingresos_transferencia, 2) }}</td>
            </tr>
            <tr>
                <td>Ingreso por Tarjeta</td>
                <td>S/ {{ number_format($caja->ingresos_tarjeta, 2) }}</td>
            </tr>
            <tr>
                <td>Ingreso por Efectivo</td>
                <td>S/ {{ number_format($caja->ingresos_efectivo, 2) }}</td>
            </tr>
            <tr>
                <td>Total ingresos</td>
                <td>S/ {{ number_format($caja->total_ingresos, 2) }}</td>
            </tr>
            <tr>
                <td>Total egresos</td>
                <td>S/ {{ number_format($caja->total_salidas, 2) }}</td>
            </tr>
            <tr>
                <td>Saldo sistema</td>
                <td>S/ {{ number_format($caja->monto_actual, 2) }}</td>
            </tr>
            <tr>
                <td>Egresos efectivo</td>
                <td>S/ {{ number_format($caja->egresos_efectivo, 2) }}</td>
            </tr>
            <tr>
                <td>Efectivo esperado</td>
                <td>S/ {{ number_format($caja->efectivo_esperado, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="mb-3">
        <thead>
            <tr>
                <th colspan="7">Detalle de movimientos</th>
            </tr>
            <tr>
                <th>Fecha</th>
                <th>Ticket</th>
                <th>Tipo</th>
                <th>Subtipo</th>
                <th>Método</th>
                <th>Descripción</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($caja->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->created_at?->format('d/m/Y h:i A') }}</td>
                    <td>{{ $detalle->numero_ticket }}</td>
                    <td>{{ $detalle->amount > 0 ? 'Ingreso' : 'Egreso' }}</td>
                    <td>{{ $detalle->subtipo->descripcion ?? '---' }}</td>
                    <td>{{ $detalle->metodoPago->descripcion ?? '---' }}</td>
                    <td>
                        {{ $detalle->description ?? '---' }}
                        @if ($detalle->anulado)
                            <br><strong>(ANULADO)</strong>
                        @endif
                    </td>
                    <td>S/ {{ number_format(abs($detalle->amount), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        <table class="no-border">
            <tr>
                <td class="text-center" style="padding-top: 30px;">
                    ___________________________<br>
                    Firma cajero
                </td>
                <td class="text-center" style="padding-top: 30px;">
                    ___________________________<br>
                    Firma supervisor
                </td>
            </tr>
        </table>
    </div>

    <div class="text-center mt-3 no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

</body>

</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Corte de Caja #{{ $caja->id }}</title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 260px; /* 80mm */
            margin: auto;
            font-size: 13px;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
        .anulado { color: red; font-weight: bold; font-size: 12px; }

        @media print {
            button { display: none; }
        }
    </style>

    <script>
        // Se abre automáticamente el modal de impresión
        window.onload = function() {
            window.print();
        };
    </script>
</head>

<body>

    <div class="center bold" style="font-size:16px;">
        CORTE DE CAJA
    </div>
    <div class="center">Caja #{{ $caja->id }}</div>

    <div class="line"></div>

    <p><strong>Usuario:</strong> {{ $caja->usuario->persona->nombres }}</p>
    <p><strong>Apertura:</strong> {{ $caja->fecha_creacion->format('d/m/Y H:i') }}</p>
    @if ($caja->fecha_cierre)
    <p><strong>Cierre:</strong> {{ $caja->fecha_cierre->format('d/m/Y H:i') }}</p>
    @endif

    <div class="line"></div>

    {{-- ========================== ENTRADAS =========================== --}}
    <div class="bold center">ENTRADAS</div>
    <div class="line"></div>

    @php
        $entradas = $caja->detalles->filter(function($d){
            return isset($d->subtipo->tipo_movimiento->descripcion)
                && $d->subtipo->tipo_movimiento->descripcion === 'Ingreso'
                && !$d->anulado;
        });
    @endphp

    @forelse($entradas as $d)
        <p>
            <strong>{{ $d->description ?? 'Venta' }}</strong><br>
            MP: {{ $d->metodoPago->nombre ?? 'N/A' }}<br>
            Ticket: {{ $d->numero_ticket ?? '-' }}<br>
            <strong>S/ {{ number_format($d->amount, 2) }}</strong>
        </p>
        <div class="line"></div>
    @empty
        <div class="center muted">-- Sin entradas --</div>
    @endforelse

    {{-- ========================== SALIDAS ============================ --}}
    <div class="bold center">SALIDAS</div>
    <div class="line"></div>

    @php
        $salidas = $caja->detalles->filter(function($d){
            return isset($d->subtipo->tipo_movimiento->descripcion)
                && $d->subtipo->tipo_movimiento->descripcion === 'Salida'
                && !$d->anulado;
        });
    @endphp

    @forelse($salidas as $d)
        <p>
            <strong>{{ $d->description ?? 'Salida' }}</strong><br>
            MP: {{ $d->metodoPago->nombre ?? 'N/A' }}<br>
            <strong>S/ {{ number_format($d->amount, 2) }}</strong>
        </p>
        <div class="line"></div>
    @empty
        <div class="center muted">-- Sin salidas --</div>
    @endforelse


    {{-- ========================== ANULADOS ============================ --}}
    <div class="bold center">ANULADOS</div>
    <div class="line"></div>

    @php
        $anulados = $caja->detalles->filter(fn($d) => $d->anulado);
    @endphp

    @forelse($anulados as $d)
        <p class="anulado">
            ANULADO<br>
            {{ $d->description ?? 'Movimiento' }}<br>
            Ticket: {{ $d->numero_ticket ?? '-' }}<br>
            S/ {{ number_format($d->amount, 2) }}
        </p>
        <div class="line"></div>
    @empty
        <div class="center muted">-- Sin anulados --</div>
    @endforelse


    {{-- ========================== RESUMEN ============================ --}}
    <div class="bold center">RESUMEN</div>
    <div class="line"></div>

    <p>Total Entradas: <strong>S/ {{ number_format($caja->total_ingresos, 2) }}</strong></p>
    <p>Total Salidas: <strong>S/ {{ number_format($caja->total_salidas, 2) }}</strong></p>
    <p>Monto Actual: <strong>S/ {{ number_format($caja->monto_actual, 2) }}</strong></p>

    <div class="line"></div>

    <div class="center">
        ¡Gracias por su trabajo!
    </div>

    <button onclick="window.print()">Reimprimir</button>

</body>
</html>

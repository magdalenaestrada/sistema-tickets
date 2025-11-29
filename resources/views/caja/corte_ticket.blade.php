<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Corte de Caja #{{ $caja->id }}</title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 260px;
            /* 80mm */
            margin: auto;
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .anulado {
            color: red;
            font-weight: bold;
            font-size: 12px;
        }

        .tabla-simple {
            width: 100%;
            margin: 5px 0;
        }

        .tabla-simple tr td {
            padding: 2px 0;
            vertical-align: top;
        }

        .tabla-simple tr td:first-child {
            width: 30%;
        }

        .tabla-simple tr td:nth-child(2) {
            width: 40%;
        }

        .tabla-simple tr td:last-child {
            width: 30%;
            text-align: right;
        }

        .seccion-titulo {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>

<body>

    <div class="center bold" style="font-size:16px;">
        CORTE DE CAJA
    </div>
    <div class="center">Caja {{ $caja->sucursal->nombre_comercial }}</div>

    <div class="line"></div>

    <p><strong>Usuario:</strong> {{ $usuario->persona->nombres }}</p>
    <p><strong>Apertura:</strong> {{ $caja->fecha_creacion }}</p>
    <p><strong>Cierre:</strong> {{ $caja->fecha_cierre }}</p>

    <div class="line"></div>
    <div class="bold center" style="font-size: 14px;">ENTRADAS</div>
    <div class="line"></div>

    @php
        $entradas = $caja->detalles->filter(function ($d) {
            return isset($d->subtipo->tipo_movimiento->id) && $d->subtipo->tipo_movimiento->id === 1 && !$d->anulado;
        });

        // Agrupar por método de pago
        $entradasEfectivo = $entradas->filter(function ($d) {
            return $d->metodoPago && strtolower($d->metodoPago->descripcion) === 'efectivo';
        });

        $entradasBilletera = $entradas->filter(function ($d) {
            return $d->metodoPago && strtolower($d->metodoPago->descripcion) !== 'efectivo';
        });
    @endphp

    {{-- EFECTIVO --}}
    @if ($entradasEfectivo->count() > 0)
        <div class="seccion-titulo">EFECTIVO</div>
        <table class="tabla-simple">
            @foreach ($entradasEfectivo as $detalle)
                @php
                    $ticket = '';
                    $descripcion = '';

                    // Verificar el tipo de servicio
                    if ($detalle->servicio) {
                        // ENCOMIENDA
                        if ($detalle->servicio instanceof \App\Models\Encomienda) {
                            $ticket = 'E-' . $detalle->servicio->id;
                            $descripcion = 'Encomienda';
                        }
                        // VENTA (ajusta según tu modelo)
                        elseif ($detalle->servicio instanceof \App\Models\Venta) {
                            $ticket = 'V-' . $detalle->servicio->id;
                            $descripcion = 'Venta';
                        }
                        // PASAJE (si lo tienes)
                        elseif ($detalle->servicio instanceof \App\Models\Pasaje) {
                            $ticket = 'P-' . $detalle->servicio->id;
                            $descripcion = 'Pasaje';
                        }
                        // Otros servicios
                        else {
                            $ticket = 'S-' . $detalle->servicio->id;
                            $descripcion = 'Servicio';
                        }
                    } else {
                        // Entrada directa de caja (sin servicio asociado)
                        $ticket = 'C-' . $detalle->id;
                        $descripcion = $detalle->description ?? 'Entrada Caja';
                    }
                @endphp
                <tr>
                    <td>{{ $ticket }}</td>
                    <td>{{ $descripcion }}</td>
                    <td>S/ {{ number_format($detalle->amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- BILLETERA / DIGITAL --}}
    @if ($entradasBilletera->count() > 0)
        <div class="seccion-titulo">BILLETERA</div>
        <table class="tabla-simple">
            @foreach ($entradasBilletera as $detalle)
                @php
                    $ticket = '';
                    $descripcion = '';

                    // Verificar el tipo de servicio
                    if ($detalle->servicio) {
                        // ENCOMIENDA
                        if ($detalle->servicio instanceof \App\Models\Encomienda) {
                            $ticket = 'E-' . $detalle->servicio->id;
                            $descripcion = 'Encomienda';
                        }
                        // VENTA
                        elseif ($detalle->servicio instanceof \App\Models\Venta) {
                            $ticket = 'V-' . $detalle->servicio->id;
                            $descripcion = 'Venta';
                        }
                        // PASAJE
                        elseif ($detalle->servicio instanceof \App\Models\Pasaje) {
                            $ticket = 'P-' . $detalle->servicio->id;
                            $descripcion = 'Pasaje';
                        }
                        // Otros servicios
                        else {
                            $ticket = 'S-' . $detalle->servicio->id;
                            $descripcion = 'Servicio';
                        }
                    } else {
                        // Entrada directa de caja
                        $ticket = 'C-' . $detalle->id;
                        $descripcion = $detalle->description ?? 'Entrada Caja';
                    }
                @endphp
                <tr>
                    <td>{{ $ticket }}</td>
                    <td>{{ $descripcion }}</td>
                    <td>S/ {{ number_format($detalle->amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($entradasEfectivo->count() === 0 && $entradasBilletera->count() === 0)
        <div class="center" style="margin: 10px 0;">-- Sin entradas --</div>
    @endif

    <div class="line"></div>

    <div class="bold center" style="font-size: 14px;">SALIDAS </div>
    <div class="line"></div>

    @php
        $salidas = $caja->detalles->filter(function ($d) {
            return isset($d->subtipo->tipo_movimiento->id) && $d->subtipo->tipo_movimiento->id === 2 && !$d->anulado;
        });

        $salidasEfectivo = $salidas->filter(function ($d) {
            return $d->metodoPago && strtolower($d->metodoPago->descripcion) === 'efectivo';
        });

        $salidasBilletera = $salidas->filter(function ($d) {
            return $d->metodoPago && strtolower($d->metodoPago->descripcion) !== 'efectivo';
        });
    @endphp

    @if ($salidasEfectivo->count() > 0)
        <div class="seccion-titulo">EFECTIVO</div>
        <table class="tabla-simple">
            @foreach ($salidasEfectivo as $detalle)
                @php
                    $motivo = $detalle->subtipo->descripcion;
                    $descripcion = $detalle->description;

                    if ($detalle->servicio && $detalle->servicio instanceof \App\Models\Encomienda) {
                        $encomienda = $detalle->servicio;
                    }
                @endphp
                <tr>
                    <td>{{ $motivo }}</td>
                    <td>{{ $descripcion }}</td>
                    <td>S/ {{ number_format($detalle->amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- BILLETERA / DIGITAL --}}
    @if ($entradasBilletera->count() > 0)
        <div class="seccion-titulo">BILLETERA</div>
        <table class="tabla-simple">
            @foreach ($salidasBilletera as $detalle)
                @php
                    $motivo = $detalle->subtipo->descripcion;
                    $descripcion = $detalle->description;

                    if ($detalle->servicio && $detalle->servicio instanceof \App\Models\Encomienda) {
                        $encomienda = $detalle->servicio;
                    }
                @endphp
                <tr>
                    <td>{{ $motivo }}</td>
                    <td>{{ $descripcion }}</td>
                    <td>S/ {{ number_format($detalle->amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
    {{-- ========================== ANULADOS ============================ --}}
    <div class="bold center" style="font-size: 14px;">ANULADOS</div>
    <div class="line"></div>

    @php
        $anulados = $caja->detalles->filter(fn($d) => $d->anulado);
    @endphp

    @if ($anulados->count() > 0)
        <table class="tabla-simple">
            @foreach ($anulados as $d)
                @php
                    $ticket = $d->numero_ticket ?? 'E-' . ($d->servicio_id ?? $d->id);
                @endphp
                <tr style="color: red;">
                    <td>{{ $ticket }}</td>
                    <td>{{ $d->description ?? 'Movimiento' }}</td>
                    <td>S/ {{ number_format($d->amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="center" style="margin: 10px 0;">-- Sin anulados --</div>
    @endif

    <div class="line"></div>

    {{-- ========================== RESUMEN ============================ --}}
    <div class="bold center" style="font-size: 14px;">RESUMEN</div>
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

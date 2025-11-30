<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $detalle->numero_ticket }}</title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 260px;
            /* 80mm = 280px, 58mm = 200px */
            margin: auto;
            font-size: 13px;
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
            font-size: 20px;
            text-align: center;
            margin-bottom: 10px;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>

    <script>
        // Abre el modal de impresión automáticamente
        window.onload = function() {
            window.print();
        };
    </script>
</head>

<body>

    {{-- Mostrar si está anulado --}}
    @if ($detalle->anulado)
        <div class="anulado">TICKET ANULADO</div>
        <div class="line"></div>
    @endif

    @php
        $empresa = $detalle->caja->usuario->sucursal->empresa;
    @endphp

    {{-- ENCABEZADO DE LA EMPRESA --}}
    <div class="center">

        <div class="bold" style="font-size:16px;">
            {{ $empresa->razon_social }}
        </div>

        <div class="bold">
            RUC: {{ $empresa->documento }}
        </div>

        <div>
            {{ $empresa->direccion }}
        </div>

        @if ($empresa->telefono)
            <div>
                Tel: {{ $empresa->telefono }}
            </div>
        @endif

        <div class="line"></div>
    </div>


    {{-- INFO DE TICKET --}}
    <p><strong>Ticket:</strong> {{ $detalle->numero_ticket }}</p>
    <p><strong>Fecha:</strong> {{ $detalle->created_at->format('d/m/Y H:i:s') }}</p>
    <p><strong>Tipo:</strong> {{ $detalle->subtipo->tipo_movimiento->descripcion ?? 'N/A' }}</p>
    <p><strong>Descripción:</strong> {{ $detalle->description }}</p>

    <div class="line"></div>

    <p><strong>Monto:</strong> S/ {{ number_format($detalle->amount, 2) }}</p>
    <p><strong>Método Pago:</strong> {{ $detalle->metodoPago->descripcion }}</p>

    <div class="line"></div>

    @php
        $servicio = $detalle->servicio;
    @endphp

    @if ($servicio)

        <div class="bold">DETALLES DEL SERVICIO</div>
        <div class="line"></div>

        {{-- SI ES ENCOMIENDA --}}
        @if ($servicio instanceof \App\Models\Encomienda)
            <p><strong>Tipo servicio:</strong> Encomienda</p>
            <p><strong>Origen:</strong> {{ $servicio->origen }}</p>
            <p><strong>Destino:</strong> {{ $servicio->destino }}</p>

            <div class="bold" style="margin-top:5px;">DETALLES</div>

            @foreach ($servicio->detalles as $d)
                <p>- {{ $d->tip }} ({{ $d->peso }} kg): S/ {{ number_format($d->costo, 2) }}</p>
            @endforeach
        @endif


        {{-- SI ES PASAJE --}}
        @if ($servicio instanceof \App\Models\Pasaje)
            <p><strong>Tipo servicio:</strong> Pasaje</p>
            <p><strong>Pasajero:</strong> {{ $servicio->persona->nombres }} {{ $servicio->persona->apellidos }}</p>
            <p><strong>Origen:</strong> {{ $servicio->horario->punto_origen->nombre_comercial }}</p>
            <p><strong>Destino:</strong> {{ $servicio->horario->punto_destino->nombre_comercial }}</p>
            <p><strong>Asiento:</strong> {{ $servicio->asiento_numero }}</p>
        @endif


        {{-- SI ES EQUIPAJE --}}
        @if ($servicio instanceof \App\Models\EquipajeExtra)
            <p><strong>Tipo servicio:</strong> Equipaje Extra</p>
            <p><strong>Peso:</strong> {{ $servicio->peso }} kg</p>
            <p><strong>Costo:</strong> S/ {{ number_format($servicio->costo, 2) }}</p>
        @endif

    @endif


    <div class="center">
        ¡Gracias por su preferencia!
    </div>

    <button onclick="window.print()">Reimprimir</button>

</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $venta->serie }}-{{ $venta->numero }}</title>

    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            width: 260px;
            margin: 0 auto;
            padding: 10px 5px;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            background-color: #fff;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-container img {
            max-width: 110px;
            height: auto;
            display: inline-block;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            height: 0;
        }

        .anulado {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            margin-bottom: 5px;
            padding: 2px;
        }

        .w-100 {
            width: 100%;
            border-collapse: collapse;
        }

        .table-data td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .table-items th {
            border-bottom: 1px dashed #000;
            font-weight: bold;
            font-size: 11px;
            padding-bottom: 2px;
        }

        .table-items td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11px;
            word-break: break-word;
        }

        .btn-print {
            display: block;
            width: 100%;
            background-color: #000;
            color: #fff;
            border: none;
            padding: 6px;
            margin-top: 15px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-weight: bold;
            border-radius: 4px;
            text-align: center;
            font-size: 11px;
        }

        /* ── Ticket extra de sobreequipaje ── */
        .ticket-sobreequipaje {
            page-break-before: always;
            padding-top: 10px;
        }

        .sobreequipaje-header {
            border: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            padding: 4px 2px;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        @media print {
            .btn-print {
                display: none !important;
            }

            body {
                width: 100%;
                padding: 5px;
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

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TICKET PRINCIPAL --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($venta->estado === \App\Enums\EstadoVenta::ANULADO || $venta->fecha_anulacion)
        <div class="anulado">*** ANULADO ***</div>
    @endif

    @php
        $empresa = $venta->sucursal?->empresa;
        $cliente = $venta->persona;

        $pasaje = $venta->pasajes?->first();
        $salida = $pasaje?->salida; // Salida model
        $descuento = $pasaje?->descuento; // Descuento model

        $montoDescuento = 0;
        if ($descuento) {
            $montoDescuento = $descuento->monto ?? ($descuento->valor ?? 0);
        }

        $sobreEquipajeItems = $venta->pasajes?->flatMap(fn($p) => $p->sobreEquipajes ?? collect()) ?? collect();

        $opGravada = 0;
        $opInafecta = 0;
        $opExonerada = 0;
        $igvTotal = 0;

        foreach ($venta->detalles as $detalle) {
            if ($detalle->tipo_afectacion_igv == 10) {
                $opGravada += $detalle->base_igv;
                $igvTotal += $detalle->igv;
            } elseif ($detalle->tipo_afectacion_igv == 30) {
                $opInafecta += $detalle->total;
            } elseif ($detalle->tipo_afectacion_igv == 20) {
                $opExonerada += $detalle->total;
            }
        }

    @endphp

    {{-- ENCABEZADO --}}
    <div class="center">
        @if ($empresa && $empresa->logo)
            <div class="logo-container">
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo">
            </div>
        @endif

        <div class="bold" style="font-size: 12px;">
            {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
        </div>
        <div class="bold">RUC: {{ $empresa->documento ?? '20513247495' }}</div>
        <div style="font-size: 10px;">
            {{ $venta->sucursal->direccion ?? ($empresa->direccion ?? 'Av. El Sol 789') }}
        </div>

        <div class="line"></div>

        <div class="bold" style="text-transform: uppercase;">
            {{ $venta->tipoDocumentoFactura->descripcion ?? 'NOTA DE VENTA' }}
        </div>
        <div class="bold" style="font-size: 12px;">{{ $venta->serie }} - {{ $venta->numero }}</div>

        <div class="line"></div>
    </div>

    {{-- EMISIÓN --}}
    <table class="table-data w-100">
        <tr>
            <td class="bold">F. Emisión:</td>
            <td class="right">
                {{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="bold">Cajero:</td>
            <td class="right">{{ $venta->usuario->persona->nombre_completo ?? 'Sistema' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DEL CLIENTE</div>
    <table class="table-data w-100">
        <tr>
            <td class="bold" style="width: 30%;">Cliente:</td>
            <td class="right">
                {{ $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : 'CLIENTE VARIOS' }}
            </td>
        </tr>
        <tr>
            <td class="bold">Documento:</td>
            <td class="right">{{ $cliente->documento ?? '00000000' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- DATOS DE VIAJE (nuevo bloque solicitado por Gretel) --}}
    @if ($salida)
        <div class="bold" style="font-size: 10px; margin-bottom: 2px;">DATOS DEL VIAJE</div>
        <table class="table-data w-100">
            <tr>
                <td class="bold" style="width: 35%;">Origen:</td>
                <td class="right">
                    {{ $pasaje->origen?->descripcion ?? ($salida->origen ?? '—') }}
                </td>
            </tr>
            <tr>
                <td class="bold">Destino:</td>
                <td class="right">
                    {{ $pasaje->destino?->descripcion ?? ($salida->destino ?? '—') }}
                </td>
            </tr>
            <tr>
                <td class="bold">F. Salida:</td>
                {{-- Ajusta el campo según tu modelo Salida: fecha_salida / fecha / fecha_programada --}}
                <td class="right">
                    {{ optional($salida->fecha_salida ?? ($salida->fecha ?? null))?->format('d/m/Y') ?? '—' }}
                </td>
            </tr>
            <tr>
                <td class="bold">H. Salida:</td>
                {{-- Ajusta el campo: hora_salida / hora / hora_programada --}}
                <td class="right">
                    {{ $salida->hora_salida ?? ($salida->hora ?? '—') }}
                </td>
            </tr>
            <tr>
                <td class="bold">Asiento:</td>
                <td class="right">{{ $pasaje->asiento_numero ?? '—' }}</td>
            </tr>
        </table>
        <div class="line"></div>
    @endif

    {{-- ÍTEMS --}}
    <table class="table-items w-100">
        <thead>
            <tr>
                <th class="left" style="width: 65%;">Descripción</th>
                <th class="center" style="width: 10%;">Cant</th>
                <th class="right" style="width: 25%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $detalle)
                <tr>
                    <td class="left">{{ $detalle->descripcion }}</td>
                    <td class="center">{{ number_format($detalle->cantidad, 0) }}</td>
                    <td class="right">S/ {{ number_format($detalle->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- TOTALES --}}
    <table class="table-data w-100">

        @if ($opGravada > 0)
            <tr>
                <td class="bold">Op. Gravada:</td>
                <td class="right">
                    S/ {{ number_format($opGravada, 2) }}
                </td>
            </tr>
        @endif


        @if ($opInafecta > 0)
            <tr>
                <td class="bold">Op. Inafecta:</td>
                <td class="right">
                    S/ {{ number_format($opInafecta, 2) }}
                </td>
            </tr>
        @endif


        @if ($opExonerada > 0)
            <tr>
                <td class="bold">Op. Exonerada:</td>
                <td class="right">
                    S/ {{ number_format($opExonerada, 2) }}
                </td>
            </tr>
        @endif


        @if ($igvTotal > 0)
            <tr>
                <td class="bold">
                    IGV (18%):
                </td>
                <td class="right">
                    S/ {{ number_format($igvTotal, 2) }}
                </td>
            </tr>
        @endif


        @if ($montoDescuento > 0)
            <tr>
                <td class="bold">Descuentos:</td>
                <td class="right">
                    - S/ {{ number_format($montoDescuento, 2) }}
                </td>
            </tr>
        @endif


        <tr style="font-size:12px;border-top:1px dashed #000;">
            <td class="bold" style="padding-top:3px;">
                TOTAL A PAGAR:
            </td>
            <td class="right bold" style="padding-top:3px;">
                S/ {{ number_format($venta->total, 2) }}
            </td>
        </tr>

    </table>
    
    <div class="line"></div>

    {{-- PIE DE PÁGINA --}}
    @if ($venta->observacion)
        <div style="font-size: 10px; font-style: italic; margin-bottom: 5px; word-break: break-word;">
            <strong>Obs:</strong> {{ $venta->observacion }}
        </div>
        <div class="line"></div>
    @endif

    <div class="center" style="font-size: 10px;">
        <div>¡Gracias por su compra!</div>
        <div>Representación impresa de la</div>
        <div class="bold">
            {{ $venta->tipoDocumentoFactura->descripcion ?? 'Nota de venta' }} Electrónica.
        </div>
    </div>

    <button class="btn-print" onclick="window.print()">Imprimir Ticket</button>


    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TICKETS ADICIONALES DE SOBREEQUIPAJE (uno por cada item) --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

    @foreach ($sobreEquipajeItems as $seItem)
        @php
            $enc = $seItem->encomienda;
            $pasajeSE = $seItem->pasaje;
            $personaSE = $pasajeSE?->persona;
            $numEtiq = str_pad($loop->iteration, 3, '0', STR_PAD_LEFT); // 001, 002 …
        @endphp

        <div class="ticket-sobreequipaje">

            {{-- Encabezado empresa --}}
            <div class="center" style="margin-bottom: 4px;">
                @if ($empresa && $empresa->logo)
                    <div class="logo-container">
                        <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo">
                    </div>
                @endif
                <div class="bold" style="font-size: 11px;">
                    {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
                </div>
            </div>

            <div class="line"></div>

            {{-- Etiqueta principal --}}
            <div class="sobreequipaje-header">
                SOBREEQUIPAJE {{ $numEtiq }}
            </div>

            {{-- Datos del pasajero propietario --}}
            <table class="table-data w-100">
                <tr>
                    <td class="bold">Cliente:</td>
                    <td class="right">
                        {{ $personaSE
                            ? $personaSE->nombres . ' ' . $personaSE->apellidos
                            : ($enc?->emisor
                                ? $enc->emisor->nombres . ' ' . $enc->emisor->apellidos
                                : 'CLIENTE VARIOS') }}
                    </td>
                </tr>
                <tr>
                    <td class="bold">DNI:</td>
                    <td class="right">
                        {{ $personaSE->documento ?? ($enc?->emisor?->documento ?? '—') }}
                    </td>
                </tr>
                <tr>
                    <td class="bold">Asiento:</td>
                    <td class="right">{{ $pasajeSE?->asiento_numero ?? '—' }}</td>
                </tr>
            </table>

            <div class="line"></div>

            {{-- Datos del envío / encomienda --}}
            @if ($enc)
                <table class="table-data w-100">
                    <tr>
                        <td class="bold">Origen:</td>
                        <td class="right">
                            {{ $enc->origenPueblito?->descripcion ?? '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bold">Destino:</td>
                        <td class="right">
                            {{ $enc->destinoPueblito?->descripcion ?? '—' }}
                        </td>
                    </tr>
                    @if ($enc->detalles->isNotEmpty())
                        @foreach ($enc->detalles as $det)
                            <tr>
                                <td class="bold">Bulto:</td>
                                <td class="right">
                                    {{ $det->descripcion }}
                                    @if ($det->peso)
                                        ({{ $det->peso }} kg)
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td class="bold">Costo:</td>
                        <td class="right bold">S/ {{ number_format($enc->total ?? 0, 2) }}</td>
                    </tr>
                </table>
                <div class="line"></div>
            @endif

            <div class="center" style="font-size: 10px;">
                <div>Boleta: <strong>{{ $venta->serie }}-{{ $venta->numero }}</strong></div>
                <div>Conserve esta etiqueta hasta llegar a destino.</div>
            </div>

        </div>

    @endforeach

</body>

</html>

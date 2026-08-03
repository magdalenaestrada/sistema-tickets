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

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .right { text-align: right; }
        .left { text-align: left; }

        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-container img {
            max-width: 110px;
            max-height: 60px;
            height: auto;
            display: inline-block;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            height: 0;
        }

        .anulado {
            border: 2px solid #000;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
            padding: 4px;
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

        /* ── Bloques Destacados ── */
        .highlight-box {
            border: 2px solid #000;
            padding: 5px;
            margin: 6px 0;
            text-align: center;
            background-color: #fcfcfc;
        }

        .asiento-box {
            font-size: 20px;
            font-weight: bold;
            margin: 2px 0;
        }

        .ruta-box {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .fecha-hora-box {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* ── Saltos de página ── */
        .ticket-pasajero {
            page-break-before: always;
        }

        .ticket-sobreequipaje {
            page-break-before: always;
            padding-top: 10px;
        }

        .sobreequipaje-header {
            border: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 4px 2px;
            margin-bottom: 6px;
            letter-spacing: 1px;
            background-color: #f0f0f0;
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
    @php
        $empresa = $venta->sucursal?->empresa;
        $cliente = $venta->persona;
    @endphp

    @foreach ($venta->pasajes as $pasaje)
        {{-- ═══════════════════════════════════════════════ --}}
        {{-- BOLETA DE PASAJE --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div class="{{ $loop->first ? '' : 'ticket-pasajero' }}">

            @if ($venta->estado === \App\Enums\EstadoVenta::ANULADO || $venta->fecha_anulacion)
                <div class="anulado">*** ANULADO ***</div>
            @endif

            @php
                $salida = $pasaje?->salida;
                $descuento = $pasaje?->descuento;

                $montoDescuento = 0;
                if ($descuento) {
                    $montoDescuento = $descuento->monto ?? ($descuento->valor ?? 0);
                }

                $detallesPasaje = $venta->detalles->where('pasaje_id', $pasaje->id);

                if ($detallesPasaje->isEmpty()) {
                    $detallesPasaje = $venta->detalles->whereNull('pasaje_id');
                }

                $sobreEquipajeItems = $pasaje->sobreEquipajes ?? collect();

                $opGravada = 0;
                $opInafecta = 0;
                $opExonerada = 0;
                $igvTotal = 0;

                foreach ($detallesPasaje as $detalle) {
                    if ($detalle->tipo_afectacion_igv == 10) {
                        $opGravada += $detalle->base_igv;
                        $igvTotal += $detalle->igv;
                    } elseif ($detalle->tipo_afectacion_igv == 30) {
                        $opInafecta += $detalle->total;
                    } elseif ($detalle->tipo_afectacion_igv == 20) {
                        $opExonerada += $detalle->total;
                    }
                }

                $totalPasaje = $detallesPasaje->sum('total');
                $origenText = $pasaje->origen?->descripcion ?? ($salida->origen ?? '—');
                $destinoText = $pasaje->destino?->descripcion ?? ($salida->destino ?? '—');
            @endphp

            {{-- 1. ENCABEZADO + DATOS EMISIÓN Y CLIENTE --}}
            <div class="center">
                @if ($empresa && $empresa->logo)
                    <div class="logo-container">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo) ? asset('storage/' . $empresa->logo) : asset($empresa->logo) }}" alt="Logo">
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
                    {{ $venta->tipoDocumentoFactura->descripcion ?? 'BOLETO DE VIAJE' }}
                </div>
                <div class="bold" style="font-size: 13px;">{{ $venta->serie }} - {{ $venta->numero }}</div>
            </div>

            <div class="line"></div>

            {{-- CLIENTE Y EMISIÓN --}}
            <table class="table-data w-100">
                <tr>
                    <td class="bold">Cliente:</td>
                    <td class="right bold">
                        {{ $cliente ? ($cliente->nombre_completo ?? $cliente->nombres . ' ' . $cliente->apellidos) : 'CLIENTE VARIOS' }}
                    </td>
                </tr>
                @if($cliente && $cliente->documento)
                <tr>
                    <td class="bold">Doc. Cliente:</td>
                    <td class="right">{{ $cliente->documento }}</td>
                </tr>
                @endif
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

            {{-- 2. BLOQUE DESTACADO DE ASIENTO Y RUTA --}}
            <div class="highlight-box">
                <div style="font-size: 10px; font-weight: bold; letter-spacing: 1px;">DATOS DE EMBARQUE</div>
                <div class="asiento-box">ASIENTO N° {{ $pasaje->asiento_numero ?? '—' }}</div>
                <div class="line" style="margin: 4px 0;"></div>
                <div class="ruta-box">{{ $origenText }} - {{ $destinoText }}</div>
                @if ($salida)
                    <div class="fecha-hora-box">
                        {{ optional($salida->fecha_salida ?? ($salida->fecha ?? null))?->format('d/m/Y') }} —
                        {{ $salida->hora_salida ?? ($salida->hora ?? '—') }}
                    </div>
                @endif
            </div>

            {{-- 3. DATOS VISIBLES DEL PASAJERO --}}
            <div class="bold" style="font-size: 10px; margin-bottom: 2px; margin-top: 4px;">DATOS DEL PASAJERO(A)</div>
            <table class="table-data w-100" style="margin-bottom: 4px;">
                <tr>
                    <td class="bold" style="width: 30%;">Nombre:</td>
                    <td class="right bold" style="font-size: 11px; text-transform: uppercase;">
                        {{ $pasaje->persona->nombre_completo ?? ($pasaje->persona->nombres . ' ' . $pasaje->persona->apellidos) }}
                    </td>
                </tr>
                <tr>
                    <td class="bold">N° Doc:</td>
                    <td class="right bold" style="font-size: 11px;">
                        {{ $pasaje->persona->documento ?? '—' }}
                    </td>
                </tr>
            </table>

            <div class="line"></div>

            {{-- 4. ÍTEMS / DETALLE DE LA BOLETA --}}
            <div class="bold" style="font-size: 10px; margin-bottom: 3px;">DETALLE DEL COMPROBANTE</div>
            <table class="table-items w-100">
                <thead>
                    <tr>
                        <th class="left" style="width: 65%;">Descripción</th>
                        <th class="center" style="width: 10%;">Cant</th>
                        <th class="right" style="width: 25%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detallesPasaje as $detalle)
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
                        <td class="right">S/ {{ number_format($opGravada, 2) }}</td>
                    </tr>
                @endif

                @if ($opInafecta > 0)
                    <tr>
                        <td class="bold">Op. Inafecta:</td>
                        <td class="right">S/ {{ number_format($opInafecta, 2) }}</td>
                    </tr>
                @endif

                @if ($opExonerada > 0)
                    <tr>
                        <td class="bold">Op. Exonerada:</td>
                        <td class="right">S/ {{ number_format($opExonerada, 2) }}</td>
                    </tr>
                @endif

                @if ($igvTotal > 0)
                    <tr>
                        <td class="bold">IGV (18%):</td>
                        <td class="right">S/ {{ number_format($igvTotal, 2) }}</td>
                    </tr>
                @endif

                @if ($montoDescuento > 0)
                    <tr>
                        <td class="bold">Descuentos:</td>
                        <td class="right">- S/ {{ number_format($montoDescuento, 2) }}</td>
                    </tr>
                @endif

                <tr style="font-size: 13px; border-top: 1px dashed #000;">
                    <td class="bold" style="padding-top: 3px;">TOTAL PAGADO:</td>
                    <td class="right bold" style="padding-top: 3px;">
                        S/ {{ number_format($totalPasaje, 2) }}
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
                <div class="bold">Presentarse 30 min antes del embarque</div>
                <div>¡Gracias por su preferencia!</div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- TICKET DE SOBREEQUIPAJE --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        @foreach ($sobreEquipajeItems as $seItem)
            @php
                $enc = $seItem->encomienda;
                $numEtiq = str_pad($loop->iteration, 3, '0', STR_PAD_LEFT);
            @endphp

            <div class="ticket-sobreequipaje">

                <div class="center" style="margin-bottom: 4px;">
                    @if ($empresa && $empresa->logo)
                        <div class="logo-container">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo) ? asset('storage/' . $empresa->logo) : asset($empresa->logo) }}" alt="Logo">
                        </div>
                    @endif
                    <div class="bold" style="font-size: 11px;">
                        {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
                    </div>
                </div>

                <div class="line"></div>

                <div class="sobreequipaje-header">
                    SOBREEQUIPAJE {{ $numEtiq }}
                </div>

                <div class="highlight-box">
                    <div style="font-size: 10px; font-weight: bold; letter-spacing: 1px;">ASIENTO ASIGNADO</div>
                    <div class="asiento-box">N° {{ $pasaje->asiento_numero ?? '—' }}</div>
                    <div class="line" style="margin: 3px 0;"></div>
                    <div class="ruta-box">{{ $origenText }} ➔ {{ $destinoText }}</div>
                </div>

                <div class="bold" style="font-size: 10px; margin-top: 6px; margin-bottom: 2px;">DATOS DEL PASAJERO</div>
                <table class="table-data w-100">
                    <tr>
                        <td class="bold" style="width: 35%;">Pasajero:</td>
                        <td class="right bold" style="font-size: 11px; text-transform: uppercase;">
                            {{ $pasaje->persona->nombre_completo ?? ($pasaje->persona->nombres . ' ' . $pasaje->persona->apellidos) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bold">DNI/Doc:</td>
                        <td class="right bold">
                            {{ $pasaje->persona->documento ?? '—' }}
                        </td>
                    </tr>
                    @if ($salida)
                        <tr>
                            <td class="bold">F. Salida:</td>
                            <td class="right">
                                {{ optional($salida->fecha_salida ?? ($salida->fecha ?? null))?->format('d/m/Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="bold">H. Salida:</td>
                            <td class="right">
                                {{ $salida->hora_salida ?? ($salida->hora ?? '—') }}
                            </td>
                        </tr>
                    @endif
                </table>

                <div class="line"></div>

                <div class="bold" style="font-size: 10px; margin-bottom: 2px;">DETALLE DEL BULTO</div>
                <table class="table-data w-100">
                    @if ($enc && $enc->detalles->isNotEmpty())
                        @foreach ($enc->detalles as $det)
                            <tr>
                                <td class="bold" style="width: 35%;">Descripción:</td>
                                <td class="right bold">
                                    {{ $det->descripcion }}
                                </td>
                            </tr>
                            @if ($det->peso)
                                <tr>
                                    <td class="bold">Peso:</td>
                                    <td class="right">{{ $det->peso }} kg</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td class="bold" style="width: 35%;">Bulto:</td>
                            <td class="right bold">Equipaje extra</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="bold">Monto Pagado:</td>
                        <td class="right bold" style="font-size: 12px;">
                            S/ {{ number_format($enc->total ?? 0, 2) }}
                        </td>
                    </tr>
                </table>

                <div class="line"></div>

                <div class="center" style="font-size: 10px; margin-top: 4px;">
                    <div>Comprobante: <strong>{{ $venta->serie }}-{{ $venta->numero }}</strong></div>
                    <div style="margin-top: 2px;">Pegar o conservar este ticket junto al equipaje.</div>
                </div>

            </div>
        @endforeach
    @endforeach

</body>

</html>
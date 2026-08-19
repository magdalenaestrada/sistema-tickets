@foreach ($venta->pasajes as $pasaje)
    <div class="{{ $loop->first && $esPrimerBloque ? '' : 'ticket-pasajero' }}">

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
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo) ? asset('storage/' . $empresa->logo) : asset($empresa->logo) }}"
                        alt="Logo">
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
                    {{ $cliente ? $cliente->nombre_completo ?? $cliente->nombres . ' ' . $cliente->apellidos : 'CLIENTE VARIOS' }}
                </td>
            </tr>
            @if ($cliente && $cliente->documento)
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
                    {{ $pasaje->persona->nombre_completo ?? $pasaje->persona->nombres . ' ' . $pasaje->persona->apellidos }}
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

    {{-- Sobreequipaje sigue igual, también usando 'ticket-pasajero' como clase de salto --}}
    @foreach ($pasaje->sobreEquipajes ?? collect() as $seItem)
        @php
            $enc = $seItem->encomienda;
        @endphp
        <div class="ticket-pasajero">

            <div class="center" style="margin-bottom: 4px;">
                @if ($empresa && $empresa->logo)
                    <div class="logo-container">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo) ? asset('storage/' . $empresa->logo) : asset($empresa->logo) }}"
                            alt="Logo">
                    </div>
                @endif
                <div class="bold" style="font-size: 11px;">
                    {{ $empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
                </div>
            </div>

            <div class="line"></div>

            <div class="sobreequipaje-header">
                SOBREEQUIPAJE {{ $enc->codigo ?? '' }}
            </div>

            <div class="highlight-box">
                <div style="font-size: 10px; font-weight: bold; letter-spacing: 1px;">ASIENTO ASIGNADO</div>
                <div class="asiento-box">N° {{ $pasaje->asiento_numero ?? '—' }}</div>
                <div class="line" style="margin: 3px 0;"></div>
                <div class="ruta-box">{{ $origenText }} ➔ {{ $destinoText }}</div>
            </div>

            <div class="bold" style="font-size: 10px; margin-top: 6px; margin-bottom: 2px;">DATOS DEL PASAJERO
            </div>
            <table class="table-data w-100">
                <tr>
                    <td class="bold" style="width: 35%;">Pasajero:</td>
                    <td class="right bold" style="font-size: 11px; text-transform: uppercase;">
                        {{ $pasaje->persona->nombre_completo ?? $pasaje->persona->nombres . ' ' . $pasaje->persona->apellidos }}
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
                        <td class="right bold">Sobrequipaje</td>
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

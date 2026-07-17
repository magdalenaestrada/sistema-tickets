<table class="header-wrap" width="100%">
    <tr>
        <!-- EMPRESA -->
        <td width="42%" style="vertical-align: top; padding-right: 8px;">
            <table class="box" width="100%">
                <tr>
                    <td style="padding: 6px 8px;">
                        <div class="empresa-nombre">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
                        <div class="empresa-texto">{{ $empresa->direccion ?? '' }}</div>
                        <div class="empresa-ruc">RUC: {{ $empresa->documento ?? '' }}</div>
                    </td>
                </tr>
            </table>
        </td>

        <!-- FECHA / HORA -->
        <td width="18%" style="vertical-align: top; padding-right: 8px;">
            <table class="fecha-box" width="100%">
                <tr>
                    <td class="fecha-head">DÍA</td>
                    <td class="fecha-head">MES</td>
                    <td class="fecha-head">AÑO</td>
                </tr>
                <tr>
                    <td class="fecha-val">{{ $salida->fecha_salida?->format('d') }}</td>
                    <td class="fecha-val">{{ $salida->fecha_salida?->format('m') }}</td>
                    <td class="fecha-val">{{ $salida->fecha_salida?->format('Y') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="fecha-head">HORA</td>
                </tr>
                <tr>
                    <td colspan="3" class="fecha-val">{{ $salida->horario?->hora_formateada }}</td>
                </tr>
            </table>
        </td>

        <td width="40%" style="vertical-align: top;">
            <table class="titulo-box" width="100%">
                <tr>
                    <td class="titulo-ruc">
                        RUC: {{ $empresa->documento ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td class="titulo-main">
                        MANIFIESTO DE PASAJEROS
                    </td>
                </tr>
                <tr>
                    <td class="titulo-sub">
                        SALIDA | {{ $origenNombre }} - {{ $destinoNombre }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="section-title mt-2" align="center">Información general del viaje</div>
<table class="info-table">
    <tr>
        <td width="50%"><span class="label">Origen:</span> {{ $origenNombre }}</td>
        <td colspan="2"><span class="label">Destino:</span> {{ $destinoNombre }}</td>
    </tr>
    <tr>
        <td width="34%"><span class="label">Marca de vehículo:</span> {{ $salida->vehiculo->marca ?? '-' }}
        </td>
        <td width="33%"><span class="label">Placa:</span> {{ $salida->vehiculo->numero_placa ?? '-' }}</td>
        <td width="33%"><span class="label">Hab. vehicular:</span>
            {{ $salida->vehiculo->habilitacion_vehicular ?? '-' }}</td>
    </tr>
    <tr>
        <td><span class="label">Conductor 1:</span>
            {{ $salida->conductorPrincipal?->persona->nombres }}
            {{ $salida->conductorPrincipal?->persona->apellidos }}
        </td>
        <td colspan="2"><span class="label">Licencia:</span>
            {{ $salida->conductorPrincipal?->licencia_conducir ?? '-' }}
        </td>
    </tr>
    <tr>
        <td><span class="label">Conductor 2:</span>
            {{ $salida->conductorSecundario?->persona->nombres }}
            {{ $salida->conductorSecundario?->persona->apellidos }}
        </td>
        <td colspan="2"><span class="label">Licencia:</span>
            {{ $salida->conductorSecundario?->licencia_conducir ?? '-' }}
        </td>
    </tr>
    <tr>
        <td><span class="label">Cantidad máx. asientos:</span> {{ $capacidad }}</td>
        <td colspan="2"><span class="label">Pasajeros embarcados:</span> {{ $pasajes->count() }}</td>
    </tr>
</table>

<div class="section-title mt-2" align="center">Detalle de pasajeros</div>
<table class="main-table">
    <thead>
        <tr>
            <th class="col-item">ITEM</th>
            <th class="col-seat">N° ASIENTO</th>
            <th class="col-name">NOMBRES Y APELLIDOS</th>
            <th class="col-doc-type">TIPO DOC.</th>
            <th class="col-doc">N° DOC</th>
            <th class="col-dest">ORIGEN</th>
            <th class="col-dest">DESTINO</th>
            <th class="col-ticket">N° BOLETO</th>
            <th class="col-amount">IMPORTE S/</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pasajes as $i => $pasaje)
            <tr>
                <td class="col-item">{{ $i + 1 }}</td>
                <td class="col-seat">{{ $pasaje->asiento_numero }}</td>
                <td class="col-name">
                    {{ $pasaje->persona?->apellidos }} {{ $pasaje->persona?->nombres }}
                </td>
                <td class="col-doc-type">{{ $pasaje->persona?->tipoDocumento?->codigo ?? 'DNI' }}</td>
                <td class="col-doc">{{ $pasaje->persona?->documento }}</td>
                {{-- 👇 antes decía $origenNombre / $destinoNombre fijos para TODOS. Ahora usa el destino real del pasajero --}}
                <td class="col-origen">{{ $pasaje->origen?->descripcion ?? $origenNombre }}</td>
                <td class="col-dest">{{ $pasaje->destino?->descripcion ?? $destinoNombre }}</td>
                <td class="col-ticket">{{ $pasaje->venta?->serie }} - {{ $pasaje->venta?->numero }}</td>
                <td class="col-amount">{{ number_format((float) $pasaje->precio_cobrado, 2) }}</td>
            </tr>
        @endforeach

        @for ($j = $pasajes->count(); $j < $capacidad; $j++)
            <tr>
                <td class="col-item">{{ $j + 1 }}</td>
                <td class="col-seat">&nbsp;</td>
                <td class="col-name"></td>
                <td class="col-doc-type"></td>
                <td class="col-doc"></td>
                <td class="col-origen"></td>
                <td class="col-dest"></td>
                <td class="col-ticket"></td>
                <td class="col-amount"></td>
            </tr>
        @endfor
    </tbody>
</table>
<table class="firma-table">
    <tr>
        <td>
            <table width="80%" align="center">
                <tr>
                    <td style="border-top: 1px solid #000; height: 15px;"></td>
                </tr>
            </table>
            <div class="firma-label">CHOFER</div>
        </td>
        <td>
            <table width="80%" align="center">
                <tr>
                    <td style="border-top: 1px solid #000; height: 15px;"></td>
                </tr>
            </table>
            <div class="firma-label">COPILOTO</div>
        </td>
    </tr>
</table>

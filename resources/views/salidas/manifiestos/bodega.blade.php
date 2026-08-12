<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manifiesto de Bodega</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 15px;
            line-height: 1.2;
        }

        /* Encabezado */
        .title-box {
            border: 2px solid #1a365d;
            background-color: #f8fafc;
            text-align: center;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .title-box h1 {
            margin: 0;
            font-size: 15px;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-box p {
            margin: 3px 0 0 0;
            font-size: 11px;
            font-weight: bold;
            color: #4a5568;
        }

        /* Estilos de Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .section-header {
            background-color: #1a365d;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 6px;
            text-transform: uppercase;
        }

        /* Clases Útiles */
        .bg-light { background-color: #f8fafc; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-transbordo {
            font-size: 8px;
            color: #c53030;
            font-weight: bold;
            display: block;
        }

        /* Sección de Totales y Firmas */
        .totals-table td {
            background-color: #f1f5f9;
            font-size: 10px;
        }

        .signatures {
            margin-top: 40px;
            border: none;
        }

        .signatures td {
            border: none;
            text-align: center;
            vertical-align: bottom;
            padding-top: 30px;
        }

        .line {
            border-top: 1px solid #000;
            width: 75%;
            margin: 0 auto 4px auto;
        }
    </style>
</head>

<body>

    <!-- Encabezado Principal -->
    <div class="title-box">
        <h1>Manifiesto de Bodega</h1>
        <p>SALIDA: {{ $origenNombre }} — {{ $destinoNombre }}</p>
    </div>

    <!-- Cabecera de Datos del Viaje -->
    <table>
        <tr>
            <td colspan="6" class="section-header">Información del Viaje y Unidad</td>
        </tr>
        <tr>
            <td width="12%" class="bg-light font-bold">Ruta:</td>
            <td width="21%">{{ $origenNombre }} - {{ $destinoNombre }}</td>
            <td width="12%" class="bg-light font-bold">Fecha:</td>
            <td width="21%">{{ $salida->fecha_salida?->format('d/m/Y') }}</td>
            <td width="12%" class="bg-light font-bold">Hora:</td>
            <td width="22%">{{ $salida->horario?->hora_formateada ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-light font-bold">Vehículo:</td>
            <td>
                {{ strtoupper($salida->vehiculo?->tipo_vehiculo?->descripcion ?? '-') }} 
                ({{ $salida->vehiculo?->numero_placa ?? '-' }})
            </td>
            <td class="bg-light font-bold">Conductor 1:</td>
            <td>
                {{ $salida->conductorPrincipal?->persona->nombres }} 
                {{ $salida->conductorPrincipal?->persona->apellidos }}
            </td>
            <td class="bg-light font-bold">Conductor 2:</td>
            <td>
                {{ $salida->conductorSecundario?->persona->nombres }} 
                {{ $salida->conductorSecundario?->persona->apellidos }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="10" class="section-header">Detalle de Bodegas Registradas</th>
            </tr>
            <tr>
                <th width="4%" class="text-center">ITEM</th>
                <th width="9%" class="text-center">DNI REM.</th>
                <th width="16%">REMITENTE</th>
                <th width="9%" class="text-center">DNI DEST.</th>
                <th width="16%">DESTINATARIO</th>
                <th width="9%">ORIGEN</th>
                <th width="9%">DESTINO</th>
                <th width="18%">DESCRIPCIÓN</th>
                <th width="5%" class="text-center">PESO</th>
                <th width="7%" class="text-right">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($encomiendas as $encomienda)
                @foreach ($encomienda->detalles as $detalle)
                    <tr>
                        <td class="text-center font-bold">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $encomienda->emisor?->documento ?? '-' }}</td>
                        <td>{{ $encomienda->emisor?->nombre_completo ?? '-' }}</td>
                        <td class="text-center">{{ $encomienda->receptor?->documento ?? '-' }}</td>
                        <td>{{ $encomienda->receptor?->nombre_completo ?? '-' }}</td>
                        <td>{{ $encomienda->origenPueblito?->descripcion ?? '-' }}</td>
                        <td>{{ $encomienda->destinoPueblito?->descripcion ?? '-' }}</td>
                        <td>
                            {{ $detalle->descripcion ?? '-' }}
                            @if ($encomienda->transbordo)
                                <span class="badge-transbordo">● TRANSBORDO EN INCUYO</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $detalle->peso ? $detalle->peso . ' kg' : '-' }}</td>
                        <td class="text-right font-bold">S/ {{ number_format((float) ($detalle->costo ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #666;">
                        No hay encomiedas ni sobrequipaje registradas para esta salida.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totales -->
    @if(count($encomiendas) > 0)
    <table class="totals-table">
        <tr>
            <td width="70%" class="text-right font-bold">TOTAL GENERAL DE ENCOMIENDAS:</td>
            <td width="30%" class="text-center font-bold">
                S/ {{ number_format($encomiendas->flatMap->detalles->sum('costo'), 2) }}
            </td>
        </tr>
    </table>
    @endif

    <!-- Firmas de Conformidad -->
    <table class="signatures">
        <tr>
            <td width="33%">
                <div class="line"></div>
                <strong>Despachador</strong><br>
                Agencia Origen
            </td>
            <td width="33%">
                <div class="line"></div>
                <strong>Conductor Responsable</strong><br>
                Firma de Conformidad
            </td>
            <td width="33%">
                <div class="line"></div>
                <strong>Agencia Destino</strong><br>
                Conformidad de Recepción
            </td>
        </tr>
    </table>

</body>

</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manifiesto de Encomiendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
        }

        th {
            background: #f2f2f2;
        }

        .no-border td {
            border: none;
        }

        .title-box {
            border: 2px solid #000;
            text-align: center;
            padding: 8px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .mt-2 {
            margin-top: 12px;
        }
    </style>
</head>

<body>

    <div class="title-box">MANIFIESTO DE ENCOMIENDAS | SALIDA {{ $origenNombre }} - {{ $destinoNombre }}</div>

    <table class="mt-2">
        <tr>
            <td><strong>Ruta: </strong>{{ $origenNombre }} - {{ $destinoNombre }}</td>
            <td><strong>Fecha:</strong> {{ $salida->fecha_salida?->format('Y-m-d') }}</td>
            <td><strong>Hora:</strong> {{ $salida->horario?->hora_formateada }}</td>
        </tr>
        <tr>
            <td><strong>Vehículo:</strong> {{ $salida->vehiculo->tipo_vehiculo->descripcion ?? '' }} -
                {{ $salida->vehiculo->numero_placa ?? '' }} </td>
            <td><strong>Conductor 1:</strong> {{ $salida->conductorPrincipal?->nombres }}
                {{ $salida->conductorPrincipal?->apellidos }}</td>
            <td><strong>Conductor 2:</strong> {{ $salida->conductorSecundario?->nombres }}
                {{ $salida->conductorSecundario?->apellidos }}</td>
        </tr>
    </table>

    <table class="mt-2">
        <thead>
            <tr>
                <th>ITEM</th>
                <th>REMITENTE</th>
                <th>DESTINATARIO</th>
                <th>ORIGEN</th>
                <th>DESTINO</th>
                <th>DESCRIPCIÓN</th>
                <th>PESO</th>
                <th>IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($encomiendas as $encomienda)
                @foreach ($encomienda->detalles as $detalle)
                    <tr>
                        <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                        <td>{{ $encomienda->emisor?->nombre_completo ?? '-' }}</td>
                        <td>{{ $encomienda->receptor?->nombre_completo ?? '-' }}</td>
                        <td>{{ $encomienda->origenPueblito->descripcion ?? '-' }}</td>
                        <td>{{ $encomienda->destinoPueblito->descripcion ?? '-' }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td>{{ $detalle->peso }}</td>
                        <td>{{ number_format((float) ($detalle->costo ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay encomiendas registradas para esta salida.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>

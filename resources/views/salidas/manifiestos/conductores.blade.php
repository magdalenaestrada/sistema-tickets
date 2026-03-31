<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manifiesto de Conductores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
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

    <div class="title-box">MANIFIESTO DE CONDUCTORES - SALIDA #{{ $salida->id }}</div>

    <table class="mt-2">
        <tr>
            <td><strong>Ruta:</strong> {{ $salida->horario?->ruta?->nombre }}</td>
            <td><strong>Fecha:</strong> {{ $salida->fecha_salida?->format('Y-m-d') }}</td>
            <td><strong>Hora:</strong> {{ $salida->horario?->hora_formateada }}</td>
        </tr>
        <tr>
            <td><strong>Vehículo:</strong> {{ $salida->vehiculo->marca ?? '-' }} - {{ $salida->vehiculo->placa ?? '-' }}
            </td>
            <td><strong>Origen:</strong> {{ $origenNombre }}</td>
            <td><strong>Destino:</strong> {{ $destinoNombre }}</td>
        </tr>
    </table>

    <table class="mt-2">
        <thead>
            <tr>
                <th>TIPO</th>
                <th>NOMBRES Y APELLIDOS</th>
                <th>DOCUMENTO</th>
                <th>LICENCIA</th>
                <th>CELULAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CHOFER</td>
                <td>{{ $salida->conductorPrincipal?->nombres }} {{ $salida->conductorPrincipal?->apellidos }}</td>
                <td>{{ $salida->conductorPrincipal?->documento }}</td>
                <td>{{ $salida->conductorPrincipal?->empleado->licencia_conducir ?? '-' }}</td>
                <td>{{ $salida->conductorPrincipal?->celular ?? '-' }}</td>
            </tr>
            <tr>
                <td>COPILOTO</td>
                <td>{{ $salida->conductorSecundario?->nombres }} {{ $salida->conductorSecundario?->apellidos }}</td>
                <td>{{ $salida->conductorSecundario?->documento }}</td>
                <td>{{ $salida->conductorSecundario?->empleado?->licencia_conducir ?? '-' }}</td>
                <td>{{ $salida->conductorSecundario?->celular ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>

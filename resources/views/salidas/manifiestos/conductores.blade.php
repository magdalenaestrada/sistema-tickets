<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manifiesto de Conductores</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 15px;
            line-height: 1.3;
        }

        /* Encabezado */
        .title-box {
            border: 2px solid #1a365d;
            background-color: #f8fafc;
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .title-box h1 {
            margin: 0;
            font-size: 16px;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-box p {
            margin: 4px 0 0 0;
            font-size: 12px;
            font-weight: bold;
            color: #4a5568;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-header {
            background-color: #1a365d;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 8px;
            text-transform: uppercase;
        }

        /* Clases auxiliares */
        .bg-light {
            background-color: #f8fafc;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        /* Sección de Firmas */
        .signatures {
            margin-top: 50px;
            border: none;
        }

        .signatures td {
            border: none;
            text-align: center;
            vertical-align: bottom;
            padding-top: 35px;
        }

        .line {
            border-top: 1px solid #000;
            width: 75%;
            margin: 0 auto 5px auto;
        }
    </style>
</head>

<body>

    <!-- Título Principal -->
    <div class="title-box">
        <h1>Manifiesto de Conductores</h1>
        <p>{{ $origenNombre }} — {{ $destinoNombre }}</p>
    </div>

    <!-- Información de la Salida / Vehículo -->
    <table>
        <tr>
            <td colspan="4" class="section-header">1. Información del Viaje y Vehículo</td>
        </tr>
        <tr>
            <td width="18%" class="bg-light font-bold">Ruta:</td>
            <td width="32%">{{ $origenNombre }} — {{ $destinoNombre }}</td>
            <td width="18%" class="bg-light font-bold">Fecha de Salida:</td>
            <td width="32%">{{ $salida->fecha_salida?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="bg-light font-bold">Origen:</td>
            <td>{{ $origenNombre }}</td>
            <td class="bg-light font-bold">Hora de Salida:</td>
            <td>{{ $salida->horario?->hora_formateada ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-light font-bold">Destino:</td>
            <td>{{ $destinoNombre }}</td>
            <td class="bg-light font-bold">Vehículo / Placa:</td>
            <td>
                {{ strtoupper($salida->vehiculo?->tipo_vehiculo?->descripcion ?? '-') }} 
                — 
                <span class="font-bold">{{ $salida->vehiculo?->numero_placa ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <!-- Detalle de la Tripulación (Conductores) -->
    <table>
        <thead>
            <tr>
                <th colspan="5" class="section-header">2. Personal a Cargo (Tripulación)</th>
            </tr>
            <tr>
                <th width="12%">ROL</th>
                <th width="38%">NOMBRES Y APELLIDOS</th>
                <th width="15%">DOCUMENTO</th>
                <th width="18%">N° LICENCIA</th>
                <th width="17%">TELÉFONO</th>
            </tr>
        </thead>
        <tbody>
            <!-- Chofer Principal -->
            <tr>
                <td class="font-bold">CHOFER</td>
                <td>
                    {{ $salida->conductorPrincipal?->persona->nombres }} 
                    {{ $salida->conductorPrincipal?->persona->apellidos }}
                </td>
                <td>{{ $salida->conductorPrincipal?->persona->documento ?? '-' }}</td>
                <td>{{ $salida->conductorPrincipal?->licencia_conducir ?? '-' }}</td>
                <td>{{ $salida->conductorPrincipal?->persona->celular ?? '-' }}</td>
            </tr>

            <!-- Copiloto / Conductor Secundario -->
            @if($salida->conductorSecundario)
            <tr>
                <td class="font-bold">COPILOTO</td>
                <td>
                    {{ $salida->conductorSecundario?->persona->nombres }} 
                    {{ $salida->conductorSecundario?->persona->apellidos }}
                </td>
                <td>{{ $salida->conductorSecundario?->persona->documento ?? '-' }}</td>
                <td>{{ $salida->conductorSecundario?->licencia_conducir ?? '-' }}</td>
                <td>{{ $salida->conductorSecundario?->persona->celular ?? '-' }}</td>
            </tr>
            @else
            <tr>
                <td class="font-bold">COPILOTO</td>
                <td colspan="4" style="color: #718096; font-style: italic;">
                    No asignado para este servicio
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Sección de Firmas de Conformidad -->
    <table class="signatures">
        <tr>
            <td width="50%">
                <div class="line"></div>
                <strong>{{ $salida->conductorPrincipal?->persona->nombres }} {{ $salida->conductorPrincipal?->persona->apellidos }}</strong><br>
                Firma Chofer Principal
            </td>
            <td width="50%">
                <div class="line"></div>
                <strong>Despacho / Inspector de Turno</strong><br>
                Firma y Sello Agencia Origen
            </td>
        </tr>
    </table>

</body>

</html>
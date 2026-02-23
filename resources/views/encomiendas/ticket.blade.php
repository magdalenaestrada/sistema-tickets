<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ticket Encomienda #{{ $encomienda->id }}</title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 300px; /* Más grande que 58mm pero no gigante */
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

        table {
            width: 100%;
            font-size: 12px;
        }

        table th, table td {
            text-align: left;
        }

        table th {
            font-weight: bold;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
        }

        table td {
            padding: 2px 0;
        }

        .right {
            text-align: right;
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

    {{-- ===========================
         ENCABEZADO DE EMPRESA
    ============================ --}}
    @php
        $empresa = $encomienda->usuario->sucursal->empresa;
    @endphp

    <div class="center">

        <div class="bold" style="font-size:16px;">
            {{ $empresa->razon_social }}
        </div>

        <div class="bold">
            RUC: {{ $empresa->documento  }}
        </div>

        <div>{{ $empresa->direccion }}</div>

        @if ($empresa->telefono)
            <div>Tel: {{ $empresa->telefono }}</div>
        @endif

        <div class="line"></div>
    </div>

    {{-- ===========================
           INFO GENERAL
    ============================ --}}
    <p><strong>N° Encomienda:</strong> {{ $encomienda->id }}</p>
    <p><strong>Fecha:</strong> {{ $encomienda->created_at->format('d/m/Y H:i:s') }}</p>
    <p><strong>Origen:</strong> {{ $encomienda->sucursal_origen->nombre_comercial }}</p>
    <p><strong>Destino:</strong> {{ $encomienda->sucursal_destino->nombre_comercial }}</p>

    <div class="line"></div>

    {{-- ===========================
           EMISOR / RECEPTOR
    ============================ --}}
    <p class="bold">DATOS DEL EMISOR</p>
    <p>{{ $encomienda->emisor->nombres }} {{ $encomienda->emisor->apellidos }}</p>
    <p>Doc: {{ $encomienda->emisor->documento }}</p>

    <div class="line"></div>

    <p class="bold">DATOS DEL RECEPTOR</p>
    <p>{{ $encomienda->receptor->nombres }} {{ $encomienda->receptor->apellidos }}</p>
    <p>Doc: {{ $encomienda->receptor->documento ?? '-'}}</p>

    <div class="line"></div>

    {{-- ===========================
           DETALLE DE ENCOMIENDA
    ============================ --}}
    <p class="bold">DETALLES:</p>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Peso</th>
                <th>Descripción</th>
                <th class="right">S/</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($encomienda->detalles as $d)
                <tr>
                    <td>{{ $d->tipo_encomienda->descripcion }}</td>
                    <td>{{ $d->peso }}kg</td>
                    <td>{{ $d->descripcion }}</td>
                    <td class="right">{{ number_format($d->costo, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- ===========================
                 TOTAL
    ============================ --}}
    <p class="bold right">TOTAL: S/ {{ number_format($encomienda->total, 2) }}</p>

    <div class="line"></div>

    <div class="center">
        ¡Gracias por confiar en nosotros!
    </div>

    <button onclick="window.print()">Reimprimir</button>

</body>

</html>

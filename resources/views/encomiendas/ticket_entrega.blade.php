<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body{
            font-family: monospace;
            font-size:11px;
            width:58mm;
        }

        h2,h3,p{
            margin:0;
            text-align:center;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:5px;
        }

        td{
            padding:2px 0;
            vertical-align:top;
        }

        hr{
            border:none;
            border-top:1px dashed #000;
            margin:6px 0;
        }
    </style>
</head>
<body>

<h2>{{ $encomienda->empresa->razon_social }}</h2>

<p>RUC: {{ $encomienda->empresa->ruc }}</p>

<hr>

<h3>CONSTANCIA DE ENTREGA</h3>

<hr>

<table>
<tr>
<td>N°:</td>
<td>{{ $encomienda->codigo }}</td>
</tr>

<tr>
<td>Fecha:</td>
<td>{{ now()->format('d/m/Y H:i') }}</td>
</tr>

<tr>
<td>Cliente:</td>
<td>{{ $encomienda->cliente->nombre }}</td>
</tr>

<tr>
<td>Documento:</td>
<td>{{ $encomienda->cliente->documento }}</td>
</tr>

<tr>
<td>Origen:</td>
<td>{{ $encomienda->origen->nombre }}</td>
</tr>

<tr>
<td>Destino:</td>
<td>{{ $encomienda->destino->nombre }}</td>
</tr>

<tr>
<td>Total:</td>
<td>S/ {{ number_format($encomienda->total,2) }}</td>
</tr>

<tr>
<td>Estado:</td>
<td>ENTREGADO</td>
</tr>

<tr>
<td>Entregado:</td>
<td>{{ auth()->user()->name }}</td>
</tr>
</table>

<hr>

<p>He recibido conforme la encomienda.</p>

<br><br><br>

<p>__________________________</p>
<p>Firma del receptor</p>

</body>
</html>
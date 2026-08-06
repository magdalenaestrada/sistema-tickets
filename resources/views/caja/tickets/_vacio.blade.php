<div class="center">
    @if ($venta->estado === \App\Enums\EstadoVenta::ANULADO || $venta->fecha_anulacion)
        <div class="anulado">*** ANULADO ***</div>
    @endif

    <div class="bold" style="font-size:12px;">
        {{ $venta->sucursal->empresa->razon_social ?? 'TRANSPORTES EDIMSA S.A.C.' }}
    </div>
    <div class="documento">
        <div class="tipo">{{ strtoupper($venta->tipoDocumentoFactura->descripcion ?? 'COMPROBANTE') }}</div>
        <div class="numero">{{ $venta->serie }} - {{ $venta->numero }}</div>
    </div>
</div>

<table class="table-items w-100">
    <thead>
        <tr><th class="left">Descripción</th><th class="center">Cant</th><th class="right">Total</th></tr>
    </thead>
    <tbody>
        @foreach ($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->descripcion }}</td>
                <td class="center">{{ number_format($detalle->cantidad, 0) }}</td>
                <td class="right">S/ {{ number_format($detalle->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
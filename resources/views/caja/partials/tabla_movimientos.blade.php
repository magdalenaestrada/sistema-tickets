{{-- Movimientos --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <strong class="small">Movimientos de caja</strong>
        <span class="text-muted small">Total: {{ $caja->detalles->count() }}</span>
    </div>

    <div class="card-body py-2">
        @if ($caja->detalles->count())
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-primary position-sticky top-0" style="z-index: 1;">
                        <tr>
                            <th>Fecha</th>
                            <th>Referencia</th>
                            <th>Tipo</th>
                            <th>Subtipo</th>
                            <th>Método</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($caja->detalles as $detalle)
                            <tr class="{{ $detalle->anulado ? 'table-danger' : '' }}">
                                <td>{{ $detalle->created_at?->format('d/m/Y h:i A') }}</td>
                                <td>
                                    {{ optional($detalle->venta)->serie
                                        ? optional($detalle->venta)->serie . '-' . optional($detalle->venta)->numero
                                        : $detalle->numero_ticket }}
                                </td>
                                </td>
                                <td>
                                    @if ($detalle->amount > 0)
                                        <span class="badge bg-success">Ingreso</span>
                                    @elseif ($detalle->amount < 0)
                                        <span class="badge bg-danger">Egreso</span>
                                    @else
                                        <span class="badge bg-secondary">Apertura</span>
                                    @endif
                                </td>
                                <td>{{ $detalle->subtipo->descripcion ?? '---' }}</td>
                                <td>{{ $detalle->metodoPago->descripcion ?? '---' }} {{ $detalle->billetera_digital->descripcion ?? '' }}</td>
                                <td>{{ $detalle->description ?? '---' }}</td>
                                <td><strong>S/ {{ number_format(abs($detalle->amount), 2) }}</strong></td>
                                <td>
                                    @if ($detalle->anulado)
                                        <span class="badge bg-secondary">Anulado</span>
                                    @else
                                        <span class="badge bg-primary">Activo</span>
                                    @endif
                                </td>
                              
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <p class="mb-0 text-muted small">No hay movimientos registrados en esta caja.</p>
            </div>
        @endif
    </div>
</div>

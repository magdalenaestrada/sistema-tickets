  {{-- Movimientos --}}
  <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
          <strong class="small">Movimientos de caja</strong>
          <span class="text-muted small">Total: {{ $caja->detalles->count() }}</span>
      </div>

      <div class="card-body py-2">
          @if ($caja->detalles->count())
              <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-primary">
                          <tr>
                              <th>Fecha</th>
                              <th>Ticket</th>
                              <th>Tipo</th>
                              <th>Subtipo</th>
                              <th>Método</th>
                              <th>Descripción</th>
                              <th>Monto</th>
                              <th>Estado</th>
                              <th width="180">Acciones</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($caja->detalles as $detalle)
                              <tr class="{{ $detalle->anulado ? 'table-danger' : '' }}">
                                  <td>{{ $detalle->created_at?->format('d/m/Y h:i A') }}</td>
                                  <td>{{ $detalle->numero_ticket }}</td>
                                  <td>
                                      @if ($detalle->amount > 0)
                                          <span class="badge bg-success">Ingreso</span>
                                      @else
                                          <span class="badge bg-danger">Egreso</span>
                                      @endif
                                  </td>
                                  <td>{{ $detalle->subtipo->descripcion ?? '---' }}</td>
                                  <td>{{ $detalle->metodoPago->descripcion ?? '---' }}</td>
                                  <td>{{ $detalle->description ?? '---' }}</td>
                                  <td><strong>S/ {{ number_format(abs($detalle->amount), 2) }}</strong></td>
                                  <td>
                                      @if ($detalle->anulado)
                                          <span class="badge bg-secondary">Anulado</span>
                                      @else
                                          <span class="badge bg-primary">Activo</span>
                                      @endif
                                  </td>
                                  <td>
                                      <div class="d-flex flex-wrap gap-1">
                                          <a href="{{ route('caja.reimprimir', $detalle->id) }}" target="_blank"
                                              class="btn btn-dark btn-xs">
                                              <i data-lucide="printer"></i>
                                          </a>

                                          @if (!$detalle->anulado && !in_array($caja->estado, ['C', 'cerrada']))
                                              <form action="{{ route('caja.anular', $detalle->id) }}" method="POST"
                                                  class="d-inline form-anular-ticket">
                                                  @csrf

                                                  <button type="button"
                                                      class="btn btn-danger btn-xs btn-anular-ticket">
                                                      <i data-lucide="trash"></i>
                                                  </button>
                                              </form>
                                          @endif
                                      </div>
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

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Compr.</th>
                <th>Pasajero</th>
                <th>Fecha</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Asiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pasajes as $pasaje)
                <tr>
                    <td>{{ $pasajes->total() - (($pasajes->currentPage() - 1) * $pasajes->perPage() + $loop->index) }}
                    </td>
                    <td>
                        @if ($pasaje->venta)
                            {{ $pasaje->venta->serie ?? '' }}-{{ $pasaje->venta->numero ?? '' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>

                        <div style="line-height:1.2">
                            <div class="fw-semibold">
                                {{ $pasaje->persona->nombre_completo }}
                            </div>
                            <small class="text-muted">
                                {{ $pasaje->persona->documento }}
                            </small>
                        </div>
                    </td>
                    <td>

                        <div style="line-height:1.2">
                            <div class="fw-semibold">
                                {{ optional($pasaje->salida)->fecha_salida?->format('d/m/Y') }}
                            </div>
                            <small class="text-muted">
                                {{ optional(optional($pasaje->salida)->horario)->hora_formateada ?? '-' }}
                            </small>
                        </div>
                    </td>

                    <td>{{ $pasaje->origen->descripcion ?? '-' }}</td>
                    <td>{{ $pasaje->destino->descripcion ?? '-' }}</td>
                    <td>{{ $pasaje->asiento_numero }}</td>
                    <td>
                        @switch($pasaje->estado)
                            @case('V')
                                <span class="badge bg-primary">Vendido</span>
                            @break

                            @case('F')
                                <span class="badge bg-primary">Abordó</span>
                            @break

                            @case('X')
                                <span class="badge bg-danger">Anulado</span>
                            @break

                            @case('R')
                                <span class="badge bg-warning text-dark">Reservado</span>
                            @break

                            @case('N')
                                <span class="badge bg-danger">Reserva anulada</span>
                            @break

                            @default
                                <span class="badge bg-secondary">{{ $pasaje->estado }}</span>
                        @endswitch
                    </td>
                    <td>
                        <div class="gap-1">

                            @if ($pasaje->estado == 'R')
                                <a href="{{ route('pasajes.editar', $pasaje->id) }}"
                                    class="btn btn-xs btn-success btn-editar-pasaje">
                                    <i data-lucide="receipt"></i>
                                </a>

                                <button class="btn btn-xs btn-danger btn-anular-reserva"
                                    data-url="{{ route('pasajes.anular_reserva', $pasaje->id) }}">
                                    <i data-lucide="x"></i>
                                </button>
                            @endif
                            @if ($pasaje->sobreEquipajes->count() > 0)
                                <button class="btn btn-info btn-xs btnVerSobreEquipaje" data-id="{{ $pasaje->id }}"
                                    title="Ver sobre equipaje">
                                    <i data-lucide="baggage-claim"></i>
                                </button>
                            @endif
                            @if ($pasaje->venta_id)
                                <a href="{{ route('encomiendas.sobreequipaje.formulario', $pasaje->id) }}"
                                    class="btn btn-warning btn-xs btn-sobreequipaje" title="Agregar sobre equipaje">
                                    <i data-lucide="plus"></i>
                                </a>
                            @endif
                            @if ($pasaje->venta_id && Route::has('ventas.imprimir'))
                                <button class="btn btn-xs btn-secondary imprimir-pasaje"
                                    data-id="{{ $pasaje->venta_id }}">
                                    <i data-lucide="printer"></i>
                                </button>
                            @endif
                            @if ($pasaje->autorizacion_pdf)
                                <a href="{{ Storage::disk('public')->url($pasaje->autorizacion_pdf) }}"
                                    class="btn btn-xs btn-danger" target="_blank" title="Ver autorización">
                                    <i data-lucide="file"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No se encontraron pasajes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $pasajes->links() }}
    </div>

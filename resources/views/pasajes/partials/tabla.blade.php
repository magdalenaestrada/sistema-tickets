<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr class="text-uppercase text-secondary fs-7 border-bottom">
                <th class="fw-semibold bg-transparent py-3 ps-3">ID</th>
                <th class="fw-semibold bg-transparent py-3">Compra</th>
                <th class="fw-semibold bg-transparent py-3">Pasajero</th>
                <th class="fw-semibold bg-transparent py-3">Salida </th>
                <th class="fw-semibold bg-transparent py-3">Origen</th>
                <th class="fw-semibold bg-transparent py-3">Destino</th>
                <th class="fw-semibold bg-transparent py-3">Abordaje</th>
                <th class="fw-semibold bg-transparent py-3 text-center">Asiento</th>
                <th class="fw-semibold bg-transparent py-3">Estado</th>
                <th class="fw-semibold bg-transparent py-3 text-center pe-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pasajes as $pasaje)
                @php
                    $requiereObservacion =
                        $pasaje->estado == 'R' && $pasaje->salida?->hora_salida < now()->subHours(24);
                @endphp

                <tr>
                    <td class="ps-3 text-secondary fw-semibold">
                        #{{ $pasajes->total() - (($pasajes->currentPage() - 1) * $pasajes->perPage() + $loop->index) }}
                    </td>

                    <td class="text-nowrap">
                        @if ($pasaje->venta)
                            <span class="badge bg-light text-dark fw-normal border px-2 py-1">
                                {{ $pasaje->venta->serie }}-{{ $pasaje->venta->numero }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td style="min-width: 180px; max-width: 220px;">
                        <div class="fw-semibold text-dark text-truncate"
                            title="{{ $pasaje->persona->nombre_completo }}">
                            {{ $pasaje->persona->nombre_completo }}
                        </div>
                        <small class="text-muted font-monospace">
                            {{ $pasaje->persona->documento }}
                        </small>
                    </td>

                    <td class="text-nowrap">
                        <div class="fw-semibold text-dark">
                            {{ $pasaje->salida?->horario?->ruta?->nombre ?? '-' }}
                        </div>

                        <small class="text-muted">
                            {{ $pasaje->salida?->fecha_salida?->format('d/m/Y') ?? '-' }}
                            {{ $pasaje->salida?->horario?->hora_formateada ?? '' }}
                        </small>
                    </td>

                    <td>{{ $pasaje->origen->descripcion ?? '-' }}</td>

                    <td>{{ $pasaje->destino->descripcion ?? '-' }}</td>

                    @php
                        $horaAbordaje = $pasaje->salida?->momentoEnPunto($pasaje->origen_pueblito_id);
                    @endphp

                    <td class="text-nowrap">
                        @if ($horaAbordaje)
                            <div class="fw-semibold">
                                {{ $horaAbordaje->format('d/m/Y') }}
                            </div>

                            <small class="text-muted">
                                {{ $horaAbordaje->format('h:i A') }}
                            </small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-2 py-1"
                            style="font-weight: bold; text-size: 0.9rem;">
                            {{ $pasaje->asiento_numero }}
                        </span>
                    </td>

                    <td>
                        @switch($pasaje->estado)
                            @case('V')
                                <span
                                    class="badge rounded-pill bg-primary bg-opacity-10 text-white border border-primary border-opacity-10 px-2.5 py-1 fw-medium">
                                    <i data-lucide="check-circle" class="me-1 align-middle"
                                        style="width: 13px; height: 13px;"></i>Vendido
                                </span>
                            @break

                            @case('R')
                                <span
                                    class="badge rounded-pill bg-warning  text-warning text-dark border border-warning border-opacity-25 px-2.5 py-1 fw-medium">
                                    <i data-lucide="clock" class="me-1 align-middle"
                                        style="width: 13px; height: 13px;"></i>Reservado
                                </span>
                            @break

                            @case('N')
                                <span
                                    class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 px-2.5 py-1 fw-medium">
                                    <i data-lucide="x-circle" class="me-1 align-middle"
                                        style="width: 13px; height: 13px;"></i>Reserva anulada
                                </span>
                            @break

                            @default
                                <span
                                    class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 px-2.5 py-1 fw-medium">
                                    {{ $pasaje->estado }}
                                </span>
                        @endswitch
                    </td>

                    <td class="text-end text-nowrap pe-3">
                        <div class="d-flex flex-wrap gap-1">



                            @if ($pasaje->estado == 'R' && !$pasaje->salida?->supero_24_horas)
                                <a href="{{ route('pasajes.editar', $pasaje->id) }}"
                                    class="btn btn-xs btn-success btn-editar-pasaje">
                                    <i data-lucide="receipt"></i>
                                </a>

                                <button type="button" class="btn btn-xs btn-danger btn-anular-reserva"
                                    data-url="{{ route('pasajes.anular_reserva', $pasaje->id) }}"
                                    data-requiere-observacion="{{ $requiereObservacion ? 1 : 0 }}">
                                    <i data-lucide="x"></i>
                                </button>
                            @endif

                            @if ($pasaje->sobreEquipajes->count() > 0 && !$pasaje->salida?->supero_24_horas)
                                <button class="btn btn-xs btn-info  btnVerSobreEquipaje" data-id="{{ $pasaje->id }}"
                                    title="Ver sobre equipaje">
                                    <i data-lucide="baggage-claim"></i>
                                </button>
                            @endif

                            @if ($pasaje->venta_id && !$pasaje->salida?->supero_24_horas)
                                <a href="{{ route('encomiendas.sobreequipaje.formulario', $pasaje->id) }}"
                                    class="btn btn-xs btn-warning  btn-sobreequipaje" title="Agregar sobre equipaje">
                                    <i data-lucide="plus"></i>
                                </a>
                            @endif

                            @if ($pasaje->venta_id && Route::has('ventas.imprimir'))
                                <button class="btn btn-xs btn-secondary imprimir-pasaje" data-id="{{ $pasaje->venta_id }}">
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
                        <td colspan="10" class="text-center py-5 text-muted">
                            No se encontraron pasajes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 px-2">
        {{ $pasajes->links() }}
    </div>

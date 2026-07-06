<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Compr.</th>
                <th>DNI</th>
                <th>Pasajero</th>
                <th>Fecha</th>
                <th>Hora</th>
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
                    <td>{{ $pasaje->persona->documento ?? '-' }}</td>
                    <td>{{ $pasaje->persona->nombres ?? '' }} {{ $pasaje->persona->apellidos ?? '' }}</td>
                    <td>{{ optional($pasaje->salida)->fecha_salida?->format('d/m/Y') }}</td>
                    <td>{{ optional(optional($pasaje->salida)->horario)->hora_formateada ?? '-' }}</td>
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
                                <span class="badge bg-danger">No abordó</span>
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
                                <a href="{{ route('pasajes.editar', $pasaje->id) }}" class="btn btn-xs btn-success">
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
                                    class="btn btn-warning btn-xs" title="Agregar sobre equipaje">
                                    <i data-lucide="plus"></i>
                                </a>
                            @endif
                            @if ($pasaje->venta_id && Route::has('ventas.imprimir'))
                                <button class="btn btn-xs btn-secondary imprimir-pasaje" data-id="{{ $pasaje->id }}">
                                    <i data-lucide="printer"></i>
                                </button>
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

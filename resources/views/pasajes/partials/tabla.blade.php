<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
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
                    <td>{{ $pasaje->id }}</td>
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
                    <td>{{ $pasaje->origen->nombre_comercial ?? '-' }}</td>
                    <td>{{ $pasaje->destino->nombre_comercial ?? '-' }}</td>
                    <td>{{ $pasaje->asiento_numero }}</td>
                    <td>
                        @switch($pasaje->estado)
                            @case('V')
                                <span class="badge bg-success">Vendido</span>
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

                            @default
                                <span class="badge bg-secondary">{{ $pasaje->estado }}</span>
                        @endswitch
                    </td>
                    <td>
                        <div class="gap-1">
                            @if ($pasaje->estado === 'V')
                                <button type="button" class="btn btn-xs btn-success btn-abordo"
                                    data-id="{{ $pasaje->id }}">
                                    <i class="link-icon" data-lucide="check" style="pointer-events:none;"></i>
                                </button>

                                <button type="button" class="btn btn-xs btn-danger btn-no-abordo"
                                    data-id="{{ $pasaje->id }}">
                                    <i class="link-icon" data-lucide="x" style="pointer-events:none;"></i>
                                </button>
                            @endif

                            @if (in_array($pasaje->estado, ['R', 'V']))
                                <a href="{{ route('pasajes.editar', $pasaje->id) }}" class="btn btn-xs btn-warning">
                                    <i class="link-icon" data-lucide="pencil" style="pointer-events:none;"></i>
                                </a>
                            @endif

                            @if ($pasaje->venta_id && Route::has('ventas.imprimir'))
                                <button class="btn btn-sm btn-secondary imprimir-pasaje" data-id="{{ $pasaje->id }}">
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

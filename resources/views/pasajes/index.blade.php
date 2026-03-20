@extends('layouts.app')

<style>
    /* ─── Variables ─────────────────────────────────────────── */
    :root {
        --blue: #2563eb;
        --blue-light: #eff6ff;
        --blue-mid: #bfdbfe;
        --green: #16a34a;
        --orange: #ea580c;
        --red: #dc2626;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-400: #94a3b8;
        --gray-600: #475569;
        --gray-800: #1e293b;
        --radius: 10px;
        --shadow: 0 1px 4px rgba(0, 0, 0, .08);
        --shadow-md: 0 4px 16px rgba(37, 99, 235, .14);
    }

    /* ─── Layout ─────────────────────────────────────────────── */
    .venta-wrapper {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }

    /* ─── Filtros ─────────────────────────────────────────────── */
    .filtros-bar {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        padding: 14px 16px;
        margin-bottom: 16px;
    }

    .filtros-bar .filtro-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 140px;
    }

    .filtros-bar label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gray-600);
    }

    .filtros-bar .form-control {
        border: 1px solid var(--gray-200);
        border-radius: 7px;
        font-size: .88rem;
        height: 36px;
        padding: 0 10px;
        background: #fff;
        transition: border-color .2s;
    }

    .filtros-bar .form-control:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        outline: none;
    }

    /* ─── Contador de resultados ─────────────────────────────── */
    .resultados-info {
        font-size: .82rem;
        color: var(--gray-400);
        margin-bottom: 10px;
        padding-left: 2px;
    }

    .resultados-info strong {
        color: var(--gray-800);
    }

    /* ─── Lista de horarios ───────────────────────────────────── */
    .horarios-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .horario-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 11px 16px;
        background: #fff;
        border: 1.5px solid var(--gray-200);
        border-radius: var(--radius);
        cursor: pointer;
        transition: border-color .18s, box-shadow .18s, background .18s;
        position: relative;
    }

    .horario-row:hover {
        border-color: var(--blue-mid);
        box-shadow: var(--shadow-md);
        background: var(--blue-light);
    }

    .horario-row.active {
        border-color: var(--blue);
        background: var(--blue-light);
        box-shadow: var(--shadow-md);
    }

    .horario-row.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--blue);
        border-radius: var(--radius) 0 0 var(--radius);
    }

    /* Ruta principal */
    .hr-route {
        flex: 1;
        min-width: 0;
    }

    .hr-route-label {
        font-size: .95rem;
        font-weight: 700;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hr-route-label .arrow-icon {
        color: var(--blue);
        font-size: .8rem;
        flex-shrink: 0;
    }

    .hr-route-sub {
        font-size: .76rem;
        color: var(--gray-400);
        margin-top: 1px;
    }

    /* Fecha */
    .hr-date {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 72px;
    }

    .hr-date-day {
        font-size: .85rem;
        font-weight: 700;
        color: var(--gray-800);
        line-height: 1;
    }

    .hr-date-time {
        font-size: .72rem;
        color: var(--gray-400);
        margin-top: 2px;
    }

    /* Bus */
    .hr-bus {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .78rem;
        color: var(--gray-600);
        min-width: 80px;
    }

    .hr-bus i {
        color: var(--blue);
        font-size: .85rem;
    }

    /* Asientos badge */
    .hr-seats {
        display: flex;
        align-items: center;
        gap: 5px;
        min-width: 64px;
        justify-content: flex-end;
    }

    .seats-badge {
        font-size: .78rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }

    .seats-badge.ok {
        background: #dcfce7;
        color: var(--green);
    }

    .seats-badge.low {
        background: #ffedd5;
        color: var(--orange);
    }

    .seats-badge.full {
        background: #fee2e2;
        color: var(--red);
    }

    /* Tramo tag */
    .hr-tramo-tag {
        font-size: .68rem;
        font-weight: 600;
        background: #e0f2fe;
        color: #0369a1;
        padding: 2px 7px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* Sin resultados */
    .no-results {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-400);
        font-size: .9rem;
    }

    .no-results i {
        font-size: 2rem;
        display: block;
        margin-bottom: 8px;
    }

    /* ─── Panel de asientos ───────────────────────────────────── */
    .asientos-panel {
        background: #fff;
        border: 1.5px solid var(--gray-200);
        border-radius: var(--radius);
        padding: 16px;
        position: sticky;
        top: 20px;
    }

    .asientos-panel-header {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--gray-400);
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--gray-100);
    }

    /* Selector de tramo (solo aparece cuando hace falta) */
    #tramo_selector {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        font-size: .83rem;
    }

    #tramo_selector label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gray-600);
        display: block;
        margin-bottom: 4px;
    }

    #tramo_selector .form-select {
        border: 1px solid var(--gray-200);
        border-radius: 7px;
        font-size: .82rem;
        height: 32px;
        padding: 0 8px;
        width: 100%;
        margin-bottom: 8px;
    }

    #svg-container {
        max-width: 100%;
        overflow: auto;
    }

    /* Leyenda */
    .leyenda {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        font-size: .75rem;
        color: var(--gray-600);
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--gray-100);
    }

    .leyenda-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .leyenda-dot {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    /* Botones de acción */
    .acciones-btns {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 14px;
    }

    /* Asiento selected */
    .seat.selected-seat .seat-body,
    .seat.selected-seat .seat-base {
        fill: #2563eb !important;
    }
</style>

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Venta de pasajes</h5>
        </div>
        <div class="card-body">
            <div class="filtros-bar">
                <div class="filtro-group">
                    <label><i class="bi bi-calendar3"></i> Fecha</label>
                    <input type="date" id="filtro_fecha" min="{{ $hoy }}" class="form-control">
                </div>
                <div class="filtro-group">
                    <label><i class="bi bi-geo-alt"></i> Origen</label>
                    <select id="filtro_origen" class="form-select">
                        <option value="">Seleccionar origen</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filtro-group">
                    <label><i class="bi bi-geo"></i> Destino</label>
                    <select id="filtro_destino" class="form-select">
                        <option value="">Seleccionar destino</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="venta-wrapper">

                {{-- Lista de horarios --}}
                <div>
                    <p class="resultados-info" id="resultados-info"></p>
                    <div class="horarios-list" id="horarios-list">

                        <div class="no-results" id="estado-inicial">
                            <i class="bi bi-search"></i>
                            Usa los filtros de arriba para buscar horarios
                        </div>

                        @foreach ($horarios as $horario)
                            @php
                                $disponibles = $horario->tipo_vehiculo->capacidad - $horario->pasajes_count;
                                $esTramo = $horario->tipo_viaje_id == 2;
                                $fechaSalida = optional($horario->fechas->first())->fecha_salida;
                            @endphp
                            <div class="horario-row" style="display:none;" data-horario-id="{{ $horario->id }}"
                                data-tipo-viaje-id="{{ $horario->tipo_viaje_id }}"
                                data-origen="{{ strtolower($horario->punto_origen->nombre_comercial) }}"
                                data-destino="{{ strtolower($horario->punto_destino->nombre_comercial) }}"
                                data-origen-id="{{ $horario->punto_origen->id ?? '' }}"
                                data-destino-id="{{ $horario->punto_destino->id ?? '' }}"
                                data-fecha="{{ $fechaSalida ? $fechaSalida->format('Y-m-d') : '' }}"
                                data-disponibles="{{ $disponibles }}">

                                {{-- Ruta --}}
                                <div class="hr-route">
                                    <div class="hr-route-label">
                                        {{ $horario->punto_origen->nombre_comercial }}
                                        <span class="arrow-icon">&#8594;</span>
                                        {{ $horario->punto_destino->nombre_comercial }}
                                    </div>
                                    <div class="hr-route-sub">{{ $horario->tipo_viaje->descripcion }}</div>
                                </div>

                                {{-- Fecha --}}
                                <div class="hr-date">
                                    <span class="hr-date-day">
                                        {{ $fechaSalida ? $fechaSalida->format('d/m') : '—' }}
                                    </span>
                                    <span class="hr-date-time">
                                        {{ $fechaSalida ? $fechaSalida->format('H:i') : '' }}
                                    </span>
                                </div>

                                {{-- Bus --}}
                                <div class="hr-bus">
                                    <i class="bi bi-bus-front"></i>
                                    {{ $horario->tipo_vehiculo->descripcion }}
                                </div>

                                {{-- Asientos --}}
                                <div class="hr-seats">
                                    @if ($esTramo)
                                        <span class="hr-tramo-tag">Tramo</span>
                                        <span class="seats-badge ok seats-disponibles" data-pendiente="1">
                                            &mdash; libres
                                        </span>
                                    @else
                                        <span
                                            class="seats-badge {{ $disponibles > 5 ? 'ok' : ($disponibles > 0 ? 'low' : 'full') }} seats-disponibles">
                                            {{ $disponibles }} libre{{ $disponibles != 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Panel asientos --}}
                <div class="asientos-panel">
                    <div class="asientos-panel-header">Mapa de asientos</div>

                    <div id="svg-container">
                        <div class="no-results">
                            <i class="bi bi-cursor"></i>
                            Selecciona un horario para ver los asientos
                        </div>
                    </div>

                    <div class="leyenda">
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#dc2626;"></span> Vendido
                        </div>
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#f97316;"></span> Reservado
                        </div>
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#c0cdda;"></span> Libre
                        </div>
                    </div>

                    <div class="acciones-btns">
                        <button id="sell-button" class="btn btn-primary" style="display:none;">Vender pasaje</button>
                        <button id="edit-button" class="btn btn-warning" style="display:none;">Editar reserva</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pasajes.js') }}"></script>
@endpush

<style>
    #tablaDescuentos thead th {
        background-color: #055999 !important;
        color: #fff;
        border-bottom: 2px solid #044a80;
    }
</style>

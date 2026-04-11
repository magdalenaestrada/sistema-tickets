@extends('layouts.app')

@section('content')
    <style>
        :root {
            --blue: #2563eb;
            --blue-light: #eff6ff;
            --blue-mid: #bfdbfe;
            --green: #16a34a;
            --orange: #f97316;
            --red: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --radius: 10px;
            --shadow-md: 0 4px 16px rgba(37, 99, 235, .14);
        }

        .venta-wrapper {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }

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

        .filtro-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 150px;
        }

        .filtro-group label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--gray-600);
        }

        .resultados-info {
            font-size: .82rem;
            color: var(--gray-400);
            margin-bottom: 10px;
        }

        .resultados-info strong {
            color: var(--gray-800);
        }

        .horarios-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .horario-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            background: #fff;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius);
            cursor: pointer;
            transition: .18s ease;
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

        .hr-route {
            flex: 1;
            min-width: 0;
        }

        .hr-route-label {
            font-size: .95rem;
            font-weight: 700;
            color: var(--gray-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hr-route-sub {
            font-size: .76rem;
            color: var(--gray-400);
            margin-top: 2px;
        }

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

        .hr-bus {
            min-width: 90px;
            font-size: .78rem;
            color: var(--gray-600);
        }

        .hr-seats {
            min-width: 90px;
            text-align: right;
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

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-400);
            font-size: .9rem;
        }

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

        #svg-container {
            max-width: 100%;
            overflow: auto;
            min-height: 250px;
        }

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
        }

        .acciones-btns {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 14px;
        }

        .seat.libre .seat-body,
        .seat.libre .seat-base {
            fill: #cbd5e1 !important;
        }

        .seat.reservado .seat-body,
        .seat.reservado .seat-base {
            fill: #f9db16 !important;
        }

        .seat.ocupado .seat-body,
        .seat.ocupado .seat-base {
            fill: #51dc26 !important;
        }

        .seat.selected-seat .seat-body,
        .seat.selected-seat .seat-base {
            fill: #2563eb !important;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Venta de pasajes</h5>
        </div>

        <div class="card-body">
            <div class="filtros-bar">
                <div class="filtro-group">
                    <label>Fecha</label>
                    <input type="date" id="filtro_fecha" min="{{ $ayer }}" class="form-control" value="{{ $hoy }}">
                </div>

                <div class="filtro-group">
                    <label>Origen</label>
                    <select id="filtro_origen" class="form-select">
                        <option value="">Seleccionar origen</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filtro-group">
                    <label>Destino</label>
                    <select id="filtro_destino" class="form-select">
                        <option value="">Seleccionar destino</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="venta-wrapper">
                <div>
                    <p class="resultados-info" id="resultados-info"></p>

                    <div class="horarios-list" id="salidas-list">
                        <div class="no-results" id="estado-inicial">
                            Usa los filtros para buscar salidas
                        </div>
                        @foreach ($salidas as $salida)
                            <div class="horario-row" style="display:none;" data-salida-id="{{ $salida->id }}"
                                data-tipo-viaje-id="{{ $salida->horario->tipo_viaje_id }}"
                                data-fecha="{{ optional($salida->fecha_salida)->format('Y-m-d') }}"
                                data-puntos="{{ $salida->puntos_json }}" data-origen-nombre="{{ $salida->origen_nombre }}"
                                data-destino-nombre="{{ $salida->destino_nombre }}">

                                <div class="hr-route">
                                    <div class="hr-route-label">
                                        {{ $salida->origen_nombre }} → {{ $salida->destino_nombre }}
                                    </div>
                                    <div class="hr-route-sub">
                                        {{ $salida->horario->tipo_viaje->descripcion ?? '-' }}
                                    </div>
                                </div>

                                <div class="hr-date">
                                    <span class="hr-date-day">{{ optional($salida->fecha_salida)->format('d/m') }}</span>
                                    <span class="hr-date-time">{{ $salida->horario->hora_formateada }}</span>
                                </div>

                                <div class="hr-bus">
                                    {{ $salida->horario->tipo_vehiculo->descripcion ?? '-' }}
                                </div>

                                <div class="hr-seats">
                                    @php $cap = $salida->capacidad_bus; @endphp
                                    <span
                                        class="seats-badge seats-disponibles {{ $cap > 10 ? 'ok' : ($cap > 0 ? 'low' : 'full') }}">
                                        {{ $cap }} libres
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="asientos-panel">
                    <div class="asientos-panel-header">Mapa de asientos</div>

                    <div class="leyenda">
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#cbd5e1;"></span> Libre
                        </div>
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#f9db16;"></span> Reservado
                        </div>
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#51dc26;"></span> Vendido
                        </div>
                        <div class="leyenda-item">
                            <span class="leyenda-dot" style="background:#2563eb;"></span> Seleccionado
                        </div>
                    </div>

                    <div id="svg-container">
                        <div class="no-results">
                            Selecciona una salida para ver los asientos
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

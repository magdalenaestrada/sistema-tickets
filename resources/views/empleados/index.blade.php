@extends('layouts.app')

@section('content')
    <div class="empleados-wrapper">

        <div class="breadcrumb-bar">
            <span class="bc-link">Personal</span>
            <span class="bc-sep">/</span>
            <span class="bc-current">Empleados</span>
        </div>

        <div class="empleados-grid">

            <div class="panel panel-left">
                <div class="panel-header">
                    <h5 class="panel-title">Personal / Empleados</h5>
                    <button class="btn-nuevo" id="btnNuevoEmpleado">
                        <span class="btn-icon">+</span> Nuevo empleado
                    </button>
                </div>

                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="buscarEmpleado" class="search-input" placeholder="Buscar empleado">
                </div>

                <select id="filtroNombre" class="d-none">
                    <option value="">Buscar por persona</option>
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->persona->nombre_completo }}">
                            {{ $empleado->persona->nombre_completo }}
                        </option>
                    @endforeach
                </select>
                <select id="filtroSucursal" class="d-none">
                    <option value="">Buscar por sucursal</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->nombre_comercial }}">{{ $sucursal->nombre_comercial }}</option>
                    @endforeach
                </select>
                <select id="filtroCargo" class="d-none">
                    <option value="">Buscar por cargo</option>
                    @foreach ($cargos as $cargo)
                        <option value="{{ $cargo->descripcion }}">{{ $cargo->descripcion }}</option>
                    @endforeach
                </select>

                <!-- Lista de empleados -->
                <div id="listaEmpleados" class="lista-empleados">
                    <div class="lista-loading">
                        <div class="spinner"></div>
                        <span>Cargando empleados...</span>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
                                                                                     COLUMNA DERECHA — Cumpleaños
                                                                                ══════════════════════════════════════ -->
            <div class="panel-right">

                <!-- Calendario -->
                <div class="panel panel-calendar">
                    <div class="panel-header">
                        <h6 class="panel-title">
                            🎂 Cumpleaños del personal
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div id="calendar"></div>
                    </div>
                </div>

                <!-- Próximos cumpleaños -->
                <div class="panel panel-proximos mt-3">
                    <div class="panel-header">
                        <h6 class="panel-title">🎁 Próximos cumpleaños</h6>
                    </div>
                    <div class="panel-body" id="proximosCumple">
                        <!-- Llenado por JS -->
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('empleados.modals.create')
    @include('horarios.modals.ver')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">
    <style>
        /* ── Variables ──────────────────────────────── */
        :root {
            --verde: #16a34a;
            --verde-bg: #dcfce7;
            --gris-bg: #f3f4f6;
            --gris-txt: #9ca3af;
            --borde:var(--bs-border-color) ;
            --texto: var(--texto);
            --subtexto:var(--bs-secondary);
            --azul: #2563eb;
            --radio: 12px;
            --sombra: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .empleados-wrapper {
            padding: 0;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
        }

        .breadcrumb-bar {
            font-size: 13px;
            color: var(--subtexto);
            margin-bottom: 20px;
        }

        .bc-sep {
            margin: 0 5px;
            color: var(--gris-txt);
        }

        .bc-current {
            color: var(--texto);
        }

        .bc-link {
            cursor: pointer;
        }

        .bc-link:hover {
            color: var(--azul);
        }

        .empleados-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 20px;
            background: var(--bs-primary) align-items: start;
        }

        .panel {
            background: var(--bs-body-bg);
            border-radius: var(--radio);
            box-shadow: var(--sombra);
            border: 1px solid var(--borde);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--borde);
            background: var(--bs-body-bg);
        }

        .panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--texto);
        }

        .panel-body {
            padding: 16px 20px;
        }

        .btn-nuevo {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--azul);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-nuevo:hover {
            background: #1d4ed8;
        }

        .btn-icon {
            font-size: 16px;
            line-height: 1;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 12px 16px;
            padding: 9px 14px;
            background: var(--gris-bg);
            border-radius: 8px;
            border: 1px solid var(--borde);
        }

        .search-icon {
            color: var(--subtexto);
            flex-shrink: 0;
        }

        .search-input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13.5px;
            color: var(--texto);
            width: 100%;
        }

        .search-input::placeholder {
            color: var(--gris-txt);
        }

        /* ── Lista empleados ────────────────────────── */
        .lista-empleados {
            padding: 0 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .lista-loading {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 12px;
            color: var(--subtexto);
            font-size: 13px;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid var(--borde);
            border-top-color: var(--azul);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Item empleado */
        .emp-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .12s;
            gap: 12px;
        }

        .emp-item:hover {
            background: var(--gris-bg);
        }

        .emp-info {
            flex: 1;
            min-width: 0;
        }

        .emp-nombre {
            font-size: 14px;
            font-weight: 600;
            color: var(--texto);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .emp-cargo {
            font-size: 12px;
            color: var(--subtexto);
            margin-top: 1px;
        }

        .emp-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .emp-fecha {
            font-size: 12px;
            color: var(--subtexto);
            white-space: nowrap;
        }

        /* Badge activo/inactivo */
        .badge-estado {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .badge-activo {
            background: var(--verde-bg);
            color: var(--verde);
        }

        .badge-inactivo {
            background: var(--gris-bg);
            color: var(--gris-txt);
        }

        /* Menú tres puntos — siempre visible */
        .emp-menu-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--subtexto);
            padding: 4px 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            opacity: 1;
            transition: background .15s, color .15s;
            position: relative;
        }

        .emp-menu-btn:hover {
            background: var(--borde);
            color: var(--texto);
        }

        /* Dropdown del menú */
        .emp-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            background: #fff;
            border: 1px solid var(--borde);
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
            z-index: 100;
            min-width: 150px;
            padding: 4px 0;
            display: none;
        }

        .emp-dropdown.open {
            display: block;
        }

        .emp-dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            font-size: 13px;
            color: var(--texto);
            cursor: pointer;
            transition: background .1s;
        }

        .emp-dropdown-item:hover {
            background: var(--gris-bg);
        }

        .emp-dropdown-item.danger {
            color: #dc2626;
        }

        .emp-dropdown-item.danger:hover {
            background: #fef2f2;
        }

        /* ── Columna derecha ────────────────────────── */
        .panel-right {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* ── Calendario FullCalendar custom ─────────── */
        .panel-calendar .panel-body {
            padding: 12px 10px;
        }

        .fc {
            font-family: 'DM Sans', 'Segoe UI', sans-serif !important;
            font-size: 12.5px !important;
        }

        .fc-toolbar {
            margin-bottom: 10px !important;
        }

        .fc-toolbar-title {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: var(--texto) !important;
        }

        .fc-button-primary {
            background: transparent !important;
            border: none !important;
            color: var(--subtexto) !important;
            padding: 2px 6px !important;
            box-shadow: none !important;
        }

        .fc-button-primary:hover {
            color: var(--texto) !important;
        }

        .fc-col-header-cell-cushion {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: var(--subtexto) !important;
            text-decoration: none !important;
            text-transform: uppercase;
        }

        .fc-daygrid-day-number {
            font-size: 12px !important;
            color: var(--texto) !important;
            text-decoration: none !important;
        }

        .fc-day-today .fc-daygrid-day-number {
            background: var(--azul) !important;
            color: #fff !important;
            border-radius: 50% !important;
            width: 22px !important;
            height: 22px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
        }

        .fc-day-today {
            background: rgba(37, 99, 235, .05) !important;
        }

        .fc-daygrid-day {
            border-color: transparent !important;
        }

        .fc-scrollgrid {
            border: none !important;
        }

        .fc-scrollgrid td,
        .fc-scrollgrid th {
            border-color: var(--borde) !important;
        }

        .fc-event {
            border: none !important;
            border-radius: 50% !important;
            background: transparent !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        .cumple-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
        }

        .cumple-dot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cumple-dot-default {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff7ac6, #ff4da6);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #fff;
            font-weight: 700;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
        }

        /* ── Próximos cumpleaños ────────────────────── */
        .proximos-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .proximo-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .proximo-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, #ff7ac6, #ff4da6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }

        .proximo-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .proximo-info {
            flex: 1;
            min-width: 0;
        }

        .proximo-fecha {
            font-size: 12px;
            font-weight: 700;
            color: var(--azul);
            white-space: nowrap;
        }

        .proximo-nombre {
            font-size: 13px;
            color: var(--texto);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Responsive ─────────────────────────────── */
        @media (max-width: 900px) {
            .empleados-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const eventosLaravel = @json($datos_eventos);
    </script>
    <script src="{{ asset('js/empleados.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
@endpush

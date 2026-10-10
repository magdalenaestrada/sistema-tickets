@extends('layouts.app')

@section('content')
    <style>
        /* ===============================
               TABS DE ESTADO
            ================================ */
        .btn-pill-tab {
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            color: #64748b;
            padding: 6px 14px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            background: transparent;
        }

        .btn-pill-tab:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .btn-pill-tab.active {
            background-color: #ffffff !important;
            color: #2563eb !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            font-weight: 600;
        }

        /* ===============================
               TARJETAS
            ================================ */
        .panel-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* ===============================
               BARRA DE FILTROS
            ================================ */
        .filtros-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .filtros-bar .ruta-wrap {
            width: 250px;
            max-width: 100%;
        }

        .chips-fecha {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 2px;
            padding: 3px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .chip-fecha {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 12.5px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .15s;
        }

        .chip-fecha:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .chip-fecha.active {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
        }

        .chip-fecha svg {
            width: 13px;
            height: 13px;
        }

        #rangoPersonalizado {
            display: none;
            align-items: center;
            gap: 8px;
        }

        #rangoPersonalizado.show {
            display: inline-flex;
        }

        #rangoPersonalizado input {
            width: 140px;
            height: 34px;
            font-size: 12.5px;
            border-radius: 8px;
        }

        .btn-limpiar {
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: 12.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 8px;
            border-radius: 7px;
        }

        .btn-limpiar:hover {
            color: #dc2626;
            background: #fef2f2;
        }

        .btn-limpiar svg {
            width: 13px;
            height: 13px;
        }

        .ultima-fecha {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 5px 12px;
        }

        .ultima-fecha svg {
            width: 13px;
            height: 13px;
            color: #94a3b8;
        }

        .ultima-fecha a {
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }

        /* Dropdown de Tom Select siempre por encima del panel lateral */
        .ts-dropdown {
            z-index: 3000 !important;
        }

        #filtroRuta+.ts-wrapper .ts-control {
            min-height: 38px;
            border-radius: 8px;
            border-color: #e2e8f0;
            font-size: 13px;
        }

        /* ===============================
               TABLA
            ================================ */
        .tabla-salidas-wrapper {
            border-radius: 14px;
            overflow-x: auto;
            overflow-y: visible;
        }

        #tablaSalidas {
            margin-bottom: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        #tablaSalidas thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom: 1px solid #e9edf2;
            padding: 13px 12px;
            white-space: nowrap;
        }

        #tablaSalidas tbody td {
            padding: 13px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f5;
            color: #334155;
            background: #fff;
        }

        #tablaSalidas tbody tr {
            transition: background .15s ease;
        }

        #tablaSalidas tbody tr:hover td {
            background: #f8fafc;
        }

        #tablaSalidas tbody tr:last-child td {
            border-bottom: 0;
        }

        #tablaSalidas .form-check-input {
            width: 17px;
            height: 17px;
            cursor: pointer;
            margin: 0;
        }

        /* Ruta */
        .ruta-cell {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 190px;
        }

        .ruta-nombre {
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .ruta-label {
            margin-top: 3px;
            font-size: 10px;
            color: #94a3b8;
        }

        /* Fecha */
        .fecha-cell {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #475569;
            font-weight: 500;
            white-space: nowrap;
        }

        .fecha-cell svg {
            width: 15px;
            height: 15px;
            color: #94a3b8;
        }

        /* Íconos lucide */
        svg.lucide {
            stroke: currentColor !important;
            fill: none !important;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: inline-block;
            vertical-align: middle;
        }

        #tablaSalidas svg.lucide {
            width: 15px;
            height: 15px;
        }

        /* Horarios */
        .hora-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 9px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .hora-badge svg {
            width: 13px;
            height: 13px;
        }

        .hora-badge.salida {
            background: #eff6ff;
            color: #2563eb;
        }

        .hora-badge.llegada {
            background: #f0fdf4;
            color: #16a34a;
        }

        .ya-salio {
            margin-top: 3px;
            font-size: 10px;
            font-weight: 700;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        /* Estados */
        #tablaSalidas .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .02em;
        }

        /* Acciones */
        #tablaSalidas .btn-xs {
            width: 31px;
            height: 31px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all .15s ease;
        }

        #tablaSalidas .btn-xs:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, .08);
        }

        #tablaSalidas .btn-xs svg {
            width: 15px;
            height: 15px;
        }

        .acciones-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        /* Columnas */
        #tablaSalidas .checkbox-col {
            width: 42px;
        }

        #tablaSalidas .acciones-col {
            width: 140px;
            min-width: 140px;
        }

        /* Paginación */
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 14px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 7px !important;
            border: 1px solid #e5e7eb !important;
            background: #fff !important;
            color: #475569 !important;
            margin-left: 4px;
            padding: 5px 10px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f8fafc !important;
            color: #2563eb !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #tablaSalidas {
                font-size: 12px;
            }

            #tablaSalidas tbody td,
            #tablaSalidas thead th {
                padding: 10px 8px;
            }

            .ruta-cell {
                min-width: 160px;
            }

            .ultima-fecha {
                margin-left: 0;
            }
        }
    </style>

    <div class="container-fluid px-2 py-2" style="background-color: #f8fafc; min-height: 100vh;">

        <!-- HEADER -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

            <!-- TÍTULO -->
            <div class="d-flex align-items-center gap-3">
                <div class="p-2.5 bg-primary bg-opacity-10 text-white rounded-3
                    d-flex align-items-center justify-content-center"
                    style="width: 44px; height: 44px;">
                    <i data-lucide="bus"></i>
                </div>

                <div>
                    <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.02em;">
                        Salidas
                    </h4>
                    <span class="text-muted fs-7">
                        Gestión y programación de salidas diarias
                    </span>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="d-flex flex-wrap align-items-center gap-2">

                <button
                    class="btn btn-light border bg-white shadow-sm fw-semibold
                    text-secondary d-inline-flex align-items-center gap-2 px-3 py-2 fs-7"
                    onclick="modoCrearSalida()">
                    <i data-lucide="plus" style="width: 16px;"></i>
                    Crear salida única
                </button>

                <button
                    class="btn btn-primary shadow-sm fw-semibold
                    d-inline-flex align-items-center gap-2 px-3 py-2 fs-7"
                    onclick="modoGenerarSalidas()">
                    <i data-lucide="calendar-plus" style="width: 16px;"></i>
                    Programar varias salidas
                </button>

                @if (auth()->user()->hasRole('Administrador'))
                    <button id="btnEliminarSeleccionados"
                        class="btn btn-outline-danger fw-semibold
                        d-inline-flex align-items-center gap-2 px-3 py-2 fs-7">
                        <i data-lucide="trash-2" style="width: 16px;"></i>
                        Eliminar
                    </button>
                @endif

            </div>
        </div>

        <!-- PESTAÑAS DE ESTADO -->
        <div class="d-flex align-items-center gap-1 p-1 bg-secondary bg-opacity-10 rounded-3 mb-4 overflow-auto"
            id="pills-tab-estados" style="max-width: 100%;">
            <button class="btn btn-pill-tab" data-estado="">Todas</button>
            <button class="btn btn-pill-tab active" data-estado="programado">Programadas</button>
            <button class="btn btn-pill-tab" data-estado="retrasado">Retrasadas</button>
            <button class="btn btn-pill-tab" data-estado="en_ruta">En ruta</button>
            <button class="btn btn-pill-tab" data-estado="finalizado">Finalizadas</button>
            <button class="btn btn-pill-tab" data-estado="vencido">Vencidas</button>
            <button class="btn btn-pill-tab" data-estado="cancelado">Canceladas</button>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="row g-4">

            <!-- TABLA -->
            <div class="col-lg-8 col-xl-9">
                <div class="panel-card p-4">

                    <!-- BARRA DE FILTROS -->
                    <div class="filtros-bar">

                        <div class="ruta-wrap">
                            <select id="filtroRuta">
                                <option value="">Todas las rutas</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="chips-fecha">
                            <button type="button" class="chip-fecha active" data-rango="todo">Todas</button>
                            <button type="button" class="chip-fecha" data-rango="hoy">Hoy</button>
                            <button type="button" class="chip-fecha" data-rango="manana">Mañana</button>
                            <button type="button" class="chip-fecha" data-rango="7">7 días</button>
                            <button type="button" class="chip-fecha" data-rango="custom">
                                <i data-lucide="calendar-range"></i> Personalizado
                            </button>
                        </div>

                        <div id="rangoPersonalizado">
                            <input type="date" id="fechaDesde" class="form-control" title="Desde">
                            <span class="text-muted fs-8">a</span>
                            <input type="date" id="fechaHasta" class="form-control" title="Hasta">
                        </div>

                        <button type="button" class="btn-limpiar" id="btnLimpiar">
                            <i data-lucide="x"></i> Limpiar
                        </button>

                        <div class="ultima-fecha">
                            <i data-lucide="calendar-check"></i>
                            Última salida creada: <a href="#" id="ultimaFecha">—</a>
                        </div>
                    </div>

                    <!-- Select oculto de estado (sincronizado con las pestañas por JS) -->
                    <div class="d-none">
                        <select id="filtroEstado" class="form-select">
                            <option value="proximas" selected>Próximas salidas</option>
                            <option value="">Todos los estados</option>
                            <option value="programado">Programado</option>
                            <option value="en_ruta">En ruta</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="reprogramado">Reprogramado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <div class="table-responsive tabla-salidas-wrapper">
                        <table id="tablaSalidas" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    @if (auth()->user()->hasRole('Administrador'))
                                        <th class="text-center checkbox-col">
                                            <input type="checkbox" id="chk-todos" class="form-check-input">
                                        </th>
                                    @endif

                                    <th>RUTA</th>
                                    <th>FECHA</th>
                                    <th class="text-center">SALIDA</th>
                                    <th class="text-center">LLEGADA</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center acciones-col">ACCIONES</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>

            <!-- PANEL LATERAL DE DETALLE -->
            <div class="col-lg-4 col-xl-3">
                <div class="panel-card sticky-top" style="top: 1.5rem; z-index: 1;">
                    <div
                        class="p-3 border-bottom bg-light bg-opacity-50 rounded-top-3 d-flex align-items-center justify-content-between">
                        <h6 id="tituloPanelSalida" class="fw-bold text-dark mb-0 fs-6">Detalle de salida</h6>
                        <span class="badge bg-white text-muted border fs-9 fw-normal">Vista Previa</span>
                    </div>
                    <div class="p-4" id="panelSalidaContenido">
                        <div class="text-center py-5">
                            <div class="mb-3 text-secondary opacity-25">
                                <i data-lucide="mouse-pointer-click" style="width: 42px; height: 42px;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 fs-7">Ninguna salida seleccionada</h6>
                            <p class="text-muted fs-8 mb-0">Haz clic en alguna fila o acción de la tabla para ver el
                                recorrido completo y gestionar manifiestos.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.VEHICULOS = @json($vehiculos);
        window.CONDUCTORES = @json($conductores);
        window.HORARIOS_SALIDA = @json($horariosSalida);
        window.RUTAS_SALIDA = @json($rutas);
        window.TIPOS_VEHICULO = @json($tiposVehiculo);
        window.IS_ADMIN = {{ auth()->user()->hasRole('Administrador') ? 'true' : 'false' }};

        window.SUCURSALES = @json(\App\Models\Sucursal::select('id', 'nombre_comercial')->get());
        window.USER_SUCURSAL = @json(auth()->user()->sucursal ? auth()->user()->sucursal->only('id', 'nombre_comercial') : null);
    </script>
    <script src="{{ asset('js/salidas.js') }}"></script>
@endpush
</document_content>

@extends('layouts.app')

@section('content')
    <style>
        /* Estilos estéticos personalizados para un acabado Premium / SaaS */
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

        .badge-count {
            background: #f1f5f9;
            color: #475569;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.75rem;
            margin-left: 4px;
        }

        .tabla-salidas-wrapper {
            border-radius: 14px;
            overflow-x: auto;
            /* antes: hidden */
            overflow-y: visible;
        }

        #tablaSalidas .acciones-col {
            width: 140px;
            min-width: 140px;
        }

        .acciones-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        #tablaSalidas .acciones-wrap .btn-xs+.btn-xs {
            margin-left: 0;
        }

        .btn-pill-tab.active .badge-count {
            background: #eff6ff;
            color: #2563eb;
        }

        /* Estilos de la Tabla */
        .table-modern {
            border-collapse: separate;
            border-spacing: 0 4px;
        }

        .table-modern thead th {
            background-color: transparent !important;
            color: #94a3b8 !important;
            font-size: 0.725rem !important;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
            border: none !important;
            padding: 12px 16px !important;
        }

        .table-modern tbody tr {
            background-color: #ffffff;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .table-modern tbody tr:hover {
            background-color: #f8fafc !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-modern tbody td {
            padding: 14px 16px !important;
            border-top: 1px solid #f1f5f9 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
        }

        .table-modern tbody tr td:first-child {
            border-left: 1px solid #f1f5f9;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .table-modern tbody tr td:last-child {
            border-right: 1px solid #f1f5f9;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .panel-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        /* Custom Scrollbar */
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

        .tabla-salidas-wrapper {
            border-radius: 14px;
            overflow: hidden;
        }

        /* Tabla */
        #tablaSalidas {
            margin-bottom: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        /* Cabecera */
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

        /* Celdas */
        #tablaSalidas tbody td {
            padding: 13px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f5;
            color: #334155;
            background: #fff;
        }

        /* Hover */
        #tablaSalidas tbody tr {
            transition: background .15s ease;
        }

        #tablaSalidas tbody tr:hover td {
            background: #f8fafc;
        }

        /* Última fila */
        #tablaSalidas tbody tr:last-child td {
            border-bottom: 0;
        }

        /* ===============================
                                                   CHECKBOX
                                                ================================ */

        #tablaSalidas .form-check-input {
            width: 17px;
            height: 17px;
            cursor: pointer;
            margin: 0;
        }

        /* ===============================
                                                   RUTA
                                                ================================ */

        .ruta-cell {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 190px;
        }

        .ruta-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
        }

        .ruta-icon svg {
            width: 17px;
            height: 17px;
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

        /* ===============================
                                                   FECHA
                                                ================================ */

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

        /* ===============================
                                                   HORARIOS
                                                ================================ */
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

        /* ===============================
                                                   ESTADOS
                                                ================================ */

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

        /* ===============================
                                                   ACCIONES
                                                ================================ */

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

        #tablaSalidas .btn-xs+.btn-xs {
            margin-left: 4px;
        }

        #tablaSalidas .btn-xs:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, .08);
        }

        #tablaSalidas .btn-xs svg {
            width: 15px;
            height: 15px;
        }

        /* ===============================
                                                   COLUMNAS
                                                ================================ */

        #tablaSalidas .checkbox-col {
            width: 42px;
        }

        #tablaSalidas .acciones-col {
            width: 125px;
        }

        /* ===============================
                                                   DATATABLE PAGINACIÓN
                                                ================================ */

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

        /* ===============================
                                                   RESPONSIVE
                                                ================================ */

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
        }
    </style>

    <div class="container-fluid px-2 py-2" style="background-color: #f8fafc; min-height: 100vh;">

        <!-- HEADER / ENCABEZADO -->
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


            <!-- FILTRO + ACCIONES -->
            <div class="d-flex flex-wrap align-items-end justify-content-end gap-2">

                <!-- FILTRO DE RUTA -->
                <div style="width: 270px;">


                    <select id="filtroRuta"
                        style="
                    height: 38px;
                    border-color: #e2e8f0;
                    border-radius: 8px;
                    font-size: 13px;
                    color: #475569;
                    background-color: #fff;
                ">
                        <option value="">Todas las rutas</option>

                        @foreach ($rutas as $ruta)
                            <option value="{{ $ruta->id }}">
                                {{ $ruta->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <!-- CREAR SALIDA -->
                <button
                    class="btn btn-light border bg-white shadow-sm fw-semibold
                   text-secondary d-inline-flex align-items-center gap-2
                   px-3 py-2 fs-7"
                    onclick="modoCrearSalida()">
                    <i data-lucide="plus" style="width: 16px;"></i>
                    Crear salida única
                </button>


                <!-- PROGRAMAR SALIDAS -->
                <button
                    class="btn btn-primary shadow-sm fw-semibold
                   d-inline-flex align-items-center gap-2
                   px-3 py-2 fs-7"
                    onclick="modoGenerarSalidas()">
                    <i data-lucide="calendar-plus" style="width: 16px;"></i>
                    Programar varias salidas
                </button>


                @if (auth()->user()->hasRole('Administrador'))
                    <button id="btnEliminarSeleccionados"
                        class="btn btn-outline-danger fw-semibold
                       d-inline-flex align-items-center gap-2
                       px-3 py-2 fs-7">
                        <i data-lucide="trash-2" style="width: 16px;"></i>
                        Eliminar
                    </button>
                @endif

            </div>
        </div>

        <!-- PESTAÑAS DE ESTADO (TABS) -->
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

        <!-- FILTROS Y CONTENIDO PRINCIPAL -->
        <div class="row g-4">

            <!-- COLUMNA IZQUIERDA: TABLA DE SALIDAS -->
            <div class="col-lg-8 col-xl-9"> {{-- tabla --}}
                <div class="panel-card p-4">

                    <!-- BARRA DE FILTROS SECUNDARIA -->
                    <div class="row g-3 align-items-center mb-4">


                        <!-- SELECT DE ESTADO OCULTO (Sincronizado con las pestañas por JS) -->
                        <div class="col-md-4 d-none">
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

            <!-- COLUMNA DERECHA: PANEL LATERAL DE DETALLE -->
            <div class="col-lg-4 col-xl-3"> {{-- panel de detalle --}} <div class="panel-card sticky-top"
                    style="top: 1.5rem; z-index: 10;">
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

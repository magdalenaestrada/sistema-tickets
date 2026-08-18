@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Principal -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="h4 fw-bold mb-1 text-dark">Centro de Reportes</h2>
                <p class="text-muted small mb-0">Selecciona el rango de tiempo y genera tus reportes parametrizados.</p>
            </div>
        </div>

        <!-- BARRA DE FILTROS UNIFICADA -->
        <div class="card shadow-sm border-0 mb-4 bg-white">
            <div class="card-body p-3">
                <form id="filterForm" class="row g-3 align-items-center">
                    <!-- Accesos Rápidos de Período -->
                    <div class="col-12 col-lg-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Período de Tiempo</label>
                        <div class="btn-group w-100" role="group" id="periodGroup">
                            <input type="radio" class="btn-check" name="period" id="period_today" value="today"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-outline-primary btn-sm" for="period_today">Hoy</label>

                            <input type="radio" class="btn-check" name="period" id="period_week" value="week"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-outline-primary btn-sm" for="period_week">Esta Semana</label>

                            <input type="radio" class="btn-check" name="period" id="period_month" value="month" checked
                                onchange="toggleDateInputs()">
                            <label class="btn btn-outline-primary btn-sm" for="period_month">Este Mes</label>

                            <input type="radio" class="btn-check" name="period" id="period_year" value="year"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-outline-primary btn-sm" for="period_year">Año</label>

                            <input type="radio" class="btn-check" name="period" id="period_custom" value="custom"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-outline-primary btn-sm" for="period_custom">Personalizado</label>
                        </div>
                    </div>

                    <!-- Fechas Desde / Hasta (Intercalado/Intervalo) -->
                    <div class="col-12 col-sm-6 col-lg-3 custom-date-container" id="customDateInputs">
                        <label class="form-label small fw-bold text-secondary mb-1">Intervalo de Fechas</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" name="date_from" id="date_from">
                            <span class="input-group-text bg-light text-muted">a</span>
                            <input type="date" class="form-control" name="date_to" id="date_to">
                        </div>
                    </div>

                    <!-- Botón Aplicar Filtro Global -->
                    <div class="col-12 col-sm-6 col-lg-4 d-flex align-items-end pt-sm-3 pt-lg-0">
                        <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold"
                            onclick="applyGlobalFilters()">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros a Reportes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- SECCIONES DE REPORTES -->
        <div class="row g-4">
            <!-- SECCIÓN 1: VENTAS -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-primary">
                            <i class="bi bi-currency-dollar me-2"></i>Reportes de Ventas
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <div class="col-12 col-md-6 border-bottom pb-3 border-bottom-md-0">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                                    Venta de Pasajes General
                                </h6>

                                <div class="mb-2">
                                    <label class="form-label micro-text mb-1">Agencia</label>
                                    <select id="reporte_agencia_id" class="form-select form-select-sm">
                                        <option value="">Todas las Agencias</option>

                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}">
                                                {{ $sucursal->nombre_comercial }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label micro-text mb-1">Ruta</label>
                                    <select id="reporte_ruta_id" class="form-select form-select-sm">
                                        <option value="">Todas las Rutas</option>

                                        @foreach ($rutas as $ruta)
                                            <option value="{{ $ruta->id }}">
                                                {{ $ruta->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Estado</label>
                                    <select id="reporte_estado" class="form-select form-select-sm">
                                        <option value="">Todos los Estados</option>
                                        <option value="V">Vendido</option>
                                        <option value="R">Reservado</option>
                                        <option value="A">Anulado</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        onclick="exportarVentasGeneral('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        PDF
                                    </button>

                                    <button type="button" class="btn btn-outline-success btn-sm w-100"
                                        onclick="exportarVentasGeneral('excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>
                            <!-- Venta por usuario -->
                            <!-- Venta por usuario -->
                            <div class="col-12 col-md-6 border-bottom pb-3 border-bottom-md-0">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-person me-2 text-primary"></i>
                                    Ventas por Usuario
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Usuario / Cajero</label>
                                    <select id="reporte_usuario_id" class="form-select form-select-sm">
                                        <option value="">Todos los Usuarios</option>

                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">
                                                {{ $usuario->persona->nombre_completo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex gap-2 pt-md-4 mt-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        onclick="exportarVentasUsuario('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        PDF
                                    </button>

                                    <button type="button" class="btn btn-outline-success btn-sm w-100"
                                        onclick="exportarVentasUsuario('excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    Ventas por Agencia
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">
                                        Agencia Específica
                                    </label>

                                    <select id="reporte_agencia_id" class="form-select form-select-sm">
                                        <option value="">Todas las Agencias</option>

                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}">
                                                {{ $sucursal->nombre_comercial }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        onclick="exportarVentasAgencia('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        PDF
                                    </button>

                                    <button type="button" class="btn btn-outline-success btn-sm w-100"
                                        onclick="exportarVentasAgencia('excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-signpost-2 me-2 text-primary"></i>
                                    Ventas por Ruta
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">
                                        Ruta de Viaje
                                    </label>

                                    <select id="reporte_ruta_id" class="form-select form-select-sm">
                                        <option value="">Todas las Rutas</option>

                                        @foreach ($rutas as $ruta)
                                            <option value="{{ $ruta->id }}">
                                                {{ $ruta->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        onclick="exportarVentasRuta('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        PDF
                                    </button>

                                    <button type="button" class="btn btn-outline-success btn-sm w-100"
                                        onclick="exportarVentasRuta('excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASAJEROS -->
            <div class="card border-0 shadow-sm flex-fill">

                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-success">
                        <i class="bi bi-people me-2"></i>
                        Pasajeros
                    </h5>
                </div>

                <div class="card-body p-3">

                    <!-- Transportados por Ruta -->
                    <div class="mb-3">

                        <h6 class="fw-bold small text-muted mb-2">
                            Transportados por Ruta
                        </h6>

                        <select id="reporte_pasajeros_ruta_id" class="form-select form-select-sm mb-2">

                            <option value="">
                                Todas las Rutas
                            </option>

                            @foreach ($rutas as $ruta)
                                <option value="{{ $ruta->id }}">
                                    {{ $ruta->descripcion }}
                                </option>
                            @endforeach

                        </select>

                        <div class="d-flex gap-2">

                            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="exportarPasajerosRuta('pdf')">

                                <i class="bi bi-file-earmark-pdf"></i>
                                PDF
                            </button>

                            <button type="button" class="btn btn-outline-success btn-sm w-100"
                                onclick="exportarPasajerosRuta('excel')">

                                <i class="bi bi-file-earmark-excel"></i>
                                Excel
                            </button>

                        </div>

                    </div>

                    <hr class="text-muted opacity-25">

                    <!-- Historial -->
                    <div>

                        <h6 class="fw-bold small text-muted mb-2">
                            Historial de Pasajero
                        </h6>

                        <input type="text" id="reporte_pasajero_busqueda" class="form-control form-control-sm mb-2"
                            placeholder="DNI, Nombre o Teléfono">

                        <div class="d-flex gap-2">

                            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="exportarHistorialPasajero('pdf')">

                                <i class="bi bi-file-earmark-pdf"></i>
                                PDF
                            </button>

                            <button type="button" class="btn btn-outline-success btn-sm w-100"
                                onclick="exportarHistorialPasajero('excel')">

                                <i class="bi bi-file-earmark-excel"></i>
                                Excel
                            </button>

                        </div>

                    </div>

                </div>
            </div>


            <!-- SOBREEQUIPAJE -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-danger">
                        <i class="bi bi-bag-dash me-2"></i>
                        Sobreequipaje
                    </h5>
                </div>

                <div class="card-body p-3">

                    <select id="reporte_sobreequipaje_ruta_id" class="form-select form-select-sm mb-3">

                        <option value="">
                            Todas las Rutas
                        </option>

                        @foreach ($rutas as $ruta)
                            <option value="{{ $ruta->id }}">
                                {{ $ruta->descripcion }}
                            </option>
                        @endforeach

                    </select>

                    <div class="d-flex gap-2">

                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                            onclick="exportarSobreequipaje('pdf')">

                            <i class="bi bi-file-earmark-pdf"></i>
                            PDF

                        </button>

                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                            onclick="exportarSobreequipaje('excel')">

                            <i class="bi bi-file-earmark-excel"></i>
                            Excel

                        </button>

                    </div>

                </div>
            </div>

            <!-- SECCIÓN 3: VIAJES Y ASIENTOS -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="bi bi-bus-front me-2 text-warning"></i>Operativa de Viajes y Asientos
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold small text-muted mb-2">Viajes Realizados</h6>
                                <select class="form-select form-select-sm mb-3">
                                    <option value="">Todos los Buses</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel"></i> Excel</button>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 border-start-md">
                                <h6 class="fw-bold small text-muted mb-2">Viajes Cancelados</h6>
                                <select class="form-select form-select-sm mb-3">
                                    <option value="">Todas las Rutas</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel"></i> Excel</button>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 border-start-md">
                                <h6 class="fw-bold small text-muted mb-2">Ocupación de Asientos</h6>
                                <select class="form-select form-select-sm mb-3">
                                    <option value="">Todos los Buses</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel"></i> Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ESTILOS Y SCRIPTS -->
    <style>
        .micro-text {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
        }

        @media (min-width: 768px) {
            .border-start-md {
                border-left: 1px solid #dee2e6 !important;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toggleDateInputs();
        });

        function obtenerFiltrosGlobales() {
            const form = document.getElementById('filterForm');

            const formData = new FormData(form);

            return {
                period: formData.get('period'),
                date_from: formData.get('date_from'),
                date_to: formData.get('date_to'),
            };
        }

        function exportarVentasUsuario(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.usuario_id = document.getElementById(
                'reporte_usuario_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.usuario_id) {
                params.append('usuario_id', filtros.usuario_id);
            }

            let url;

            if (tipo === 'excel') {
                url = "{{ route('reportes.ventas.usuario.excel') }}";
            } else {
                url = "{{ route('reportes.ventas.usuario.pdf') }}";
            }

            window.location.href = url + '?' + params.toString();
        }

        function toggleDateInputs() {
            const customSelected = document.getElementById('period_custom').checked;
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');

            if (customSelected) {
                dateFrom.removeAttribute('disabled');
                dateTo.removeAttribute('disabled');
            } else {
                dateFrom.setAttribute('disabled', 'true');
                dateTo.setAttribute('disabled', 'true');

                // Opcional: Asignar fechas automáticas según la selección rápida
                const selectedPeriod = document.querySelector('input[name="period"]:checked').value;
                setPredefinedDates(selectedPeriod);
            }
        }

        function exportarVentasGeneral(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.agencia_id = document.getElementById(
                'reporte_agencia_id'
            ).value;

            filtros.ruta_id = document.getElementById(
                'reporte_ruta_id'
            ).value;

            filtros.estado = document.getElementById(
                'reporte_estado'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.agencia_id) {
                params.append('agencia_id', filtros.agencia_id);
            }

            if (filtros.ruta_id) {
                params.append('ruta_id', filtros.ruta_id);
            }

            if (filtros.estado) {
                params.append('estado', filtros.estado);
            }

            let url;

            if (tipo === 'excel') {
                url = "{{ route('reportes.ventas.general.excel') }}";
            } else {
                url = "{{ route('reportes.ventas.general.pdf') }}";
            }

            window.location.href = url + '?' + params.toString();
        }


        function exportarVentasAgencia(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.agencia_id = document.getElementById(
                'reporte_agencia_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.agencia_id) {
                params.append('agencia_id', filtros.agencia_id);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.ventas.agencia.excel') }}" :
                "{{ route('reportes.ventas.agencia.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarVentasRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.ruta_id = document.getElementById(
                'reporte_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.ruta_id) {
                params.append('ruta_id', filtros.ruta_id);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.ventas.ruta.excel') }}" :
                "{{ route('reportes.ventas.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function setPredefinedDates(period) {
            const today = new Date();
            let fromDate = new Date();
            let toDate = new Date();

            if (period === 'today') {
                // Hoy
            } else if (period === 'week') {
                const firstDay = today.getDate() - today.getDay() + 1; // Lunes
                fromDate = new Date(today.setDate(firstDay));
                toDate = new Date();
            } else if (period === 'month') {
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                toDate = new Date();
            } else if (period === 'year') {
                fromDate = new Date(today.getFullYear(), 0, 1);
                toDate = new Date();
            }

            document.getElementById('date_from').value = formatDate(fromDate);
            document.getElementById('date_to').value = formatDate(toDate);
        }

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function applyGlobalFilters() {
            const period = document.querySelector('input[name="period"]:checked').value;
            const from = document.getElementById('date_from').value;
            const to = document.getElementById('date_to').value;

            alert(
                `Filtros Aplicados:\nPeríodo: ${period}\nDesde: ${from}\nHasta: ${to}\n\nLos reportes generados tomarán estos rangos.`
            );
        }

        function exportarPasajerosRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_pasajeros_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.pasajeros.ruta.excel') }}" :
                "{{ route('reportes.pasajeros.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarHistorialPasajero(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const busqueda = document.getElementById(
                'reporte_pasajero_busqueda'
            ).value.trim();

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (busqueda) {
                params.append('busqueda', busqueda);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.historial.pasajero.excel') }}" :
                "{{ route('reportes.historial.pasajero.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function exportarPasajerosRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_pasajeros_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.pasajeros.ruta.excel') }}" :
                "{{ route('reportes.pasajeros.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarHistorialPasajero(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const busqueda = document.getElementById(
                'reporte_pasajero_busqueda'
            ).value.trim();

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (busqueda) {
                params.append('busqueda', busqueda);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.historial.pasajero.excel') }}" :
                "{{ route('reportes.historial.pasajero.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function exportarSobreequipaje(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_sobreequipaje_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.sobreequipaje.excel') }}" :
                "{{ route('reportes.sobreequipaje.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }
    </script>
@endsection









@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 px-md-4 min-vh-100">
        <!-- Header Principal -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-bar-chart-line-fill me-1"></i> Módulo de reportes
                </span>
                <h2 class="h3 fw-bold mb-1 text-dark">Centro de Reportes</h2>
                <p class="text-secondary small mb-0">Selecciona los parámetros de búsqueda para consultar y exportar la
                    información del sistema.</p>
            </div>
        </div>

        <!-- BARRA DE FILTROS UNIFICADA -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <!-- Accesos Rápidos de Período -->
                    <div class="col-12 col-xl-5">
                        <label class="form-label micro-text fw-bold text-uppercase text-muted tracking-wide mb-2">
                            <i class="bi bi-calendar3 me-1"></i> Período
                        </label>
                        <div class="btn-group w-100 p-1 bg-light rounded-3" role="group" id="periodGroup">
                            <input type="radio" class="btn-check" name="period" id="period_today" value="today"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_today">Hoy</label>

                            <input type="radio" class="btn-check" name="period" id="period_week" value="week"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_week">Esta
                                Semana</label>

                            <input type="radio" class="btn-check" name="period" id="period_month" value="month" checked
                                onchange="toggleDateInputs()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_month">Este Mes</label>

                            <input type="radio" class="btn-check" name="period" id="period_year" value="year"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_year">Año</label>

                            <input type="radio" class="btn-check" name="period" id="period_custom" value="custom"
                                onchange="toggleDateInputs()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_custom">Personalizado</label>
                        </div>
                    </div>

                    <!-- Fechas Desde / Hasta -->
                    <div class="col-12 col-md-7 col-xl-4 custom-date-container" id="customDateInputs">
                        <label class="form-label micro-text fw-bold text-uppercase text-muted tracking-wide mb-2">
                            <i class="bi bi-calendar-range me-1"></i> Rango de Fechas
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="bi bi-calendar-event text-muted"></i></span>
                            <input type="date" class="form-control border-start-0" name="date_from" id="date_from"
                                placeholder="Desde">
                            <span class="input-group-text bg-light text-muted fw-bold">a</span>
                            <input type="date" class="form-control border-start-0" name="date_to" id="date_to"
                                placeholder="Hasta">
                        </div>
                    </div>

                    <!-- Botón Aplicar Filtro Global -->
                    <div class="col-12 col-md-5 col-xl-3 ms-auto">
                        <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold py-2 rounded-3 shadow-sm"
                            onclick="applyGlobalFilters()">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtro Global
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SECCIONES DE REPORTES -->
        <div class="row g-4">

            <!-- SECCIÓN 1: VENTAS -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-currency-dollar fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">Reportes de Ventas</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Finanzas & Facturación</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <!-- Venta General -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <i class="bi bi-ticket-perforated me-2 text-primary"></i> Venta General de
                                            Pasajes
                                        </h6>
                                        <div class="mb-2">
                                            <label class="form-label micro-text text-muted mb-1">Agencia</label>
                                            <select id="reporte_general_agencia_id"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todas las Agencias</option>
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label micro-text text-muted mb-1">Ruta</label>
                                            <select id="reporte_general_ruta_id"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todas las Rutas</option>
                                                @foreach ($rutas as $ruta)
                                                    <option value="{{ $ruta->id }}">{{ $ruta->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label micro-text text-muted mb-1">Estado</label>
                                            <select id="reporte_estado"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todos los Estados</option>
                                                <option value="V">Vendido</option>
                                                <option value="R">Reservado</option>
                                                <option value="A">Anulado</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasGeneral('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasGeneral('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Venta por Usuario -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <i class="bi bi-person me-2 text-primary"></i> Ventas por Usuario / Cajero
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label micro-text text-muted mb-1">Usuario</label>
                                            <select id="reporte_usuario_id"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todos los Usuarios</option>
                                                @foreach ($usuarios as $usuario)
                                                    <option value="{{ $usuario->id }}">
                                                        {{ $usuario->persona->nombre_completo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasUsuario('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasUsuario('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Venta por Agencia -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <i class="bi bi-building me-2 text-primary"></i> Ventas por Agencia
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label micro-text text-muted mb-1">Agencia Específica</label>
                                            <select id="reporte_agencia_especifica_id"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todas las Agencias</option>
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasAgencia('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasAgencia('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Venta por Ruta -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <i class="bi bi-signpost-2 me-2 text-primary"></i> Ventas por Ruta
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label micro-text text-muted mb-1">Ruta de Viaje</label>
                                            <select id="reporte_ruta_especifica_id"
                                                class="form-select form-select-sm border-0 shadow-sm">
                                                <option value="">Todas las Rutas</option>
                                                @foreach ($rutas as $ruta)
                                                    <option value="{{ $ruta->id }}">{{ $ruta->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasRuta('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasRuta('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: PASAJEROS Y SOBREEQUIPAJE -->
            <div class="col-12 col-xl-4 d-flex flex-column gap-4">

                <!-- PASAJEROS -->
                <div class="card border-0 shadow-sm rounded-4 flex-fill">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center gap-2">
                        <div class="icon-box bg-success-subtle text-success rounded-3 p-2 d-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px;">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0 text-dark">Pasajeros</h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between gap-3">

                        <!-- Transportados por Ruta -->
                        <div class="p-3 rounded-3 bg-light-subtle border">
                            <h6 class="fw-bold small text-dark mb-2">Transportados por Ruta</h6>
                            <select id="reporte_pasajeros_ruta_id"
                                class="form-select form-select-sm border-0 shadow-sm mb-3">
                                <option value="">Todas las Rutas</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->descripcion }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarPasajerosRuta('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarPasajerosRuta('excel')">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </button>
                            </div>
                        </div>

                        <!-- Historial -->
                        <div class="p-3 rounded-3 bg-light-subtle border">
                            <h6 class="fw-bold small text-dark mb-2">Historial de Pasajero</h6>
                            <input type="text" id="reporte_pasajero_busqueda"
                                class="form-control form-control-sm border-0 shadow-sm mb-3"
                                placeholder="DNI, Nombre o Teléfono">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarHistorialPasajero('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarHistorialPasajero('excel')">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SOBREEQUIPAJE -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center gap-2">
                        <div class="icon-box bg-danger-subtle text-danger rounded-3 p-2 d-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px;">
                            <i class="bi bi-bag-dash fs-5"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0 text-dark">Sobreequipaje</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 rounded-3 bg-light-subtle border">
                            <label class="form-label micro-text text-muted mb-1">Filtrar por Ruta</label>
                            <select id="reporte_sobreequipaje_ruta_id"
                                class="form-select form-select-sm border-0 shadow-sm mb-3">
                                <option value="">Todas las Rutas</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->descripcion }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarSobreequipaje('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                    onclick="exportarSobreequipaje('excel')">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECCIÓN 3: OPERATIVA DE VIAJES Y ASIENTOS -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-bus-front fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">Operativa de Viajes y Asientos</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Monitoreo Operativo</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <!-- Viajes Realizados -->
                            <div class="col-12 col-md-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold small text-dark mb-2">Viajes Realizados</h6>
                                        <select class="form-select form-select-sm border-0 shadow-sm mb-3">
                                            <option value="">Todos los Buses</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Viajes Cancelados -->
                            <div class="col-12 col-md-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold small text-dark mb-2">Viajes Cancelados</h6>
                                        <select class="form-select form-select-sm border-0 shadow-sm mb-3">
                                            <option value="">Todas las Rutas</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Ocupación de Asientos -->
                            <div class="col-12 col-md-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold small text-dark mb-2">Ocupación de Asientos</h6>
                                        <select class="form-select form-select-sm border-0 shadow-sm mb-3">
                                            <option value="">Todos los Buses</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Estilos CSS auxiliares sugeridos -->
    <style>
        .micro-text {
            font-size: 0.75rem;
        }

        .tracking-wide {
            letter-spacing: 0.05em;
        }

        .btn-outline-custom {
            color: #6c757d;
            background-color: transparent;
        }

        .btn-check:checked+.btn-outline-custom {
            background-color: #ffffff;
            color: #0d6efd;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            font-weight: 600 !important;
        }

        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }
    </style>

    <!-- ESTILOS Y SCRIPTS -->
    <style>
        .micro-text {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
        }

        @media (min-width: 768px) {
            .border-start-md {
                border-left: 1px solid #dee2e6 !important;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toggleDateInputs();
        });

        function obtenerFiltrosGlobales() {
            const form = document.getElementById('filterForm');

            const formData = new FormData(form);

            return {
                period: formData.get('period'),
                date_from: formData.get('date_from'),
                date_to: formData.get('date_to'),
            };
        }

        function exportarVentasUsuario(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.usuario_id = document.getElementById(
                'reporte_usuario_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.usuario_id) {
                params.append('usuario_id', filtros.usuario_id);
            }

            let url;

            if (tipo === 'excel') {
                url = "{{ route('reportes.ventas.usuario.excel') }}";
            } else {
                url = "{{ route('reportes.ventas.usuario.pdf') }}";
            }

            window.location.href = url + '?' + params.toString();
        }

        function toggleDateInputs() {
            const customSelected = document.getElementById('period_custom').checked;
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');

            if (customSelected) {
                dateFrom.removeAttribute('disabled');
                dateTo.removeAttribute('disabled');
            } else {
                dateFrom.setAttribute('disabled', 'true');
                dateTo.setAttribute('disabled', 'true');

                // Opcional: Asignar fechas automáticas según la selección rápida
                const selectedPeriod = document.querySelector('input[name="period"]:checked').value;
                setPredefinedDates(selectedPeriod);
            }
        }

        function exportarVentasGeneral(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.agencia_id = document.getElementById(
                'reporte_agencia_id'
            ).value;

            filtros.ruta_id = document.getElementById(
                'reporte_ruta_id'
            ).value;

            filtros.estado = document.getElementById(
                'reporte_estado'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.agencia_id) {
                params.append('agencia_id', filtros.agencia_id);
            }

            if (filtros.ruta_id) {
                params.append('ruta_id', filtros.ruta_id);
            }

            if (filtros.estado) {
                params.append('estado', filtros.estado);
            }

            let url;

            if (tipo === 'excel') {
                url = "{{ route('reportes.ventas.general.excel') }}";
            } else {
                url = "{{ route('reportes.ventas.general.pdf') }}";
            }

            window.location.href = url + '?' + params.toString();
        }


        function exportarVentasAgencia(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.agencia_id = document.getElementById(
                'reporte_agencia_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.agencia_id) {
                params.append('agencia_id', filtros.agencia_id);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.ventas.agencia.excel') }}" :
                "{{ route('reportes.ventas.agencia.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarVentasRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            filtros.ruta_id = document.getElementById(
                'reporte_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (filtros.ruta_id) {
                params.append('ruta_id', filtros.ruta_id);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.ventas.ruta.excel') }}" :
                "{{ route('reportes.ventas.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function setPredefinedDates(period) {
            const today = new Date();
            let fromDate = new Date();
            let toDate = new Date();

            if (period === 'today') {
                // Hoy
            } else if (period === 'week') {
                const firstDay = today.getDate() - today.getDay() + 1; // Lunes
                fromDate = new Date(today.setDate(firstDay));
                toDate = new Date();
            } else if (period === 'month') {
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                toDate = new Date();
            } else if (period === 'year') {
                fromDate = new Date(today.getFullYear(), 0, 1);
                toDate = new Date();
            }

            document.getElementById('date_from').value = formatDate(fromDate);
            document.getElementById('date_to').value = formatDate(toDate);
        }

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function applyGlobalFilters() {
            const period = document.querySelector('input[name="period"]:checked').value;
            const from = document.getElementById('date_from').value;
            const to = document.getElementById('date_to').value;

            alert(
                `Filtros Aplicados:\nPeríodo: ${period}\nDesde: ${from}\nHasta: ${to}\n\nLos reportes generados tomarán estos rangos.`
            );
        }

        function exportarPasajerosRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_pasajeros_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.pasajeros.ruta.excel') }}" :
                "{{ route('reportes.pasajeros.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarHistorialPasajero(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const busqueda = document.getElementById(
                'reporte_pasajero_busqueda'
            ).value.trim();

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (busqueda) {
                params.append('busqueda', busqueda);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.historial.pasajero.excel') }}" :
                "{{ route('reportes.historial.pasajero.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function exportarPasajerosRuta(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_pasajeros_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.pasajeros.ruta.excel') }}" :
                "{{ route('reportes.pasajeros.ruta.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }


        function exportarHistorialPasajero(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const busqueda = document.getElementById(
                'reporte_pasajero_busqueda'
            ).value.trim();

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (busqueda) {
                params.append('busqueda', busqueda);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.historial.pasajero.excel') }}" :
                "{{ route('reportes.historial.pasajero.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }

        function exportarSobreequipaje(tipo) {

            const filtros = obtenerFiltrosGlobales();

            const rutaId = document.getElementById(
                'reporte_sobreequipaje_ruta_id'
            ).value;

            const params = new URLSearchParams();

            params.append('period', filtros.period);

            if (filtros.date_from) {
                params.append('date_from', filtros.date_from);
            }

            if (filtros.date_to) {
                params.append('date_to', filtros.date_to);
            }

            if (rutaId) {
                params.append('ruta_id', rutaId);
            }

            const url = tipo === 'excel' ?
                "{{ route('reportes.sobreequipaje.excel') }}" :
                "{{ route('reportes.sobreequipaje.pdf') }}";

            window.location.href = url + '?' + params.toString();
        }
    </script>
@endsection

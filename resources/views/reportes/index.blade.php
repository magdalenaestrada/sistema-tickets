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
                            <!-- Venta de pasajes -->
                            <div class="col-12 col-md-6 border-bottom pb-3 border-bottom-md-0">
                                <h6 class="fw-bold text-dark mb-3"><i
                                        class="bi bi-ticket-perforated me-2 text-primary"></i>Venta de Pasajes General</h6>
                                <div class="mb-2">
                                    <label class="form-label micro-text mb-1">Agencia</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todas las Agencias</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label micro-text mb-1">Ruta</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todas las Rutas</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Estado</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todos los Estados</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                </div>
                            </div>

                            <!-- Venta por usuario -->
                            <div class="col-12 col-md-6 border-bottom pb-3 border-bottom-md-0">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person me-2 text-primary"></i>Ventas
                                    por Usuario</h6>
                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Usuario / Cajero</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todos los Usuarios</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2 pt-md-4 mt-md-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                </div>
                            </div>

                            <!-- Venta por agencia -->
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-building me-2 text-primary"></i>Ventas
                                    por Agencia</h6>
                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Agencia Específica</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todas las Agencias</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                </div>
                            </div>

                            <!-- Venta por ruta -->
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark mb-3"><i
                                        class="bi bi-signpost-2 me-2 text-primary"></i>Ventas por Ruta</h6>
                                <div class="mb-3">
                                    <label class="form-label micro-text mb-1">Ruta de Viaje</label>
                                    <select class="form-select form-select-sm">
                                        <option value="">Todas las Rutas</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: PASAJEROS Y SOBREEQUIPAJE -->
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-4 h-100">
                    <!-- Pasajeros -->
                    <div class="card border-0 shadow-sm flex-fill">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0 text-success">
                                <i class="bi bi-people me-2"></i>Pasajeros
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <h6 class="fw-bold small text-muted mb-2">Transportados por Ruta</h6>
                                <select class="form-select form-select-sm mb-2">
                                    <option value="">Todas las Rutas</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel"></i> Excel</button>
                                </div>
                            </div>
                            <hr class="text-muted opacity-25">
                            <div>
                                <h6 class="fw-bold small text-muted mb-2">Historial de Pasajero</h6>
                                <input type="text" class="form-control form-control-sm mb-2"
                                    placeholder="DNI, Nombre o Teléfono">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i
                                            class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i
                                            class="bi bi-file-earmark-excel"></i> Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sobreequipaje -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0 text-danger">
                                <i class="bi bi-bag-dash me-2"></i>Sobreequipaje
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <select class="form-select form-select-sm mb-3">
                                <option value="">Todas las Rutas</option>
                            </select>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-earmark-pdf"></i>
                                    PDF</button>
                                <button class="btn btn-outline-success btn-sm w-100"><i
                                        class="bi bi-file-earmark-excel"></i> Excel</button>
                            </div>
                        </div>
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
                `Filtros Aplicados:\nPeríodo: ${period}\nDesde: ${from}\nHasta: ${to}\n\nLos reportes generados tomarán estos rangos.`);
        }
    </script>
@endsection

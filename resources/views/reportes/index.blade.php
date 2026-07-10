@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Encabezado Principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold mb-0">Reportes</h2>
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar3 me-2"></i>Filtrar por rango de fechas
            </button>
        </div>

        <!-- FILA 1: VENTAS, PASAJEROS -->
        <div class="row g-4 mb-4">
            <!-- Bloque Reportes de Ventas -->
            <div class="col-12 col-md-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-1 fw-bold text-uppercase small">
                        Reportes de Ventas
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Reporte de venta de pasajes -->
                            <div class="col-md-6">
                                <h6 class="fw-bold"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Reporte de
                                    venta de pasajes</h6>
                                <div class="mb-2"><select class="form-select form-select-sm">
                                        <option>Agencia: Todas</option>
                                    </select></div>
                                <div class="mb-2"><select class="form-select form-select-sm">
                                        <option>Ruta: Todas</option>
                                    </select></div>
                                <div class="mb-3"><select class="form-select form-select-sm">
                                        <option>Estado: Todos</option>
                                    </select></div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                        PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                        Excel</button>
                                </div>
                            </div>
                            <!-- Venta por usuario -->
                            <div class="col-md-6">
                                <h6 class="fw-bold"><i class="bi bi-person me-2 text-primary"></i>Venta por usuario</h6>
                                <div class="mb-3"><select class="form-select form-select-sm">
                                        <option>Usuario: Todos</option>
                                    </select></div>
                                <div class="d-flex gap-2 pt-4">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                        PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                        Excel</button>
                                </div>
                            </div>
                            <!-- Venta por agencia -->
                            <div class="col-md-6">
                                <h6 class="fw-bold"><i class="bi bi-building me-2 text-primary"></i>Venta por agencia</h6>
                                <div class="mb-3"><select class="form-select form-select-sm">
                                        <option>Agencia: Todas</option>
                                    </select></div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                        PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                        Excel</button>
                                </div>
                            </div>
                            <!-- Venta por ruta -->
                            <div class="col-md-6">
                                <h6 class="fw-bold"><i class="bi bi-signpost-2 me-2 text-primary"></i>Venta por ruta</h6>
                                <div class="mb-3"><select class="form-select form-select-sm">
                                        <option>Ruta: Todas</option>
                                    </select></div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                        PDF</button>
                                    <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                        Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloque Reportes de Pasajeros -->
            <div class="col-12 col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white text-center py-1 fw-bold text-uppercase small">
                        Reportes de Pasajeros
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <!-- Pasajeros transportados -->
                        <div class="mb-4">
                            <h6 class="fw-bold"><i class="bi bi-people me-2 text-success"></i>Pasajeros transportados</h6>
                            <div class="mb-2"><select class="form-select form-select-sm">
                                    <option>Ruta: Todas</option>
                                </select></div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                    PDF</button>
                                <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                    Excel</button>
                            </div>
                        </div>
                        <!-- Historial de un pasajero -->
                        <div class="mb-4">
                            <h6 class="fw-bold"><i class="bi bi-person-badge me-2 text-success"></i>Historial de un pasajero
                            </h6>
                            <div class="mb-2"><input type="text" class="form-control form-control-sm"
                                    placeholder="DNI, Nombre o Teléfono"></div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-pdf"></i>
                                    PDF</button>
                                <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-excel"></i>
                                    Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 2: VIAJES, ASIENTOS, SOBREEQUIPAJE -->
        <div class="row g-4 mb-4">
            <!-- Reportes de Viajes -->
            <div class="col-12 col-md-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark text-center py-1 fw-bold text-uppercase small">
                        Reportes de Viajes
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <h6 class="fw-bold small"><i class="bi bi-bus-front text-warning me-1"></i>Viajes realizados
                                </h6>
                                <div class="mb-2"><select class="form-select form-select-sm">
                                        <option>Bus: Todos</option>
                                    </select></div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-danger btn-sm p-1 small w-100">PDF</button>
                                    <button class="btn btn-outline-success btn-sm p-1 small w-100">Excel</button>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="fw-bold small"><i class="bi bi-x-circle text-danger me-1"></i>Viajes cancelados
                                </h6>
                                <div class="mb-2"><select class="form-select form-select-sm">
                                        <option>Ruta: Todas</option>
                                    </select></div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-danger btn-sm p-1 small w-100">PDF</button>
                                    <button class="btn btn-outline-success btn-sm p-1 small w-100">Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Asientos -->
            <div class="col-12 col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-purple text-white text-center py-1 fw-bold text-uppercase small"
                        style="background-color: #6f42c1;">
                        Reportes de Asientos
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold small"><i class="bi bi-chair text-purple me-1"></i>Estado de ocupación</h6>
                        <div class="mb-2"><select class="form-select form-select-sm">
                                <option>Bus: Todos</option>
                            </select></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger btn-sm w-100">PDF</button>
                            <button class="btn btn-outline-success btn-sm w-100">Excel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Sobreequipaje -->
            <div class="col-12 col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-danger text-white text-center py-1 fw-bold text-uppercase small">
                        Reportes de Sobreequipaje
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold small"><i class="bi bi-bag-dash text-danger me-1"></i>Venta de sobreequipaje
                        </h6>
                        <div class="mb-2"><select class="form-select form-select-sm">
                                <option>Ruta: Todas</option>
                            </select></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger btn-sm w-100">PDF</button>
                            <button class="btn btn-outline-success btn-sm w-100">Excel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DASHBOARD GERENCIAL (INFERIOR) -->
        <div class="card shadow-sm mt-4">
            <div class="card-header text-white text-center py-1 fw-bold text-uppercase small"
                style="background-color: #0f2c59;">
                Reportes Gerenciales (Dashboard)
            </div>
            <div class="card-body p-0">
                <div class="row g-0 text-center divide-x">
                    <div class="col border-end p-3">
                        <i class="bi bi-list-ol fs-3 text-secondary"></i>
                        <p class="mb-0 fw-bold small mt-2">Ranking de rutas</p>
                        <span class="text-muted muted-text">Rutas más vendidas</span>
                    </div>
                    <div class="col border-end p-3">
                        <i class="bi bi-building-up fs-3 text-secondary"></i>
                        <p class="mb-0 fw-bold small mt-2">Ranking de agencias</p>
                        <span class="text-muted muted-text">Agencias con más ventas</span>
                    </div>
                    <div class="col border-end p-3">
                        <i class="bi bi-graph-up-arrow fs-3 text-success"></i>
                        <p class="mb-0 fw-bold small mt-2">Ingresos por mes</p>
                        <span class="text-muted muted-text">Gráfico mensual</span>
                    </div>
                    <div class="col p-3">
                        <i class="bi bi-currency-dollar fs-3 text-primary"></i>
                        <p class="mb-0 fw-bold small mt-2">Utilidad</p>
                        <span class="text-muted muted-text">Ingresos - Gastos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 px-md-4 min-vh-100">
        <!-- Header Principal -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-1">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-bar-chart-line-fill me-1"></i> Módulo de Reportes
                </span>
            
            </div>
        </div>

        <!-- BARRA DE FILTROS UNIFICADA GLOBAL -->
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
                                onchange="updateDateRange()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_today">Hoy</label>

                            <input type="radio" class="btn-check" name="period" id="period_week" value="week"
                                onchange="updateDateRange()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_week">Esta
                                Semana</label>

                            <input type="radio" class="btn-check" name="period" id="period_month" value="month" checked
                                onchange="updateDateRange()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_month">Este Mes</label>

                            <input type="radio" class="btn-check" name="period" id="period_year" value="year"
                                onchange="updateDateRange()">
                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium"
                                for="period_year">Año</label>

                            <input type="radio" class="btn-check" name="period" id="period_custom" value="custom"
                                onchange="updateDateRange()">
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

        <!-- GRID DE SECCIONES DE REPORTES -->
        <div class="row g-4">

            <!-- 1. REPORTES DE VENTAS E INGRESOS -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">1. Reportes de Ventas e Ingresos</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Finanzas & Ventas</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            {{-- =====================================================
            REPORTE GENERAL DE VENTAS
        ====================================================== --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                                            Reporte General de Ventas
                                        </h6>

                                        <p class="text-muted micro-text mb-2">
                                            Consolidado general de pasajes, encomiendas,
                                            sobreequipaje, métodos de pago, agencias y vendedores.
                                        </p>

                                        <select id="reporte_general_agencia"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">

                                            <option value="">
                                                Todas las Agencias
                                            </option>

                                            @foreach ($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id }}">
                                                    {{ $sucursal->nombre_comercial }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-2">

                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasGeneral()">

                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF

                                        </button>

                                    </div>

                                </div>

                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-receipt me-2 text-primary"></i>
                                            Ventas Diarias (Cierre)</h6>
                                        <p class="text-muted micro-text mb-2">Cuadre diario por boletería, ruta o turno.
                                        </p>
                                        <select id="reporte_ventas_diarias_agencia"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">
                                            <option value="">Todas las Agencias</option>
                                            @foreach ($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('ventas-diarias', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('ventas-diarias', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Ventas por Método de Pago -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i
                                                class="bi bi-credit-card me-2 text-primary"></i> Métodos de Pago</h6>
                                        <p class="text-muted micro-text mb-2">Efectivo, tarjetas, transferencias y
                                            billeteras digitales.</p>
                                        <select id="reporte_metodo_pago"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">
                                            <option value="">Todos los Métodos</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta (POS)</option>
                                            <option value="transferencia">Transferencia Bancaria</option>
                                            <option value="billetera">Billeteras Digitales (Yape/Plin)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('metodos-pago', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('metodos-pago', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pasajes vs. Encomiendas -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i
                                                class="bi bi-pie-chart me-2 text-primary"></i> Pasajes vs. Encomiendas</h6>
                                        <p class="text-muted micro-text mb-2">Comparativo desagregado para tratamiento
                                            contable.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('pasajes-vs-encomiendas', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('pasajes-vs-encomiendas', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Comisiones de Agencias / Intermediarios -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i
                                                class="bi bi-diagram-3 me-2 text-primary"></i> Comisiones de Agencias</h6>
                                        <p class="text-muted micro-text mb-2">Comisiones por ventas mediante
                                            intermediarios/terminales.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('comisiones-agencias', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('comisiones-agencias', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Ventas por Usuario / Cajero -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i
                                                class="bi bi-person-badge me-2 text-primary"></i> Ventas por Cajero</h6>
                                        <select id="reporte_usuario_id"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">
                                            <option value="">Todos los Usuarios</option>
                                            @foreach ($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}">
                                                    {{ $usuario->persona->nombre_completo ?? $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasUsuario('pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasUsuario('excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Ventas por Ruta -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i
                                                class="bi bi-signpost-2 me-2 text-primary"></i> Ventas por Ruta</h6>
                                        <select id="reporte_ruta_especifica_id"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">
                                            <option value="">Todas las Rutas</option>
                                            @foreach ($rutas as $ruta)
                                                <option value="{{ $ruta->id }}">
                                                    {{ $ruta->nombre ?? $ruta->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasRuta('pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarVentasRuta('excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. REPORTES TRIBUTARIOS / FISCALES -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-info-subtle text-info rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">2. Reportes Tributarios / Fiscales</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Contabilidad</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Comprobantes Emitidos -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-journal-check me-2 text-info"></i> Comprobantes Emitidos</h6>
                                        <p class="text-muted micro-text mb-2">Facturas, boletas, tickets y detalle de
                                            IGV/IVA.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('comprobantes-emitidos', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('comprobantes-emitidos', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Anulaciones y Notas de Crédito -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-file-earmark-x me-2 text-info"></i> Anulaciones y N/C</h6>
                                        <p class="text-muted micro-text mb-2">Notas de crédito, débito y documentos
                                            anulados.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('anulaciones-nc', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('anulaciones-nc', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Libro de Ventas SUNAT/SAT -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-book me-2 text-info"></i> Libro
                                            de Ventas</h6>
                                        <p class="text-muted micro-text mb-2">Formato oficial exigido por entes fiscales
                                            (PLE/SIRE/SUNAT).</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('libro-ventas', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('libro-ventas', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Control de Correlativos -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-list-ol me-2 text-info"></i>
                                            Control Correlativos</h6>
                                        <p class="text-muted micro-text mb-2">Auditoría de saltos y comprobantes no
                                            utilizados.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('control-correlativos', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('control-correlativos', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. REPORTES DE CAJA Y CONCILIACIÓN -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-success-subtle text-success rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">3. Reportes de Caja y Conciliación</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Auditoría de Caja</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Arqueo de Cierre de Caja -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-calculator me-2 text-success"></i> Cierre de Caja</h6>
                                        <p class="text-muted micro-text mb-2">Comparativo de Ingresos esperados vs.
                                            Efectivo real.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cierre-caja', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cierre-caja', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Faltantes y Sobrantes -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-exclamation-triangle me-2 text-success"></i> Faltantes y
                                            Sobrantes</h6>
                                        <p class="text-muted micro-text mb-2">Descalces detectados en los arqueos de turno.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('faltantes-sobrantes', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('faltantes-sobrantes', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Depósitos a Bancos -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-bank me-2 text-success"></i>
                                            Depósitos y Bancos</h6>
                                        <p class="text-muted micro-text mb-2">Registro de remesas y vouchers depositados a
                                            cuentas.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('depositos-bancos', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('depositos-bancos', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Flujo de Caja -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-arrow-down-up me-2 text-success"></i> Flujo de Caja</h6>
                                        <p class="text-muted micro-text mb-2">Entradas y salidas de efectivo por período.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('flujo-caja', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('flujo-caja', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. REPORTES ESPECÍFICOS DE ENCOMIENDAS -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-box-seam fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">4. Reportes de Encomiendas</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Carga y Envíos</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Encomiendas por Estado -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-truck me-2 text-warning"></i>
                                            Encomiendas por Estado</h6>
                                        <select id="reporte_encomienda_estado"
                                            class="form-select form-select-sm border-0 shadow-sm mb-2">
                                            <option value="">Todos los Estados</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="transito">En Tránsito</option>
                                            <option value="entregada">Entregada</option>
                                            <option value="devuelta">Devuelta</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('encomiendas-estado', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('encomiendas-estado', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Cobros Contra-Entrega -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-cash-coin me-2 text-warning"></i> Cobros Contra-Entrega</h6>
                                        <p class="text-muted micro-text mb-2">Reporte de pagos recaudados al entregar el
                                            paquete.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cobros-contraentrega', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cobros-contraentrega', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Encomiendas Pendientes de Pago -->
                            <div class="col-12">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-clock-history me-2 text-warning"></i> Encomiendas Por Cobrar /
                                            Pendientes</h6>
                                        <p class="text-muted micro-text mb-0">Listado de carga entregada o almacenada sin
                                            liquidar pago.</p>
                                    </div>
                                    <div class="d-flex gap-2 w-100 w-md-auto" style="min-width: 200px;">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('encomiendas-pendientes-pago', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('encomiendas-pendientes-pago', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. REPORTES OPERATIVOS CON IMPACTO CONTABLE -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-danger-subtle text-danger rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-bus-front fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">5. Operativa y Rentabilidad</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Operaciones</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Ocupación por Bus/Ruta -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-pie-chart-fill me-2 text-danger"></i> Ocupación de Buses</h6>
                                        <p class="text-muted micro-text mb-2">% de asientos ocupados para análisis de
                                            rentabilidad.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('ocupacion-bus', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('ocupacion-bus', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Descuentos y Promociones -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-tags me-2 text-danger"></i>
                                            Descuentos Aplicados</h6>
                                        <p class="text-muted micro-text mb-2">Promociones y cortesías que impactan el
                                            ingreso neto.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('descuentos-promociones', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('descuentos-promociones', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Reembolsos y Devoluciones -->
                            <div class="col-12">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-arrow-counterclockwise me-2 text-danger"></i> Reembolsos y
                                            Devoluciones de Pasajes</h6>
                                        <p class="text-muted micro-text mb-0">Control de dinero reintegrado por boletos
                                            cancelados.</p>
                                    </div>
                                    <div class="d-flex gap-2 w-100 w-md-auto" style="min-width: 200px;">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('reembolsos-devoluciones', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('reembolsos-devoluciones', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. REPORTES DE CUENTAS POR COBRAR Y PAGAR -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box bg-dark-subtle text-dark rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 38px; height: 38px;">
                                <i class="bi bi-person-lines-fill fs-5"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 text-dark">6. Cuentas por Cobrar / Pagar y Terceros</h5>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border">Créditos & Terceros</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Cuentas por Cobrar Corporativas -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-building-add me-2 text-dark"></i> Cuentas por Cobrar (Clientes
                                            Corporativos)</h6>
                                        <p class="text-muted micro-text mb-2">Créditos pendientes de cobro para empresas e
                                            instituciones.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cuentas-por-cobrar', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('cuentas-por-cobrar', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagos a Conductores / Alquiler de Unidades -->
                            <div class="col-12 col-md-6">
                                <div
                                    class="p-3 rounded-3 bg-light-subtle border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i
                                                class="bi bi-person-workspace me-2 text-dark"></i> Pagos a Conductores y
                                            Terceros</h6>
                                        <p class="text-muted micro-text mb-2">Liquidación de comisiones, viáticos o
                                            alquiler de buses.</p>
                                    </div>
                                    <div class="d-flex gap-2 pt-2">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('pagos-terceros', 'pdf')"><i
                                                class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm w-100 rounded-2 fw-medium"
                                            onclick="exportarReporte('pagos-terceros', 'excel')"><i
                                                class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT JS PARA MANEJO DINÁMICO DE FECHAS Y EXPORTACIÓN -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                updateDateRange(); // Inicializa las fechas por defecto al cargar (Este Mes)
            });

            // Formatea fecha a YYYY-MM-DD para asignarla a inputs date
            function formatDate(date) {
                const d = new Date(date);
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                const year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }

            // Calcula dinámicamente las fechas desde y hasta según la opción seleccionada
            function updateDateRange() {
                const selectedPeriod = document.querySelector('input[name="period"]:checked')?.value || 'month';
                const dateFromInput = document.getElementById('date_from');
                const dateToInput = document.getElementById('date_to');
                const customContainer = document.getElementById('customDateInputs');

                const now = new Date();
                let fromDate, toDate;

                if (selectedPeriod === 'today') {
                    fromDate = new Date(now);
                    toDate = new Date(now);
                } else if (selectedPeriod === 'week') {
                    const dayOfWeek = now.getDay();
                    const distanceToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Lunes como inicio
                    fromDate = new Date(now);
                    fromDate.setDate(now.getDate() - distanceToMonday);
                    toDate = new Date(now);
                } else if (selectedPeriod === 'month') {
                    fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    toDate = new Date(now.getFullYear(), now.getMonth() + 1, 0); // Último día del mes
                } else if (selectedPeriod === 'year') {
                    fromDate = new Date(now.getFullYear(), 0, 1);
                    toDate = new Date(now.getFullYear(), 11, 31);
                }

                if (selectedPeriod === 'custom') {
                    dateFromInput.removeAttribute('readonly');
                    dateToInput.removeAttribute('readonly');
                    customContainer.style.opacity = '1';
                } else {
                    dateFromInput.value = formatDate(fromDate);
                    dateToInput.value = formatDate(toDate);
                    dateFromInput.setAttribute('readonly', 'true');
                    dateToInput.setAttribute('readonly', 'true');
                    customContainer.style.opacity = '0.85';
                }
            }

            function getGlobalFilters() {
                const period = document.querySelector('input[name="period"]:checked')?.value || 'month';
                const dateFrom = document.getElementById('date_from')?.value || '';
                const dateTo = document.getElementById('date_to')?.value || '';
                return {
                    period,
                    dateFrom,
                    dateTo
                };
            }

            function applyGlobalFilters() {
                const filters = getGlobalFilters();
                alert(`Filtro global aplicado:\nDesde: ${filters.dateFrom}\nHasta: ${filters.dateTo}`);
            }

            // Exportación de reportes generales usando las fechas globales
            function exportarReporte(tipoReporte, formato) {
                const filters = getGlobalFilters();
                let url =
                    `/reportes/exportar?tipo=${tipoReporte}&formato=${formato}&periodo=${filters.period}&desde=${filters.dateFrom}&hasta=${filters.dateTo}`;

                if (tipoReporte === 'ventas-diarias') {
                    const agencia = document.getElementById('reporte_ventas_diarias_agencia')?.value;
                    if (agencia) url += `&agencia_id=${agencia}`;
                }

                if (tipoReporte === 'metodos-pago') {
                    const metodo = document.getElementById('reporte_metodo_pago')?.value;
                    if (metodo) url += `&metodo_pago=${metodo}`;
                }

                if (tipoReporte === 'encomiendas-estado') {
                    const estado = document.getElementById('reporte_encomienda_estado')?.value;
                    if (estado) url += `&estado=${estado}`;
                }

                window.open(url, '_blank');
            }

            // Exportación específica para Cajero
            function exportarVentasUsuario(formato) {
                const usuarioId = document.getElementById('reporte_usuario_id')?.value || '';
                const filters = getGlobalFilters();
                window.open(
                    `/reportes/ventas-usuario?formato=${formato}&usuario_id=${usuarioId}&periodo=${filters.period}&desde=${filters.dateFrom}&hasta=${filters.dateTo}`,
                    '_blank');
            }

            // Exportación específica para Ruta
            function exportarVentasRuta(formato) {
                const rutaId = document.getElementById('reporte_ruta_especifica_id')?.value || '';
                const filters = getGlobalFilters();
                window.open(
                    `/reportes/ventas-ruta?formato=${formato}&ruta_id=${rutaId}&periodo=${filters.period}&desde=${filters.dateFrom}&hasta=${filters.dateTo}`,
                    '_blank');
            }

            function exportarVentasGeneral() {
                const filters = getGlobalFilters();

                const agenciaId =
                    document.getElementById("reporte_general_agencia")?.value || "";

                const params = {
                    periodo: filters.period,
                    desde: filters.dateFrom,
                    hasta: filters.dateTo,
                };

                if (agenciaId) {
                    params.agencia_id = agenciaId;
                }

                window.open(
                    route("reportes.ventas-general.pdf", params),
                    "_blank"
                );
            }
        </script>
    @endpush
@endsection

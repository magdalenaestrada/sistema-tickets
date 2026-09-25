@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 px-md-4 min-vh-100">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-bar-chart-line-fill me-1"></i>
                    Módulo de Reportes
                </span>

                <h4 class="fw-bold text-dark mb-1">
                    Reportes y Consultas
                </h4>

                <p class="text-muted mb-0">
                    Consulta y exporta información de ventas, operaciones, caja y comprobantes.
                </p>
            </div>
        </div>


        {{-- =========================================================
            FILTRO GLOBAL
        ========================================================== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">

            <div class="card-body p-4">

                <form id="filterForm" class="row g-3 align-items-end">

                    {{-- PERÍODO --}}
                    <div class="col-12 col-xl-5">

                        <label class="form-label micro-text fw-bold text-uppercase text-muted tracking-wide mb-2">
                            <i class="bi bi-calendar3 me-1"></i>
                            Período
                        </label>

                        <div class="btn-group w-100 p-1 bg-light rounded-3" role="group">

                            <input type="radio" class="btn-check" name="period" id="period_today" value="today"
                                onchange="updateDateRange()">

                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_today">
                                Hoy
                            </label>


                            <input type="radio" class="btn-check" name="period" id="period_week" value="week"
                                onchange="updateDateRange()">

                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_week">
                                Esta Semana
                            </label>


                            <input type="radio" class="btn-check" name="period" id="period_month" value="month" checked
                                onchange="updateDateRange()">

                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_month">
                                Este Mes
                            </label>


                            <input type="radio" class="btn-check" name="period" id="period_year" value="year"
                                onchange="updateDateRange()">

                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_year">
                                Año
                            </label>


                            <input type="radio" class="btn-check" name="period" id="period_custom" value="custom"
                                onchange="updateDateRange()">

                            <label class="btn btn-sm btn-outline-custom rounded-2 border-0 fw-medium" for="period_custom">
                                Personalizado
                            </label>

                        </div>

                    </div>


                    {{-- FECHAS --}}
                    <div class="col-12 col-md-7 col-xl-4" id="customDateInputs">

                        <label class="form-label micro-text fw-bold text-uppercase text-muted tracking-wide mb-2">
                            <i class="bi bi-calendar-range me-1"></i>
                            Rango de Fechas
                        </label>

                        <div class="input-group input-group-sm">

                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-calendar-event text-muted"></i>
                            </span>

                            <input type="date" class="form-control border-start-0" name="date_from" id="date_from">

                            <span class="input-group-text bg-light text-muted fw-bold">
                                a
                            </span>

                            <input type="date" class="form-control border-start-0" name="date_to" id="date_to">

                        </div>

                    </div>


                    {{-- BOTÓN --}}
                    <div class="col-12 col-md-5 col-xl-3 ms-auto">

                        <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold py-2 rounded-3 shadow-sm"
                            onclick="applyGlobalFilters()">

                            <i class="bi bi-funnel-fill me-1"></i>
                            Aplicar Filtro Global

                        </button>

                    </div>

                </form>

            </div>
        </div>


        {{-- =========================================================
            GRID
        ========================================================== --}}
        <div class="row g-4">


            {{-- =====================================================
                1. VENTAS
            ====================================================== --}}
            <div class="col-12">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-2">

                                <div class="icon-box bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center"
                                    style="width:38px;height:38px">

                                    <i class="bi bi-cash-stack fs-5"></i>

                                </div>

                                <div>
                                    <h5 class="card-title fw-bold mb-0">
                                        1. Reportes de Ventas
                                    </h5>

                                    <small class="text-muted">
                                        Ventas, servicios y desempeño comercial
                                    </small>
                                </div>

                            </div>

                            <span class="badge bg-light text-muted fw-normal border">
                                Ventas
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">


                            {{-- GENERAL --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                                            Reporte General de Ventas
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Consolidado general de pasajes, encomiendas y sobreequipajes.
                                        </p>

                                        <select id="reporte_general_sucursal"
                                            class="form-select form-select-sm border-0 shadow-sm">

                                            <option value="">
                                                Todas las Sucursales
                                            </option>

                                            @foreach ($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id }}">
                                                    {{ $sucursal->nombre_comercial }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-2"
                                            onclick="exportarVentasGeneral('pdf')">

                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF

                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-2"
                                            onclick="exportarVentasGeneral('excel')">

                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel

                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- PASAJES --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                                            Venta de Pasajes
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Detalle de boletos, pasajeros, rutas, asientos y viajes.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('venta-pasajes','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('venta-pasajes','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- ENCOMIENDAS --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-box-seam me-2 text-primary"></i>
                                            Venta de Encomiendas
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Remitentes, destinatarios, bultos, peso, rutas e importes.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('venta-encomiendas','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('venta-encomiendas','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- SOBREEQUIPAJE --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-luggage me-2 text-primary"></i>
                                            Venta de Sobreequipaje
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Sobreequipaje relacionado al boleto, pasajero y viaje.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('venta-sobreequipaje','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('venta-sobreequipaje','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- RUTA --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-signpost-2 me-2 text-primary"></i>
                                            Ventas por Ruta
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Resumen de operaciones e ingresos por origen y destino.
                                        </p>

                                        <select id="reporte_ruta_id"
                                            class="form-select form-select-sm border-0 shadow-sm">

                                            <option value="">
                                                Todas las Rutas
                                            </option>

                                            @foreach ($rutas as $ruta)
                                                <option value="{{ $ruta->id }}">
                                                    {{ $ruta->nombre ?? $ruta->descripcion }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

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


                            {{-- VENDEDOR --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-person-badge me-2 text-primary"></i>
                                            Ventas por Vendedor
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Ventas, operaciones, anulaciones y total neto por vendedor.
                                        </p>

                                        <select id="reporte_vendedor_id"
                                            class="form-select form-select-sm border-0 shadow-sm">

                                            <option value="">
                                                Todos los Vendedores
                                            </option>

                                            @foreach ($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}">
                                                    {{ $usuario->persona->nombre_completo ?? $usuario->name }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

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

                            </div>


                            {{-- SUCURSAL --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-building me-2 text-primary"></i>
                                            Ventas por Sucursal
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Resumen de ventas y operaciones por agencia.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('ventas-sucursal','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('ventas-sucursal','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                2. OPERACIONES Y VIAJES
            ====================================================== --}}
            <div class="col-12 col-xl-6">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-2">

                                <div class="icon-box bg-danger-subtle text-danger rounded-3 p-2"
                                    style="width:38px;height:38px">

                                    <i class="bi bi-bus-front fs-5"></i>

                                </div>

                                <h5 class="fw-bold mb-0">
                                    2. Operaciones y Viajes
                                </h5>

                            </div>

                            <span class="badge bg-light text-muted border">
                                Operaciones
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">

                            <div class="col-12">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-bus-front-fill me-2 text-danger"></i>
                                            Salidas y Liquidación por Viaje
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Relaciona cada salida con sus pasajes,
                                            sobreequipajes, encomiendas y ventas totales.
                                        </p>

                                        <div class="row g-2">

                                            <div class="col-md-6">

                                                <select id="reporte_salida_estado" class="form-select form-select-sm">

                                                    <option value="">
                                                        Todos los Estados
                                                    </option>

                                                    <option value="programado">
                                                        Programado
                                                    </option>

                                                    <option value="en_ruta">
                                                        En Ruta
                                                    </option>

                                                    <option value="finalizado">
                                                        Finalizado
                                                    </option>

                                                    <option value="cancelado">
                                                        Cancelado
                                                    </option>

                                                </select>

                                            </div>

                                            <div class="col-md-6">

                                                <select id="reporte_salida_ruta" class="form-select form-select-sm">

                                                    <option value="">
                                                        Todas las Rutas
                                                    </option>

                                                    @foreach ($rutas as $ruta)
                                                        <option value="{{ $ruta->id }}">
                                                            {{ $ruta->nombre ?? $ruta->descripcion }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('salidas-liquidacion','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('salidas-liquidacion','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                3. CAJA Y RECAUDACIÓN
            ====================================================== --}}
            <div class="col-12 col-xl-6">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-2">

                                <div class="icon-box bg-success-subtle text-success rounded-3 p-2"
                                    style="width:38px;height:38px">

                                    <i class="bi bi-wallet2 fs-5"></i>

                                </div>

                                <h5 class="fw-bold mb-0">
                                    3. Caja y Recaudación
                                </h5>

                            </div>

                            <span class="badge bg-light text-muted border">
                                Caja
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">


                            {{-- CAJA VENDEDOR --}}
                            <div class="col-12 col-md-6">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-person-check me-2 text-success"></i>
                                            Cuadre de Caja por Vendedor
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Ventas, anulaciones, devoluciones,
                                            declarado, esperado y diferencias.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('cuadre-caja-vendedor','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('cuadre-caja-vendedor','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- CAJA SUCURSAL --}}
                            <div class="col-12 col-md-6">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-building-check me-2 text-success"></i>
                                            Cuadre de Caja por Sucursal
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Consolidado de caja de todos los vendedores
                                            de cada sucursal.
                                        </p>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('cuadre-caja-sucursal','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('cuadre-caja-sucursal','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- MEDIO DE PAGO --}}
                            <div class="col-12">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-credit-card me-2 text-success"></i>
                                            Recaudación por Medio de Pago
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Efectivo, tarjeta, transferencia,
                                            Yape, Plin y otros medios registrados.
                                        </p>

                                        <select id="reporte_medio_pago" class="form-select form-select-sm">

                                            <option value="">
                                                Todos los Medios de Pago
                                            </option>

                                            <option value="efectivo">
                                                Efectivo
                                            </option>

                                            <option value="tarjeta">
                                                Tarjeta / POS
                                            </option>

                                            <option value="transferencia">
                                                Transferencia
                                            </option>

                                            <option value="billetera">
                                                Yape / Plin
                                            </option>

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('recaudacion-medio-pago','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('recaudacion-medio-pago','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                4. CONTROL DE OPERACIONES
            ====================================================== --}}
            <div class="col-12 col-xl-6">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-2">

                                <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 p-2"
                                    style="width:38px;height:38px">

                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>

                                </div>

                                <h5 class="fw-bold mb-0">
                                    4. Control de Operaciones
                                </h5>

                            </div>

                            <span class="badge bg-light text-muted border">
                                Auditoría
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">

                            <div class="col-12">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-file-earmark-x me-2 text-warning"></i>
                                            Anulaciones y Devoluciones
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Control de operaciones anuladas,
                                            importes devueltos, motivos y usuarios responsables.
                                        </p>

                                        <select id="reporte_anulacion_servicio" class="form-select form-select-sm">

                                            <option value="">
                                                Todos los Servicios
                                            </option>

                                            <option value="pasaje">
                                                Pasajes
                                            </option>

                                            <option value="encomienda">
                                                Encomiendas
                                            </option>

                                            <option value="sobreequipaje">
                                                Sobreequipaje
                                            </option>

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('anulaciones-devoluciones','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('anulaciones-devoluciones','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                5. COMPROBANTES
            ====================================================== --}}
            <div class="col-12 col-xl-6">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-2">

                                <div class="icon-box bg-info-subtle text-info rounded-3 p-2"
                                    style="width:38px;height:38px">

                                    <i class="bi bi-receipt fs-5"></i>

                                </div>

                                <h5 class="fw-bold mb-0">
                                    5. Comprobantes de Venta
                                </h5>

                            </div>

                            <span class="badge bg-light text-muted border">
                                Tributario
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">

                            <div class="col-12">

                                <div class="report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-journal-check me-2 text-info"></i>
                                            Reporte de Comprobantes de Venta
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Facturas, boletas y demás comprobantes
                                            emitidos durante el período seleccionado.
                                        </p>

                                        <select id="reporte_tipo_comprobante" class="form-select form-select-sm">

                                            <option value="">
                                                Todos los Comprobantes
                                            </option>

                                            <option value="01">
                                                Factura
                                            </option>

                                            <option value="03">
                                                Boleta
                                            </option>

                                        </select>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarReporte('comprobantes-venta','pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarReporte('comprobantes-venta','excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
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


    {{-- =============================================================
        ESTILOS
    ============================================================== --}}
    @push('styles')
        <style>
            .report-card {
                padding: 1rem;
                border-radius: .75rem;
                background: var(--bs-light-bg-subtle, #f8f9fa);
                border: 1px solid var(--bs-border-color);
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .report-card h6 {
                line-height: 1.4;
            }

            .report-card .form-select {
                background-color: #fff;
            }

            .report-card button {
                font-weight: 500;
            }
        </style>
    @endpush


    {{-- =============================================================
        JAVASCRIPT
    ============================================================== --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                updateDateRange();
            });


            /*
            |--------------------------------------------------------------------------
            | FECHAS
            |--------------------------------------------------------------------------
            */

            function formatDate(date) {

                const d = new Date(date);

                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();

                const year = d.getFullYear();

                if (month.length < 2) {
                    month = '0' + month;
                }

                if (day.length < 2) {
                    day = '0' + day;
                }

                return [year, month, day].join('-');
            }


            function updateDateRange() {

                const selectedPeriod =
                    document.querySelector('input[name="period"]:checked')?.value || 'month';

                const dateFromInput =
                    document.getElementById('date_from');

                const dateToInput =
                    document.getElementById('date_to');

                const customContainer =
                    document.getElementById('customDateInputs');


                const now = new Date();

                let fromDate;
                let toDate;


                if (selectedPeriod === 'today') {

                    fromDate = new Date(now);
                    toDate = new Date(now);

                } else if (selectedPeriod === 'week') {

                    const dayOfWeek = now.getDay();

                    const distanceToMonday =
                        dayOfWeek === 0 ? 6 : dayOfWeek - 1;

                    fromDate = new Date(now);

                    fromDate.setDate(
                        now.getDate() - distanceToMonday
                    );

                    toDate = new Date(now);

                } else if (selectedPeriod === 'month') {

                    fromDate =
                        new Date(
                            now.getFullYear(),
                            now.getMonth(),
                            1
                        );

                    toDate =
                        new Date(
                            now.getFullYear(),
                            now.getMonth() + 1,
                            0
                        );

                } else if (selectedPeriod === 'year') {

                    fromDate =
                        new Date(
                            now.getFullYear(),
                            0,
                            1
                        );

                    toDate =
                        new Date(
                            now.getFullYear(),
                            11,
                            31
                        );

                }


                if (selectedPeriod === 'custom') {

                    dateFromInput.removeAttribute('readonly');
                    dateToInput.removeAttribute('readonly');

                    customContainer.style.opacity = '1';

                } else {

                    dateFromInput.value =
                        formatDate(fromDate);

                    dateToInput.value =
                        formatDate(toDate);

                    dateFromInput.setAttribute(
                        'readonly',
                        'true'
                    );

                    dateToInput.setAttribute(
                        'readonly',
                        'true'
                    );

                    customContainer.style.opacity = '0.85';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | FILTROS
            |--------------------------------------------------------------------------
            */

            function getGlobalFilters() {

                const period =
                    document.querySelector(
                        'input[name="period"]:checked'
                    )?.value || 'month';

                const dateFrom =
                    document.getElementById('date_from')?.value || '';

                const dateTo =
                    document.getElementById('date_to')?.value || '';


                return {
                    period: period,
                    dateFrom: dateFrom,
                    dateTo: dateTo
                };

            }


            function applyGlobalFilters() {

                const filters =
                    getGlobalFilters();

                console.log(
                    'Filtros globales:',
                    filters
                );

                alert(
                    `Filtro global aplicado:\n\n` +
                    `Desde: ${filters.dateFrom}\n` +
                    `Hasta: ${filters.dateTo}`
                );

            }


            /*
            |--------------------------------------------------------------------------
            | EXPORTACIÓN GENERAL
            |--------------------------------------------------------------------------
            */

            function exportarReporte(tipoReporte, formato) {

                const filters =
                    getGlobalFilters();


                const rutas = {

                    'venta-pasajes': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'venta-encomiendas': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'venta-sobreequipaje': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'ventas-sucursal': {
                        pdf: "{{ route('reportes.ventas.sucursal.pdf') }}",
                        excel: "{{ route('reportes.ventas.sucursal.pdf') }}" 
                    },

                    'salidas-liquidacion': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'cuadre-caja-vendedor': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'cuadre-caja-sucursal': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'recaudacion-medio-pago': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'anulaciones-devoluciones': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    },

                    'comprobantes-venta': {
                        pdf: "{{ route('reportes.historial.pasajero.pdf') }}",
                        excel: "{{ route('reportes.historial.pasajero.pdf') }}"
                    }

                };


                if (
                    !rutas[tipoReporte] ||
                    !rutas[tipoReporte][formato]
                ) {

                    alert(
                        `El reporte "${tipoReporte}" todavía no tiene una ruta configurada.`
                    );

                    return;

                }


                const params =
                    new URLSearchParams({

                        periodo: filters.period,

                        desde: filters.dateFrom,

                        hasta: filters.dateTo

                    });


                /*
                 * Filtros específicos
                 */

                if (tipoReporte === 'venta-sobreequipaje') {

                    const servicio =
                        document.getElementById(
                            'reporte_sobreequipaje_servicio'
                        )?.value || '';

                    if (servicio) {
                        params.append(
                            'servicio',
                            servicio
                        );
                    }

                }


                if (tipoReporte === 'salidas-liquidacion') {

                    const estado =
                        document.getElementById(
                            'reporte_salida_estado'
                        )?.value || '';

                    const ruta =
                        document.getElementById(
                            'reporte_salida_ruta'
                        )?.value || '';

                    if (estado) {
                        params.append(
                            'estado',
                            estado
                        );
                    }

                    if (ruta) {
                        params.append(
                            'ruta_id',
                            ruta
                        );
                    }

                }


                if (tipoReporte === 'recaudacion-medio-pago') {

                    const medio =
                        document.getElementById(
                            'reporte_medio_pago'
                        )?.value || '';

                    if (medio) {
                        params.append(
                            'medio_pago',
                            medio
                        );
                    }

                }


                if (tipoReporte === 'anulaciones-devoluciones') {

                    const servicio =
                        document.getElementById(
                            'reporte_anulacion_servicio'
                        )?.value || '';

                    if (servicio) {
                        params.append(
                            'tipo_servicio',
                            servicio
                        );
                    }

                }


                if (tipoReporte === 'comprobantes-venta') {

                    const tipo =
                        document.getElementById(
                            'reporte_tipo_comprobante'
                        )?.value || '';

                    if (tipo) {
                        params.append(
                            'tipo_comprobante',
                            tipo
                        );
                    }

                }


                window.open(
                    `${rutas[tipoReporte][formato]}?${params.toString()}`,
                    '_blank'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | VENTAS GENERAL
            |--------------------------------------------------------------------------
            */

            function exportarVentasGeneral(formato) {

                const filters =
                    getGlobalFilters();

                const sucursalId =
                    document.getElementById(
                        'reporte_general_sucursal'
                    )?.value || '';


                const url =
                    formato === 'pdf' ?
                    "{{ route('reportes.ventas.general.pdf') }}" :
                    "{{ route('reportes.ventas.general.excel') }}";


                const params =
                    new URLSearchParams({

                        periodo: filters.period,

                        desde: filters.dateFrom,

                        hasta: filters.dateTo

                    });


                if (sucursalId) {

                    params.append(
                        'sucursal_id',
                        sucursalId
                    );

                }


                window.open(
                    `${url}?${params.toString()}`,
                    '_blank'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | VENTAS POR VENDEDOR
            |--------------------------------------------------------------------------
            */

            function exportarVentasUsuario(formato) {

                const usuarioId =
                    document.getElementById(
                        'reporte_vendedor_id'
                    )?.value || '';


                const filters =
                    getGlobalFilters();


                const url =
                    formato === 'pdf' ?
                    "{{ route('reportes.ventas.usuario.pdf') }}" :
                    "{{ route('reportes.ventas.usuario.excel') }}";


                const params =
                    new URLSearchParams({

                        usuario_id: usuarioId,

                        periodo: filters.period,

                        desde: filters.dateFrom,

                        hasta: filters.dateTo

                    });


                window.open(
                    `${url}?${params.toString()}`,
                    '_blank'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | VENTAS POR RUTA
            |--------------------------------------------------------------------------
            */

            function exportarVentasRuta(formato) {

                const rutaId =
                    document.getElementById(
                        'reporte_ruta_id'
                    )?.value || '';


                const filters =
                    getGlobalFilters();


                const url =
                    formato === 'pdf' ?
                    "{{ route('reportes.ventas.ruta.pdf') }}" :
                    "{{ route('reportes.ventas.ruta.excel') }}";


                const params =
                    new URLSearchParams({

                        ruta_id: rutaId,

                        periodo: filters.period,

                        desde: filters.dateFrom,

                        hasta: filters.dateTo

                    });


                window.open(
                    `${url}?${params.toString()}`,
                    '_blank'
                );

            }
        </script>
    @endpush
@endsection

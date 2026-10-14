@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 px-md-4 min-vh-100 ed-reportes">

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
            BUSCADOR ÚNICO PARA HISTORIALES PERSONALES
            No modifica los filtros de reportes generales, caja o administración.
        ========================================================== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white ed-person-search">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="ed-person-icon"><i class="bi bi-person-vcard"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Buscar historial de una persona</h6>
                        <p class="text-muted small mb-0">Escribe una sola vez el DNI para filtrar los reportes de Pasajes, Encomiendas y Sobreequipajes.</p>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5 col-xl-4">
                        <label class="form-label fw-semibold small" for="reporte_persona_dni">DNI (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-person-badge text-muted"></i></span>
                            <input type="text" id="reporte_persona_dni" class="form-control"
                                inputmode="numeric" maxlength="8" pattern="[0-9]{8}"
                                placeholder="Ej.: 01234567" autocomplete="off"
                                aria-describedby="reporte_persona_ayuda"
                                oninput="reiniciarBusquedaPersonaGlobal()"
                                onkeydown="if (event.key === 'Enter') { event.preventDefault(); buscarPersonaGlobal(); }">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-xl-5">
                        <label class="form-label fw-semibold small" for="reporte_persona_nombre">Persona encontrada</label>
                        <input type="text" id="reporte_persona_nombre" class="form-control bg-light"
                            readonly placeholder="Todas las personas" aria-live="polite">
                    </div>
                    <div class="col-12 col-md-3 col-xl-3">
                        <div class="d-flex gap-2">
                            <button type="button" id="btnBuscarPersonaGlobal" class="btn btn-primary flex-fill"
                                onclick="buscarPersonaGlobal()"><i class="bi bi-search me-1"></i> Buscar</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="limpiarBusquedaPersonaGlobal()" title="Mostrar todos" aria-label="Limpiar DNI">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                    <div id="reporte_persona_ayuda" class="form-text mt-0">
                        Sin DNI, se exportan todos los registros del período. Si ingresas uno, pulsa Buscar para confirmar el nombre.
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ed-person-tag"><i class="bi bi-ticket-perforated"></i> Pasajes</span>
                        <span class="ed-person-tag"><i class="bi bi-box-seam"></i> Encomiendas</span>
                        <span class="ed-person-tag"><i class="bi bi-luggage"></i> Sobreequipajes</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navegación visual: no modifica los filtros ni las exportaciones --}}
        <div class="ed-overview row g-3 mb-4">
            <div class="col-6 col-xl-3"><div class="ed-stat"><span class="ed-stat-icon purple"><i class="bi bi-grid"></i></span><div><small>REPORTES DISPONIBLES</small><strong id="edTotalReportes">13</strong><span>En el catálogo</span></div></div></div>
            <div class="col-6 col-xl-3"><div class="ed-stat"><span class="ed-stat-icon green"><i class="bi bi-collection"></i></span><div><small>CATEGORÍAS</small><strong>5</strong><span>Áreas de consulta</span></div></div></div>
            <div class="col-6 col-xl-3"><div class="ed-stat"><span class="ed-stat-icon blue"><i class="bi bi-file-earmark-pdf"></i></span><div><small>EXPORTACIÓN PDF</small><strong>PDF</strong><span>Documentos descargables</span></div></div></div>
            <div class="col-6 col-xl-3"><div class="ed-stat"><span class="ed-stat-icon amber"><i class="bi bi-file-earmark-excel"></i></span><div><small>EXPORTACIÓN EXCEL</small><strong>XLS</strong><span>Archivos de análisis</span></div></div></div>
        </div>
        <div class="ed-toolbar d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-3">
            <div><h5 class="fw-bold mb-1">Catálogo de reportes</h5><p class="text-muted mb-0 small">Encuentra tus reportes por nombre o área.</p></div>
            <div class="ed-search"><i class="bi bi-search"></i><input id="edBuscarReporte" type="search" class="form-control" placeholder="Buscar reporte por nombre o tema…" aria-label="Buscar reportes"></div>
        </div>
        <div class="ed-category-nav mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><strong>Explorar por área</strong><div class="text-muted small">Selecciona una categoría para ver sus reportes.</div></div></div>
            <div class="ed-category-pills" id="edCategoryPills">
                <button type="button" class="ed-pill active" data-category="all"><i class="bi bi-grid"></i> Todos</button>
                <button type="button" class="ed-pill" data-category="ventas"><i class="bi bi-cash-stack"></i> Ventas</button>
                <button type="button" class="ed-pill" data-category="operaciones"><i class="bi bi-bus-front"></i> Operaciones</button>
                <button type="button" class="ed-pill" data-category="caja"><i class="bi bi-wallet2"></i> Caja</button>
                <button type="button" class="ed-pill" data-category="control"><i class="bi bi-shield-check"></i> Control</button>
                <button type="button" class="ed-pill" data-category="comprobantes"><i class="bi bi-receipt"></i> Comprobantes</button>
            </div>
        </div>
        <div id="edSinResultados" class="ed-empty mb-3" hidden>No encontramos reportes con esos criterios.</div>

        {{-- =========================================================
            GRID
        ========================================================== --}}
        <div class="row g-4">


            {{-- =====================================================
                1. VENTAS
            ====================================================== --}}
            <div class="col-12 ed-report-section" data-ed-category="ventas">

                <div class="card border-0 shadow-sm rounded-4 ed-category-card">

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

                                <div class="report-card ed-report-card">

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



                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="report-card ed-report-card">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                                            Venta de Pasajes
                                        </h6>
                                        <p class="text-muted micro-text mb-3">
                                            Boletos, pasajeros, rutas, asientos e importes del período seleccionado.
                                        </p>

                                        <div class="ed-linked-filter">
                                            <i class="bi bi-person-check me-1"></i>
                                            Usa el DNI del buscador general.
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 pt-3">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarVentaPasajes('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarVentaPasajes('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>



                            {{-- ENCOMIENDAS --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card ed-report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-box-seam me-2 text-primary"></i>
                                            Venta de Encomiendas
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Remitentes, destinatarios, bultos, peso, rutas e importes.
                                        </p>

                                        {{-- DNI opcional: puede coincidir con emisor o receptor --}}
                                        <label for="reporte_encomiendas_tipo_persona" class="form-label small fw-semibold">
                                            Buscar como
                                        </label>
                                        <select id="reporte_encomiendas_tipo_persona"
                                            class="form-select form-select-sm mb-2">
                                            <option value="ambos">Emisor o receptor</option>
                                            <option value="emisor">Solo emisor</option>
                                            <option value="receptor">Solo receptor</option>
                                        </select>

                                        <div class="ed-linked-filter mt-2">
                                            <i class="bi bi-person-check me-1"></i>
                                            Usa el DNI del buscador general.
                                        </div>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarVentaEncomiendas('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarVentaEncomiendas('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- SOBREEQUIPAJE --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card ed-report-card">

                                    <div>

                                        <h6 class="fw-bold text-dark mb-2">
                                            <i class="bi bi-luggage me-2 text-primary"></i>
                                            Venta de Sobreequipaje
                                        </h6>

                                        <p class="text-muted micro-text mb-3">
                                            Sobreequipaje relacionado al boleto, pasajero y viaje.
                                        </p>
                                        <div class="ed-linked-filter">
                                            <i class="bi bi-person-check me-1"></i>
                                            Filtra por DNI del pasajero desde el buscador general.
                                        </div>

                                    </div>

                                    <div class="d-flex gap-2 pt-3">

                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="exportarVentaSobreequipaje('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>

                                        <button type="button" class="btn btn-outline-success btn-sm w-100"
                                            onclick="exportarVentaSobreequipaje('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- RUTA --}}
                            <div class="col-12 col-md-6 col-lg-4">

                                <div class="report-card ed-report-card">

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

                                <div class="report-card ed-report-card">

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

                                <div class="report-card ed-report-card">

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
            <div class="col-12 col-xl-6 ed-report-section" data-ed-category="operaciones">

                <div class="card border-0 shadow-sm rounded-4 h-100 ed-category-card">

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

                                <div class="report-card ed-report-card">

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
            <div class="col-12 col-xl-6 ed-report-section" data-ed-category="caja">

                <div class="card border-0 shadow-sm rounded-4 h-100 ed-category-card">

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

                                <div class="report-card ed-report-card">

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

                                <div class="report-card ed-report-card">

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

                                <div class="report-card ed-report-card">

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
            <div class="col-12 col-xl-6 ed-report-section" data-ed-category="control">

                <div class="card border-0 shadow-sm rounded-4 h-100 ed-category-card">

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

                                <div class="report-card ed-report-card">

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
            <div class="col-12 col-xl-6 ed-report-section" data-ed-category="comprobantes">

                <div class="card border-0 shadow-sm rounded-4 h-100 ed-category-card">

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

                                <div class="report-card ed-report-card">

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
            .ed-reportes{--ed-primary:#625cff;--ed-border:#e6ebf2;--ed-muted:#738096;--ed-shadow:0 3px 12px rgba(30,45,75,.055);color:#182230;font-size:14px}
            .ed-reportes .ed-category-card,.ed-reportes .ed-category-nav,.ed-reportes .ed-stat{background:#fff;border:1px solid #edf0f5!important;border-radius:16px!important;box-shadow:var(--ed-shadow)!important}
            .ed-reportes .ed-category-card{overflow:hidden}
            .ed-reportes .ed-category-card>.card-header{padding:20px 20px 5px!important}
            .ed-reportes .ed-category-card>.card-body{padding:16px 20px 20px!important}
            .ed-reportes .ed-category-card h5{font-size:15px;font-weight:800;letter-spacing:-.3px}
            .ed-reportes .ed-category-card>.card-header .badge{border:0!important;background:#f0f3f7!important;color:#6f7b8f!important;padding:7px 10px;border-radius:8px;font-size:10px}
            .ed-reportes .ed-category-card .icon-box{border-radius:12px!important;width:38px!important;height:38px!important;display:grid!important;place-items:center}
            .ed-reportes .bg-primary-subtle{background:#e8e8ff!important}.ed-reportes .text-primary{color:#625cff!important}
            .ed-reportes .report-card{padding:17px;border:1px solid var(--ed-border);border-radius:13px;background:linear-gradient(160deg,#fff,#fcfcfe);height:100%;display:flex;flex-direction:column;justify-content:space-between;gap:12px;transition:border-color .15s,box-shadow .15s,transform .15s}
            .ed-reportes .report-card:hover{border-color:#d8d8ff;box-shadow:0 8px 20px rgba(55,62,109,.07);transform:translateY(-1px)}
            .ed-reportes .report-card h6{font-size:13px;font-weight:800!important;line-height:1.4;color:#182230!important;margin-bottom:7px!important}
            .ed-reportes .report-card p{font-size:11px;color:var(--ed-muted)!important;line-height:1.55}
            .ed-reportes .report-card .form-select,.ed-reportes .report-card .form-control{background-color:#fff!important;border:1px solid var(--ed-border)!important;border-radius:8px!important;min-height:35px;font-size:12px;box-shadow:none!important}
            .ed-reportes .report-card .form-select:focus,.ed-reportes .report-card .form-control:focus{border-color:var(--ed-primary)!important;box-shadow:0 0 0 3px #625cff16!important}
            .ed-reportes .report-card .form-label{font-size:11px;color:#72809a}
            .ed-reportes .report-card .form-text{font-size:10px;color:#8a97aa}
            .ed-reportes .report-card button{font-weight:700;font-size:11px;min-height:35px;border-radius:8px}
            .ed-reportes .report-card .btn-outline-danger{color:#ff3864;border-color:#ff3864}.ed-reportes .report-card .btn-outline-danger:hover{background:#ff3864;color:white}
            .ed-reportes .report-card .btn-outline-success{color:#00a85a;border-color:#00a85a}.ed-reportes .report-card .btn-outline-success:hover{background:#00a85a;color:white}
            .ed-reportes #filterForm .btn-primary{background:var(--ed-primary);border-color:var(--ed-primary);min-height:39px}
            .ed-reportes #filterForm .btn-primary:hover{background:#4e49e8}
            .ed-reportes #filterForm .btn-check:checked+.btn-outline-custom{background:var(--ed-primary);color:#fff;box-shadow:0 3px 7px #625cff30}
            .ed-reportes #filterForm .btn-outline-custom{color:var(--ed-primary);border:1px solid #c9c7ff!important;white-space:normal;font-size:11px;padding:9px 7px}
            .ed-reportes #filterForm .btn-check:checked+.btn-outline-custom{border-color:var(--ed-primary)!important}
            .ed-reportes #filterForm .btn-group{background:#fff!important;gap:0;padding:0!important}
            .ed-reportes #filterForm .form-label{font-size:11px;letter-spacing:.5px}
            .ed-reportes #filterForm .form-control,.ed-reportes #filterForm .input-group-text{min-height:39px;font-size:12px}
            .ed-reportes .ed-stat{padding:15px 17px;display:flex;gap:12px;align-items:center;min-height:92px}
            .ed-reportes .ed-stat-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:11px;font-size:18px}
            .ed-reportes .ed-stat-icon.purple{color:#625cff;background:#e8e8ff}.ed-reportes .ed-stat-icon.green{color:#00a85a;background:#e1f8ec}.ed-reportes .ed-stat-icon.blue{color:#2670ff;background:#e4efff}.ed-reportes .ed-stat-icon.amber{color:#b77e00;background:#fff2d2}
            .ed-reportes .ed-stat small,.ed-reportes .ed-stat span:not(.ed-stat-icon){display:block;color:#738096;font-size:10px;font-weight:650}
            .ed-reportes .ed-stat strong{display:block;font-size:22px;line-height:1.25;font-weight:800}
            .ed-reportes .ed-toolbar h5{font-size:17px}.ed-reportes .ed-search{position:relative;width:min(330px,100%)}
            .ed-reportes .ed-search i{position:absolute;left:13px;top:11px;color:#91a0b4}.ed-reportes .ed-search input{border-radius:10px;border:1px solid var(--ed-border);padding:10px 12px 10px 38px;font-size:12px}
            .ed-reportes .ed-search input:focus{border-color:var(--ed-primary);box-shadow:0 0 0 3px #625cff16}
            .ed-reportes .ed-category-nav{padding:15px 16px}.ed-reportes .ed-category-nav strong{font-size:12px}.ed-reportes .ed-category-pills{display:flex;gap:7px;flex-wrap:wrap}
            .ed-reportes .ed-pill{border:1px solid var(--ed-border);border-radius:9px;background:#fff;color:#526078;padding:9px 12px;font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:8px}
            .ed-reportes .ed-pill:hover{border-color:#c9c7ff;color:#625cff}.ed-reportes .ed-pill.active{background:#625cff;border-color:#625cff;color:#fff;box-shadow:0 3px 8px #625cff25}
            .ed-reportes .ed-empty{padding:25px;text-align:center;color:#738096;background:#fff;border:1px dashed var(--ed-border);border-radius:12px}
            .ed-reportes .ed-person-search{border:1px solid #e7e5ff!important}
            .ed-reportes .ed-person-search .card-body{background:linear-gradient(130deg,#fff 72%,#f8f7ff)}
            .ed-reportes .ed-person-icon{flex:0 0 42px;width:42px;height:42px;display:grid;place-items:center;border-radius:12px;color:#625cff;background:#e8e8ff;font-size:20px}
            .ed-reportes .ed-person-search .form-control,.ed-reportes .ed-person-search .input-group-text{border-color:#e6ebf2;min-height:40px;font-size:13px}
            .ed-reportes .ed-person-search #btnBuscarPersonaGlobal{background:#625cff;border-color:#625cff;min-height:40px}
            .ed-reportes .ed-person-search #btnBuscarPersonaGlobal:hover{background:#4e49e8}
            .ed-reportes .ed-person-tag{font-size:11px;color:#635ed4;border-radius:8px;background:#f0efff;padding:5px 9px;display:inline-flex;gap:6px;align-items:center}
            .ed-reportes .ed-linked-filter{font-size:11px;color:#66748b;background:#f7f8fc;border:1px dashed #e2e6f1;border-radius:8px;padding:9px 10px}
            .ed-reportes [hidden]{display:none!important}
            @media(max-width:767px){.ed-reportes .ed-category-card>.card-body{padding:14px!important}.ed-reportes .ed-category-card>.card-header{padding:16px 14px 5px!important}.ed-reportes .ed-stat{padding:11px;gap:8px}.ed-reportes .ed-stat strong{font-size:19px}.ed-reportes .ed-stat-icon{flex-basis:32px;height:32px}.ed-reportes .ed-search{width:100%}.ed-reportes #filterForm .btn-group{flex-wrap:wrap}.ed-reportes #filterForm .btn-group label{flex:1 0 30%}}
        </style>
    @endpush


    {{-- =============================================================
        JAVASCRIPT
    ============================================================== --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                updateDateRange();
                // Navegación y búsqueda únicamente visuales sobre los reportes existentes.
                const buscador = document.getElementById('edBuscarReporte');
                const botones = document.querySelectorAll('#edCategoryPills [data-category]');
                const secciones = document.querySelectorAll('.ed-report-section');
                const tarjetas = document.querySelectorAll('.ed-report-section .report-card');
                const totalReportes = document.getElementById('edTotalReportes');
                if (totalReportes) totalReportes.textContent = String(tarjetas.length);
                let categoriaActiva = 'all';
                function filtrarCatalogo() {
                    const termino = (buscador?.value || '').trim().toLocaleLowerCase('es');
                    let visibles = 0;
                    secciones.forEach(seccion => {
                        const categoriaVisible = categoriaActiva === 'all' || seccion.dataset.edCategory === categoriaActiva;
                        let cantidad = 0;
                        seccion.querySelectorAll('.report-card').forEach(tarjeta => {
                            const coincide = tarjeta.textContent.toLocaleLowerCase('es').includes(termino);
                            const mostrar = categoriaVisible && coincide;
                            tarjeta.closest('[class*="col-"]')?.classList.toggle('d-none', !mostrar);
                            if (mostrar) { cantidad++; visibles++; }
                        });
                        seccion.classList.toggle('d-none', cantidad === 0);
                    });
                    const sinResultados = document.getElementById('edSinResultados');
                    if (sinResultados) sinResultados.hidden = visibles !== 0;
                }
                botones.forEach(boton => boton.addEventListener('click', () => {
                    categoriaActiva = boton.dataset.category;
                    botones.forEach(item => item.classList.toggle('active', item === boton));
                    filtrarCatalogo();
                }));
                buscador?.addEventListener('input', filtrarCatalogo);

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

            /* ===============================================================
             * FILTRO COMPARTIDO DE PERSONAS (PASAJES, ENCOMIENDAS Y SOBREEQUIPAJES)
             * No se agrega a Caja, Ventas generales ni otros reportes.
             * =============================================================== */
            let solicitudPersonaGlobal = null;
            let versionBusquedaPersonaGlobal = 0;
            let dniGlobalConfirmado = '';

            function reiniciarBusquedaPersonaGlobal() {
                versionBusquedaPersonaGlobal++;
                dniGlobalConfirmado = '';

                if (solicitudPersonaGlobal) {
                    solicitudPersonaGlobal.abort();
                    solicitudPersonaGlobal = null;
                }

                const dni = (document.getElementById('reporte_persona_dni')?.value || '').trim();
                const nombre = document.getElementById('reporte_persona_nombre');
                nombre.value = '';
                nombre.placeholder = dni ? 'Pulsa Buscar para confirmar el nombre' : 'Todas las personas';
                $('#btnBuscarPersonaGlobal').prop('disabled', false).html('<i class="bi bi-search me-1"></i> Buscar');
            }

            function limpiarBusquedaPersonaGlobal() {
                document.getElementById('reporte_persona_dni').value = '';
                reiniciarBusquedaPersonaGlobal();
                document.getElementById('reporte_persona_dni').focus();
            }

            function buscarPersonaGlobal() {
                const documento = (document.getElementById('reporte_persona_dni').value || '').trim();
                reiniciarBusquedaPersonaGlobal();

                if (!/^[0-9]{8}$/.test(documento)) {
                    Swal.fire('Aviso', 'Escribe un DNI de exactamente 8 dígitos.', 'warning');
                    return;
                }

                const versionActual = versionBusquedaPersonaGlobal;
                $('#btnBuscarPersonaGlobal').prop('disabled', true).text('Buscando…');

                solicitudPersonaGlobal = $.getJSON(@json(route('buscar.buscar')), { documento })
                    .done(function(data) {
                        if (versionActual !== versionBusquedaPersonaGlobal ||
                            document.getElementById('reporte_persona_dni').value.trim() !== documento) return;

                        if (!data || data.error) {
                            Swal.fire('Aviso', data?.error || 'No se encontró el DNI.', 'warning');
                            return;
                        }

                        const nombreCompleto = String(data.razon_social || [
                            data.nombres,
                            data.apellido_paterno,
                            data.apellido_materno
                        ].filter(Boolean).join(' ')).trim();

                        if (!nombreCompleto) {
                            Swal.fire('Aviso', 'La búsqueda no devolvió el nombre de la persona.', 'warning');
                            return;
                        }

                        document.getElementById('reporte_persona_nombre').value = nombreCompleto;
                        dniGlobalConfirmado = documento;
                    })
                    .fail(function(_xhr, estado) {
                        if (estado === 'abort' || versionActual !== versionBusquedaPersonaGlobal) return;
                        Swal.fire('Error', 'No se pudo buscar el DNI. Intenta nuevamente.', 'error');
                    })
                    .always(function() {
                        if (versionActual !== versionBusquedaPersonaGlobal) return;
                        solicitudPersonaGlobal = null;
                        $('#btnBuscarPersonaGlobal').prop('disabled', false)
                            .html('<i class="bi bi-search me-1"></i> Buscar');
                    });
            }

            // Devuelve el filtro válido para los tres historiales; null si existe un error.
            function validarFiltroHistorialPersonal() {
                const dni = (document.getElementById('reporte_persona_dni')?.value || '').trim();
                const filters = getGlobalFilters();

                if (dni && !/^[0-9]{8}$/.test(dni)) {
                    Swal.fire('Aviso', 'El DNI debe tener 8 dígitos o quedar vacío.', 'warning');
                    return null;
                }
                if (dni && dniGlobalConfirmado !== dni) {
                    Swal.fire('Aviso', 'Pulsa Buscar y confirma el nombre antes de exportar.', 'warning');
                    return null;
                }
                if (filters.period === 'custom' && (!filters.dateFrom || !filters.dateTo)) {
                    Swal.fire('Aviso', 'Selecciona la fecha inicial y la fecha final.', 'warning');
                    return null;
                }
                if (filters.dateFrom && filters.dateTo && filters.dateFrom > filters.dateTo) {
                    Swal.fire('Aviso', 'La fecha final debe ser igual o posterior a la inicial.', 'warning');
                    return null;
                }

                return { dni, filters };
            }

            function descargarHistorialPersonal(formato, rutas, extra = {}) {
                if (!Object.prototype.hasOwnProperty.call(rutas, formato)) return;
                const filtro = validarFiltroHistorialPersonal();
                if (!filtro) return;

                const url = new URL(rutas[formato], window.location.origin);
                url.searchParams.set('period', filtro.filters.period);
                if (filtro.filters.dateFrom) url.searchParams.set('date_from', filtro.filters.dateFrom);
                if (filtro.filters.dateTo) url.searchParams.set('date_to', filtro.filters.dateTo);
                if (filtro.dni) url.searchParams.set('dni', filtro.dni);
                Object.entries(extra).forEach(([clave, valor]) => url.searchParams.set(clave, valor));

                window.open(url.toString(), '_blank', 'noopener,noreferrer');
            }

            function exportarVentaPasajes(formato) {
                descargarHistorialPersonal(formato, {
                    pdf: @json(route('reportes.historial.pasajero.pdf')),
                    excel: @json(route('reportes.historial.pasajero.excel')),
                });
            }

            function exportarVentaEncomiendas(formato) {
                descargarHistorialPersonal(formato, {
                    pdf: @json(route('reportes.historial.encomiendas.pdf')),
                    excel: @json(route('reportes.historial.encomiendas.excel')),
                }, {
                    tipo_persona: document.getElementById('reporte_encomiendas_tipo_persona')?.value || 'ambos'
                });
            }

            function exportarVentaSobreequipaje(formato) {
                descargarHistorialPersonal(formato, {
                    pdf: @json(route('reportes.historial.sobreequipaje.pdf')),
                    excel: @json(route('reportes.historial.sobreequipaje.excel')),
                });
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
                        excel: "{{ route('reportes.historial.pasajero.excel') }}"
                    },

                    'venta-encomiendas': {
                        pdf: "{{ route('reportes.historial.encomiendas.pdf') }}",
                        excel: "{{ route('reportes.historial.encomiendas.excel') }}"
                    },

                    'venta-sobreequipaje': {
                        pdf: "{{ route('reportes.historial.sobreequipaje.pdf') }}",
                        excel: "{{ route('reportes.historial.sobreequipaje.excel') }}"
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
                        pdf: "{{ route('reportes.caja.vendedor.pdf') }}",
                        excel: "{{ route('reportes.caja.vendedor.pdf') }}"
                    },

                    'cuadre-caja-sucursal': {
                        pdf: "{{ route('reportes.caja.sucursal.pdf') }}",
                        excel: "{{ route('reportes.caja.sucursal.pdf') }}"
                    },

                    'recaudacion-medio-pago': {
                        pdf: "{{ route('reportes.caja.medio_pago.pdf') }}",
                        excel: "{{ route('reportes.caja.medio_pago.pdf') }}"
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

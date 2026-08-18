@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-4 px-3 px-lg-4 min-vh-100">
        {{-- ENCABEZADO Y FILTROS --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">


                <form method="GET" action="{{ route('dashboard.admin') }}" id="filterForm" class="row g-3 align-items-end">
                    {{-- PERÍODO --}}
                    <div class="col-12 col-xl-5">
                        <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                            <i class="bi bi-calendar3 me-1"></i> Período
                        </label>
                        <div class="btn-group w-100 shadow-sm rounded-3 overflow-hidden" role="group" id="periodGroup">
                            <input type="radio" class="btn-check auto-filter-radio" name="period" id="period_today"
                                value="today" @checked(request('period') === 'today') onchange="toggleDateInputs('today')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="period_today">Hoy</label>

                            <input type="radio" class="btn-check auto-filter-radio" name="period" id="period_week"
                                value="week" @checked(request('period') === 'week') onchange="toggleDateInputs('week')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="period_week">Esta Semana</label>

                            <input type="radio" class="btn-check auto-filter-radio" name="period" id="period_month"
                                value="month" @checked(!request('period') || request('period') === 'month') onchange="toggleDateInputs('month')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="period_month">Este Mes</label>

                            <input type="radio" class="btn-check auto-filter-radio" name="period" id="period_year"
                                value="year" @checked(request('period') === 'year') onchange="toggleDateInputs('year')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="period_year">Año</label>

                            <input type="radio" class="btn-check auto-filter-radio" name="period" id="period_custom"
                                value="custom" @checked(request('period') === 'custom') onchange="toggleDateInputs('custom')">
                            <label class="btn btn-outline-primary btn-sm py-2" for="period_custom">Personalizado</label>
                        </div>
                    </div>

                    {{-- FECHAS --}}
                    <div class="col-12 col-xl-3" id="customDateInputs">
                        <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                            <i class="bi bi-calendar-range me-1"></i> Rango de Fechas
                        </label>
                        <div class="input-group input-group-sm shadow-sm rounded-3">
                            <input type="date" class="form-control auto-filter-input" name="desde" id="date_from"
                                value="{{ request('desde') }}">
                            <span class="input-group-text bg-light text-muted border-start-0 border-end-0">a</span>
                            <input type="date" class="form-control auto-filter-input" name="hasta" id="date_to"
                                value="{{ request('hasta') }}">
                        </div>
                    </div>

                    {{-- SUCURSAL --}}
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                            <i class="bi bi-building me-1"></i> Sucursal
                        </label>
                        <select name="sucursal_id" class="form-select form-select-sm shadow-sm rounded-3 auto-filter-input">
                            <option value="">Todas las sucursales</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" @selected(request('sucursal_id') == $sucursal->id)>
                                    {{ $sucursal->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- RUTA --}}
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                            <i class="bi bi-signpost-split me-1"></i> Ruta
                        </label>
                        <select name="ruta_id" class="form-select form-select-sm shadow-sm rounded-3 auto-filter-input">
                            <option value="">Todas las rutas</option>
                            @foreach ($rutas as $ruta)
                                <option value="{{ $ruta->id }}" @selected(request('ruta_id') == $ruta->id)>
                                    {{ $ruta->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- CARDS DE KPIS --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-primary border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Ventas hoy</span>
                            <div class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                <i class="bi bi-currency-dollar fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">S/ {{ number_format($kpis['ventas_hoy'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-success border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Ventas mes</span>
                            <div class="badge bg-success-subtle text-success rounded-circle p-2">
                                <i class="bi bi-graph-up-arrow fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">S/ {{ number_format($kpis['ventas_mes'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-info border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Tickets hoy</span>
                            <div class="badge bg-info-subtle text-info rounded-circle p-2">
                                <i class="bi bi-ticket-perforated fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['tickets_hoy'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-warning border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Tickets mes</span>
                            <div class="badge bg-warning-subtle text-warning rounded-circle p-2">
                                <i class="bi bi-receipt fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['tickets_mes'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-secondary border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Ocupación</span>
                            <div class="badge bg-secondary-subtle text-secondary rounded-circle p-2">
                                <i class="bi bi-pie-chart fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $kpis['ocupacion_promedio'] ?? 0 }}%</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-danger border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fs-7 fw-semibold text-uppercase">Anulaciones</span>
                            <div class="badge bg-danger-subtle text-danger rounded-circle p-2">
                                <i class="bi bi-x-circle fs-6"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['anulaciones'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRÁFICOS PRINCIPALES --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-bar-chart-line text-primary me-2"></i>Ventas por día
                        </h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="ventasPorDiaChart" height="110"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-donut-chart text-primary me-2"></i>Estado de salidas
                        </h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="estadoSalidasChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLAS DE RENDIMIENTO Y VENTAS POR SUCURSAL --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shop text-primary me-2"></i>Ventas por sucursal
                        </h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="ventasPorSucursalChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-trophy text-primary me-2"></i>Top rutas
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-4 text-uppercase fs-7 text-secondary">Ruta</th>
                                        <th class="text-center text-uppercase fs-7 text-secondary">Tickets</th>
                                        <th class="text-end text-uppercase fs-7 text-secondary">Ingresos</th>
                                        <th class="text-center pe-4 text-uppercase fs-7 text-secondary">Ocupación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topRutas as $ruta)
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">{{ $ruta['nombre'] }}</td>
                                            <td class="text-center"><span
                                                    class="badge bg-light text-dark border">{{ $ruta['tickets'] }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-success">S/
                                                {{ number_format($ruta['ingresos'], 2) }}</td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <div class="progress w-50" style="height: 6px;">
                                                        <div class="progress-bar bg-primary"
                                                            style="width: {{ $ruta['ocupacion'] }}%"></div>
                                                    </div>
                                                    <span class="small fw-semibold">{{ $ruta['ocupacion'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Sin datos disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- VENDEDORES Y RESUMEN SUCURSALES --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-badge text-primary me-2"></i>Ranking de vendedores
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-4 text-uppercase fs-7 text-secondary">Vendedor</th>
                                        <th class="text-uppercase fs-7 text-secondary">Sucursal</th>
                                        <th class="text-center text-uppercase fs-7 text-secondary">Tickets</th>
                                        <th class="text-end pe-4 text-uppercase fs-7 text-secondary">Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingVendedores as $item)
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">{{ $item['nombre'] }}</td>
                                            <td><span
                                                    class="badge bg-secondary-subtle text-secondary">{{ $item['sucursal'] }}</span>
                                            </td>
                                            <td class="text-center"><span
                                                    class="badge bg-light text-dark border">{{ $item['tickets'] }}</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-dark">S/
                                                {{ number_format($item['ventas'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Sin datos disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-buildings text-primary me-2"></i>Resumen por sucursal
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-4 text-uppercase fs-7 text-secondary">Sucursal</th>
                                        <th class="text-center text-uppercase fs-7 text-secondary">Salidas</th>
                                        <th class="text-center text-uppercase fs-7 text-secondary">Tickets</th>
                                        <th class="text-end pe-4 text-uppercase fs-7 text-secondary">Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumenSucursales as $item)
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">{{ $item['nombre_comercial'] }}</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info-subtle text-info">{{ $item['salidas'] }}</span>
                                            </td>
                                            <td class="text-center"><span
                                                    class="badge bg-light text-dark border">{{ $item['tickets'] }}</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-dark">S/
                                                {{ number_format($item['ventas'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Sin datos disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLE ANALÍTICO DE SALIDAS --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i>Detalle analítico de salidas
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 text-uppercase fs-7 text-secondary">Fecha</th>
                                <th class="text-uppercase fs-7 text-secondary">Hora</th>
                                <th class="text-uppercase fs-7 text-secondary">Sucursal</th>
                                <th class="text-uppercase fs-7 text-secondary">Ruta</th>
                                <th class="text-uppercase fs-7 text-secondary">Vehículo</th>
                                <th class="text-center text-uppercase fs-7 text-secondary">Capacidad</th>
                                <th class="text-center text-uppercase fs-7 text-secondary">Embarcados</th>
                                <th class="text-center text-uppercase fs-7 text-secondary">Ocupación</th>
                                <th class="text-end text-uppercase fs-7 text-secondary">Ingresos</th>
                                <th class="pe-4 text-uppercase fs-7 text-secondary">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalleSalidas as $salida)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $salida['fecha'] }}</td>
                                    <td class="text-muted">{{ $salida['hora'] }}</td>
                                    <td>{{ $salida['sucursal'] }}</td>
                                    <td class="fw-semibold text-dark">{{ $salida['ruta'] }}</td>
                                    <td><span
                                            class="badge bg-light text-secondary border">{{ $salida['vehiculo'] }}</span>
                                    </td>
                                    <td class="text-center">{{ $salida['capacidad'] }}</td>
                                    <td class="text-center fw-semibold">{{ $salida['embarcados'] }}</td>
                                    @php
                                        $ocupacion = $salida['ocupacion'];

                                        $ocupacionColor = match (true) {
                                            $ocupacion >= 80 => 'success',
                                            $ocupacion >= 50 => 'primary',
                                            $ocupacion >= 25 => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $ocupacionColor }}-subtle text-{{ $ocupacionColor }} rounded-pill">
                                            {{ number_format($ocupacion, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">S/ {{ number_format($salida['ingresos'], 2) }}
                                    </td>
                                    @php
                                        $estado = strtolower($salida['estado']);

                                        $estadoColor = match ($estado) {
                                            'programado' => 'primary',
                                            'en_ruta' => 'warning',
                                            'finalizado' => 'success',
                                            'cancelado' => 'danger',
                                            default => 'secondary',
                                        };

                                        $estadoTexto = match ($estado) {
                                            'programado' => 'Programado',
                                            'en_ruta' => 'En ruta',
                                            'finalizado' => 'Finalizado',
                                            'cancelado' => 'Cancelado',
                                            default => ucfirst($estado),
                                        };
                                    @endphp

                                    <td class="pe-4">
                                        <span
                                            class="badge bg-{{ $estadoColor }}-subtle text-{{ $estadoColor }} rounded-pill">
                                            {{ $estadoTexto }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                        No hay registros disponibles.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ventasPorDiaChart = @json($ventasPorDiaChart ?? ['labels' => [], 'data' => []]);
        const estadoSalidasChart = @json($estadoSalidasChart ?? ['labels' => [], 'data' => []]);
        const ventasPorSucursalChart = @json($ventasPorSucursalChart ?? ['labels' => [], 'data' => []]);

        const ctxVentasPorDia = document.getElementById('ventasPorDiaChart');
        if (ctxVentasPorDia) {
            new Chart(ctxVentasPorDia, {
                type: 'line',
                data: {
                    labels: ventasPorDiaChart.labels,
                    datasets: [{
                        label: 'Ventas por día',
                        data: ventasPorDiaChart.data,
                        tension: 0.4,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.05)',
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#0d6efd',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        const ctxSalidas = document.getElementById('estadoSalidasChart').getContext('2d');
        new Chart(ctxSalidas, {
            type: 'bar',
            data: {
                labels: @json($estadoSalidasChart['labels']),
                datasets: [{
                    label: 'Total Salidas',
                    data: @json($estadoSalidasChart['data']),
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        const ctxVentasPorSucursal = document.getElementById('ventasPorSucursalChart');
        if (ctxVentasPorSucursal) {
            new Chart(ctxVentasPorSucursal, {
                type: 'bar',
                data: {
                    labels: ventasPorSucursalChart.labels,
                    datasets: [{
                        label: 'Ventas por sucursal',
                        data: ventasPorSucursalChart.data,
                        backgroundColor: '#0d6efd',
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // --- AUTOMATIZACIÓN DE FILTROS ---
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('filterForm');
            const checkedRadio = document.querySelector('input[name="period"]:checked');
            const initialPeriod = checkedRadio ? checkedRadio.value : 'month';

            // Estado inicial de los inputs de fecha (deshabilitados salvo si es personalizado)
            toggleDateInputs(initialPeriod, false);

            // Escuchar cambios en los select y radios (se envía inmediatamente)
            const autoFilterElements = document.querySelectorAll('.auto-filter-input, .auto-filter-radio');
            autoFilterElements.forEach(element => {
                element.addEventListener('change', function() {
                    // Si el cambio proviene de las fechas personalizadas, se puede aplicar un leve tiempo de espera
                    if (this.type === 'date') {
                        debounceSubmit();
                    } else {
                        form.submit();
                    }
                });
            });
        });

        // Habilita/Deshabilita inputs de fecha y actualiza su contenido
        function toggleDateInputs(period, shouldSubmit = true) {
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');

            if (period === 'custom') {
                dateFrom.readOnly = false;
                dateTo.readOnly = false;
            } else {
                dateFrom.readOnly = true;
                dateTo.readOnly = true;
                setPredefinedDates(period);
            }

            if (shouldSubmit) {
                document.getElementById('filterForm').submit();
            }
        }

        function setPredefinedDates(period) {
            const today = new Date();
            let fromDate = new Date();
            let toDate = new Date();

            if (period === 'today') {
                fromDate = new Date();
                toDate = new Date();
            } else if (period === 'week') {
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                fromDate = new Date(today.getFullYear(), today.getMonth(), diff);
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
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Función debounce para evitar recargar dos veces seguidas si el usuario cambia las dos fechas muy rápido
        let timeout = null;

        function debounceSubmit() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 600);
        }
    </script>
@endpush

@push('styles')
    <style>
        .fs-7 {
            font-size: 0.75rem;
        }

        .card-kpi {
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card-kpi:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .table> :not(caption)>*>* {
            padding: 0.85rem 0.75rem;
        }
    </style>
@endpush

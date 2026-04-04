@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Administrativo</h2>
                <p class="text-muted mb-0">Análisis de ventas, ocupación, salidas y rendimiento general.</p>
            </div>

            <form method="GET" action="{{ route('dashboard.admin') }}" class="row g-2">
                <div class="col-auto">
                    <input type="date" name="desde"
                        value="{{ request('desde', now()->startOfMonth()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-auto">
                    <input type="date" name="hasta" value="{{ request('hasta', now()->format('Y-m-d')) }}"
                        class="form-control">
                </div>
                <div class="col-auto">
                    <select name="sucursal_id" class="form-select">
                        <option value="">Todas las sucursales</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" @selected(request('sucursal_id') == $sucursal->id)>
                                {{ $sucursal->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="ruta_id" class="form-select">
                        <option value="">Todas las rutas</option>
                        @foreach ($rutas as $ruta)
                            <option value="{{ $ruta->id }}" @selected(request('ruta_id') == $ruta->id)>
                                {{ $ruta->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary px-4">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Ventas hoy</span>
                        <h4 class="fw-bold mb-0">S/ {{ number_format($kpis['ventas_hoy'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Ventas mes</span>
                        <h4 class="fw-bold mb-0">S/ {{ number_format($kpis['ventas_mes'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Tickets hoy</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['tickets_hoy'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Tickets mes</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['tickets_mes'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Ocupación promedio</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['ocupacion_promedio'] ?? 0 }}%</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Anulaciones</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['anulaciones'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Ventas por día</h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="ventasPorDiaChart" height="110"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Estado de salidas</h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="estadoSalidasChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Ventas por sucursal</h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="ventasPorSucursalChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Top rutas</h5>
                    </div>
                    <div class="card-body pt-2 px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Ruta</th>
                                        <th class="text-center">Tickets</th>
                                        <th class="text-end">Ingresos</th>
                                        <th class="text-center pe-4">Ocupación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topRutas as $ruta)
                                        <tr>
                                            <td class="ps-4">{{ $ruta['nombre'] }}</td>
                                            <td class="text-center">{{ $ruta['tickets'] }}</td>
                                            <td class="text-end">S/ {{ number_format($ruta['ingresos'], 2) }}</td>
                                            <td class="text-center pe-4">{{ $ruta['ocupacion'] }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sin datos disponibles
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

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Ranking de vendedores</h5>
                    </div>
                    <div class="card-body pt-2 px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Vendedor</th>
                                        <th>Sucursal</th>
                                        <th class="text-center">Tickets</th>
                                        <th class="text-end pe-4">Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingVendedores as $item)
                                        <tr>
                                            <td class="ps-4">{{ $item['nombre'] }}</td>
                                            <td>{{ $item['sucursal'] }}</td>
                                            <td class="text-center">{{ $item['tickets'] }}</td>
                                            <td class="text-end pe-4">S/ {{ number_format($item['ventas'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sin datos disponibles
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
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Resumen por sucursal</h5>
                    </div>
                    <div class="card-body pt-2 px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Sucursal</th>
                                        <th class="text-center">Salidas</th>
                                        <th class="text-center">Tickets</th>
                                        <th class="text-end pe-4">Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumenSucursales as $item)
                                        <tr>
                                            <td class="ps-4">{{ $item['nombre_comercial'] }}</td>
                                            <td class="text-center">{{ $item['salidas'] }}</td>
                                            <td class="text-center">{{ $item['tickets'] }}</td>
                                            <td class="text-end pe-4">S/ {{ number_format($item['ventas'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sin datos disponibles
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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">Detalle analítico de salidas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Hora</th>
                                <th>Sucursal</th>
                                <th>Ruta</th>
                                <th>Vehículo</th>
                                <th class="text-center">Capacidad</th>
                                <th class="text-center">Embarcados</th>
                                <th class="text-center">Ocupación</th>
                                <th class="text-end">Ingresos</th>
                                <th class="pe-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalleSalidas as $salida)
                                <tr>
                                    <td class="ps-4">{{ $salida['fecha'] }}</td>
                                    <td>{{ $salida['hora'] }}</td>
                                    <td>{{ $salida['sucursal'] }}</td>
                                    <td>{{ $salida['ruta'] }}</td>
                                    <td>{{ $salida['vehiculo'] }}</td>
                                    <td class="text-center">{{ $salida['capacidad'] }}</td>
                                    <td class="text-center">{{ $salida['embarcados'] }}</td>
                                    <td class="text-center">{{ $salida['ocupacion'] }}%</td>
                                    <td class="text-end">S/ {{ number_format($salida['ingresos'], 2) }}</td>
                                    <td class="pe-4">{{ $salida['estado'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">No hay registros disponibles.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">Alertas administrativas</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <ul class="list-group list-group-flush">
                    @forelse($alertas as $alerta)
                        <li class="list-group-item px-0">{{ $alerta }}</li>
                    @empty
                        <li class="list-group-item px-0 text-muted">No hay alertas por mostrar.</li>
                    @endforelse
                </ul>
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
                        tension: 0.35,
                        fill: false,
                        borderWidth: 2,
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        const ctxEstadoSalidas = document.getElementById('estadoSalidasChart');
        if (ctxEstadoSalidas) {
            new Chart(ctxEstadoSalidas, {
                type: 'doughnut',
                data: {
                    labels: estadoSalidasChart.labels,
                    datasets: [{
                        data: estadoSalidasChart.data,
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        const ctxVentasPorSucursal = document.getElementById('ventasPorSucursalChart');
        if (ctxVentasPorSucursal) {
            new Chart(ctxVentasPorSucursal, {
                type: 'bar',
                data: {
                    labels: ventasPorSucursalChart.labels,
                    datasets: [{
                        label: 'Ventas por sucursal',
                        data: ventasPorSucursalChart.data,
                        borderWidth: 1,
                        borderRadius: 10,
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .card {
            transition: .2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .table> :not(caption)>*>* {
            padding-top: 0.95rem;
            padding-bottom: 0.95rem;
            vertical-align: middle;
        }

        .table-light th {
            font-size: .84rem;
            font-weight: 700;
            color: #4b5563;
            white-space: nowrap;
        }

        .card-header h5 {
            color: #111827;
        }

        .badge {
            font-weight: 600;
        }
    </style>
@endpush
